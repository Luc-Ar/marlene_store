<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <style>
    body {
      visibility: hidden;
    }
  </style>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <?php
  // ─── Valores por defecto ───
  // Si la página no define estas variables, se usan estos valores genéricos
  $meta_titulo      = $titulo ?? 'Marlene STORE';
  $meta_descripcion = $meta_descripcion ?? 'Mochilas, termos, calzado, bazar y tecnología para toda la familia. Comprá online y recibí en todo el país.';
  $og_titulo        = $og_titulo ?? $meta_titulo;
  $og_descripcion   = $og_descripcion ?? $meta_descripcion;
  $og_imagen        = $og_imagen ?? 'https://marlene-store.com.ar/assets/img/MS2.png';
  $og_url           = $og_url ?? 'https://marlene-store.com.ar' . ($_SERVER['REQUEST_URI'] ?? '/');
  ?>

  <title><?= htmlspecialchars($meta_titulo) ?></title>

  <!-- Meta tags para Google -->
  <meta name="description" content="<?= htmlspecialchars($meta_descripcion) ?>">
  <meta name="robots" content="index, follow">

  <!-- Open Graph — para WhatsApp, Facebook, Instagram -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Marlene STORE">
  <meta property="og:url" content="<?= htmlspecialchars($og_url) ?>">
  <meta property="og:title" content="<?= htmlspecialchars($og_titulo) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($og_descripcion) ?>">
  <meta property="og:image" content="<?= htmlspecialchars($og_imagen) ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:locale" content="es_AR">

  <!-- Twitter Card — para Twitter/X -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($og_titulo) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($og_descripcion) ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($og_imagen) ?>">

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="/assets/img/favicon/favicon.ico">
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/assets/img/favicon/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/img/favicon/apple-touch-icon.png">
  <link rel="manifest" href="/assets/img/favicon/site.webmanifest">
  <meta name="theme-color" content="#a06a3a">

  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Montserrat:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/styles.css">
  <?php if (!empty($css_extra)): foreach ($css_extra as $css): ?>
      <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
  <?php endforeach;
  endif; ?>
  <?php if (!empty($estilos_extra)): ?>
    <style>
      <?= $estilos_extra ?>
    </style>
  <?php endif; ?>
</head>

<body>
  <?php if (empty($sin_nav)): ?>
    <?php require_once __DIR__ . '/nav.php'; ?>
  <?php endif; ?>