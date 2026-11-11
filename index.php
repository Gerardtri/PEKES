<?php
/**
 * PEKES — Para la vida que comparten
 * Tienda Pública Oficial (PHP + MySQL)
 * Compatible con Hostinger Single
 */

require_once __DIR__ . '/includes/functions.php';

$db = get_db();

// 1. Obtener configuraciones de tienda
$wa_number = get_setting('whatsapp_number', DEFAULT_WHATSAPP);

// 2. Obtener productos activos (MySQL con respaldo autónomo a JSON)
$products = get_all_products();

// 3. Conteo de categorías
$count_all = count($products);
$count_piso = count(array_filter($products, fn($p) => $p['category'] === 'Gimnasios de piso'));
$count_pared = count(array_filter($products, fn($p) => $p['category'] === 'Gimnasios de pared'));
$count_rascadores = count(array_filter($products, fn($p) => $p['category'] === 'Rascadores'));

$css_version = file_exists(__DIR__ . '/assets/css/main.css') ? filemtime(__DIR__ . '/assets/css/main.css') : '2.3.0';
$js_version  = file_exists(__DIR__ . '/assets/js/app.js') ? filemtime(__DIR__ . '/assets/js/app.js') : '2.3.0';
?>
<!DOCTYPE html>
<html lang="es-CO">

<head>
  <!-- Google Consent Mode v2 (Default: Granted para Colombia) -->
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('consent', 'default', {
      'ad_storage': 'granted',
      'ad_user_data': 'granted',
      'ad_personalization': 'granted',
      'analytics_storage': 'granted'
    });
  </script>

  <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-PW7VGGHP');</script>
  <!-- End Google Tag Manager -->

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PEKES — Para la vida que comparten | Gimnasios y Catálogo Felino Cali</title>
  <meta name="description"
    content="Catálogo exclusivo de gimnasios de piso y pared para gatos en Cali. Diseños contemporáneos, bienestar y conexión. Consulta y compra directa vía WhatsApp." />
  <link rel="icon" type="image/png" href="/images/logos/mascota.png" />
  <link rel="stylesheet" href="/assets/css/main.css?v=<?php echo $css_version; ?>" />
</head>

<body>
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PW7VGGHP"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->

  <!-- ==========================================================================
       Encabezado y Navegación
       ========================================================================== -->
  <header class="site-header">
    <div class="container header-inner">
      <a href="/" class="header-logo" aria-label="Ir al inicio de PEKES">
        <img src="/images/logos/logo_morado_cropped.png" alt="PEKES Pet Shop" />
        <span class="brand-tagline-badge">Cali · Catálogo Oficial</span>
      </a>

      <nav class="main-nav" aria-label="Navegación principal">
        <a href="#test-michi" class="nav-link nav-highlight">Test Michi</a>
        <a href="#catalogo" class="nav-link active">Gimnasios</a>
        <a href="#bienestar" class="nav-link">Bienestar</a>
        <a href="#comunidad" class="nav-link">Comunidad</a>
        <a href="#como-comprar" class="nav-link">Cómo Comprar</a>
      </nav>

      <div class="header-actions">
        <button class="btn-purr-toggle" id="btn-purr-toggle" aria-label="Activar Modo Ronroneo">
          <svg class="purr-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 18V5l12-2v13"/>
            <circle cx="6" cy="18" r="3"/>
            <circle cx="18" cy="16" r="3"/>
          </svg>
          <span class="purr-text">Modo Michi</span>
        </button>

        <a href="https://wa.me/<?php echo e($wa_number); ?>?text=Hola%2C%20PEKES.%20Deseo%20hacer%20una%20consulta%20general%20sobre%20su%20cat%C3%A1logo%20de%20gimnasios."
          class="btn-header-wa" id="btn-header-wa" target="_blank" rel="noopener"
          aria-label="Abrir conversación de WhatsApp con PEKES">
          <svg viewBox="0 0 24 24">
            <path
              d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.966.528 1.884.813 2.796.813h.005c3.179 0 5.767-2.587 5.767-5.766.001-3.182-2.587-5.768-5.772-5.768zm4.493 8.358c-.188.528-.962 1.014-1.393 1.047-.384.029-.868.044-2.613-.679-1.921-.796-3.14-2.756-3.234-2.883-.095-.128-.772-1.028-.772-1.959s.489-1.386.662-1.576c.174-.189.378-.236.504-.236.126 0 .252.002.362.007.116.006.27-.044.423.324.157.378.536 1.309.583 1.404.047.095.079.206.016.332-.063.127-.095.206-.189.317-.095.111-.2.247-.286.332-.095.095-.194.198-.083.388.111.19.493.813 1.057 1.316.726.647 1.339.847 1.528.942.19.095.3.079.412-.048.111-.127.475-.555.602-.745.127-.19.253-.159.427-.095.174.063 1.107.523 1.297.618.19.095.317.143.364.222.047.079.047.459-.141.987z" />
          </svg>
          <span>WhatsApp</span>
        </a>
      </div>
    </div>
  </header>

  <!-- ==========================================================================
       Hero Principal
       ========================================================================== -->
  <section class="hero">
    <div class="container hero-grid">
      <div class="hero-content">
        <div class="hero-pill">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg>
          <span>Edición Cali · Catálogo 2026</span>
        </div>
        <h1 class="hero-title">
          PEKES — Para la vida <span>que comparten.</span>
        </h1>
        <p class="hero-subtitle">
          Mobiliario y gimnasios para gatos diseñados con maderas seleccionadas y textiles suaves. Estructuras armónicas
          que transforman tu hogar en un territorio de bienestar felino.
        </p>
        <div class="hero-actions">
          <a href="#catalogo" class="btn-primary">
            Explorar Catálogo — <?php echo $count_all; ?> Referencias
          </a>
          <a href="#comunidad" class="btn-secondary">
            Hogares Reales en Cali
          </a>
        </div>
        <div class="hero-features">
          <div class="hero-feature-item">
            <strong><?php echo $count_piso; ?></strong>
            <span>Gimnasios de Piso</span>
          </div>
          <div class="hero-feature-item">
            <strong><?php echo $count_pared; ?></strong>
            <span>Combos de Pared</span>
          </div>
          <div class="hero-feature-item">
            <strong>100%</strong>
            <span>Atención WhatsApp</span>
          </div>
        </div>
      </div>

      <div class="hero-visual">
        <div class="hero-slider" id="hero-slider" aria-label="Galería destacada de gimnasios PEKES">
          <div class="hero-slides-wrapper" id="hero-slides-wrapper">
            <!-- Slide 1: SKY -->
            <div class="hero-slide active" data-product-id="piso-16">
              <span class="hero-badge-tag">Torre Destacada · Piso</span>
              <div class="hero-img-wrap">
                <img src="/images/products/P16_SKY.jpeg" alt="Gimnasio para gatos SKY" />
              </div>
              <div class="hero-card-footer">
                <div>
                  <h3 class="hero-card-name">SKY</h3>
                  <p class="hero-card-dims">95 cm × 70 cm × 40 cm</p>
                </div>
                <div class="hero-card-price">$278.000 COP</div>
              </div>
            </div>

            <!-- Slide 2: Combo #1 -->
            <div class="hero-slide" data-product-id="pared-1">
              <span class="hero-badge-tag">Circuito Aéreo · Pared</span>
              <div class="hero-img-wrap">
                <img src="/images/products/P46_Combo_1.jpeg" alt="Combo 1 de pared en triplex de pino" />
              </div>
              <div class="hero-card-footer">
                <div>
                  <h3 class="hero-card-name">Combo #1 de Pared</h3>
                  <p class="hero-card-dims">Triplex Pino 12 mm · Domo acrílico</p>
                </div>
                <div class="hero-card-price">$687.000 COP</div>
              </div>
            </div>

            <!-- Slide 3: HEFESTO -->
            <div class="hero-slide" data-product-id="piso-35">
              <span class="hero-badge-tag">Edición Monumental · Piso</span>
              <div class="hero-img-wrap">
                <img src="/images/products/P35_HEFESTO.jpeg" alt="Gimnasio HEFESTO con doble cápsula" />
              </div>
              <div class="hero-card-footer">
                <div>
                  <h3 class="hero-card-name">HEFESTO</h3>
                  <p class="hero-card-dims">165 cm × 110 cm × 70 cm · Doble domo</p>
                </div>
                <div class="hero-card-price">$564.000 COP</div>
              </div>
            </div>

            <!-- Slide 4: ZAFIRO -->
            <div class="hero-slide" data-product-id="piso-11">
              <span class="hero-badge-tag">Diseño Circular · Piso</span>
              <div class="hero-img-wrap">
                <img src="/images/products/P11_ZAFIRO.jpeg" alt="Gimnasio ZAFIRO con cesta cóncava" />
              </div>
              <div class="hero-card-footer">
                <div>
                  <h3 class="hero-card-name">ZAFIRO</h3>
                  <p class="hero-card-dims">95 cm alto · Base ⌀60 cm</p>
                </div>
                <div class="hero-card-price">$216.000 COP</div>
              </div>
            </div>

            <!-- Slide 5: Combo #21 -->
            <div class="hero-slide" data-product-id="pared-21">
              <span class="hero-badge-tag">Circuito Máximo · Pared</span>
              <div class="hero-img-wrap">
                <img src="/images/products/P66_Combo_21.jpeg" alt="Combo 21 de pared con domo y mueble flotante" />
              </div>
              <div class="hero-card-footer">
                <div>
                  <h3 class="hero-card-name">Combo #21</h3>
                  <p class="hero-card-dims">Domo premium + Arenero flotante</p>
                </div>
                <div class="hero-card-price">$1.039.000 COP</div>
              </div>
            </div>
          </div>

          <!-- Controles del Slider -->
          <div class="hero-slider-nav">
            <button class="slider-arrow prev" id="hero-prev-btn" aria-label="Anterior modelo">&#8249;</button>
            <div class="slider-dots" id="hero-slider-dots">
              <span class="slider-dot active" data-index="0"></span>
              <span class="slider-dot" data-index="1"></span>
              <span class="slider-dot" data-index="2"></span>
              <span class="slider-dot" data-index="3"></span>
              <span class="slider-dot" data-index="4"></span>
            </div>
            <button class="slider-arrow next" id="hero-next-btn" aria-label="Siguiente modelo">&#8250;</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       Ticker Editorial de Valores de Marca
       ========================================================================== -->
  <div class="editorial-ticker" aria-hidden="true">
    <div class="container editorial-ticker-inner">
      <span>Madera de Pino Seleccionada</span>
      <span class="ticker-sep">&bull;</span>
      <span>Cabuya de Fique 100% Natural</span>
      <span class="ticker-sep">&bull;</span>
      <span>Fabricado Artesanalmente en Cali</span>
      <span class="ticker-sep">&bull;</span>
      <span>Bienestar Felino &amp; Interiorismo</span>
      <span class="ticker-sep">&bull;</span>
      <span>Atención Directa por WhatsApp</span>
    </div>
  </div>

  <!-- ==========================================================================
       Curaduría Editorial PEKES: Diseños Icónicos (Fable & MiaCara Inspired)
       ========================================================================== -->
  <section class="curated-section" id="destacados">
    <div class="container">
      <div class="curated-head">
        <div class="curated-head-text">
          <span class="brand-tag">Selección Destacada</span>
          <h2>Piezas Icónicas para el Hogar</h2>
        </div>
        <a href="#catalogo" class="btn-secondary" style="font-size: 0.82rem; padding: 0.65rem 1.25rem;">
          Ver Catálogo Completo &rarr;
        </a>
      </div>

      <div class="curated-grid">
        <!-- Pieza 1: SKY -->
        <div class="curated-card">
          <a href="/producto/gimnasio-sky" class="curated-media">
            <span class="curated-tag">Piso · Torre</span>
            <img src="/images/products/P16_SKY.jpeg" alt="Gimnasio Sky PEKES" loading="lazy" />
          </a>
          <div class="curated-body">
            <h3 class="curated-title">Gimnasio Sky</h3>
            <div class="curated-price">$278.000 COP</div>
            <div class="curated-actions">
              <a href="/producto/gimnasio-sky" class="btn-curated-detail">Ver Ficha Completa</a>
              <a href="https://wa.me/<?php echo e($wa_number); ?>?text=<?php echo rawurlencode('¡Hola PEKES! Me interesa el Gimnasio Sky ($278.000 COP) que vi en su selección destacada: https://pekes.shop/producto/gimnasio-sky. ¿Tienen disponibilidad para entrega en Cali?'); ?>" target="_blank" rel="noopener" class="btn-curated-wa" title="Consultar por WhatsApp" aria-label="Consultar Sky por WhatsApp">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                  <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.966.528 1.884.813 2.796.813h.005c3.179 0 5.767-2.587 5.767-5.766.001-3.182-2.587-5.768-5.772-5.768zm4.493 8.358c-.188.528-.962 1.014-1.393 1.047-.384.029-.868.044-2.613-.679-1.921-.796-3.14-2.756-3.234-2.883-.095-.128-.772-1.028-.772-1.959s.489-1.386.662-1.576c.174-.189.378-.236.504-.236.126 0 .252.002.362.007.116.006.27-.044.423.324.157.378.536 1.309.583 1.404.047.095.079.206.016.332-.063.127-.095.206-.189.317-.095.111-.2.247-.286.332-.095.095-.194.198-.083.388.111.19.493.813 1.057 1.316.726.647 1.339.847 1.528.942.19.095.3.079.412-.048.111-.127.475-.555.602-.745.127-.19.253-.159.427-.095.174.063 1.107.523 1.297.618.19.095.317.143.364.222.047.079.047.459-.141.987z"/>
                </svg>
              </a>
            </div>
          </div>
        </div>

        <!-- Pieza 2: HEFESTO -->
        <div class="curated-card">
          <a href="/producto/gimnasio-hefesto" class="curated-media">
            <span class="curated-tag">Piso · Doble Domo</span>
            <img src="/images/products/P35_HEFESTO.jpeg" alt="Gimnasio Hefesto PEKES" loading="lazy" />
          </a>
          <div class="curated-body">
            <h3 class="curated-title">Gimnasio Hefesto</h3>
            <div class="curated-price">$564.000 COP</div>
            <div class="curated-actions">
              <a href="/producto/gimnasio-hefesto" class="btn-curated-detail">Ver Ficha Completa</a>
              <a href="https://wa.me/<?php echo e($wa_number); ?>?text=<?php echo rawurlencode('¡Hola PEKES! Me interesa el Gimnasio Hefesto ($564.000 COP) que vi en su selección destacada: https://pekes.shop/producto/gimnasio-hefesto. ¿Tienen disponibilidad para entrega en Cali?'); ?>" target="_blank" rel="noopener" class="btn-curated-wa" title="Consultar por WhatsApp" aria-label="Consultar Hefesto por WhatsApp">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                  <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.966.528 1.884.813 2.796.813h.005c3.179 0 5.767-2.587 5.767-5.766.001-3.182-2.587-5.768-5.772-5.768zm4.493 8.358c-.188.528-.962 1.014-1.393 1.047-.384.029-.868.044-2.613-.679-1.921-.796-3.14-2.756-3.234-2.883-.095-.128-.772-1.028-.772-1.959s.489-1.386.662-1.576c.174-.189.378-.236.504-.236.126 0 .252.002.362.007.116.006.27-.044.423.324.157.378.536 1.309.583 1.404.047.095.079.206.016.332-.063.127-.095.206-.189.317-.095.111-.2.247-.286.332-.095.095-.194.198-.083.388.111.19.493.813 1.057 1.316.726.647 1.339.847 1.528.942.19.095.3.079.412-.048.111-.127.475-.555.602-.745.127-.19.253-.159.427-.095.174.063 1.107.523 1.297.618.19.095.317.143.364.222.047.079.047.459-.141.987z"/>
                </svg>
              </a>
            </div>
          </div>
        </div>

        <!-- Pieza 3: ZAFIRO -->
        <div class="curated-card">
          <a href="/producto/gimnasio-zafiro" class="curated-media">
            <span class="curated-tag">Piso · Cesta Nido</span>
            <img src="/images/products/P11_ZAFIRO.jpeg" alt="Gimnasio Zafiro PEKES" loading="lazy" />
          </a>
          <div class="curated-body">
            <h3 class="curated-title">Gimnasio Zafiro</h3>
            <div class="curated-price">$216.000 COP</div>
            <div class="curated-actions">
              <a href="/producto/gimnasio-zafiro" class="btn-curated-detail">Ver Ficha Completa</a>
              <a href="https://wa.me/<?php echo e($wa_number); ?>?text=<?php echo rawurlencode('¡Hola PEKES! Me interesa el Gimnasio Zafiro ($216.000 COP) que vi en su selección destacada: https://pekes.shop/producto/gimnasio-zafiro. ¿Tienen disponibilidad para entrega en Cali?'); ?>" target="_blank" rel="noopener" class="btn-curated-wa" title="Consultar por WhatsApp" aria-label="Consultar Zafiro por WhatsApp">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                  <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.966.528 1.884.813 2.796.813h.005c3.179 0 5.767-2.587 5.767-5.766.001-3.182-2.587-5.768-5.772-5.768zm4.493 8.358c-.188.528-.962 1.014-1.393 1.047-.384.029-.868.044-2.613-.679-1.921-.796-3.14-2.756-3.234-2.883-.095-.128-.772-1.028-.772-1.959s.489-1.386.662-1.576c.174-.189.378-.236.504-.236.126 0 .252.002.362.007.116.006.27-.044.423.324.157.378.536 1.309.583 1.404.047.095.079.206.016.332-.063.127-.095.206-.189.317-.095.111-.2.247-.286.332-.095.095-.194.198-.083.388.111.19.493.813 1.057 1.316.726.647 1.339.847 1.528.942.19.095.3.079.412-.048.111-.127.475-.555.602-.745.127-.19.253-.159.427-.095.174.063 1.107.523 1.297.618.19.095.317.143.364.222.047.079.047.459-.141.987z"/>
                </svg>
              </a>
            </div>
          </div>
        </div>

        <!-- Pieza 4: COMBO #1 DE PARED -->
        <div class="curated-card">
          <a href="/producto/gimnasio-pared-combo-1" class="curated-media">
            <span class="curated-tag">Pared · Circuito Aéreo</span>
            <img src="/images/products/P46_Combo_1.jpeg" alt="Combo 1 de Pared PEKES" loading="lazy" />
          </a>
          <div class="curated-body">
            <h3 class="curated-title">Combo #1 de Pared</h3>
            <div class="curated-price">$687.000 COP</div>
            <div class="curated-actions">
              <a href="/producto/gimnasio-pared-combo-1" class="btn-curated-detail">Ver Ficha Completa</a>
              <a href="https://wa.me/<?php echo e($wa_number); ?>?text=<?php echo rawurlencode('¡Hola PEKES! Me interesa el Combo #1 de Pared ($687.000 COP) que vi en su selección destacada: https://pekes.shop/producto/gimnasio-pared-combo-1. ¿Tienen disponibilidad para entrega en Cali?'); ?>" target="_blank" rel="noopener" class="btn-curated-wa" title="Consultar por WhatsApp" aria-label="Consultar Combo 1 por WhatsApp">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                  <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.966.528 1.884.813 2.796.813h.005c3.179 0 5.767-2.587 5.767-5.766.001-3.182-2.587-5.768-5.772-5.768zm4.493 8.358c-.188.528-.962 1.014-1.393 1.047-.384.029-.868.044-2.613-.679-1.921-.796-3.14-2.756-3.234-2.883-.095-.128-.772-1.028-.772-1.959s.489-1.386.662-1.576c.174-.189.378-.236.504-.236.126 0 .252.002.362.007.116.006.27-.044.423.324.157.378.536 1.309.583 1.404.047.095.079.206.016.332-.063.127-.095.206-.189.317-.095.111-.2.247-.286.332-.095.095-.194.198-.083.388.111.19.493.813 1.057 1.316.726.647 1.339.847 1.528.942.19.095.3.079.412-.048.111-.127.475-.555.602-.745.127-.19.253-.159.427-.095.174.063 1.107.523 1.297.618.19.095.317.143.364.222.047.079.047.459-.141.987z"/>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       Manifiesto Editorial PEKES (Diseño · Bienestar · Conexión)
       ========================================================================== -->
  <section class="manifesto-section" id="manifiesto">
    <div class="container manifesto-inner">
      <span class="brand-tag">Filosofía PEKES</span>
      <blockquote class="manifesto-quote">
        "No diseñamos muebles para mascotas. Diseñamos piezas para hogares donde los animales <span>son familia</span>."
      </blockquote>
      <p class="manifesto-body">
        Concebimos cada gimnasio y rascador como una extensión armónica de la arquitectura de tu espacio. Seleccionamos triplex de pino de primera calidad y cabuya de fique 100% natural sin tintes tóxicos, creando un territorio que satisface los instintos de altura, juego y refugio de tu gato mientras enriquece la calidez visual de tu sala o habitación.
      </p>
      <div class="manifesto-signature">
        Diseño · Bienestar · Conexión · Cali, Colombia
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       Los 4 Pilares del Bienestar Felino (Infografía Minimalista SVG)
       ========================================================================== -->
  <section class="wellness-section" id="bienestar">
    <div class="container">
      <div class="section-head" style="text-align: center; max-width: 650px; margin: 0 auto;">
        <span class="section-tag">Fundamentos Etológicos</span>
        <h2 class="section-title">El bienestar de tu gato, en sintonía con tu hogar</h2>
        <p class="section-desc">Cada decisión de diseño en PEKES responde a la etología felina y a la durabilidad de los materiales nobles.</p>
      </div>

      <div class="wellness-grid">
        <div class="wellness-card">
          <span class="wellness-num">01 / ETOLOGÍA</span>
          <div class="wellness-icon-wrap">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
            </svg>
          </div>
          <h3>Territorio Vertical</h3>
          <p>Los gatos experimentan calma y reducen el estrés cuando vigilan su entorno desde alturas estratégicas y refugios panorámicos.</p>
        </div>

        <div class="wellness-card">
          <span class="wellness-num">02 / SALUD</span>
          <div class="wellness-icon-wrap">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="9"></circle>
              <path d="M12 3a9 9 0 0 0 0 18v-9h9"></path>
            </svg>
          </div>
          <h3>Fibras 100% Naturales</h3>
          <p>Postes forrados en cabuya de fique natural de alto gramaje para satisfacer el instinto de marcaje y la salud de sus garras.</p>
        </div>

        <div class="wellness-card">
          <span class="wellness-num">03 / ARQUITECTURA</span>
          <div class="wellness-icon-wrap">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="3" y1="9" x2="21" y2="9"></line>
              <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
          </div>
          <h3>Estética Integrada</h3>
          <p>Maderas claras, domos transparentes y líneas sobrias que complementan el interiorismo sin saturar visualmente tu casa.</p>
        </div>

        <div class="wellness-card">
          <span class="wellness-num">04 / ARTESANÍA</span>
          <div class="wellness-icon-wrap">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
          </div>
          <h3>Hecho a Mano en Cali</h3>
          <p>Fabricación artesanal local con ensambles reforzados, fijaciones de seguridad y asesoría directa personalizada.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       El Test Michi — Recomendador Interactivo
       ========================================================================== -->
  <section class="michi-quiz-section" id="test-michi">
    <div class="container">
      <div class="michi-quiz-card">
        <div class="michi-quiz-header">
          <div class="quiz-badge">
            <svg viewBox="0 0 24 24">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            Experiencia Interactiva PEKES
          </div>
          <h2 class="quiz-title">Descubre el Gimnasio Ideal para tu Gato</h2>
          <p class="quiz-subtitle">
            Cada gato tiene una rutina y una forma única de habitar tu hogar. Responde 3 preguntas y nuestro algoritmo
            te recomendará el modelo ideal para su bienestar.
          </p>
          <div class="quiz-progress-bar">
            <div class="quiz-progress-fill" id="quiz-progress-fill" style="width: 33.3%;"></div>
          </div>
          <span class="quiz-step-indicator" id="quiz-step-indicator">Paso 1 de 3</span>
        </div>

        <div class="michi-quiz-body">
          <!-- Paso 1 -->
          <div class="quiz-step active" id="quiz-step-1">
            <h3 class="step-question">1. ¿Cómo describirías la personalidad de tu michi?</h3>
            <div class="quiz-options-grid">
              <button class="quiz-option-card" data-step="1" data-answer="observador">
                <div class="option-icon">
                  <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <strong class="option-title">El Observador Real</strong>
                <span class="option-desc">Le fascina vigilar desde las alturas, mirar por la ventana y contemplar su
                  territorio en calma.</span>
              </button>
              <button class="quiz-option-card" data-step="1" data-answer="atleta">
                <div class="option-icon">
                  <svg viewBox="0 0 24 24"><polyline points="13 17 18 12 13 7"/><polyline points="6 17 11 12 6 7"/></svg>
                </div>
                <strong class="option-title">El Atleta Intrépido</strong>
                <span class="option-desc">Corre, trepa con energía, rasca intensamente y necesita desafíos aéreos para
                  liberar vitalidad.</span>
              </button>
              <button class="quiz-option-card" data-step="1" data-answer="zen">
                <div class="option-icon">
                  <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <strong class="option-title">El Zen Dormilón</strong>
                <span class="option-desc">Ama las siestas largas, las telas suaves y los refugios cálidos tipo cueva
                  para sentirse protegido.</span>
              </button>
              <button class="quiz-option-card" data-step="1" data-answer="social">
                <div class="option-icon">
                  <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </div>
                <strong class="option-title">El Curioso Guardián</strong>
                <span class="option-desc">Siempre te acompaña cerca del escritorio o sofá. Quiere estar presente en cada
                  rutina de la casa.</span>
              </button>
            </div>
          </div>

          <!-- Paso 2 -->
          <div class="quiz-step" id="quiz-step-2">
            <h3 class="step-question">2. ¿Cómo es el espacio disponible en tu hogar en Cali?</h3>
            <div class="quiz-options-grid">
              <button class="quiz-option-card" data-step="2" data-answer="pared">
                <div class="option-icon">
                  <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
                </div>
                <strong class="option-title">Paredes Libres (Apto)</strong>
                <span class="option-desc">Quiero aprovechar la altura vertical sin ocupar metros útiles en el suelo de
                  mi sala o habitación.</span>
              </button>
              <button class="quiz-option-card" data-step="2" data-answer="suelo">
                <div class="option-icon">
                  <svg viewBox="0 0 24 24"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                </div>
                <strong class="option-title">Espacio Amplio en Piso</strong>
                <span class="option-desc">Tengo un buen espacio en el suelo para una torre de piso imponente, estable y
                  acogedora.</span>
              </button>
              <button class="quiz-option-card" data-step="2" data-answer="mixto">
                <div class="option-icon">
                  <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <strong class="option-title">Rincón de Ventana o Esquina</strong>
                <span class="option-desc">Busco una pieza compacta o modular para colocar junto a ventanales o rincones
                  arquitectónicos.</span>
              </button>
            </div>
            <button class="quiz-btn-back" id="quiz-back-to-1">&#8592; Volver al paso anterior</button>
          </div>

          <!-- Paso 3 -->
          <div class="quiz-step" id="quiz-step-3">
            <h3 class="step-question">3. ¿Cuántos gatos disfrutarán del gimnasio?</h3>
            <div class="quiz-options-grid">
              <button class="quiz-option-card" data-step="3" data-answer="uno">
                <div class="option-icon">
                  <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                </div>
                <strong class="option-title">1 Michi consentido</strong>
                <span class="option-desc">Un único rey o reina de la casa que disfrutará todos los niveles en
                  exclusiva.</span>
              </button>
              <button class="quiz-option-card" data-step="3" data-answer="dos">
                <div class="option-icon">
                  <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M1 20c0-3.3 2.9-6 8-6"/><circle cx="17" cy="8" r="3"/><path d="M23 20c0-3.3-2.9-6-6-6"/><path d="M12 20c0-3.3 1.1-6 5-6"/></svg>
                </div>
                <strong class="option-title">2 Compañeros felinos</strong>
                <span class="option-desc">Necesitamos al menos dos zonas de descanso para evitar disputas de
                  territorio.</span>
              </button>
              <button class="quiz-option-card" data-step="3" data-answer="manada">
                <div class="option-icon">
                  <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <strong class="option-title">Manada (3 o más michis)</strong>
                <span class="option-desc">Buscamos una estructura robusta o circuito amplio con múltiples plataformas y
                  rascadores.</span>
              </button>
            </div>
            <button class="quiz-btn-back" id="quiz-back-to-2">&#8592; Volver al paso anterior</button>
          </div>

          <!-- Pantalla de Resultado -->
          <div class="quiz-result-view" id="quiz-result-view">
            <div class="result-badge-tag">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              Match Ideal Encontrado
            </div>
            <div class="result-grid">
              <div class="result-media">
                <img src="/images/products/P16_SKY.jpeg" id="result-img" alt="Gimnasio recomendado" />
                <span class="result-category-pill" id="result-category-pill">Torre de Piso</span>
              </div>
              <div class="result-info">
                <div class="result-profile-label">Personalidad de tu Gato:</div>
                <h3 class="result-profile-title" id="result-profile-title">El Observador Real</h3>
                <p class="result-profile-desc" id="result-profile-desc">
                  Tu gato necesita una atalaya elevada para sentirse seguro mientras domina visualmente toda la
                  estancia.
                </p>

                <div class="result-product-box">
                  <div class="result-product-meta">
                    <span class="result-product-name" id="result-product-name">SKY</span>
                    <span class="result-product-dims" id="result-product-dims">95 cm × 70 cm × 40 cm</span>
                  </div>
                  <div class="result-product-price" id="result-product-price">$278.000 COP</div>
                </div>

                <div class="result-actions">
                  <a href="#" class="btn-primary btn-primary-violet result-wa-btn" id="result-wa-btn" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:#fff;">
                      <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.966.528 1.884.813 2.796.813h.005c3.179 0 5.767-2.587 5.767-5.766.001-3.182-2.587-5.768-5.772-5.768zm4.493 8.358c-.188.528-.962 1.014-1.393 1.047-.384.029-.868.044-2.613-.679-1.921-.796-3.14-2.756-3.234-2.883-.095-.128-.772-1.028-.772-1.959s.489-1.386.662-1.576c.174-.189.378-.236.504-.236.126 0 .252.002.362.007.116.006.27-.044.423.324.157.378.536 1.309.583 1.404.047.095.079.206.016.332-.063.127-.095.206-.189.317-.095.111-.2.247-.286.332-.095.095-.194.198-.083.388.111.19.493.813 1.057 1.316.726.647 1.339.847 1.528.942.19.095.3.079.412-.048.111-.127.475-.555.602-.745.127-.19.253-.159.427-.095.174.063 1.107.523 1.297.618.19.095.317.143.364.222.047.079.047.459-.141.987z" />
                    </svg>
                    Consultar este modelo por WhatsApp
                  </a>
                  <button class="btn-secondary" id="quiz-restart-btn">&#8634; Repetir el test</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       Capítulo Editorial 01 (Estilo MiaCara Split Banner)
       "Tu sofá es tuyo... hasta que él decide lo contrario"
       ========================================================================== -->
  <section class="miacara-editorial-split">
    <div class="container miacara-split-grid">
      <div class="miacara-split-media">
        <img src="/images/campaign/sofa_campana.jpg" alt="PEKES — Tu sofá es tuyo hasta que él decide lo contrario" loading="lazy" />
      </div>
      <div class="miacara-split-content">
        <span class="miacara-kicker">Campaña Oficial PEKES · Hábitat en Armonía</span>
        <h2 class="miacara-title">Espacios para convivir, no para competir por ellos</h2>
        <p class="miacara-text">
          Convivir con un felino no significa renunciar a la belleza de tu mobiliario. En PEKES diseñamos gimnasios de suelo con postes robustos en cabuya 100% natural y plataformas acogedoras que satisfacen el estiramiento y rascado instintivo, preservando la tranquilidad y estilo de tu hogar en Cali.
        </p>
        <div class="miacara-actions">
          <a href="#catalogo" class="btn-miacara-primary">Descubrir Gimnasios de Piso</a>
          <a href="https://wa.me/<?php echo e($wa_number); ?>?text=<?php echo rawurlencode('¡Hola PEKES! Me gustaría recibir asesoría sobre qué gimnasio de piso se adapta mejor a los espacios de mi casa en Cali.'); ?>" target="_blank" rel="noopener" class="btn-miacara-ghost" id="banner-wa-sofa">Asesoría por WhatsApp &rarr;</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       Catálogo Comercial de Productos
       ========================================================================== -->
  <section class="catalog-section" id="catalogo">
    <div class="container">
      <div class="section-head" style="margin-bottom: 2.25rem;">
        <span class="section-tag">Catálogo Comercial Oficial</span>
        <h2 class="section-title">Colección de Gimnasios PEKES</h2>
        <p class="section-desc">Selecciona tu modelo favorito y consúltalo de inmediato por WhatsApp con su ficha
          técnica y medidas exactas.</p>
      </div>

      <!-- Barra de Filtros y Búsqueda -->
      <div class="catalog-toolbar">
        <div class="category-tabs" role="tablist">
          <button class="tab-btn active" data-category="all">Todos (<?php echo $count_all; ?>)</button>
          <button class="tab-btn" data-category="Gimnasios de piso">Gimnasios de Piso (<?php echo $count_piso; ?>)</button>
          <button class="tab-btn" data-category="Gimnasios de pared">Gimnasios de Pared (<?php echo $count_pared; ?>)</button>
          <button class="tab-btn" data-category="Rascadores" style="opacity: 0.65;">Rascadores — Próximamente (<?php echo $count_rascadores; ?>)</button>
        </div>

        <div class="search-box">
          <span class="search-icon">
            <svg viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
          </span>
          <input type="text" id="search-input" class="search-input" placeholder="Buscar por nombre o medida..." />
        </div>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <span id="catalog-count" style="font-size: 0.82rem; font-weight: 600; color: var(--color-text-muted);"><?php echo $count_all; ?> productos disponibles</span>
        <span style="font-size: 0.78rem; color: var(--color-brand-violet); font-weight: 600; font-family: var(--font-heading);">Envíos y entregas en Cali por coordinar</span>
      </div>

      <!-- Cuadrícula de Productos (Pre-renderizada para indexación y carga inmediata) -->
      <div class="products-grid" id="products-grid">
        <?php foreach ($products as $p): 
            $p_slug = !empty($p['slug']) ? $p['slug'] : slugify($p['name']);
            $p_url = '/producto/' . $p_slug;
            $p_wa  = build_product_whatsapp_url($p, $wa_number);
            $p_cat_badge = ($p['category'] === 'Gimnasios de pared') ? 'Pared' : (($p['category'] === 'Rascadores') ? 'Rascador' : 'Piso');
        ?>
          <article class="product-card" data-id="<?php echo e($p['id']); ?>" data-category="<?php echo e($p['category']); ?>" data-slug="<?php echo e($p_slug); ?>">
            <div class="card-img-container" tabindex="0" role="button" aria-label="Ver ficha de <?php echo e($p['name']); ?>">
              <span class="card-badge">Por encargo</span>
              <span class="card-badge stage-badge"><?php echo $p_cat_badge; ?></span>
              <img src="<?php echo e($p['image']); ?>" alt="Gimnasio para gatos <?php echo e($p['name']); ?>" loading="lazy" onerror="this.src='/images/logos/mascota.png'" />
            </div>
            <div class="card-body">
              <span class="card-sku"><?php echo e($p['sku']); ?></span>
              <h3 class="card-title"><?php echo e($p['name']); ?></h3>
              <p class="card-dims"><?php echo e($p['dimensions']); ?></p>
              <div class="card-price-row">
                <span class="card-price"><?php echo format_cop($p['price']); ?></span>
              </div>
              <div class="card-actions-row">
                <button type="button" class="card-btn-view" data-action="view" aria-label="Ver ficha técnica de <?php echo e($p['name']); ?>">
                  <span>Ver Ficha</span>
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                  </svg>
                </button>
                <a href="<?php echo e($p_wa); ?>" target="_blank" rel="noopener" class="card-btn-wa" title="Consultar por WhatsApp" aria-label="Consultar <?php echo e($p['name']); ?> por WhatsApp">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.966.528 1.884.813 2.796.813h.005c3.179 0 5.767-2.587 5.767-5.766.001-3.182-2.587-5.768-5.772-5.768zm4.493 8.358c-.188.528-.962 1.014-1.393 1.047-.384.029-.868.044-2.613-.679-1.921-.796-3.14-2.756-3.234-2.883-.095-.128-.772-1.028-.772-1.959s.489-1.386.662-1.576c.174-.189.378-.236.504-.236.126 0 .252.002.362.007.116.006.27-.044.423.324.157.378.536 1.309.583 1.404.047.095.079.206.016.332-.063.127-.095.206-.189.317-.095.111-.2.247-.286.332-.095.095-.194.198-.083.388.111.19.493.813 1.057 1.316.726.647 1.339.847 1.528.942.19.095.3.079.412-.048.111-.127.475-.555.602-.745.127-.19.253-.159.427-.095.174.063 1.107.523 1.297.618.19.095.317.143.364.222.047.079.047.459-.141.987z"/>
                  </svg>
                </a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       Capítulo Editorial 02 (Estilo MiaCara Split Banner)
       "Porque su mundo merece lo mejor"
       ========================================================================== -->
  <section class="miacara-editorial-split reverse">
    <div class="container miacara-split-grid">
      <div class="miacara-split-content">
        <span class="miacara-kicker">Campaña Oficial PEKES · Territorio Vertical</span>
        <h2 class="miacara-title">La perspectiva que tu gato busca, con el diseño que mereces</h2>
        <p class="miacara-text">
          Los gatos necesitan dominar las alturas para sentirse en paz y seguros. Nuestros circuitos aéreos de pared modulares, fabricados en triplex de pino de 12 mm con domos acrílicos panorámicos, transforman muros convencionales en piezas esculturales de bienestar felino e interiorismo contemporáneo en Cali.
        </p>
        <div class="miacara-actions">
          <a href="#catalogo" class="btn-miacara-primary">Explorar Combos de Pared</a>
          <a href="https://wa.me/<?php echo e($wa_number); ?>?text=<?php echo rawurlencode('¡Hola PEKES! Me interesa conocer las opciones de circuitos aéreos de pared para instalar en mi apartamento en Cali.'); ?>" target="_blank" rel="noopener" class="btn-miacara-ghost" id="banner-wa-mundo">Consultar Instalación en Cali &rarr;</a>
        </div>
      </div>
      <div class="miacara-split-media">
        <img src="/images/campaign/mundo_campana.jpg" alt="PEKES — Porque su mundo merece lo mejor" loading="lazy" />
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       Comunidad PEKES: Hogares Reales en Cali
       ========================================================================== -->
  <section class="community-section" id="comunidad">
    <div class="container">
      <div class="section-head" style="text-align: center; max-width: 650px; margin: 0 auto;">
        <span class="section-tag">Comunidad PEKES</span>
        <h2 class="section-title">Hogares y gatos reales en Cali</h2>
        <p class="section-desc">Así disfrutan nuestros clientes y sus felinos las piezas PEKES en sus propios espacios.</p>
      </div>

      <div class="community-mosaic">
        <div class="community-tile">
          <img src="/images/products/P16_SKY.jpeg" alt="Gato en Torre Sky en sala de Cali" loading="lazy" />
          <div class="community-overlay">
            <span>Torre Sky · San Antonio, Cali</span>
          </div>
        </div>
        <div class="community-tile">
          <img src="/images/products/P11_ZAFIRO.jpeg" alt="Gato descansando en Gimnasio Zafiro" loading="lazy" />
          <div class="community-overlay">
            <span>Gimnasio Zafiro · Ciudad Jardín, Cali</span>
          </div>
        </div>
        <div class="community-tile">
          <img src="/images/products/P46_Combo_1.jpeg" alt="Circuito aéreo Combo 1 en pared" loading="lazy" />
          <div class="community-overlay">
            <span>Circuito Combo #1 · Granada, Cali</span>
          </div>
        </div>
        <div class="community-tile">
          <img src="/images/products/P66_Combo_21.jpeg" alt="Combo 21 instalado en apartamento" loading="lazy" />
          <div class="community-overlay">
            <span>Combo #21 en Esquina · Pance, Cali</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       Cómo Comprar por WhatsApp
       ========================================================================== -->
  <section class="flow-section" id="como-comprar">
    <div class="container">
      <div class="section-head">
        <span class="section-tag">Modelo Consultivo</span>
        <h2 class="section-title">Cómo Adquirir tu Gimnasio PEKES</h2>
        <p class="section-desc">Para garantizarte la tonalidad adecuada y resolver medidas específicas de tu hogar,
          coordinamos cada pedido de forma personalizada por WhatsApp.</p>
      </div>

      <div class="flow-grid">
        <div class="flow-step">
          <div class="flow-num">1</div>
          <h4 class="flow-title">Elige tu Producto</h4>
          <p class="flow-desc">Revisa el catálogo, las medidas normalizadas en cm y los componentes del modelo o combo.</p>
        </div>

        <div class="flow-step">
          <div class="flow-num">2</div>
          <h4 class="flow-title">Clic en WhatsApp</h4>
          <p class="flow-desc">El botón construye un mensaje exacto con el nombre, referencia SKU y precio publicado.</p>
        </div>

        <div class="flow-step">
          <div class="flow-num">3</div>
          <h4 class="flow-title">Confirmación</h4>
          <p class="flow-desc">Acordamos contigo tonalidades disponibles de fábrica, dirección en Cali y opciones de entrega.</p>
        </div>

        <div class="flow-step">
          <div class="flow-num">4</div>
          <h4 class="flow-title">Fabricación y Entrega</h4>
          <p class="flow-desc">Tu gimnasio se elabora por encargo y se programa su despacho directamente a tu domicilio.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       Preguntas Frecuentes (FAQ)
       ========================================================================== -->
  <section class="faq-section" id="faq">
    <div class="container">
      <div class="section-head">
        <span class="section-tag">Información y Dudas</span>
        <h2 class="section-title">Preguntas Frecuentes</h2>
      </div>

      <div class="faq-list">
        <div class="faq-item">
          <h4>¿De qué material están fabricados los gimnasios?</h4>
          <p>Los combos de pared están construidos en triplex de pino de 12 mm con acabados pulidos. Los gimnasios de piso cuentan con postes forrados en cabuya natural para el afilado de uñas y superficies forradas en tela suave para descanso.</p>
        </div>

        <div class="faq-item">
          <h4>¿Cómo se eligen las tonalidades y telas?</h4>
          <p>Las tonalidades y accesorios están sujetos a disponibilidad de proveedores textiles. Al iniciar la conversación por WhatsApp, te compartimos la paleta física vigente para que elijas la combinación perfecta.</p>
        </div>

        <div class="faq-item">
          <h4>¿Los productos tienen entrega inmediata?</h4>
          <p>Los gimnasios se elaboran artesanalmente bajo pedido en Triplex de Pino de 12 mm. El tiempo habitual de despacho es de 5 a 10 días hábiles según la programación de taller.</p>
        </div>

        <div class="faq-item">
          <h4>¿Cómo funciona la instalación y los envíos?</h4>
          <p>En Cali, los combos de pared incluyen el servicio de instalación técnica en tu muro. El costo de envío o transporte es asumido por el cliente. También despachamos a nivel nacional (no incluye instalación fuera de Cali).</p>
        </div>

        <div class="faq-item">
          <h4>¿Qué garantía tienen los productos?</h4>
          <p>Cuentas con 6 meses de garantía legal directa por defectos de fabricación o ensamble estructural en madera y componentes.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       Pie de Página (Footer)
       ========================================================================== -->
  <footer class="site-footer">
    <div class="container footer-grid">
      <div class="footer-brand">
        <div class="footer-brand-name">PEKES</div>
        <p>Para la vida que comparten. Mobiliario, gimnasios y experiencias para gatos y perros en Cali, Colombia.</p>
        <div class="footer-brand-meta">
          Atención personalizada: Lunes a Sábado · 9:00 am a 6:00 pm<br />
          Contacto oficial: <a href="mailto:pekespetshop2026@gmail.com">pekespetshop2026@gmail.com</a><br />
          WhatsApp: <a href="https://wa.me/<?php echo e($wa_number); ?>">+57 321 542 7745</a>
        </div>
      </div>

      <div class="footer-nav">
        <h5>Catálogo Etapa 1</h5>
        <ul>
          <li><a href="#catalogo">Gimnasios de Piso (<?php echo $count_piso; ?>)</a></li>
          <li><a href="#catalogo">Gimnasios de Pared (<?php echo $count_pared; ?>)</a></li>
          <li><a href="#catalogo">Combos con Cápsula</a></li>
          <li><a href="#catalogo">Rascadores (Próximamente)</a></li>
        </ul>
      </div>

      <div class="footer-nav">
        <h5>Atención & Legal</h5>
        <ul>
          <li><a href="#como-comprar">Pedidos y Entregas</a></li>
          <li><a href="#faq">Preguntas Frecuentes</a></li>
          <li><a href="javascript:void(0)" class="legal-trigger" data-legal="privacidad">Tratamiento de Datos</a></li>
          <li><a href="javascript:void(0)" class="legal-trigger" data-legal="terminos">Términos y Condiciones</a></li>
          <li><a href="javascript:void(0)" class="legal-trigger" data-legal="garantias">Envíos y Garantía (6 Meses)</a></li>
        </ul>
      </div>
    </div>

    <div class="container footer-bottom">
      <div>&copy; 2026 PEKES Pet Shop · RUT 1006071451. Todos los derechos reservados. Cali, Colombia.</div>
      <div>Los precios publicados son informativos y se confirman por WhatsApp. Sin pasarela de pago activa.</div>
    </div>
  </footer>

  <!-- ==========================================================================
       Modal de Ficha Técnica de Producto
       ========================================================================== -->
  <div class="modal-overlay" id="product-modal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-content">
      <button class="modal-close-btn" id="modal-close-btn" aria-label="Cerrar ficha de producto">&times;</button>

      <div class="modal-grid">
        <div class="modal-gallery">
          <img src="" id="modal-img" alt="" />
        </div>

        <div class="modal-details">
          <div class="modal-tag-row">
            <span class="modal-tag" id="modal-cat">Gimnasios de piso</span>
            <span class="modal-tag" id="modal-sku">SKU: PEK-000</span>
            <span class="modal-tag" style="background: var(--color-bg); color: var(--color-text-main); border-color: var(--color-border);">Por encargo</span>
          </div>

          <h2 class="modal-title" id="modal-title">Nombre Producto</h2>

          <div class="modal-price-box">
            <div class="modal-price-label">Precio publicado para Cali</div>
            <div class="modal-price-val" id="modal-price-val">$0</div>
          </div>

          <p id="modal-desc" style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.65;"></p>

          <div class="modal-info-list">
            <div class="info-row">
              <strong>Dimensiones:</strong>
              <span id="modal-dims">-</span>
            </div>
            <div class="info-row">
              <strong>Disponibilidad:</strong>
              <span>Por encargo. Sujeto a disponibilidad de fábrica.</span>
            </div>
            <div class="info-row">
              <strong>Tonalidades:</strong>
              <span>Sujetas a disponibilidad; se confirman por WhatsApp.</span>
            </div>
          </div>

          <!-- Componentes para Combos de Pared -->
          <div class="includes-box" id="modal-includes-box">
            <h5>Lo que incluye este combo:</h5>
            <ul class="includes-list" id="modal-includes-list"></ul>
          </div>

          <!-- Botón de WhatsApp -->
          <a href="#" class="btn-wa-master" id="modal-btn-wa" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24">
              <path
                d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.966.528 1.884.813 2.796.813h.005c3.179 0 5.767-2.587 5.767-5.766.001-3.182-2.587-5.768-5.772-5.768zm4.493 8.358c-.188.528-.962 1.014-1.393 1.047-.384.029-.868.044-2.613-.679-1.921-.796-3.14-2.756-3.234-2.883-.095-.128-.772-1.028-.772-1.959s.489-1.386.662-1.576c.174-.189.378-.236.504-.236.126 0 .252.002.362.007.116.006.27-.044.423.324.157.378.536 1.309.583 1.404.047.095.079.206.016.332-.063.127-.095.206-.189.317-.095.111-.2.247-.286.332-.095.095-.194.198-.083.388.111.19.493.813 1.057 1.316.726.647 1.339.847 1.528.942.19.095.3.079.412-.048.111-.127.475-.555.602-.745.127-.19.253-.159.427-.095.174.063 1.107.523 1.297.618.19.095.317.143.364.222.047.079.047.459-.141.987z" />
            </svg>
            CONSULTAR Y COMPRAR POR WHATSAPP
          </a>

          <a href="#" class="modal-permalink" id="modal-permalink" target="_blank" rel="noopener">
            <span>Ver página individual independiente de este producto</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
              <polyline points="15 3 21 3 21 9"></polyline>
              <line x1="10" y1="14" x2="21" y2="3"></line>
            </svg>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       Modal Legal (Términos, Privacidad, Garantías)
       ========================================================================== -->
  <div class="modal-overlay" id="legal-modal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-content legal-modal-content">
      <button class="modal-close-btn" id="legal-close-btn" aria-label="Cerrar ventana legal">&times;</button>

      <div class="legal-header">
        <span class="badge" style="margin-bottom: 0.5rem; display: inline-block;">Marco Legal Colombiano</span>
        <h2 id="legal-modal-title">Políticas y Términos Comerciales</h2>
        <p style="font-size: 0.82rem; color: var(--color-text-muted); margin-top: 0.25rem;">
          PEKES Pet Shop · Persona Natural RUT 1006071451 · Cali, Colombia
        </p>
      </div>

      <div class="legal-nav-tabs" role="tablist">
        <button class="legal-tab-btn active" data-target="pane-privacidad" role="tab">Tratamiento de Datos</button>
        <button class="legal-tab-btn" data-target="pane-terminos" role="tab">Términos y Condiciones</button>
        <button class="legal-tab-btn" data-target="pane-garantias" role="tab">Envíos, Instalación & Garantía</button>
      </div>

      <!-- Pestaña 1: Tratamiento de Datos -->
      <div class="legal-pane active" id="pane-privacidad" role="tabpanel">
        <div class="legal-alert-box">
          <strong>Protección de Datos Personales (Habeas Data):</strong> En cumplimiento de la Ley 1581 de 2012 y el
          Decreto 1377 de 2013, PEKES garantiza la confidencialidad y el uso exclusivo de tus datos para la cotización,
          despacho y soporte de tu pedido.
        </div>

        <h3>1. Responsable del Tratamiento</h3>
        <p>El responsable del tratamiento de los datos personales es la titularidad de <strong>PEKES Pet Shop</strong>
          (Persona Natural identificada con RUT No. <strong>1006071451</strong>), con domicilio principal en Santiago de
          Cali, Colombia. Canal oficial de atención de datos: <code>pekespetshop2026@gmail.com</code>.</p>

        <h3>2. Datos que se recolectan</h3>
        <p>Para la debida atención comercial, cotización y entrega física de los productos, PEKES podrá solicitar a
          través de WhatsApp o correo electrónico:</p>
        <ul>
          <li>Nombre y apellidos del comprador o persona autorizada para recibir.</li>
          <li>Cédula de ciudadanía o NIT (para remisiones de entrega y facturación).</li>
          <li>Dirección exacta de entrega y número de contacto telefónico / WhatsApp.</li>
          <li>Correo electrónico para envío de constancias y garantías.</li>
        </ul>
        <p><strong>Nota de seguridad:</strong> PEKES no recolecta, procesa ni almacena números de tarjetas de crédito o
          contraseñas bancarias a través del sitio web.</p>

        <h3>3. Finalidad de la recolección</h3>
        <ul>
          <li>Gestionar y dar respuesta a consultas comerciales y cotizaciones iniciadas por el usuario.</li>
          <li>Coordinar el servicio de instalación técnica en la ciudad de Cali.</li>
          <li>Despachar pedidos a nivel nacional mediante empresas transportadoras autorizadas.</li>
          <li>Hacer seguimiento al servicio posventa y brindar cobertura de garantía directa de 6 meses.</li>
        </ul>

        <h3>4. Derechos del titular (Habeas Data)</h3>
        <p>Conforme al artículo 8 de la Ley 1581 de 2012, el titular podrá en cualquier momento conocer, actualizar,
          rectificar o solicitar la supresión de sus datos personales enviando una comunicación al correo
          <code>pekespetshop2026@gmail.com</code> con el asunto "Habeas Data PEKES". El plazo de respuesta legal es de
          máximo 15 días hábiles.
        </p>
      </div>

      <!-- Pestaña 2: Términos y Condiciones -->
      <div class="legal-pane" id="pane-terminos" role="tabpanel">
        <div class="legal-alert-box">
          <strong>Modelo Catálogo a WhatsApp:</strong> Este sitio web opera como catálogo y vitrina virtual informativa.
          El acuerdo comercial definitivo y la confirmación del pago se realizan de mutuo acuerdo a través de WhatsApp.
        </div>

        <h3>1. Naturaleza del Sitio Web</h3>
        <p>El portal web de PEKES tiene como función exhibir el catálogo de mobiliario felino, rascadores y accesorios.
          No cuenta con pasarela de pago automático ni cobros directos en línea. Toda transacción comercial se concreta
          de forma manual y personalizada por WhatsApp.</p>

        <h3>2. Precios e Inventario</h3>
        <p>Todos los precios se encuentran expresados en pesos colombianos (COP). Los precios exhibidos son
          orientativos; el valor final, disponibilidad de acabados y costos de transporte se confirman en la
          conversación de WhatsApp previa al pago. Los productos con etiqueta de precio pendiente se cotizan
          directamente a solicitud del usuario.</p>

        <h3>3. Perfeccionamiento del Contrato de Compraventa</h3>
        <p>La relación de compraventa a distancia (conforme al Estatuto del Consumidor - Ley 1480 de 2011) se
          perfecciona cuando el cliente confirma la referencia, características acordadas y efectúa el pago por los
          canales bancarios autorizados por PEKES.</p>
      </div>

      <!-- Pestaña 3: Envíos, Instalación & Garantía -->
      <div class="legal-pane" id="pane-garantias" role="tabpanel">
        <div class="legal-alert-box">
          <strong>Transparencia en Envíos & Garantía:</strong> En Cali los combos de pared incluyen instalación técnica.
          El costo del flete/envío (local y nacional) es asumido por el cliente. Todos nuestros gimnasios cuentan con 6
          meses de garantía.
        </div>

        <h3>1. Costos de Envío (Cali y Nacional)</h3>
        <ul>
          <li><strong>El envío lo asume el cliente:</strong> Tanto para entregas locales en Cali como para despachos al
            resto del país, el costo del transporte/flete corre por cuenta del comprador y se liquida según la dirección
            de entrega.</li>
          <li><strong>Envíos Nacionales:</strong> Se realizan con empresas transportadoras legalmente autorizadas con
            guía de rastreo. El producto viaja debidamente embalado y protegido. <em>Los despachos fuera de Cali no
              incluyen servicio de instalación presencial.</em></li>
        </ul>

        <h3>2. Servicio de Instalación en Cali</h3>
        <ul>
          <li><strong>Combos de pared en Cali:</strong> La compra de combos modulares incluye el servicio técnico de
            instalación en muros estructurales (ladrillo, concreto o bloque).</li>
          <li><strong>Zonas especiales:</strong> Sectores periféricos o de acceso especial en Cali pueden generar un
            valor adicional de viático técnico, informado y aprobado previamente por WhatsApp.</li>
          <li><strong>Requisitos:</strong> El cliente debe garantizar que el muro seleccionado se encuentre despejado y
            libre de tuberías de agua o redes eléctricas.</li>
        </ul>

        <h3>3. Política de Garantía de 6 Meses</h3>
        <p>PEKES otorga una <strong>garantía legal de seis (6) meses</strong> a partir de la fecha de entrega sobre
          todos sus gimnasios y mobiliario:</p>
        <ul>
          <li><strong>Qué cubre:</strong> Defectos de fabricación en el ensamble, desajustes estructurales del Triplex
            de Pino de 12 mm, fallas en la fijación de rascadores y defectos en domos acrílicos.</li>
          <li><strong>Exclusiones:</strong> Daños por humedad o intemperie no techada, sobrepeso humano, manipulaciones
            o perforaciones no autorizadas, y el desgaste normal de la cabuya por el uso diario de rascado de las
            mascotas.</li>
        </ul>
        <p>Para activar una garantía, comunícate a nuestro WhatsApp o al correo <code>pekespetshop2026@gmail.com</code>
          adjuntando foto o video del producto y soporte de compra.</p>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       Popup Mascota PEKES
       ========================================================================== -->
  <aside class="cat-peeker" id="cat-peeker" aria-label="Asistente interactivo PEKES">
    <div class="cat-bubble" id="cat-bubble">
      <button class="cat-bubble-close" id="cat-bubble-close" aria-label="Minimizar mensaje">&times;</button>
      <div class="cat-bubble-badge">
        <svg viewBox="0 0 24 24">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        Asistente PEKES
      </div>
      <p class="cat-bubble-text">
        <strong>Bienvenido a PEKES.</strong><br />
        ¿Tienes dudas con las medidas o el gimnasio ideal para tu espacio?
      </p>
      <a href="https://wa.me/<?php echo e($wa_number); ?>?text=Hola%2C%20PEKES.%20Vi%20al%20gatito%20en%20su%20web%20y%20quisiera%20asesor%C3%ADa%20para%20elegir%20el%20gimnasio%20ideal."
        class="cat-bubble-btn" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24">
          <path
            d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.966.528 1.884.813 2.796.813h.005c3.179 0 5.767-2.587 5.767-5.766.001-3.182-2.587-5.768-5.772-5.768zm4.493 8.358c-.188.528-.962 1.014-1.393 1.047-.384.029-.868.044-2.613-.679-1.921-.796-3.14-2.756-3.234-2.883-.095-.128-.772-1.028-.772-1.959s.489-1.386.662-1.576c.174-.189.378-.236.504-.236.126 0 .252.002.362.007.116.006.27-.044.423.324.157.378.536 1.309.583 1.404.047.095.079.206.016.332-.063.127-.095.206-.189.317-.095.111-.2.247-.286.332-.095.095-.194.198-.083.388.111.19.493.813 1.057 1.316.726.647 1.339.847 1.528.942.19.095.3.079.412-.048.111-.127.475-.555.602-.745.127-.19.253-.159.427-.095.174.063 1.107.523 1.297.618.19.095.317.143.364.222.047.079.047.459-.141.987z" />
        </svg>
        <span>Asesorarme por WhatsApp</span>
      </a>
    </div>

    <div class="cat-character-wrap" id="cat-character-wrap" role="button" tabindex="0"
      aria-label="Abrir mensaje de la mascota PEKES">
      <img src="/images/logos/mascota_cropped.png" alt="Mascota gato de PEKES" class="cat-character-img" />
      <span class="cat-character-badge">Hola</span>
    </div>
  </aside>

  <!-- ==========================================================================
       Botón Flotante Global de WhatsApp
       ========================================================================== -->
  <a href="https://wa.me/<?php echo e($wa_number); ?>?text=Hola%2C%20PEKES.%20Tengo%20una%20pregunta%20general%20sobre%20los%20gimnasios%20para%20gatos."
    class="floating-wa-btn" id="floating-wa-btn" target="_blank" rel="noopener"
    aria-label="Contactar a PEKES por WhatsApp">
    <svg viewBox="0 0 24 24">
      <path
        d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.966.528 1.884.813 2.796.813h.005c3.179 0 5.767-2.587 5.767-5.766.001-3.182-2.587-5.768-5.772-5.768zm4.493 8.358c-.188.528-.962 1.014-1.393 1.047-.384.029-.868.044-2.613-.679-1.921-.796-3.14-2.756-3.234-2.883-.095-.128-.772-1.028-.772-1.959s.489-1.386.662-1.576c.174-.189.378-.236.504-.236.126 0 .252.002.362.007.116.006.27-.044.423.324.157.378.536 1.309.583 1.404.047.095.079.206.016.332-.063.127-.095.206-.189.317-.095.111-.2.247-.286.332-.095.095-.194.198-.083.388.111.19.493.813 1.057 1.316.726.647 1.339.847 1.528.942.19.095.3.079.412-.048.111-.127.475-.555.602-.745.127-.19.253-.159.427-.095.174.063 1.107.523 1.297.618.19.095.317.143.364.222.047.079.047.459-.141.987z" />
    </svg>
  </a>

  <!-- ==========================================================================
       Toast Eventos Analytics
       ========================================================================== -->
  <div class="toast" id="analytics-toast">
    <div>
      <strong id="toast-message" style="display: block; margin-bottom: 0.25rem;"></strong>
      <pre id="toast-payload"
        style="font-size: 0.7rem; font-family: monospace; background: rgba(255,255,255,0.08); padding: 0.4rem; border-radius: 4px; display: none;"></pre>
    </div>
  </div>

  <!-- Inyección de Datos para JavaScript Autónomo -->
  <script>
    window.PEKES_PRODUCTS = <?php echo json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    window.PEKES_WHATSAPP = <?php echo json_encode($wa_number); ?>;
    window.PEKES_SITE_URL = 'https://www.pekes.shop';
  </script>

  <script src="/assets/js/app.js?v=<?php echo $js_version; ?>"></script>
</body>

</html>

