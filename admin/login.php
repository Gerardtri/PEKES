<?php
/**
 * PEKES Pet Shop — Iniciar Sesión de Administrador
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

start_secure_session();

// Si ya está autenticado, redirigir al panel principal
if (is_logged_in()) {
    header('Location: /admin/');
    exit;
}

$error = '';
$has_admin = has_any_admin();

// Procesar formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Error de seguridad (token CSRF inválido o expirado). Recarga la página.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $result = login_admin($username, $password);
        if ($result['success']) {
            $redirect = $_SESSION['login_redirect'] ?? '/admin/';
            unset($_SESSION['login_redirect']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es-CO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso Administrativo — PEKES Pet Shop</title>
  <link rel="icon" type="image/png" href="/images/logos/mascota.png">
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>

<div class="login-screen">
  <div class="login-card">
    <div class="login-header">
      <a href="/" title="Ir a la tienda pública">
        <img src="/images/logos/logo_morado_cropped.png" alt="PEKES" class="login-logo">
      </a>
      <h1 class="login-title">Panel Administrativo</h1>
      <p class="login-subtitle">Gestión privada de catálogo y productos</p>
    </div>

    <?php if (!$has_admin): ?>
      <div class="alert alert-warning">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
          <strong>Primer acceso detectado:</strong><br>
          Aún no se ha configurado el administrador inicial.
          <a href="/admin/setup.php" style="text-decoration: underline; font-weight: 700; color: #B45309; display: block; margin-top: 0.25rem;">
            Configurar Administrador Inicial &rarr;
          </a>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        <span><?php echo e($error); ?></span>
      </div>
    <?php endif; ?>

    <form method="POST" action="/admin/login.php" autocomplete="off">
      <?php echo csrf_field(); ?>

      <div class="form-group">
        <label for="username" class="form-label">Usuario o Correo Electrónico</label>
        <input type="text" id="username" name="username" class="admin-input" required autofocus placeholder="admin o tu@correo.com">
      </div>

      <div class="form-group" style="margin-bottom: 1.75rem;">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" id="password" name="password" class="admin-input" required placeholder="Tu contraseña privada">
      </div>

      <button type="submit" class="btn-admin-primary btn-admin-violet" style="width: 100%; justify-content: center; padding: 0.85rem;">
        <svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Iniciar Sesión
      </button>
    </form>

    <div style="text-align: center; margin-top: 1.75rem; border-top: 1px solid var(--color-border); padding-top: 1.25rem;">
      <a href="/" style="font-size: 0.8rem; color: var(--color-text-muted); display: inline-flex; align-items: center; gap: 0.35rem;">
        &larr; Volver a la Tienda Pública
      </a>
    </div>
  </div>
</div>

</body>
</html>

