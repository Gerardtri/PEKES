<?php
/**
 * PEKES Pet Shop — Asistente de Configuración Inicial del Administrador
 * Por seguridad, este archivo queda AUTOMÁTICAMENTE BLOQUEADO una vez creado el primer usuario.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

start_secure_session();

$already_configured = has_any_admin();
$error = '';
$success = '';

if ($already_configured && empty($_SESSION['setup_completed'])) {
    // Bloqueo total si ya existe administrador
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_configured) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Error de seguridad (token CSRF inválido). Recarga e intenta de nuevo.';
    } else {
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $pass1     = $_POST['password'] ?? '';
        $pass2     = $_POST['password_confirm'] ?? '';

        if ($pass1 !== $pass2) {
            $error = 'Las contraseñas no coinciden. Por favor verifícalas.';
        } else {
            $res = create_admin($username, $email, $pass1, $full_name);
            if ($res['success']) {
                $_SESSION['setup_completed'] = true;
                // Auto-login
                login_admin($username, $pass1);
                header('Location: /admin/index.php?welcome=1');
                exit;
            } else {
                $error = $res['error'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es-CO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Configuración Inicial — PEKES Pet Shop</title>
  <link rel="icon" type="image/png" href="/images/logos/mascota.png">
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>

<div class="login-screen">
  <div class="login-card" style="max-width: 480px;">
    <div class="login-header">
      <img src="/images/logos/logo_morado_cropped.png" alt="PEKES" class="login-logo">
      <h1 class="login-title">Configuración Inicial</h1>
      <p class="login-subtitle">Creación segura del primer administrador</p>
    </div>

    <?php if ($already_configured): ?>
      <div class="alert alert-danger">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
          <strong>Asistente Bloqueado por Seguridad</strong><br>
          Ya existe un administrador registrado en el sistema. Para ingresar usa la pantalla de inicio de sesión.
        </div>
      </div>
      <div style="margin-top: 1.5rem; text-align: center;">
        <a href="/admin/login.php" class="btn-admin-primary btn-admin-violet" style="justify-content: center; width: 100%;">
          Ir a Iniciar Sesión &rarr;
        </a>
      </div>
    <?php else: ?>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          <span><?php echo e($error); ?></span>
        </div>
      <?php endif; ?>

      <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 1.5rem; line-height: 1.5;">
        Crea tus credenciales maestras. Tu contraseña será encriptada con <code>password_hash()</code> con algoritmo criptográfico seguro (Bcrypt / Argon2).
      </p>

      <form method="POST" action="/admin/setup.php" autocomplete="off">
        <?php echo csrf_field(); ?>

        <div class="form-group">
          <label for="full_name" class="form-label">Nombre Completo</label>
          <input type="text" id="full_name" name="full_name" class="admin-input" required placeholder="Ej: Administrador PEKES">
        </div>

        <div class="form-group">
          <label for="username" class="form-label">Nombre de Usuario</label>
          <input type="text" id="username" name="username" class="admin-input" required placeholder="admin">
          <span class="form-help">Mínimo 3 caracteres, sin espacios.</span>
        </div>

        <div class="form-group">
          <label for="email" class="form-label">Correo Electrónico</label>
          <input type="email" id="email" name="email" class="admin-input" required placeholder="pekespetshop2026@gmail.com">
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" id="password" name="password" class="admin-input" required minlength="6" placeholder="Mínimo 6 caracteres">
          </div>
          <div class="form-group">
            <label for="password_confirm" class="form-label">Confirmar Contraseña</label>
            <input type="password" id="password_confirm" name="password_confirm" class="admin-input" required minlength="6" placeholder="Repite contraseña">
          </div>
        </div>

        <button type="submit" class="btn-admin-primary btn-admin-violet" style="width: 100%; justify-content: center; padding: 0.85rem; margin-top: 1rem;">
          <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Crear Administrador y Comenzar
        </button>
      </form>

    <?php endif; ?>

  </div>
</div>

</body>
</html>

