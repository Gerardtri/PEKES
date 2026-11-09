<?php
/**
 * PEKES Pet Shop — API REST de Productos
 * Endpoints:
 * GET    /api/products.php          -> Lista de productos (filtros: ?category=, ?search=, ?stage=)
 * GET    /api/products.php?id=xxx   -> Ficha individual de producto
 * POST   /api/products.php          -> Crear producto (requiere sesión admin)
 * PUT    /api/products.php?id=xxx   -> Actualizar producto (requiere sesión admin)
 * DELETE /api/products.php?id=xxx   -> Eliminar producto (requiere sesión admin)
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$db = get_db();
$method = $_SERVER['REQUEST_METHOD'];

// Helper para normalizar el producto
function format_product_row($row) {
    if (!$row) return null;
    $d = $row;
    
    // Decodificar JSON includes
    if (!empty($d['includes'])) {
        $decoded = json_decode($d['includes'], true);
        $d['includes'] = is_array($decoded) ? $decoded : [$d['includes']];
    } else {
        $d['includes'] = [];
    }

    // Decodificar JSON tags
    if (!empty($d['tags'])) {
        $decoded = json_decode($d['tags'], true);
        $d['tags'] = is_array($decoded) ? $decoded : [];
    } else {
        $d['tags'] = [];
    }

    $d['price_formatted'] = format_cop($d['price']);
    $d['is_render']  = (bool)($d['is_render'] ?? 0);
    $d['is_pending'] = (bool)($d['is_pending'] ?? 0);
    $d['is_active']  = isset($d['is_active']) ? (bool)$d['is_active'] : true;
    
    return $d;
}

// -------------------------------------------------------------
// GET: Consultar productos
// -------------------------------------------------------------
if ($method === 'GET') {
    $id   = isset($_GET['id']) ? trim($_GET['id']) : '';
    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

    // Consulta por ID
    if (!empty($id)) {
        $stmt = $db->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            json_response(['error' => 'Producto no encontrado'], 404);
        }
        json_response(format_product_row($row));
    }

    // Consulta por Slug (URL amigable / SEO)
    if (!empty($slug)) {
        $stmt = $db->prepare('SELECT * FROM products WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        if (!$row) {
            json_response(['error' => 'Producto no encontrado'], 404);
        }
        json_response(format_product_row($row));
    }

    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $search   = isset($_GET['search']) ? trim($_GET['search']) : '';
    $stage    = isset($_GET['stage']) ? trim($_GET['stage']) : '';
    $all      = isset($_GET['all']) && $_GET['all'] === '1' && is_logged_in();

    $query = 'SELECT * FROM products WHERE 1=1';
    $params = [];

    // Por defecto en la tienda pública solo mostramos activos
    if (!$all) {
        $query .= ' AND (is_active = 1 OR is_active IS NULL)';
    }

    if (!empty($category) && $category !== 'all') {
        $query .= ' AND category = ?';
        $params[] = $category;
    }

    if (!empty($stage) && is_numeric($stage)) {
        $query .= ' AND stage = ?';
        $params[] = (int)$stage;
    }

    if (!empty($search)) {
        $term = '%' . mb_strtolower($search) . '%';
        $query .= ' AND (LOWER(name) LIKE ? OR LOWER(sku) LIKE ? OR LOWER(dimensions) LIKE ? OR LOWER(description) LIKE ?)';
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $query .= ' ORDER BY stage ASC, page ASC, id ASC';

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $products = array_map('format_product_row', $rows);
    json_response($products);
}

// -------------------------------------------------------------
// OPERACIONES DE ESCRITURA (Requieren sesión de administrador)
// -------------------------------------------------------------
if (!is_logged_in()) {
    json_response(['error' => 'No autorizado. Se requiere sesión de administrador.'], 401);
}

// POST: Crear producto
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $name = trim($input['name'] ?? '');
    if (empty($name)) {
        json_response(['error' => 'El nombre del producto es obligatorio.'], 400);
    }

    $category = trim($input['category'] ?? 'Gimnasios de piso');
    $sku      = trim($input['sku'] ?? '');
    if (empty($sku)) {
        $sku = generate_sku($category);
    }

    $slug = trim($input['slug'] ?? '');
    if (empty($slug)) {
        if (stripos($category, 'piso') !== false) {
            $slug = 'gimnasio-' . slugify($name);
        } elseif (stripos($category, 'pared') !== false) {
            $num = preg_replace('/[^0-9]/', '', $name);
            $slug = 'gimnasio-pared-combo-' . ($num ?: slugify($name));
        } elseif (stripos($category, 'rascador') !== false) {
            $slug = 'rascador-' . slugify($name);
        } else {
            $slug = slugify($name);
        }
    }

    $id = slugify($name);
    // Verificar colisión de ID y Slug
    $check = $db->prepare('SELECT id FROM products WHERE id = ? OR slug = ?');
    $check->execute([$id, $slug]);
    if ($check->fetch()) {
        $id .= '-' . time();
        $slug .= '-' . time();
    }

    $catalog_price = isset($input['catalog_price']) && is_numeric($input['catalog_price']) ? (int)$input['catalog_price'] : null;
    $price = isset($input['price']) && is_numeric($input['price']) ? (int)$input['price'] : calculate_price_cop($catalog_price);

    $dimensions  = trim($input['dimensions'] ?? '');
    $image       = trim($input['image'] ?? '/images/products/placeholder.jpeg');
    $description = trim($input['description'] ?? '');
    $includes    = json_encode($input['includes'] ?? ['Estructura principal', 'Accesorios de descanso'], JSON_UNESCAPED_UNICODE);
    $tags        = json_encode($input['tags'] ?? ['gatos'], JSON_UNESCAPED_UNICODE);
    $stage       = (int)($input['stage'] ?? 1);
    $is_render   = !empty($input['is_render']) ? 1 : 0;
    $is_pending  = !empty($input['is_pending']) ? 1 : 0;
    $is_active   = isset($input['is_active']) ? (int)(bool)$input['is_active'] : 1;
    $page        = (int)($input['page'] ?? 99);

    $stmt = $db->prepare('
        INSERT INTO products (id, sku, slug, name, category, stage, catalog_price, price, dimensions, image, description, includes, is_render, is_pending, is_active, tags, page)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $id, $sku, $slug, $name, $category, $stage, $catalog_price, $price, $dimensions, $image, $description, $includes, $is_render, $is_pending, $is_active, $tags, $page
    ]);

    json_response(['success' => true, 'id' => $id, 'sku' => $sku, 'slug' => $slug], 201);
}

// PUT: Actualizar producto
if ($method === 'PUT') {
    $id = isset($_GET['id']) ? trim($_GET['id']) : '';
    if (empty($id)) {
        json_response(['error' => 'ID de producto no especificado'], 400);
    }

    $stmt = $db->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $current = $stmt->fetch();
    if (!$current) {
        json_response(['error' => 'Producto no encontrado'], 404);
    }

    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $name = isset($input['name']) ? trim($input['name']) : $current['name'];
    $category = isset($input['category']) ? trim($input['category']) : $current['category'];
    $sku = isset($input['sku']) ? trim($input['sku']) : $current['sku'];
    $slug = isset($input['slug']) && !empty(trim($input['slug'])) ? slugify($input['slug']) : ($current['slug'] ?? slugify($name));
    $catalog_price = isset($input['catalog_price']) ? (int)$input['catalog_price'] : $current['catalog_price'];

    if (isset($input['price']) && is_numeric($input['price'])) {
        $price = (int)$input['price'];
    } elseif ($catalog_price) {
        $price = calculate_price_cop($catalog_price);
    } else {
        $price = (int)$current['price'];
    }

    $dimensions  = isset($input['dimensions']) ? trim($input['dimensions']) : $current['dimensions'];
    $image       = isset($input['image']) ? trim($input['image']) : $current['image'];
    $description = isset($input['description']) ? trim($input['description']) : $current['description'];
    
    $includes = isset($input['includes']) ? json_encode($input['includes'], JSON_UNESCAPED_UNICODE) : $current['includes'];
    $tags     = isset($input['tags']) ? json_encode($input['tags'], JSON_UNESCAPED_UNICODE) : $current['tags'];
    
    $stage      = isset($input['stage']) ? (int)$input['stage'] : (int)$current['stage'];
    $is_render  = isset($input['is_render']) ? (!empty($input['is_render']) ? 1 : 0) : $current['is_render'];
    $is_pending = isset($input['is_pending']) ? (!empty($input['is_pending']) ? 1 : 0) : $current['is_pending'];
    $is_active  = isset($input['is_active']) ? (!empty($input['is_active']) ? 1 : 0) : ($current['is_active'] ?? 1);

    $up = $db->prepare('
        UPDATE products
        SET sku = ?, slug = ?, name = ?, category = ?, stage = ?, catalog_price = ?, price = ?,
            dimensions = ?, image = ?, description = ?, includes = ?, is_render = ?, is_pending = ?,
            is_active = ?, tags = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ');

    $up->execute([
        $sku, $slug, $name, $category, $stage, $catalog_price, $price, $dimensions, $image, $description, $includes, $is_render, $is_pending, $is_active, $tags, $id
    ]);

    json_response(['success' => true, 'id' => $id]);
}

// DELETE: Eliminar producto
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? trim($_GET['id']) : '';
    if (empty($id)) {
        json_response(['error' => 'ID de producto no especificado'], 400);
    }

    $stmt = $db->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);

    json_response(['success' => true, 'message' => 'Producto eliminado con éxito']);
}

json_response(['error' => 'Método no soportado'], 405);
