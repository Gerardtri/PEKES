<?php
/**
 * PEKES Pet Shop — Cerrar Sesión
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

logout_admin();

header('Location: /admin/login.php?logged_out=1');
exit;

