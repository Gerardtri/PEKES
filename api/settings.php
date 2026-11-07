<?php
/**
 * PEKES Pet Shop — API de Configuraciones
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_logged_in()) {
        json_response(['error' => 'No autorizado'], 401);
    }

    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    foreach ($input as $k => $v) {
        set_setting($k, (string)$v);
    }
    json_response(['success' => true]);
}

// GET
$settings = [
    'whatsapp_number' => get_setting('whatsapp_number', DEFAULT_WHATSAPP),
    'store_name'      => get_setting('store_name', SITE_FULL_NAME),
    'city'            => get_setting('city', SITE_CITY),
    'markup_factor'   => get_setting('markup_factor', '1.30'),
    'email'           => get_setting('email', SITE_EMAIL)
];

json_response($settings);

