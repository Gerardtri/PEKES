<?php
/**
 * PEKES Pet Shop — Gestión de Categorías
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$db = get_db();
$user = current_user();

$error = '';
$success = '';

// Procesar Creación o Edición de Categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Error de seguridad CSRF. Recarga la página.';
    } else {
        $cat_id = isset($_POST['cat_id']) ? (int)$_POST['cat_id'] : 0;
        $name   = trim($_POST['name'] ?? '');
        $slug   = trim($_POST['slug'] ?? '');
        $desc   = trim($_POST['description'] ?? '');
        $order  = (int)($_POST['display_order'] ?? 0);

        if (empty($name)) {
            $error = 'El nombre de la categoría es obligatorio.';
        } else {
            if (empty($slug)) {
                $slug = slugify($name);
            }

            try {
                if ($cat_id > 0) {
                    // Actualizar categoría
                    // Primero obtenemos el nombre anterior para actualizar los productos si cambió el nombre
                    $old_stmt = $db->prepare('SELECT name FROM categories WHERE id = ?');
                    $old_stmt->execute([$cat_id]);
                    $old_name = $old_stmt->fetchColumn();

                    $stmt = $db->prepare('UPDATE categories SET name = ?, slug = ?, description = ?, display_order = ? WHERE id = ?');
                    $stmt->execute([$name, $slug, $desc, $order, $cat_id]);

                    if ($old_name && $old_name !== $name) {
                        $up_prod = $db->prepare('UPDATE products SET category = ? WHERE category = ?');
                        $up_prod->execute([$name, $old_name]);
                    }

                    $success = 'Categoría actualizada exitosamente.';
                } else {
                    // Crear categoría
                    $stmt = $db->prepare('INSERT INTO categories (name, slug, description, display_order) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$name, $slug, $desc, $order]);
                    $success = 'Nueva categoría creada con éxito.';
                }
            } catch (Exception $e) {
                $error = 'Error al guardar categoría: ' . $e->getMessage();
            }
        }
    }
}

// Procesar Eliminación segura
$action = $_GET['action'] ?? '';
$del_id = isset($_GET['del_id']) ? (int)$_GET['del_id'] : 0;
if ($action === 'delete' && $del_id > 0) {
    $token = $_GET['token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Error de seguridad CSRF al intentar eliminar.';
    } else {
        try {
            // Obtener el nombre de la categoría
            $stmt = $db->prepare('SELECT name FROM categories WHERE id = ?');
            $stmt->execute([$del_id]);
            $cat_name = $stmt->fetchColumn();

            if ($cat_name) {
                // Verificar si tiene productos asociados
                $count_stmt = $db->prepare('SELECT COUNT(*) FROM products WHERE category = ?');
                $count_stmt->execute([$cat_name]);
                $prod_count = (int)$count_stmt->fetchColumn();

                if ($prod_count > 0) {
                    $error = sprintf('No se puede eliminar la categoría "%s" porque tiene %d productos asociados. Primero reasigna o elimina los productos.', e($cat_name), $prod_count);
                } else {
                    $del_stmt = $db->prepare('DELETE FROM categories WHERE id = ?');
                    $del_stmt->execute([$del_id]);
                    $success = 'Categoría eliminada con éxito.';
                }
            }
        } catch (Exception $e) {
            $error = 'Error al eliminar categoría: ' . $e->getMessage();
        }
    }
}

// Obtener todas las categorías y contar productos por categoría
$categories = [];
try {
    $sql = '
        SELECT c.*, COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON p.category = c.name
        GROUP BY c.id, c.name, c.slug, c.description, c.display_order
        ORDER BY c.display_order ASC, c.name ASC
    ';
    $categories = $db->query($sql)->fetchAll();
} catch (Exception $e) {
    // Si la tabla categories no existiera aún en SQLite
    $categories = [];
}

// Cargar categoría para editar si se seleccionó
$edit_category = null;
if (isset($_GET['edit_id'])) {
    $e_id = (int)$_GET['edit_id'];
    foreach ($categories as $c) {
        if ((int)$c['id'] === $e_id) {
            $edit_category = $c;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es-CO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Categorías — Administrador PEKES</title>
  <link rel="icon" type="image/png" href="/images/logos/mascota.png">
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>

<div class="admin-wrapper">

  <!-- Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
      <img src="/images/logos/logo_morado_cropped.png" alt="PEKES" class="sidebar-logo">
      <div class="sidebar-title-box">
        <span class="sidebar-title">PEKES</span>
        <span class="sidebar-badge">Administrador</span>
      </div>
    </div>

    <nav class="sidebar-nav">
      <a href="/admin/index.php" class="nav-item">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <span>Productos</span>
      </a>
      <a href="/admin/categories.php" class="nav-item active">
        <svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        <span>Categorías</span>
      </a>
      <a href="/admin/product-edit.php" class="nav-item">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Nuevo Producto</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <a href="/" target="_blank" class="btn-sidebar-store">
        <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        <span>Ver Tienda Pública</span>
      </a>
      <div class="user-profile">
        <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
        <div class="user-info">
          <span class="user-name"><?php echo e($user['full_name']); ?></span>
          <span class="user-role"><?php echo e($user['role']); ?></span>
        </div>
      </div>
      <a href="/admin/logout.php" style="color: #9B97A8; font-size: 0.75rem; display: flex; align-items: center; gap: 0.4rem; padding: 0.2rem 0.5rem;">
        <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>Cerrar Sesión</span>
      </a>
    </div>
  </aside>

  <!-- Main -->
  <main class="admin-main">
    <header class="admin-topbar">
      <div class="topbar-left">
        <button class="btn-menu-toggle" id="btnMenuToggle">
          <svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <h1 class="topbar-page-title">Gestión de Categorías</h1>
      </div>
    </header>

    <div class="admin-content">

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          <span><?php echo e($error); ?></span>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success">
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          <span><?php echo e($success); ?></span>
        </div>
      <?php endif; ?>

      <div class="form-grid">

        <!-- Lista de Categorías Existentes -->
        <div class="card-panel">
          <div class="panel-header">
            <h2 class="panel-title">Categorías del Catálogo (<?php echo count($categories); ?>)</h2>
          </div>

          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Slug</th>
                  <th>Orden</th>
                  <th>Productos</th>
                  <th style="text-align: right;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($categories)): ?>
                  <tr>
                    <td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--color-text-muted);">
                      No hay categorías registradas.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($categories as $cat): ?>
                    <tr>
                      <td>
                        <strong><?php echo e($cat['name']); ?></strong>
                        <?php if (!empty($cat['description'])): ?>
                          <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.15rem;">
                            <?php echo e($cat['description']); ?>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td><code><?php echo e($cat['slug']); ?></code></td>
                      <td><?php echo (int)($cat['display_order'] ?? 0); ?></td>
                      <td>
                        <span class="badge-status stage">
                          <?php echo (int)($cat['product_count'] ?? 0); ?> productos
                        </span>
                      </td>
                      <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 0.35rem;">
                          <a href="/admin/categories.php?edit_id=<?php echo (int)$cat['id']; ?>" class="btn-icon" title="Editar categoría">
                            <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                          </a>
                          <a href="/admin/categories.php?action=delete&del_id=<?php echo (int)$cat['id']; ?>&token=<?php echo urlencode(csrf_token()); ?>"
                             class="btn-icon danger"
                             title="Eliminar categoría"
                             onclick="return confirm('¿Seguro que deseas eliminar la categoría <?php echo addslashes($cat['name']); ?>?');">
                            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Formulario Crear / Editar Categoría -->
        <div class="card-panel" style="padding: 1.75rem; height: fit-content;">
          <h2 class="panel-title" style="margin-bottom: 1.25rem;">
            <?php echo $edit_category ? 'Editar Categoría' : 'Nueva Categoría'; ?>
          </h2>

          <form method="POST" action="/admin/categories.php">
            <?php echo csrf_field(); ?>
            <?php if ($edit_category): ?>
              <input type="hidden" name="cat_id" value="<?php echo (int)$edit_category['id']; ?>">
            <?php endif; ?>

            <div class="form-group">
              <label for="name" class="form-label">Nombre de Categoría *</label>
              <input type="text" id="name" name="name" class="admin-input" required value="<?php echo e($edit_category['name'] ?? ''); ?>" placeholder="Ej: Gimnasios de techo">
            </div>

            <div class="form-group">
              <label for="slug" class="form-label">Slug URL</label>
              <input type="text" id="slug" name="slug" class="admin-input" value="<?php echo e($edit_category['slug'] ?? ''); ?>" placeholder="gimnasios-de-techo">
              <span class="form-help">Si se deja vacío se genera automáticamente.</span>
            </div>

            <div class="form-group">
              <label for="display_order" class="form-label">Orden de Aparición</label>
              <input type="number" id="display_order" name="display_order" class="admin-input" value="<?php echo (int)($edit_category['display_order'] ?? 0); ?>" placeholder="0">
            </div>

            <div class="form-group">
              <label for="description" class="form-label">Descripción Breve</label>
              <textarea id="description" name="description" rows="3" class="admin-textarea" placeholder="Descripción de los modelos pertenecientes a esta línea..."><?php echo e($edit_category['description'] ?? ''); ?></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
              <button type="submit" class="btn-admin-primary btn-admin-violet" style="flex-grow: 1; justify-content: center;">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                <?php echo $edit_category ? 'Guardar Cambios' : 'Crear Categoría'; ?>
              </button>
              <?php if ($edit_category): ?>
                <a href="/admin/categories.php" class="btn-admin-secondary">
                  Cancelar
                </a>
              <?php endif; ?>
            </div>
          </form>
        </div>

      </div>

    </div>
  </main>
</div>

<script>
  const toggleBtn = document.getElementById('btnMenuToggle');
  const sidebar = document.getElementById('adminSidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });
  }
</script>

</body>
</html>

