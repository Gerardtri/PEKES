<?php
/**
 * PEKES Pet Shop — Panel Administrativo Principal
 * Gestión de Productos y Catálogo
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$user = current_user();
$db = get_db();
$flash_msg = '';
$flash_type = 'success';

// -------------------------------------------------------------
// Acciones Rápidas (Toggle Estado Activo / Inactivo, Eliminar)
// -------------------------------------------------------------
$action = $_GET['action'] ?? '';
$target_id = $_GET['id'] ?? '';

if ($action && $target_id) {
    $token = $_GET['token'] ?? '';
    if (!verify_csrf_token($token)) {
        $flash_msg = 'Error de validación de seguridad (CSRF). Intenta nuevamente.';
        $flash_type = 'danger';
    } else {
        if ($action === 'toggle_status') {
            try {
                $stmt = $db->prepare('UPDATE products SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?');
                $stmt->execute([$target_id]);
                $flash_msg = 'Estado del producto actualizado correctamente.';
            } catch (Exception $e) {
                $flash_msg = 'Error al actualizar estado: ' . $e->getMessage();
                $flash_type = 'danger';
            }
        } elseif ($action === 'delete') {
            try {
                $stmt = $db->prepare('DELETE FROM products WHERE id = ?');
                $stmt->execute([$target_id]);
                $flash_msg = 'Producto eliminado con éxito del catálogo.';
            } catch (Exception $e) {
                $flash_msg = 'Error al eliminar producto: ' . $e->getMessage();
                $flash_type = 'danger';
            }
        }
    }
}

// -------------------------------------------------------------
// Consultas para KPIs / Resumen
// -------------------------------------------------------------
$total_products = 0;
$total_piso = 0;
$total_pared = 0;
$total_rascadores = 0;
$total_activos = 0;

try {
    $total_products   = (int)$db->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $total_piso       = (int)$db->query("SELECT COUNT(*) FROM products WHERE category = 'Gimnasios de piso'")->fetchColumn();
    $total_pared      = (int)$db->query("SELECT COUNT(*) FROM products WHERE category = 'Gimnasios de pared'")->fetchColumn();
    $total_rascadores = (int)$db->query("SELECT COUNT(*) FROM products WHERE category = 'Rascadores'")->fetchColumn();
    $total_activos    = (int)$db->query('SELECT COUNT(*) FROM products WHERE is_active = 1 OR is_active IS NULL')->fetchColumn();
} catch (Exception $e) {}

// -------------------------------------------------------------
// Filtrado y Búsqueda de Productos
// -------------------------------------------------------------
$filter_cat    = trim($_GET['cat'] ?? '');
$filter_status = trim($_GET['status'] ?? '');
$search_query  = trim($_GET['q'] ?? '');

$sql = 'SELECT * FROM products WHERE 1=1';
$params = [];

if ($filter_cat && $filter_cat !== 'all') {
    $sql .= ' AND category = ?';
    $params[] = $filter_cat;
}

if ($filter_status === 'active') {
    $sql .= ' AND (is_active = 1 OR is_active IS NULL)';
} elseif ($filter_status === 'inactive') {
    $sql .= ' AND is_active = 0';
}

if ($search_query) {
    $term = '%' . mb_strtolower($search_query) . '%';
    $sql .= ' AND (LOWER(name) LIKE ? OR LOWER(sku) LIKE ? OR LOWER(dimensions) LIKE ?)';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= ' ORDER BY stage ASC, page ASC, id ASC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Obtener categorías para el filtro
$categories = [];
try {
    $cat_stmt = $db->query('SELECT name FROM categories ORDER BY display_order ASC, name ASC');
    $categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = ['Gimnasios de piso', 'Gimnasios de pared', 'Rascadores'];
}
?>
<!DOCTYPE html>
<html lang="es-CO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catálogo de Productos — Administrador PEKES</title>
  <link rel="icon" type="image/png" href="/images/logos/mascota.png">
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>

<div class="admin-wrapper">

  <!-- ========================================================================
       Barra Lateral (Sidebar)
       ======================================================================== -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
      <img src="/images/logos/logo_morado_cropped.png" alt="PEKES" class="sidebar-logo">
      <div class="sidebar-title-box">
        <span class="sidebar-title">PEKES</span>
        <span class="sidebar-badge">Administrador</span>
      </div>
    </div>

    <nav class="sidebar-nav">
      <a href="/admin/index.php" class="nav-item active">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <span>Productos</span>
      </a>
      <a href="/admin/categories.php" class="nav-item">
        <svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        <span>Categorías</span>
      </a>
      <a href="/admin/product-edit.php" class="nav-item">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Nuevo Producto</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <a href="/" target="_blank" class="btn-sidebar-store" title="Abrir tienda en nueva pestaña">
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

      <a href="/admin/logout.php" style="color: #9B97A8; font-size: 0.75rem; display: flex; align-items: center; gap: 0.4rem; padding: 0.2rem 0.5rem; transition: color 0.15s ease;">
        <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>Cerrar Sesión</span>
      </a>
    </div>
  </aside>

  <!-- ========================================================================
       Contenido Principal
       ======================================================================== -->
  <main class="admin-main">
    <header class="admin-topbar">
      <div class="topbar-left">
        <button class="btn-menu-toggle" id="btnMenuToggle" aria-label="Abrir menú">
          <svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <h1 class="topbar-page-title">Gestión de Catálogo</h1>
      </div>

      <div class="topbar-actions">
        <a href="/admin/product-edit.php" class="btn-admin-primary btn-admin-violet">
          <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Nuevo Producto</span>
        </a>
      </div>
    </header>

    <div class="admin-content">

      <?php if (!empty($flash_msg)): ?>
        <div class="alert alert-<?php echo $flash_type; ?>">
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          <span><?php echo e($flash_msg); ?></span>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['welcome'])): ?>
        <div class="alert alert-success">
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          <div>
            <strong>¡Bienvenido a PEKES!</strong> Has configurado tu cuenta de administrador con éxito.
          </div>
        </div>
      <?php endif; ?>

      <!-- Tarjetas de Resumen (KPIs) -->
      <section class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">
            <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
          </div>
          <div class="stat-details">
            <span class="stat-label">Total Productos</span>
            <span class="stat-value"><?php echo $total_products; ?></span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #EEF2FF; color: #4F46E5;">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
          </div>
          <div class="stat-details">
            <span class="stat-label">Gimnasios de Piso</span>
            <span class="stat-value"><?php echo $total_piso; ?></span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #FDF2F8; color: #DB2777;">
            <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
          </div>
          <div class="stat-details">
            <span class="stat-label">Combos de Pared</span>
            <span class="stat-value"><?php echo $total_pared; ?></span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: var(--color-success-bg); color: var(--color-success);">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <div class="stat-details">
            <span class="stat-label">Publicados Activos</span>
            <span class="stat-value"><?php echo $total_activos; ?></span>
          </div>
        </div>
      </section>

      <!-- Panel de Catálogo -->
      <section class="card-panel">
        <div class="panel-header">
          <h2 class="panel-title">Lista de Productos (<?php echo count($products); ?>)</h2>

          <!-- Filtros y Búsqueda -->
          <form method="GET" action="/admin/index.php" class="panel-toolbar">
            <div class="search-wrapper">
              <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <input type="text" name="q" class="admin-input" placeholder="Buscar por nombre, SKU o medida..." value="<?php echo e($search_query); ?>">
            </div>

            <select name="cat" class="admin-select" style="width: auto;" onchange="this.form.submit()">
              <option value="all">Todas las Categorías</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?php echo e($cat); ?>" <?php echo $filter_cat === $cat ? 'selected' : ''; ?>>
                  <?php echo e($cat); ?>
                </option>
              <?php endforeach; ?>
            </select>

            <select name="status" class="admin-select" style="width: auto;" onchange="this.form.submit()">
              <option value="">Todos los Estados</option>
              <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Activos</option>
              <option value="inactive" <?php echo $filter_status === 'inactive' ? 'selected' : ''; ?>>Inactivos</option>
            </select>

            <?php if ($search_query || $filter_cat || $filter_status): ?>
              <a href="/admin/index.php" class="btn-admin-secondary" title="Limpiar filtros">
                Limpiar
              </a>
            <?php endif; ?>
          </form>
        </div>

        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Precio Venta</th>
                <th>Etapa</th>
                <th>Estado</th>
                <th style="text-align: right;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($products)): ?>
                <tr>
                  <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: var(--color-text-muted);">
                    No se encontraron productos con los criterios seleccionados.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($products as $p): ?>
                  <?php
                    $is_active = isset($p['is_active']) ? (bool)$p['is_active'] : true;
                    $status_text = $is_active ? 'Activo' : 'Inactivo';
                    $status_class = $is_active ? 'active' : 'inactive';
                    $csrf = csrf_token();
                  ?>
                  <tr>
                    <td>
                      <div class="product-row-media">
                        <img src="<?php echo e($p['image']); ?>" alt="" class="product-thumb" onerror="this.src='/images/logos/mascota.png'">
                        <div class="product-info-text">
                          <a href="/admin/product-edit.php?id=<?php echo urlencode($p['id']); ?>" class="product-name-link">
                            <?php echo e($p['name']); ?>
                          </a>
                          <span class="product-sku-text"><?php echo e($p['sku']); ?> · <?php echo e($p['dimensions']); ?></span>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span style="font-size: 0.82rem; font-weight: 500; color: var(--color-text-main);">
                        <?php echo e($p['category']); ?>
                      </span>
                    </td>
                    <td>
                      <strong style="font-family: var(--font-heading); font-size: 0.95rem; color: var(--color-brand-violet-dark);">
                        <?php echo format_cop($p['price']); ?>
                      </strong>
                    </td>
                    <td>
                      <span class="badge-status stage">
                        Etapa <?php echo (int)($p['stage'] ?? 1); ?>
                      </span>
                    </td>
                    <td>
                      <a href="/admin/index.php?action=toggle_status&id=<?php echo urlencode($p['id']); ?>&token=<?php echo urlencode($csrf); ?>"
                         class="badge-status <?php echo $status_class; ?>"
                         title="Haz clic para <?php echo $is_active ? 'desactivar' : 'activar'; ?>">
                        <?php echo $status_text; ?>
                      </a>
                    </td>
                    <td style="text-align: right;">
                      <div style="display: inline-flex; gap: 0.35rem;">
                        <a href="/admin/product-edit.php?id=<?php echo urlencode($p['id']); ?>" class="btn-icon" title="Editar producto">
                          <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <a href="/admin/index.php?action=delete&id=<?php echo urlencode($p['id']); ?>&token=<?php echo urlencode($csrf); ?>"
                           class="btn-icon danger"
                           title="Eliminar producto"
                           onclick="return confirm('¿Seguro que deseas eliminar permanentemente <?php echo addslashes($p['name']); ?>? Esta acción no se puede deshacer.');">
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
      </section>

    </div>
  </main>
</div>

<script>
  // Mobile drawer toggle
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

