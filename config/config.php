<?php
/**
 * PEKES Pet Shop — Configuración General de la Aplicación
 */

// Información de Marca
define('SITE_NAME',      'PEKES');
define('SITE_FULL_NAME', 'PEKES Pet Shop');
define('SITE_TAGLINE',   'Para la vida que comparten');
define('SITE_CITY',      'Cali, Colombia');
define('SITE_EMAIL',     'pekespetshop2026@gmail.com');
define('DEFAULT_WHATSAPP', '573215427745');
define('SITE_OFFICIAL_URL', 'https://www.pekes.shop');

// Moneda
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_SUFFIX', ' COP');

// Detección dinámica de la URL base
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
// Si está en /admin o /api, subir un nivel para la URL base de assets
if (basename($base_path) === 'admin' || basename($base_path) === 'api') {
    $base_path = dirname($base_path);
}
$base_path = rtrim($base_path, '/\\');

define('BASE_URL', $protocol . $host . ($base_path ? $base_path : ''));

// Directorios físicos de subida de imágenes
$target_upload = dirname(__DIR__) . '/public/images/products';
if (!is_dir($target_upload)) {
    $target_upload = dirname(__DIR__) . '/images/products';
}
define('UPLOAD_DIR', $target_upload);
define('UPLOAD_WEB_PATH', '/images/products/');
define('MAX_FILE_SIZE', 6 * 1024 * 1024); // 6 MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
define('ALLOWED_MIMES', ['image/jpeg', 'image/png', 'image/webp']);

