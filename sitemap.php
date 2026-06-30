<?php
ob_start();
require_once __DIR__ . '/config/Database.php';
$conexion = Database::getConexion();

$base = 'https://marlene-store.com.ar';
$hoy  = date('Y-m-d');

$stmt = $conexion->prepare("SELECT slug FROM categorias WHERE activo = 1 ORDER BY orden_display ASC");
$stmt->execute();
$categorias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conexion->prepare("SELECT id FROM productos WHERE activo = 1 ORDER BY id ASC");
$stmt->execute();
$productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

ob_end_clean();

header('Content-Type: application/xml; charset=utf-8');

$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach (
  [
    ['/', 'weekly', '1.0'],
    ['/catalogo.php', 'daily', '0.9'],
  ] as [$loc, $freq, $pri]
) {
  $xml .= "  <url>\n    <loc>{$base}{$loc}</loc>\n    <lastmod>{$hoy}</lastmod>\n    <changefreq>{$freq}</changefreq>\n    <priority>{$pri}</priority>\n  </url>\n";
}

foreach ($categorias as $cat) {
  $slug = htmlspecialchars($cat['slug']);
  $xml .= "  <url>\n    <loc>{$base}/catalogo.php?cat={$slug}</loc>\n    <lastmod>{$hoy}</lastmod>\n    <changefreq>weekly</changefreq>\n    <priority>0.8</priority>\n  </url>\n";
}

foreach ($productos as $p) {
  $xml .= "  <url>\n    <loc>{$base}/producto.php?id={$p['id']}</loc>\n    <lastmod>{$hoy}</lastmod>\n    <changefreq>weekly</changefreq>\n    <priority>0.7</priority>\n  </url>\n";
}

$xml .= '</urlset>';
echo $xml;
