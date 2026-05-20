<?php
// Conexión
require_once __DIR__ . '/config/Database.php';
$conexion = Database::getConexion();

// Categoría seleccionada
$categoria_seleccionada = isset($_GET['cat']) ? $_GET['cat'] : '';

// Consulta con prepared statements
if (isset($_GET['cat'])) {
  $stmt = $conexion->prepare("
        SELECT p.* FROM productos p
        INNER JOIN categorias c ON p.categoria = c.id
        WHERE c.slug = ? AND p.activo = 1
    ");
  $stmt->bind_param("s", $_GET['cat']);
} else {
  $stmt = $conexion->prepare("SELECT * FROM productos WHERE activo = 1");
}

$stmt->execute();
$resultado = $stmt->get_result();

// Cargar array
$productos = [];
while ($fila = $resultado->fetch_assoc()) {
  $productos[] = $fila;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catálogo — Marlene STORE</title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Montserrat:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="assets/css/catalogo.css">
</head>

<body>

  <div class="cat-header">
    <div class="cat-header-inner">
      <p class="cat-breadcrumb">
        <a href="index.php">INICIO</a> <span>›</span>
        <span><?php echo ($categoria_seleccionada) ? strtoupper($categoria_seleccionada) : 'TODOS LOS PRODUCTOS'; ?></span>
      </p>

      <h1 class="cat-header-title">
        <?php echo ($categoria_seleccionada) ? ucfirst($categoria_seleccionada) : 'Catálogo'; ?>
      </h1>

      <div class="filtros" id="filtros-container">
        <button class="filtro-btn activo" onclick="filtrar('todos')">Todos</button>
        <button class="filtro-btn" onclick="filtrar('infantil')">🎒 Infantiles</button>
        <button class="filtro-btn" onclick="filtrar('escolar')">📚 Escolares</button>
        <button class="filtro-btn" onclick="filtrar('adulto')">💼 Adultos</button>
      </div>


      <section class="catalogo-section" id="sec-infantiles">
        <div class="sub-titulo">
          <h2>👶 Infantiles</h2>
        </div>
        <div class="catalogo-grid">
          <?php foreach ($productos as $p): ?>
            <?php if (isset($p['subcategoria']) && strtolower(trim($p['subcategoria'])) == 'escolar'): ?>
              <div class="cat-prod-card">
                <div class="cat-prod-img">
                  <img src="<?= $p['imagen_principal'] ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                </div>
                <div class="cat-prod-body">
                  <p class="cat-prod-sub">Escolar</p>
                  <h3 class="cat-prod-name"><?= htmlspecialchars($p['nombre']) ?></h3>
                  <p class="cat-prod-desc"><?= htmlspecialchars($p['descripcion_corta']) ?></p>
                  <div class="cat-prod-foot">
                    <span class="cat-prod-precio">$<?= number_format($p['precio'], 0, ',', '.') ?></span>
                  <a href="producto.php?id=<?= $p['id'] ?>" class="cat-prod-btn-agregar">Ver producto</a>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>

          <?php if (!$hay_productos): ?>
            <div style="grid-column: 1/-1; background: #ffeeee; padding: 20px; border: 1px solid red;">
              <p style="color: red;">No hay match. Datos detectados en la tabla:</p>
              <pre><?php print_r($productos); ?></pre>
            </div>
          <?php endif; ?>
        </div>
    </div>
    </section>

    <section class="catalogo-section" id="sec-escolares" style="background: var(--crema2);">
      <div class="sub-titulo">
        <h2>📚 Escolares</h2>
      </div>
      <div class="catalogo-grid">
        <?php foreach ($productos as $p): ?>
          <?php if (isset($p['subcategoria']) && strtolower($p['subcategoria']) == 'escolar'): ?>
            <div class="cat-prod-card">
              <div class="cat-prod-img">
                <img src="imágenes/<?= $p['imagen'] ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
              </div>
              <div class="cat-prod-body">
                <p class="cat-prod-sub">Escolar</p>
                <h3 class="cat-prod-name"><?= htmlspecialchars($p['nombre']) ?></h3>
                <p class="cat-prod-desc"><?= htmlspecialchars($p['descripcion']) ?></p>
                <div class="cat-prod-foot">
                  <span class="cat-prod-precio">$<?= number_format($p['precio'], 0, ',', '.') ?></span>
                  <button class="cat-prod-btn-agregar" onclick="agregarAlCarrito(this, '<?= addslashes($p['nombre']) ?>', 'imágenes/<?= $p['imagen'] ?>', 'Escolar', <?= $p['precio'] ?>)">+ Agregar</button>
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
          <?php if (isset($p['subcategoria']) && strtolower(trim($p['subcategoria'])) == 'adulto'): ?>
            <div class="cat-prod-card">
              <div class="cat-prod-img">
                <img src="<?= $p['imagen_principal'] ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
              </div>
              <div class="cat-prod-body">
                <p class="cat-prod-sub">Adulto</p>
                <h3 class="cat-prod-name"><?= htmlspecialchars($p['nombre']) ?></h3>
                <p class="cat-prod-desc"><?= htmlspecialchars($p['descripcion_corta']) ?></p>
                <div class="cat-prod-foot">
                  <span class="cat-prod-precio">$<?= number_format($p['precio'], 0, ',', '.') ?></span>
                  <a href="producto.php?id=<?= $p['id'] ?>" class="cat-prod-btn-agregar">Ver producto</a>
                </div>
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>

    </section>
  </div>
  <script src="assets/js/catalogo.js"></script>
</body>

</html>