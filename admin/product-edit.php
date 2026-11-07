<?php
/**
 * PEKES Pet Shop — Crear / Editar Producto
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$db = get_db();
$user = current_user();

$id = trim($_GET['id'] ?? '');
$is_edit = !empty($id);

$product = [
    'id'            => '',
    'sku'           => '',
    'name'          => '',
    'category'      => 'Gimnasios de piso',
    'stage'         => 1,
    'catalog_price' => '',
    'price'         => '',
    'dimensions'    => '',
    'image'         => '/images/products/placeholder.jpeg',
    'description'   => '',
    'includes'      => '',
    'is_render'     => 0,
    'is_pending'    => 0,
    'is_active'     => 1,
    'tags'          => 'gatos',
    'page'          => 99
];

$error = '';
$success = '';

// Si es modo edición, cargar datos existentes
if ($is_edit) {
    $stmt = $db->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        header('Location: /admin/index.php');
        exit;
    }
    $product = $existing;

    // Normalizar includes a líneas de texto
    if (!empty($product['includes'])) {
        $inc_arr = json_decode($product['includes'], true);
        if (is_array($inc_arr)) {
            $product['includes'] = implode("\n", $inc_arr);
        }
    }

    // Normalizar tags a texto separado por coma
    if (!empty($product['tags'])) {
        $tags_arr = json_decode($product['tags'], true);
        if (is_array($tags_arr)) {
            $product['tags'] = implode(', ', $tags_arr);
        }
    }
}

// Cargar categorías disponibles
$categories = [];
try {
    $cat_stmt = $db->query('SELECT name FROM categories ORDER BY display_order ASC, name ASC');
    $categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = ['Gimnasios de piso', 'Gimnasios de pared', 'Rascadores'];
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Error de validación CSRF. Por favor recarga el formulario.';
    } else {
        $name        = trim($_POST['name'] ?? '');
        $category    = trim($_POST['category'] ?? 'Gimnasios de piso');
        $sku         = trim($_POST['sku'] ?? '');
        $cat_price   = !empty($_POST['catalog_price']) ? (int)$_POST['catalog_price'] : null;
        $price_input = !empty($_POST['price']) ? (int)$_POST['price'] : null;
        $dimensions  = trim($_POST['dimensions'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $stage       = (int)($_POST['stage'] ?? 1);
        $is_active   = isset($_POST['is_active']) ? 1 : 0;
        $is_render   = isset($_POST['is_render']) ? 1 : 0;
        $is_pending  = isset($_POST['is_pending']) ? 1 : 0;

        // Calcular precio de venta si no se indicó manualmente
        if ($price_input !== null && $price_input > 0) {
            $price = $price_input;
        } elseif ($cat_price !== null) {
            $price = calculate_price_cop($cat_price);
        } else {
            $price = 0;
        }

        // Procesar includes
        $includes_raw = trim($_POST['includes'] ?? '');
        $includes_arr = [];
        if (!empty($includes_raw)) {
            $lines = explode("\n", $includes_raw);
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed !== '') {
                    $includes_arr[] = $trimmed;
                }
            }
        }
        $includes_json = json_encode($includes_arr, JSON_UNESCAPED_UNICODE);

        // Procesar tags
        $tags_raw = trim($_POST['tags'] ?? '');
        $tags_arr = [];
        if (!empty($tags_raw)) {
            $parts = explode(',', $tags_raw);
            foreach ($parts as $p) {
                $trimmed = trim($p);
                if ($trimmed !== '') {
                    $tags_arr[] = $trimmed;
                }
            }
        }
        $tags_json = json_encode($tags_arr, JSON_UNESCAPED_UNICODE);

        // Procesar imagen
        $image_path = trim($_POST['image_url'] ?? $product['image']);
        if (!empty($_FILES['image_file']['name'])) {
            $upload_res = upload_product_image($_FILES['image_file'], $name ?: 'producto');
            if ($upload_res['success']) {
                $image_path = $upload_res['url'];
            } else {
                $error = 'Error con la imagen: ' . $upload_res['error'];
            }
        }

        if (empty($name)) {
            $error = 'El nombre del producto es obligatorio.';
        }

        if (empty($error)) {
            if ($is_edit) {
                // Actualizar producto existente
                if (empty($sku)) {
                    $sku = $product['sku'];
                }

                $stmt = $db->prepare('
                    UPDATE products
                    SET sku = ?, name = ?, category = ?, stage = ?, catalog_price = ?, price = ?,
                        dimensions = ?, image = ?, description = ?, includes = ?, is_render = ?,
                        is_pending = ?, is_active = ?, tags = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ');
                $stmt->execute([
                    $sku, $name, $category, $stage, $cat_price, $price, $dimensions,
                    $image_path, $description, $includes_json, $is_render, $is_pending,
                    $is_active, $tags_json, $id
                ]);

                $success = 'Producto "' . e($name) . '" actualizado con éxito.';
                // Refrescar datos
                $product['sku']           = $sku;
                $product['name']          = $name;
                $product['category']      = $category;
                $product['stage']         = $stage;
                $product['catalog_price'] = $cat_price;
                $product['price']         = $price;
                $product['dimensions']    = $dimensions;
                $product['image']         = $image_path;
                $product['description']   = $description;
                $product['includes']      = $includes_raw;
                $product['tags']          = $tags_raw;
                $product['is_render']     = $is_render;
                $product['is_pending']    = $is_pending;
                $product['is_active']     = $is_active;

            } else {
                // Crear nuevo producto
                if (empty($sku)) {
                    $sku = generate_sku($category);
                }

                $new_id = slugify($name);
                // Evitar ID repetido
                $check = $db->prepare('SELECT id FROM products WHERE id = ?');
                $check->execute([$new_id]);
                if ($check->fetch()) {
                    $new_id .= '-' . time();
                }

                $stmt = $db->prepare('
                    INSERT INTO products (id, sku, name, category, stage, catalog_price, price, dimensions, image, description, includes, is_render, is_pending, is_active, tags, page)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 99)
                ');
                $stmt->execute([
                    $new_id, $sku, $name, $category, $stage, $cat_price, $price,
                    $dimensions, $image_path, $description, $includes_json,
                    $is_render, $is_pending, $is_active, $tags_json
                ]);

                header('Location: /admin/product-edit.php?id=' . urlencode($new_id) . '&created=1');
                exit;
            }
        }
    }
}

if (isset($_GET['created'])) {
    $success = '¡Nuevo producto creado exitosamente en el catálogo!';
}
?>
<!DOCTYPE html>
<html lang="es-CO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $is_edit ? 'Editar ' . e($product['name']) : 'Nuevo Producto'; ?> — Administrador PEKES</title>
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
      <a href="/admin/categories.php" class="nav-item">
        <svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        <span>Categorías</span>
      </a>
      <a href="/admin/product-edit.php" class="nav-item <?php echo !$is_edit ? 'active' : ''; ?>">
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
        <h1 class="topbar-page-title">
          <?php echo $is_edit ? 'Editar Producto: ' . e($product['name']) : 'Crear Nuevo Producto'; ?>
        </h1>
      </div>

      <div class="topbar-actions">
        <a href="/admin/index.php" class="btn-admin-secondary">
          &larr; Volver a la Lista
        </a>
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

      <form method="POST" action="<?php echo $is_edit ? '/admin/product-edit.php?id=' . urlencode($id) : '/admin/product-edit.php'; ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <div class="form-grid">

          <!-- Columna Izquierda: Datos Principales -->
          <div class="card-panel" style="padding: 1.75rem;">
            <h2 class="panel-title" style="margin-bottom: 1.5rem;">Información del Producto</h2>

            <div class="form-group">
              <label for="name" class="form-label">Nombre del Producto *</label>
              <input type="text" id="name" name="name" class="admin-input" required value="<?php echo e($product['name']); ?>" placeholder="Ej: SKY, Combo #1, etc.">
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label for="category" class="form-label">Categoría *</label>
                <select id="category" name="category" class="admin-select" required>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo e($cat); ?>" <?php echo $product['category'] === $cat ? 'selected' : ''; ?>>
                      <?php echo e($cat); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label for="sku" class="form-label">SKU / Referencia</label>
                <input type="text" id="sku" name="sku" class="admin-input" value="<?php echo e($product['sku']); ?>" placeholder="Auto-generado si se deja vacío">
                <span class="form-help">Ej: PEK-PIS-016</span>
              </div>
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label for="catalog_price" class="form-label">Precio Catálogo Base (Mayorista)</label>
                <input type="number" id="catalog_price" name="catalog_price" class="admin-input" value="<?php echo e($product['catalog_price']); ?>" placeholder="Ej: 214000">
                <span class="form-help">Base sobre la cual aplica el factor 1.30</span>
              </div>

              <div class="form-group">
                <label for="price" class="form-label">Precio de Venta COP (Final al Público)</label>
                <input type="number" id="price" name="price" class="admin-input" value="<?php echo e($product['price']); ?>" placeholder="Ej: 278000">
                <span class="form-help">Si se deja vacío, se calcula con markup del 30%</span>
              </div>
            </div>

            <div class="form-group">
              <label for="dimensions" class="form-label">Medidas / Dimensiones Físicas</label>
              <input type="text" id="dimensions" name="dimensions" class="admin-input" value="<?php echo e($product['dimensions']); ?>" placeholder="Ej: Alto: 95 cm | Ancho: 70 cm | Fondo: 40 cm">
            </div>

            <div class="form-group">
              <label for="description" class="form-label">Descripción Comercial y Técnica</label>
              <textarea id="description" name="description" rows="4" class="admin-textarea" placeholder="Describe los materiales, confort, fijación y beneficios para el gato..."><?php echo e($product['description']); ?></textarea>
            </div>

            <div class="form-group">
              <label for="includes" class="form-label">Componentes Incluidos (Uno por línea)</label>
              <textarea id="includes" name="includes" rows="4" class="admin-textarea" placeholder="Domo de observación acrílico&#10;Repisa flotante en L&#10;Puente colgante de 80 cm"><?php echo e($product['includes']); ?></textarea>
              <span class="form-help">Escribe cada componente en un renglón separado. Se mostrarán con viñetas en la ficha técnica.</span>
            </div>

            <div class="form-group">
              <label for="tags" class="form-label">Etiquetas de Búsqueda (Separadas por coma)</label>
              <input type="text" id="tags" name="tags" class="admin-input" value="<?php echo e($product['tags']); ?>" placeholder="gatos, piso, torre, rascador">
            </div>
          </div>

          <!-- Columna Derecha: Imagen y Estados -->
          <div>
            <!-- Caja de Imagen -->
            <div class="card-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
              <h3 class="panel-title" style="margin-bottom: 1rem; font-size: 0.95rem;">Fotografía del Producto</h3>

              <div class="preview-img-container">
                <img id="imagePreview" src="<?php echo e($product['image']); ?>" alt="Previsualización" onerror="this.src='/images/logos/mascota.png'">
              </div>

              <div class="form-group">
                <label for="image_file" class="form-label">Subir Nueva Foto</label>
                <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png,image/webp" class="admin-input" onchange="previewSelectedImage(this)">
                <span class="form-help">Formatos: JPG, PNG, WEBP (Máx. 6 MB)</span>
              </div>

              <div class="form-group" style="margin-bottom: 0;">
                <label for="image_url" class="form-label">O Ruta / URL de Imagen</label>
                <input type="text" id="image_url" name="image_url" class="admin-input" value="<?php echo e($product['image']); ?>" oninput="document.getElementById('imagePreview').src = this.value">
              </div>
            </div>

            <!-- Caja de Estado y Opciones -->
            <div class="card-panel" style="padding: 1.5rem;">
              <h3 class="panel-title" style="margin-bottom: 1.25rem; font-size: 0.95rem;">Estado y Visibilidad</h3>

              <div class="form-group">
                <label for="stage" class="form-label">Etapa Comercial</label>
                <select id="stage" name="stage" class="admin-select">
                  <option value="1" <?php echo (int)$product['stage'] === 1 ? 'selected' : ''; ?>>Etapa 1 — Venta Inmediata</option>
                  <option value="2" <?php echo (int)$product['stage'] === 2 ? 'selected' : ''; ?>>Etapa 2 — Próximamente (Rascadores)</option>
                </select>
              </div>

              <div style="display: flex; flex-direction: column; gap: 0.85rem; margin-top: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.65rem; font-size: 0.85rem; cursor: pointer;">
                  <input type="checkbox" name="is_active" value="1" <?php echo (!empty($product['is_active']) || !isset($product['is_active'])) ? 'checked' : ''; ?> style="width: 17px; height: 17px; accent-color: var(--color-brand-violet);">
                  <strong>Producto Activo (Visible en tienda)</strong>
                </label>

                <label style="display: flex; align-items: center; gap: 0.65rem; font-size: 0.85rem; cursor: pointer;">
                  <input type="checkbox" name="is_pending" value="1" <?php echo !empty($product['is_pending']) ? 'checked' : ''; ?> style="width: 17px; height: 17px; accent-color: var(--color-brand-violet);">
                  <span>Precio por confirmar (Cotizar por WhatsApp)</span>
                </label>

                <label style="display: flex; align-items: center; gap: 0.65rem; font-size: 0.85rem; cursor: pointer;">
                  <input type="checkbox" name="is_render" value="1" <?php echo !empty($product['is_render']) ? 'checked' : ''; ?> style="width: 17px; height: 17px; accent-color: var(--color-brand-violet);">
                  <span>Es render 3D de alta definición</span>
                </label>
              </div>

              <div style="margin-top: 1.75rem; border-top: 1px solid var(--color-border); padding-top: 1.25rem;">
                <button type="submit" class="btn-admin-primary btn-admin-violet" style="width: 100%; justify-content: center; padding: 0.85rem;">
                  <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                  <?php echo $is_edit ? 'Guardar Cambios' : 'Crear Producto'; ?>
                </button>
              </div>
            </div>
          </div>

        </div>
      </form>

    </div>
  </main>
</div>

<script>
  function previewSelectedImage(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById('imagePreview').src = e.target.result;
      }
      reader.readAsDataURL(input.files[0]);
    }
  }

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

