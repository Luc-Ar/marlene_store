<?php
require_once __DIR__ . '/config/Database.php';
$conexion = Database::getConexion();

// Traer TODOS los productos activos siempre
$stmt = $conexion->prepare("SELECT * FROM productos WHERE activo = 1 ORDER BY orden_display ASC, id ASC");
$stmt->execute();
$resultado = $stmt->get_result();

$productos = [];
while ($fila = $resultado->fetch_assoc()) {
  $productos[] = $fila;
}


?>
<?php include __DIR__ . '/includes/carrito-panel.php'; ?>
<script src="assets/js/catalogo.js"></script>
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
  <title>Catálogo — Marlene STORE</title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Montserrat:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
  <style>
    .carrito-panel {
      right: -420px !important;
    }

    .carrito-overlay {
      opacity: 0 !important;
      pointer-events: none !important;
    }
  </style>
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="assets/css/catalogo.css">
  <style>
    .carrito-panel {
      right: -420px !important;
    }

    .carrito-overlay {
      opacity: 0 !important;
      pointer-events: none !important;
    }
  </style>
</head>

<body>

  <div class="cat-header">
    <div class="cat-header-inner">
      <p class="cat-breadcrumb">
        <a href="index.php">INICIO</a> <span>›</span>
        <span>CATÁLOGO</span>
      </p>
      <h1 class="cat-header-title">Catálogo</h1>

      <div class="filtros" id="filtros-container">
        <button class="filtro-btn activo" data-seccion="todos">Todos</button>
        <?php
        $stmtCats = $conexion->prepare("SELECT id, nombre, slug, icono FROM categorias WHERE activo = 1 ORDER BY orden_display ASC");
        $stmtCats->execute();
        $cats = $stmtCats->get_result();
        while ($cat = $cats->fetch_assoc()):
        ?>
          <button class="filtro-btn" data-seccion="sec-<?= htmlspecialchars($cat['slug']) ?>">
            <?= $cat['icono'] ? $cat['icono'] . ' ' : '' ?><?= htmlspecialchars($cat['nombre']) ?>
          </button>
        <?php endwhile; ?>
      </div>
    </div>
  </div>

  <?php
  // Reiniciar consulta de categorías
  $stmtCats2 = $conexion->prepare("SELECT id, nombre, slug, icono FROM categorias WHERE activo = 1 ORDER BY orden_display ASC");
  $stmtCats2->execute();
  $cats2 = $stmtCats2->get_result();

  while ($cat = $cats2->fetch_assoc()):
    $secId = 'sec-' . $cat['slug'];
    $hay = false;
  ?>
    <section class="catalogo-section" id="<?= $secId ?>">
      <div class="sub-titulo">
        <h2><?= $cat['icono'] ? $cat['icono'] . ' ' : '' ?><?= htmlspecialchars($cat['nombre']) ?></h2>
      </div>
      <div class="catalogo-grid">
        <?php foreach ($productos as $p):
          // Buscar si el producto pertenece a esta categoría
          if ((int)$p['categoria'] !== (int)$cat['id']) continue;
          $hay = true;
        ?>
          <div class="cat-prod-card">
            <a href="producto.php?id=<?= $p['id'] ?>" class="cat-prod-img">
              <img src="<?= htmlspecialchars($p['imagen_principal'] ?? '') ?>"
                alt="<?= htmlspecialchars($p['nombre']) ?>">
            </a>
            <div class="cat-prod-body">
              <p class="cat-prod-sub"><?= htmlspecialchars($cat['nombre']) ?></p>
              <h3 class="cat-prod-name"><?= htmlspecialchars($p['nombre']) ?></h3>
              <p class="cat-prod-desc"><?= htmlspecialchars($p['descripcion_corta'] ?? '') ?></p>
              <div class="cat-prod-foot">
                <span class="cat-prod-precio">$<?= number_format($p['precio'], 0, ',', '.') ?></span>
                <a href="producto.php?id=<?= $p['id'] ?>" class="cat-prod-btn-agregar">Ver producto</a>
                <div><button class="cat-prod-btn-agregar"
                    onclick="agregarAlCarrito(this, '<?= addslashes($p['nombre']) ?>', '<?= $p['imagen_principal'] ?>', '<?= addslashes($cat['nombre']) ?>', <?= $p['precio'] ?>)">
                    + Agregar
                  </button></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (!$hay): ?>
          <p style="color:#999; padding: 20px; grid-column: 1/-1;">No hay productos en esta categoría aún.</p>
        <?php endif; ?>
      </div>
    </section>
  <?php endwhile; ?>

  <script src="assets/js/catalogo.js"></script>
  <script>
    document.body.style.visibility = 'visible';
  </script>
  <?php include __DIR__ . '/includes/carrito-panel.php'; ?>
  <script src="assets/js/catalogo.js"></script>
</body>

</html>