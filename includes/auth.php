<?php
/**
 * PEKES Pet Shop — Sistema de Autenticación y Seguridad de Sesiones
 */

require_once __DIR__ . '/functions.php';

/**
 * Inicia la sesión de PHP con parámetros de alta seguridad
 */
function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            $cookieParams = [
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax'
            ];
            session_set_cookie_params($cookieParams);
            session_start();
        } else {
            session_start();
        }
    }
}

/**
 * Verifica si el usuario actual ha iniciado sesión como administrador
 * @return bool
 */
function is_logged_in() {
    start_secure_session();
    return !empty($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true && !empty($_SESSION['admin_id']);
}

/**
 * Retorna la información del usuario administrador actual
 * @return array|null
 */
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'        => $_SESSION['admin_id'] ?? null,
        'username'  => $_SESSION['admin_username'] ?? '',
        'email'     => $_SESSION['admin_email'] ?? '',
        'full_name' => $_SESSION['admin_name'] ?? '',
        'role'      => $_SESSION['admin_role'] ?? 'admin'
    ];
}

/**
 * Middleware que bloquea el acceso a no autenticados y redirige al login
 */
function require_admin() {
    start_secure_session();
    if (!is_logged_in()) {
        // Recordar URL solicitada
        $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'] ?? '/admin/';
        header('Location: /admin/login.php');
        exit;
    }
}

/**
 * Intenta autenticar un administrador mediante usuario/email y contraseña
 * @param string $username_or_email
 * @param string $password
 * @return array ['success' => bool, 'error' => string]
 */
function login_admin($username_or_email, $password) {
    start_secure_session();
    $db = get_db();

    $identifier = trim($username_or_email);
    if (empty($identifier) || empty($password)) {
        return ['success' => false, 'error' => 'Por favor ingresa usuario/correo y contraseña.'];
    }

    try {
        $stmt = $db->prepare('SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$identifier, $identifier]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return ['success' => false, 'error' => 'Credenciales inválidas. Verifica tu usuario y contraseña.'];
        }

        // Regenerar ID de sesión para prevenir Session Fixation
        session_regenerate_id(true);

        $_SESSION['admin_logged']   = true;
        $_SESSION['admin_id']       = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_email']    = $admin['email'];
        $_SESSION['admin_name']     = $admin['full_name'] ?: $admin['username'];
        $_SESSION['admin_role']     = $admin['role'];

        // Actualizar último login
        try {
            $up = $db->prepare('UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE id = ?');
            $up->execute([$admin['id']]);
        } catch (Exception $e) {}

        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Error de conexión: ' . $e->getMessage()];
    }
}

/**
 * Cierra la sesión de administrador de manera segura
 */
function logout_admin() {
    start_secure_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/**
 * Verifica si ya existe al menos un administrador registrado
 * @return bool
 */
function has_any_admin() {
    try {
        $db = get_db();
        $stmt = $db->query('SELECT COUNT(*) FROM admins');
        return ((int)$stmt->fetchColumn()) > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Crea un nuevo administrador con contraseña cifrada
 * @param string $username
 * @param string $email
 * @param string $password
 * @param string $full_name
 * @return array ['success' => bool, 'error' => string]
 */
function create_admin($username, $email, $password, $full_name = '') {
    $db = get_db();

    $username = trim($username);
    $email    = trim($email);

    if (strlen($username) < 3) {
        return ['success' => false, 'error' => 'El nombre de usuario debe tener al menos 3 caracteres.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'El correo electrónico no tiene un formato válido.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'error' => 'La contraseña debe tener mínimo 6 caracteres.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $db->prepare('INSERT INTO admins (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, "admin")');
        $stmt->execute([$username, $email, $hash, $full_name ?: $username]);
        return ['success' => true];
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'UNIQUE') !== false || $e->getCode() == 23000) {
            return ['success' => false, 'error' => 'El usuario o el correo ya se encuentran registrados.'];
        }
        return ['success' => false, 'error' => 'Error al registrar: ' . $e->getMessage()];
    }
}
