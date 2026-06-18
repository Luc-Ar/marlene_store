<?php
http_response_code(500);
$titulo = 'Error del servidor — Marlene STORE';
// No intentamos conectar a la DB ni cargar nada complejo
// para evitar loops si el error original fue de conexión
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $titulo ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Montserrat:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>

<nav>
  <a href="/index.php" class="logo-wrap">
    <span class="logo-script">Marlene</span>
    <span class="logo-store">STORE</span>
  </a>
  <ul class="nav-links">
    <li><a href="/index.php#categorias">Categorías</a></li>
    <li><a href="/catalogo.php">Productos</a></li>
    <li><a href="/index.php#envios">Envíos</a></li>
    <li><a href="/index.php#contacto">Contacto</a></li>
  </ul>
</nav>

<section style="
  min-height: 60vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 80px 24px;
">
  <p style="font-size: 6rem; margin: 0; line-height: 1;">⚙️</p>
  <p class="s-eyebrow" style="margin-top: 24px;">✦ Error 500</p>
  <h1 class="s-title" style="margin-top: 8px;">Algo salió mal</h1>
  <p class="s-sub" style="max-width: 480px; margin: 16px auto 40px;">
    Tuvimos un problema interno. Ya estamos trabajando para solucionarlo. 
    Intentá de nuevo en unos minutos o contactanos por WhatsApp.
  </p>
  <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center;">
    <a href="/index.php" class="btn-main">Volver al inicio</a>
    <a href="https://wa.me/5493704097831" class="btn-outline" target="_blank">WhatsApp</a>
  </div>
</section>

<footer>
  <div class="footer-bottom" style="padding: 24px; text-align: center;">
    <p>© <?= date('Y') ?> Marlene STORE — Todos los derechos reservados</p>
  </div>
</footer>

<a href="https://wa.me/5493704097831" class="wa-btn" target="_blank">
  <span class="wa-icon">💬</span>
  <span>WhatsApp</span>
</a>

</body>
</html>
