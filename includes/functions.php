<?php
/**
 * PEKES Pet Shop — Funciones Globales y Utilidades
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Retorna conexión a BD
 * @return PDO
 */
function get_db() {
    return get_db_connection();
}

/**
 * Formatea un valor numérico a pesos colombianos (COP)
 * @param int|float|null $val
 * @return string
 */
function format_cop($val) {
    if ($val === null || $val === '' || !is_numeric($val)) {
        return '$0 COP';
    }
    return '$' . number_format((float)$val, 0, ',', '.') . ' COP';
}

/**
 * Sanitiza una cadena de texto para evitar XSS
 * @param string|null $str
 * @return string
 */
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * Genera o recupera el token CSRF de la sesión
 * @return string
 */
function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Retorna el campo HTML oculto con el token CSRF
 * @return string
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Valida un token CSRF recibido contra el de la sesión
 * @param string|null $token
 * @return bool
 */
function verify_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * Obtiene un valor de configuración de la BD
 * @param string $key
 * @param string $default
 * @return string
 */
function get_setting($key, $default = '') {
    try {
        $db = get_db();
        // Soporta tanto la columna setting_key (MySQL nuevo) como key (SQLite original)
        $stmt = $db->prepare('SELECT setting_value FROM settings WHERE setting_key = ? UNION SELECT value FROM settings WHERE key = ? LIMIT 1');
        $stmt->execute([$key, $key]);
        $row = $stmt->fetch();
        if ($row) {
            $val = reset($row);
            return $val !== null ? $val : $default;
        }
    } catch (Exception $ex) {
        // Fallback en caso de tabla no inicializada
    }
    return $default;
}

/**
 * Guarda o actualiza un valor de configuración
 * @param string $key
 * @param string $value
 * @return bool
 */
function set_setting($key, $value) {
    try {
        $db = get_db();
        $stmt = $db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?');
        return $stmt->execute([$key, $value, $value]);
    } catch (Exception $e) {
        try {
            // Fallback para SQLite
            $stmt = $db->prepare('INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)');
            return $stmt->execute([$key, $value]);
        } catch (Exception $e2) {
            return false;
        }
    }
}

/**
 * Calcula el precio final redondeado a miles según markup mayorista
 * @param int|float $catalog_price
 * @param float $factor
 * @return int
 */
function calculate_price_cop($catalog_price, $factor = 1.30) {
    if (!$catalog_price || !is_numeric($catalog_price)) {
        return 0;
    }
    $raw = (float)$catalog_price * (float)$factor;
    return (int)(round($raw / 1000.0) * 1000);
}

/**
 * Convierte un texto a slug limpio y seguro
 * @param string $text
 * @return string
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'item-' . time() : $text;
}

/**
 * Genera el siguiente SKU secuencial para una categoría
 * @param string $category
 * @return string
 */
function generate_sku($category) {
    $db = get_db();
    $cat_lower = mb_strtolower($category);
    if (strpos($cat_lower, 'piso') !== false) {
        $prefix = 'PIS';
    } elseif (strpos($cat_lower, 'pared') !== false) {
        $prefix = 'PAR';
    } else {
        $prefix = 'RAS';
    }

    $stmt = $db->prepare('SELECT COUNT(*) FROM products WHERE category = ?');
    $stmt->execute([$category]);
    $count = (int)$stmt->fetchColumn() + 1;

    return sprintf('PEK-%s-%03d', $prefix, $count);
}

/**
 * Valida y procesa la subida segura de una imagen de producto
 * @param array $file_array Entrada de $_FILES['file']
 * @param string $product_name Nombre del producto para prefijo del archivo
 * @return array ['success' => bool, 'url' => string, 'filename' => string, 'error' => string]
 */
function upload_product_image($file_array, $product_name = 'producto') {
    if (!isset($file_array) || $file_array['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'No se subió ningún archivo o hubo un error en la transferencia.'];
    }

    // Validar tamaño máximo
    if ($file_array['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'El archivo supera el tamaño máximo permitido (6 MB).'];
    }

    // Validar extensión
    $ext = strtolower(pathinfo($file_array['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'error' => 'Formato no permitido. Solo se aceptan: JPG, PNG, WEBP.'];
    }

    // Validar tipo MIME real con finfo
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file_array['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, ALLOWED_MIMES)) {
            return ['success' => false, 'error' => 'El archivo no es una imagen válida (' . $mime . ').'];
        }
    }

    // Crear directorio de subida si no existe
    $upload_dir = UPLOAD_DIR;
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Generar nombre de archivo único y seguro
    $prefix = slugify($product_name);
    $filename = sprintf('p_%s_%d_%s.%s', $prefix, time(), bin2hex(random_bytes(3)), $ext);
    $target_path = $upload_dir . '/' . $filename;

    if (!move_uploaded_file($file_array['tmp_name'], $target_path)) {
        return ['success' => false, 'error' => 'Error al mover el archivo al directorio de destino.'];
    }

    return [
        'success'  => true,
        'url'      => UPLOAD_WEB_PATH . $filename,
        'filename' => $filename
    ];
}

/**
 * Envía una respuesta HTTP en formato JSON
 * @param mixed $data
 * @param int $status_code
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

