<?php
/**
 * PEKES — Ficha Individual de Producto (PDP)
 * Server-Side Rendering (SSR) optimizado para SEO, Schema.org y Googlebot
 * Diseño inspirado en Fable Pets y MiaCara: Premium Pet Lifestyle
 */

require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$id   = trim($_GET['id'] ?? '');

$product = null;

// 1. Buscar por slug primero (URL amigable)
if (!empty($slug)) {
    $product = get_product_by_slug($slug);
}

// 2. Si no se encontró por slug o se pasó id, buscar por id
if (!$product && !empty($id)) {
    try {
        $db = get_db();
        $stmt = $db->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['includes'] = !empty($row['includes']) ? json_decode($row['includes'], true) : [];
            $row['tags']     = !empty($row['tags']) ? json_decode($row['tags'], true) : [];
            $product = $row;
            // Si tiene slug y se accedió por id, redirigir permanentemente a la URL canónica
            if (!empty($product['slug'])) {
                header('Location: /producto/' . urlencode($product['slug']), true, 301);
                exit;
            }
        }
    } catch (Exception $e) {
        // Fallback
    }
}

// 3. Manejo de Producto No Encontrado (404)
if (!$product) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="es-CO">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Producto no encontrado — PEKES Pet Shop</title>
      <link rel="stylesheet" href="/assets/css/main.css">
    </head>
    <body style="display: flex; flex-direction: column; min-height: 100vh; justify-content: center; align-items: center; text-align: center; padding: 2rem; background: var(--color-bg);">
      <div style="max-width: 480px; background: #fff; padding: 3rem 2rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-md); border: 1px solid var(--color-border);">
        <img src="/images/logos/mascota.png" alt="PEKES" style="width: 80px; height: auto; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.75rem; margin-bottom: 0.75rem; color: var(--color-text-main);">Producto no disponible</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem;">
          La pieza que buscas ya no está disponible o ha cambiado de enlace en nuestro catálogo oficial de Cali.
        </p>
        <a href="/" class="btn-primary" style="display: inline-block; padding: 0.85rem 1.75rem; background: var(--color-brand-violet); color: #fff; border-radius: var(--radius-full); text-decoration: none; font-weight: 600;">
          Volver al Catálogo Principal
        </a>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// 4. Variables canónicas para SEO y Redes
$product_name        = e($product['name']);
$product_slug        = !empty($product['slug']) ? $product['slug'] : slugify($product['name']);
$canonical_url       = SITE_OFFICIAL_URL . '/producto/' . $product_slug;
$product_price_num   = (int)($product['price'] ?? 0);
$product_price_fmt   = format_cop($product_price_num);
$product_cat         = e($product['category']);
$product_sku         = e($product['sku']);
$product_dims        = !empty($product['dimensions']) ? e($product['dimensions']) : 'Dimensiones a medida';
$product_desc        = !empty($product['description']) ? e($product['description']) : 'Gimnasio y mobiliario exclusivo para gatos diseñado para integrar el bienestar felino con el interiorismo de tu hogar.';
$product_image_rel   = !empty($product['image']) ? $product['image'] : '/images/logos/mascota.png';
$product_image_abs   = (strpos($product_image_rel, 'http') === 0) ? $product_image_rel : (SITE_OFFICIAL_URL . $product_image_rel);

// Meta Description precisa y orientada a intención de búsqueda en Cali
$seo_meta_desc = sprintf(
    '%s para gatos en Cali. %s. Medidas: %s. Fabricación artesanal en triplex de pino y cabuya natural. Consulta y compra vía WhatsApp.',
    $product_name,
    $product_cat,
    $product_dims
);
$seo_meta_desc = mb_substr(strip_tags($seo_meta_desc), 0, 155);

// WhatsApp URL
$wa_number = get_setting('whatsapp_number', DEFAULT_WHATSAPP);
$wa_url    = build_product_whatsapp_url($product, $wa_number);

// Productos relacionados
$related_products = get_related_products($product['category'], $product['id'], 4);
?>
<!DOCTYPE html>
<html lang="es-CO">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Metadatos Primarios de SEO -->
  <title><?php echo $product_name; ?> — <?php echo $product_cat; ?> en Cali | PEKES</title>
  <meta name="description" content="<?php echo e($seo_meta_desc); ?>" />
  <link rel="canonical" href="<?php echo e($canonical_url); ?>" />
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/images/logos/mascota.png" />

  <!-- Open Graph / Facebook / WhatsApp Preview -->
  <meta property="og:type" content="product" />
  <meta property="og:locale" content="es_CO" />
  <meta property="og:site_name" content="PEKES Pet Shop" />
  <meta property="og:title" content="<?php echo $product_name; ?> — PEKES Pet Shop" />
  <meta property="og:description" content="<?php echo e($seo_meta_desc); ?>" />
  <meta property="og:url" content="<?php echo e($canonical_url); ?>" />
  <meta property="og:image" content="<?php echo e($product_image_abs); ?>" />
  <meta property="og:image:alt" content="<?php echo $product_name; ?> - <?php echo $product_cat; ?>" />
  <meta property="product:price:amount" content="<?php echo $product_price_num; ?>" />
  <meta property="product:price:currency" content="COP" />
  <meta property="product:availability" content="in stock" />
  <meta property="product:condition" content="new" />

  <!-- Twitter Cards -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?php echo $product_name; ?> — PEKES Pet Shop" />
  <meta name="twitter:description" content="<?php echo e($seo_meta_desc); ?>" />
  <meta name="twitter:image" content="<?php echo e($product_image_abs); ?>" />

  <!-- Hojas de Estilo Oficiales PEKES -->
  <link rel="stylesheet" href="/assets/css/main.css" />

  <!-- Datos Estructurados Schema.org: Product & BreadcrumbList -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org/",
    "@graph": [
      {
        "@type": "Product",
        "@id": "<?php echo e($canonical_url); ?>#product",
        "name": "<?php echo $product_name; ?>",
        "url": "<?php echo e($canonical_url); ?>",
        "description": "<?php echo e($product_desc); ?>",
        "sku": "<?php echo $product_sku; ?>",
        "image": [
          "<?php echo e($product_image_abs); ?>"
        ],
        "category": "<?php echo $product_cat; ?>",
        "brand": {
          "@type": "Brand",
          "name": "PEKES"
        },
        "offers": {
          "@type": "Offer",
          "priceCurrency": "COP",
          "price": "<?php echo $product_price_num; ?>",
          "priceValidUntil": "2027-12-31",
          "itemCondition": "https://schema.org/NewCondition",
          "availability": "https://schema.org/InStock",
          "url": "<?php echo e($canonical_url); ?>",
          "seller": {
            "@type": "Organization",
            "name": "PEKES Pet Shop",
            "url": "https://pekes.shop"
          }
        }
      },
      {
        "@type": "BreadcrumbList",
        "@id": "<?php echo e($canonical_url); ?>#breadcrumb",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "Inicio",
            "item": "https://pekes.shop/"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "<?php echo $product_cat; ?>",
            "item": "https://pekes.shop/#catalogo"
          },
          {
            "@type": "ListItem",
            "position": 3,
            "name": "<?php echo $product_name; ?>",
            "item": "<?php echo e($canonical_url); ?>"
          }
        ]
      }
    ]
  }
  </script>
</head>

<body class="pdp-body">

  <!-- ==========================================================================
       Encabezado Principal
       ========================================================================== -->
  <header class="site-header">
    <div class="container header-inner">
      <a href="/" class="header-logo" aria-label="Ir al inicio de PEKES">
        <img src="/images/logos/logo_morado_cropped.png" alt="PEKES Pet Shop" />
        <span class="brand-tagline-badge">Cali · Catálogo Oficial</span>
      </a>

      <nav class="header-nav" aria-label="Navegación principal">
        <a href="/" class="nav-link">Inicio</a>
        <a href="/#catalogo" class="nav-link">Catálogo</a>
        <a href="/#manifiesto" class="nav-link">Nosotros</a>
        <a href="/#contacto" class="nav-link">Contacto</a>
      </nav>

      <div class="header-actions">
        <a href="<?php echo e($wa_url); ?>" target="_blank" rel="noopener" class="btn-wa-header" aria-label="Consultar por WhatsApp">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.966.528 1.884.813 2.796.813h.005c3.179 0 5.767-2.587 5.767-5.766.001-3.182-2.587-5.768-5.772-5.768zm4.493 8.358c-.188.528-.962 1.014-1.393 1.047-.384.029-.868.044-2.613-.679-1.921-.796-3.14-2.756-3.234-2.883-.095-.128-.772-1.028-.772-1.959s.489-1.386.662-1.576c.174-.189.378-.236.504-.236.126 0 .252.002.362.007.116.006.27-.044.423.324.157.378.536 1.309.583 1.404.047.095.079.206.016.332-.063.127-.095.206-.189.317-.095.111-.2.247-.286.332-.095.095-.194.198-.083.388.111.19.493.813 1.057 1.316.726.647 1.339.847 1.528.942.19.095.3.079.412-.048.111-.127.475-.555.602-.745.127-.19.253-.159.427-.095.174.063 1.107.523 1.297.618.19.095.317.143.364.222.047.079.047.459-.141.987z" />
          </svg>
          <span>Asesoría Cali</span>
        </a>
      </div>
    </div>
  </header>

  <!-- ==========================================================================
       Barra de Navegación / Migas de Pan (Breadcrumbs)
       ========================================================================== -->
  <div class="pdp-breadcrumbs-wrapper">
    <div class="container pdp-breadcrumbs-inner">
      <nav class="pdp-breadcrumbs" aria-label="Migas de pan">
        <a href="/">Inicio</a>
        <span class="sep">/</span>
        <a href="/#catalogo"><?php echo $product_cat; ?></a>
        <span class="sep">/</span>
        <span class="current"><?php echo $product_name; ?></span>
      </nav>

      <a href="/#catalogo" class="pdp-back-link">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="19" y1="12" x2="5" y2="12"></line>
          <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        <span>Volver al Catálogo</span>
      </a>
    </div>
  </div>

  <!-- ==========================================================================
       Sección Principal de Producto (PDP Layout Fable + MiaCara)
       ========================================================================== -->
  <main class="pdp-main-section">
    <div class="container pdp-grid">

      <!-- Columna Izquierda: Galería y Materiales -->
      <div class="pdp-gallery-col">
        <div class="pdp-image-stage">
          <img src="<?php echo e($product_image_rel); ?>" alt="<?php echo $product_name; ?> — <?php echo $product_cat; ?>" class="pdp-main-image" onerror="this.src='/images/logos/mascota.png'" />
          <div class="pdp-image-badge">
            <span class="badge-dot"></span>
            <span>Fabricación Artesanal en Cali</span>
          </div>
        </div>

        <!-- Módulos de Confianza y Materiales Nobles (Estilo MiaCara) -->
        <div class="pdp-craft-highlights">
          <div class="craft-item">
            <div class="craft-icon">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
              </svg>
            </div>
            <div class="craft-text">
              <strong>Triplex de Pino Seleccionado</strong>
              <span>Madera resistente de primera calidad tratada para alta durabilidad y estética natural.</span>
            </div>
          </div>

          <div class="craft-item">
            <div class="craft-icon">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="9"></circle>
                <path d="M12 3a9 9 0 0 0 0 18v-9h9"></path>
              </svg>
            </div>
            <div class="craft-text">
              <strong>Cabuya de Fique 100% Natural</strong>
              <span>Fibra vegetal no tóxica de alto gramaje para satisfacer el rascado instintivo felino.</span>
            </div>
          </div>

          <div class="craft-item">
            <div class="craft-icon">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
            </div>
            <div class="craft-text">
              <strong>Diseñado y Fabricado en Cali</strong>
              <span>Piezas producidas localmente con asesoría personalizada de armado e instalación.</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Columna Derecha: Especificaciones y CTA Comercial (Sticky) -->
      <div class="pdp-info-col">
        <div class="pdp-info-card">

          <div class="pdp-meta-row">
            <span class="pdp-category-pill"><?php echo $product_cat; ?></span>
            <span class="pdp-sku-pill">REF: <?php echo $product_sku; ?></span>
            <span class="pdp-status-pill">Por encargo</span>
          </div>

          <h1 class="pdp-title"><?php echo $product_name; ?></h1>

          <div class="pdp-price-panel">
            <div class="pdp-price-caption">Precio sugerido para Cali y alrededores</div>
            <div class="pdp-price-amount"><?php echo $product_price_fmt; ?></div>
          </div>

          <div class="pdp-description">
            <p><?php echo $product_desc; ?></p>
          </div>

          <!-- Tabla de Especificaciones Técnicas -->
          <div class="pdp-specs-box">
            <h2 class="specs-title">Ficha Técnica</h2>
            <div class="spec-row">
              <span class="spec-label">Dimensiones</span>
              <span class="spec-value"><?php echo $product_dims; ?></span>
            </div>
            <div class="spec-row">
              <span class="spec-label">Materiales</span>
              <span class="spec-value">Madera de pino seleccionada, tela térmica suave y cabuya natural</span>
            </div>
            <div class="spec-row">
              <span class="spec-label">Disponibilidad</span>
              <span class="spec-value">Fabricación bajo pedido (3 a 5 días hábiles en Cali)</span>
            </div>
            <div class="spec-row">
              <span class="spec-label">Tonalidades</span>
              <span class="spec-value">Sujetas a disponibilidad; personalizables por WhatsApp</span>
            </div>
          </div>

          <!-- Si incluye componentes específicos -->
          <?php if (!empty($product['includes']) && is_array($product['includes'])): ?>
            <div class="pdp-includes-box">
              <h2 class="specs-title">Lo que incluye esta pieza</h2>
              <ul class="includes-list">
                <?php foreach ($product['includes'] as $inc): ?>
                  <li>
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="var(--color-brand-violet)" stroke-width="2.5">
                      <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span><?php echo e($inc); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <!-- BOTÓN MAESTRO DE WHATSAPP -->
          <div class="pdp-cta-container">
            <a href="<?php echo e($wa_url); ?>" target="_blank" rel="noopener" class="btn-pdp-wa" aria-label="Consultar y Comprar por WhatsApp">
              <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.966.528 1.884.813 2.796.813h.005c3.179 0 5.767-2.587 5.767-5.766.001-3.182-2.587-5.768-5.772-5.768zm4.493 8.358c-.188.528-.962 1.014-1.393 1.047-.384.029-.868.044-2.613-.679-1.921-.796-3.14-2.756-3.234-2.883-.095-.128-.772-1.028-.772-1.959s.489-1.386.662-1.576c.174-.189.378-.236.504-.236.126 0 .252.002.362.007.116.006.27-.044.423.324.157.378.536 1.309.583 1.404.047.095.079.206.016.332-.063.127-.095.206-.189.317-.095.111-.2.247-.286.332-.095.095-.194.198-.083.388.111.19.493.813 1.057 1.316.726.647 1.339.847 1.528.942.19.095.3.079.412-.048.111-.127.475-.555.602-.745.127-.19.253-.159.427-.095.174.063 1.107.523 1.297.618.19.095.317.143.364.222.047.079.047.459-.141.987z" />
              </svg>
              <span>CONSULTAR Y COMPRAR POR WHATSAPP</span>
            </a>
            <div class="pdp-cta-guarantee">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
              </svg>
              <span>Atención directa y personalizada en Cali · Sin pasarela de pago obligatoria</span>
            </div>
          </div>

          <!-- Acordeones de Información Adicional (Inspiración Fable Pets) -->
          <div class="pdp-accordions">
            <details class="pdp-accordion-item" open>
              <summary class="accordion-header">
                <span>Envíos e Instalación en Cali</span>
                <span class="accordion-arrow">&rsaquo;</span>
              </summary>
              <div class="accordion-content">
                Coordinamos envíos seguros en toda el área metropolitana de Cali y municipios aledaños (Jamundí, Yumbo, Palmira). Para gimnasios de pared, brindamos asesoría técnica sobre anclajes y tipos de muros.
              </div>
            </details>

            <details class="pdp-accordion-item">
              <summary class="accordion-header">
                <span>Garantía y Calidad PEKES</span>
                <span class="accordion-arrow">&rsaquo;</span>
              </summary>
              <div class="accordion-content">
                Todas nuestras piezas cuentan con garantía de 6 meses en estructura y ensamble. Seleccionamos maderas con tratamiento anti-humedad y fibras naturales de alta resistencia.
              </div>
            </details>
          </div>

        </div>
      </div>

    </div>
  </main>

  <!-- ==========================================================================
       Piezas Complementarias (Cross-Selling Editorial)
       ========================================================================== -->
  <?php if (!empty($related_products)): ?>
    <section class="pdp-related-section">
      <div class="container">
        <div class="related-header">
          <span class="brand-tag">Colección PEKES</span>
          <h2 class="related-title">Piezas que complementan este espacio</h2>
        </div>

        <div class="related-grid">
          <?php foreach ($related_products as $rel): 
            $rel_slug = !empty($rel['slug']) ? $rel['slug'] : slugify($rel['name']);
            $rel_url  = '/producto/' . $rel_slug;
          ?>
            <article class="product-card">
              <a href="<?php echo e($rel_url); ?>" class="product-card-link" aria-label="Ver <?php echo e($rel['name']); ?>">
                <div class="product-media">
                  <img src="<?php echo e($rel['image']); ?>" alt="<?php echo e($rel['name']); ?>" loading="lazy" onerror="this.src='/images/logos/mascota.png'" />
                </div>
              </a>

              <div class="product-body">
                <span class="product-cat"><?php echo e($rel['category']); ?></span>
                <h3 class="product-title">
                  <a href="<?php echo e($rel_url); ?>"><?php echo e($rel['name']); ?></a>
                </h3>
                <div class="product-price"><?php echo format_cop($rel['price']); ?></div>
                <a href="<?php echo e($rel_url); ?>" class="btn-card-view">Ver Detalles</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- ==========================================================================
       Pie de Página Oficial
       ========================================================================== -->
  <footer class="site-footer" id="contacto">
    <div class="container footer-grid">
      <div class="footer-col brand-col">
        <a href="/" class="footer-logo">
          <img src="/images/logos/logo_morado_cropped.png" alt="PEKES Pet Shop" />
        </a>
        <p class="footer-tagline">
          Diseño, bienestar y conexión para gatos y hogares en Cali. Mobiliario contemporáneo que respeta tu espacio.
        </p>
      </div>

      <div class="footer-col">
        <h3 class="footer-heading">Categorías</h3>
        <ul class="footer-links">
          <li><a href="/#catalogo">Gimnasios de Piso</a></li>
          <li><a href="/#catalogo">Gimnasios de Pared</a></li>
          <li><a href="/#catalogo">Rascadores Naturales</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h3 class="footer-heading">Contacto y Asesoría</h3>
        <ul class="footer-links">
          <li>WhatsApp: +<?php echo e($wa_number); ?></li>
          <li>Cali, Valle del Cauca, Colombia</li>
          <li>Atención: Lunes a Sábado 8:00 AM – 6:00 PM</li>
        </ul>
      </div>

      <div class="footer-col">
        <h3 class="footer-heading">Garantía Comercial</h3>
        <p style="font-size: 0.85rem; color: var(--color-text-muted); line-height: 1.6;">
          Catálogo digital de consulta directa. Confirmación de especificaciones, acabados y tiempos de entrega directamente por WhatsApp.
        </p>
      </div>
    </div>

    <div class="container footer-bottom">
      <div>&copy; <?php echo date('Y'); ?> PEKES Pet Shop · RUT 1006071451. Todos los derechos reservados. Cali, Colombia.</div>
      <div>https://pekes.shop · Comercio digital directo sin pasarela de pago.</div>
    </div>
  </footer>

</body>
</html>

