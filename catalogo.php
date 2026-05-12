<?php
session_start();
require_once 'config/Database.php';

try {
  $conexion = Database::getConexion();
  // Traemos los productos activos
  $resultado = $conexion->query("SELECT * FROM productos WHERE activo = 1 ORDER BY subcategoria, id DESC");
  $productos = $resultado->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
  die("Error al conectar con el catálogo: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catálogo — Marlene Velazquez STORE</title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Montserrat:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="assets/css/catalogo.css">
</head>

<body>

  <button class="carrito-btn-flotante" onclick="abrirCarrito()">
    🛒 Carrito <span class="carrito-badge" id="carrito-badge">0</span>
  </button>

  <div class="carrito-overlay" id="carrito-overlay" onclick="cerrarCarrito()"></div>
  <div class="carrito-panel" id="carrito-panel">
    <div class="carrito-panel-header">
      <h2>Mi pedido</h2>
      <button class="carrito-cerrar" onclick="cerrarCarrito()">✕</button>
    </div>
    <div class="carrito-items" id="carrito-items"></div>
    <div class="carrito-panel-footer" id="carrito-footer" style="display:none;">
      <p class="carrito-resumen">Total: <span id="carrito-subtotal">$0</span></p>
      <button class="carrito-wa-btn" onclick="enviarPorWhatsapp()">💬 Enviar por WhatsApp</button>
      <button class="btn-vaciar" onclick="vaciarCarrito()">Vaciar carrito</button>
    </div>
  </div>

  <nav>
    <a href="index.php" class="logo-wrap">
      <span class="logo-script">Marlene</span> <span class="logo-store">STORE</span>
    </a>
    <ul class="nav-links">
      <li><a href="index.php#categorias">CATEGORÍAS</a></li>
      <li><a href="#sec-infantiles">PRODUCTOS</a></li>
      <li><a href="index.php#contacto">CONTACTO</a></li>
      <li><a href="https://wa.me/5493704097831" class="nav-cta" target="_blank">CONSULTAR</a></li>
    </ul>
    </div>
  </nav>

  <div class="cat-header">
    <div class="cat-header-inner">
      <p class="cat-breadcrumb">
        <a href="index.php">INICIO</a> <span>›</span> MOCHILAS
      </p>
      <h1 class="cat-header-title">Mochilas</h1>
    </div>
  </div>

  <section class="catalogo-section" id="sec-infantiles">
    <div class="sub-titulo">
      <h2>🎒 Infantiles</h2>
    </div>
    <div class="catalogo-grid">
      <?php foreach ($productos as $p): ?>
        <?php if (strtolower($p['subcategoria']) == 'infantil'): ?>
          <div class="cat-prod-card">
            <div class="cat-prod-img">
              <img src="assets/imagenes/<?= $p['imagen_principal'] ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
            </div>
            <div class="cat-prod-body">
              <p class="cat-prod-sub">Infantil</p>
              <h3 class="cat-prod-name"><?= htmlspecialchars($p['nombre']) ?></h3>
              <p class="cat-prod-desc"><?= htmlspecialchars($p['descripcion_corta']) ?></p>
              <div class="cat-prod-foot">
                <span class="cat-prod-precio">$<?= number_format($p['precio'], 0, ',', '.') ?></span>
                <button class="cat-prod-btn-agregar" onclick="agregarAlCarrito(this, '<?= addslashes($p['nombre']) ?>', 'assets/imagenes/<?= $p['imagen_principal'] ?>', 'Infantil', <?= $p['precio'] ?>)">+ Agregar</button>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="catalogo-section" id="sec-escolares" style="background: var(--crema2);">
    <div class="sub-titulo">
      <h2>📚 Escolares</h2>
    </div>
    <div class="catalogo-grid">
      <?php foreach ($productos as $p): ?>
        <?php if (strtolower($p['subcategoria']) == 'escolar'): ?>
          <div class="cat-prod-card">
            <div class="cat-prod-img">
              <img src="assets/imagenes/<?= $p['imagen_principal'] ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
            </div>
            <div class="cat-prod-body">
              <p class="cat-prod-sub">Escolar</p>
              <h3 class="cat-prod-name"><?= htmlspecialchars($p['nombre']) ?></h3>
              <p class="cat-prod-desc"><?= htmlspecialchars($p['descripcion_corta']) ?></p>
              <div class="cat-prod-foot">
                <span class="cat-prod-precio">$<?= number_format($p['precio'], 0, ',', '.') ?></span>
                <button class="cat-prod-btn-agregar" onclick="agregarAlCarrito(this, '<?= addslashes($p['nombre']) ?>', 'assets/imagenes/<?= $p['imagen_principal'] ?>', 'Escolar', <?= $p['precio'] ?>)">+ Agregar</button>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="catalogo-section" id="sec-adultos">
    <div class="sub-titulo">
      <h2>💼 Adultos</h2>
    </div>
    <div class="catalogo-grid">
      <?php foreach ($productos as $p): ?>
        <?php if (strtolower($p['subcategoria']) == 'adulto'): ?>
          <div class="cat-prod-card">
            <div class="cat-prod-img">
              <img src="assets/imagenes/<?= $p['imagen_principal'] ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
            </div>
            <div class="cat-prod-body">
              <p class="cat-prod-sub">Adulto</p>
              <h3 class="cat-prod-name"><?= htmlspecialchars($p['nombre']) ?></h3>
              <p class="cat-prod-desc"><?= htmlspecialchars($p['descripcion_corta']) ?></p>
              <div class="cat-prod-foot">
                <span class="cat-prod-precio">$<?= number_format($p['precio'], 0, ',', '.') ?></span>
                <button class="cat-prod-btn-agregar" onclick="agregarAlCarrito(this, '<?= addslashes($p['nombre']) ?>', 'assets/imagenes/<?= $p['imagen_principal'] ?>', 'Adulto', <?= $p['precio'] ?>)">+ Agregar</button>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </section>

  <script src="assets/js/catalogo.js"></script>
</body>

</html>