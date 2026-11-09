<?php
/**
 * PEKES — Generador dinámico de Sitemap XML
 * Cumple con el estándar de Google Search Console y Bing Webmaster Tools
 */

require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

$products = get_all_products();

$base_url = 'https://pekes.shop';

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

  <!-- Página Principal / Home -->
  <url>
    <loc><?php echo $base_url; ?>/</loc>
    <lastmod><?php echo date('Y-m-d'); ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>

  <!-- Fichas Individuales de Producto (URLs Indexables) -->
  <?php foreach ($products as $p): 
      $slug = !empty($p['slug']) ? $p['slug'] : slugify($p['name']);
      $loc  = $base_url . '/producto/' . $slug;
      $date = !empty($p['updated_at']) ? date('Y-m-d', strtotime($p['updated_at'])) : date('Y-m-d');
      $img  = !empty($p['image']) ? $p['image'] : '/images/logos/mascota.png';
      $img_url = (strpos($img, 'http') === 0) ? $img : ($base_url . $img);
  ?>
  <url>
    <loc><?php echo htmlspecialchars($loc, ENT_XML1, 'UTF-8'); ?></loc>
    <lastmod><?php echo $date; ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
    <image:image>
      <image:loc><?php echo htmlspecialchars($img_url, ENT_XML1, 'UTF-8'); ?></image:loc>
      <image:title><?php echo htmlspecialchars($p['name'] . ' — PEKES Pet Shop Cali', ENT_XML1, 'UTF-8'); ?></image:title>
    </image:image>
  </url>
  <?php endforeach; ?>

</urlset>

