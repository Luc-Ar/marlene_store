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
  <title><?= htmlspecialchars($titulo ?? 'Marlene STORE') ?></title>
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