<?php
session_start();
require_once __DIR__ . '/includes/error-handler.php';
require_once __DIR__ . '/config/Database.php';
$conexion = Database::getConexion();

// Traer todos los productos activos
$stmt = $conexion->prepare("SELECT * FROM productos WHERE activo = 1 ORDER BY orden_display ASC, id ASC");
$stmt->execute();
$productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Variables para header.php
$titulo    = 'Catálogo — Marlene STORE';
$css_extra = ['/assets/css/catalogo.css'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="cat-header">
  <div class="cat-header-inner">
    <p class="cat-breadcrumb">
      <a href="index.php">INICIO</a> <span>›</span>
      <span>CATÁLOGO</span>
    </p>
    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:20px;">
      <h1 class="cat-header-title">CATÁLOGO</h1>
      <span style="font-family:'Montserrat',sans-serif;font-size:0.62rem;color:#999;letter-spacing:1px;">
        <?= count($productos) ?> productos
      </span>
    </div>
    <div class="filtros-bar" id="filtros-container">
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
              <div style="display:flex;gap:6px;">
                <a href="producto.php?id=<?= $p['id'] ?>" class="cat-prod-btn-agregar" style="background:transparent;color:var(--marron);border:1px solid var(--marron);">Ver</a>
                <button class="cat-prod-btn-agregar"
                  onclick="agregarAlCarrito(this, '<?= addslashes($p['nombre']) ?>', '<?= $p['imagen_principal'] ?>', '<?= addslashes($cat['nombre']) ?>', <?= $p['precio'] ?>)">
                  + Agregar
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$hay): ?>
        <p style="color:#999;padding:20px;grid-column:1/-1;">No hay productos en esta categoría aún.</p>
      <?php endif; ?>
    </div>
  </section>
<?php endwhile; ?>

<?php
$scripts_extra = ['/assets/js/catalogo.js'];
require_once __DIR__ . '/includes/footer.php';
