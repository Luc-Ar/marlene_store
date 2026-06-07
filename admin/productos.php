<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.php');
  exit;
}

require_once '../autoload.php';

try {
  $db = Database::getConexion();
  $productoRepo = new ProductoRepository($db);
  $busqueda = $_GET['buscar'] ?? '';
  $ver      = $_GET['ver'] ?? 'activos';
  $cat_id   = $_GET['categoria'] ?? '';
  $filtros  = ['buscar' => $busqueda, 'ver' => $ver, 'categoria' => $cat_id];
  $productos = $productoRepo->listarProductos($filtros);
} catch (Exception $e) {
  error_log("Error en productos.php: " . $e->getMessage());
  $productos = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Productos — Marlene Store</title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Montserrat', sans-serif;
      background: #F2EBE0;
      color: #3A2526;
      display: flex;
      min-height: 100vh;
    }

    .sidebar {
      width: 260px;
      background: #5C3D3E;
      min-height: 100vh;
      position: fixed;
      display: flex;
      flex-direction: column;
    }

    .sidebar-logo {
      padding: 28px 20px;
      text-align: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .logo-script {
      font-family: 'Great Vibes', cursive;
      font-size: 2.6rem;
      color: #FAF6F1;
      display: block;
    }

    .logo-store {
      font-family: 'Montserrat', sans-serif;
      font-weight: 900;
      font-size: 0.65rem;
      letter-spacing: 0.8rem;
      color: #C9A96E;
      text-transform: uppercase;
    }

    .sidebar-nav {
      flex: 1;
      padding: 20px 0;
    }

    .nav-section {
      font-size: 0.55rem;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.3);
      padding: 16px 25px 6px;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 11px 25px;
      color: rgba(255, 255, 255, 0.7);
      text-decoration: none;
      font-size: 0.82rem;
      transition: 0.2s;
      border-left: 4px solid transparent;
    }

    .nav-item:hover,
    .nav-item.activo {
      background: rgba(255, 255, 255, 0.1);
      color: #FAF6F1;
      border-left-color: #C9A96E;
    }

    .sidebar-footer {
      padding: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .btn-logout-sidebar {
      display: block;
      text-align: center;
      padding: 10px;
      background: rgba(255, 255, 255, 0.1);
      color: rgba(255, 255, 255, 0.7);
      text-decoration: none;
      border-radius: 6px;
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      transition: 0.2s;
    }

    .btn-logout-sidebar:hover {
      background: rgba(192, 57, 43, 0.4);
      color: #fff;
    }

    .main {
      margin-left: 260px;
      flex: 1;
      padding: 40px;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 28px;
    }

    .page-header h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem;
      color: #5C3D3E;
    }

    .page-header p {
      font-size: 0.8rem;
      color: #999;
      margin-top: 4px;
    }

    .search-area {
      background: white;
      padding: 16px 20px;
      border-radius: 8px;
      border: 1px solid rgba(200, 152, 154, 0.2);
      margin-bottom: 20px;
      display: flex;
      gap: 12px;
      align-items: center;
    }

    .search-input {
      flex: 1;
      padding: 10px 14px;
      border: 1.5px solid rgba(200, 152, 154, 0.3);
      border-radius: 6px;
      font-family: 'Montserrat', sans-serif;
      font-size: 0.82rem;
    }

    .btn-buscar {
      background: #5C3D3E;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 700;
      font-size: 0.7rem;
      transition: 0.2s;
    }

    .btn-buscar:hover {
      background: #C9A96E;
    }

    .btn-filtro {
      padding: 8px 16px;
      border-radius: 4px;
      text-decoration: none;
      font-size: 0.62rem;
      font-weight: 700;
      text-transform: uppercase;
      border: 1px solid rgba(200, 152, 154, 0.3);
      color: #666;
      background: white;
      transition: 0.2s;
    }

    .btn-filtro.activo {
      background: #5C3D3E;
      color: white;
      border-color: #5C3D3E;
    }

    .btn-nuevo {
      background: #C9A96E;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      font-weight: 700;
      font-size: 0.65rem;
      text-transform: uppercase;
      transition: 0.2s;
    }

    .btn-nuevo:hover {
      background: #5C3D3E;
    }

    .tabla-wrap {
      background: white;
      border-radius: 8px;
      border: 1px solid rgba(200, 152, 154, 0.2);
      overflow: hidden;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead {
      background: #5C3D3E;
    }

    thead th {
      padding: 14px 16px;
      text-align: left;
      font-size: 0.6rem;
      text-transform: uppercase;
      color: #C9A96E;
      letter-spacing: 1px;
    }

    tbody td {
      padding: 14px 16px;
      font-size: 0.82rem;
      border-bottom: 1px solid #F9F5F0;
      vertical-align: middle;
    }

    tbody tr:hover {
      background: #FDFAF8;
    }

    .prod-img-wrap {
      width: 52px;
      height: 52px;
      border-radius: 6px;
      overflow: hidden;
      background: #F9F5F0;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .prod-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }

    .badge {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.58rem;
      font-weight: 700;
      text-transform: uppercase;
    }

    .badge-activo {
      background: #DCFCE7;
      color: #166534;
    }

    .badge-inactivo {
      background: #FEE2E2;
      color: #991B1B;
    }

    .btn-accion {
      padding: 6px 12px;
      font-size: 0.62rem;
      font-weight: 700;
      text-decoration: none;
      border-radius: 4px;
      text-transform: uppercase;
      display: inline-block;
      transition: 0.2s;
      margin-right: 4px;
      border: 1px solid transparent;
    }

    .btn-editar {
      background: #F2EBE0;
      color: #5C3D3E;
      border-color: rgba(200, 152, 154, 0.3);
    }

    .btn-editar:hover {
      background: #e5ddd1;
    }

    .btn-pausar {
      background: #FEE2E2;
      color: #991B1B;
    }

    .btn-activar {
      background: #DCFCE7;
      color: #166534;
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <div class="sidebar-logo">
      <span class="logo-script">Marlene</span>
      <span class="logo-store">Store</span>
    </div>
    <nav class="sidebar-nav">
      <p class="nav-section">Principal</p>
      <a href="index.php" class="nav-item">📊 Dashboard</a>
      <p class="nav-section">Catálogo</p>
      <a href="productos.php" class="nav-item activo">🎒 Productos</a>
      <a href="categorias.php" class="nav-item">📁 Categorías</a>
      <p class="nav-section">Ventas</p>
      <a href="pedidos.php" class="nav-item">📦 Pedidos</a>
      <a href="clientes.php" class="nav-item">👥 Clientes</a>
      <p class="nav-section">Tienda</p>
<a href="../index.php" class="nav-item" target="_blank" rel="noopener noreferrer">
    🌐 Ver tienda
</a>    </nav>
    <div class="sidebar-footer">
      <a href="logout.php" class="btn-logout-sidebar">🚪 Cerrar sesión</a>
    </div>
  </div>

  <div class="main">
    <div class="page-header">
      <div>
        <h2>Productos</h2>
        <p>Gestioná el catálogo e inventario</p>
      </div>
      <a href="producto-nuevo.php" class="btn-nuevo">+ Nuevo producto</a>
    </div>

    <form method="GET" class="search-area">
      <input type="text" name="buscar" class="search-input"
        placeholder="Buscar por nombre o SKU..."
        value="<?= htmlspecialchars($busqueda) ?>">
      <input type="hidden" name="ver" value="<?= htmlspecialchars($ver) ?>">
      <button type="submit" class="btn-buscar">Buscar</button>
      <a href="productos.php?ver=activos" class="btn-filtro <?= $ver === 'activos' ? 'activo' : '' ?>">Activos</a>
      <a href="productos.php?ver=inactivos" class="btn-filtro <?= $ver === 'inactivos' ? 'activo' : '' ?>">Inactivos</a>
      <a href="productos.php" class="btn-filtro <?= !$ver || $ver === 'todos' ? 'activo' : '' ?>">Todos</a>
    </form>

    <?php if (!empty($cat_id)): ?>
      <div style="margin-bottom:16px;padding:10px 16px;background:#FAF6F1;border-left:4px solid #C9A96E;border-radius:4px;font-size:0.8rem;">
        Filtrando por categoría ID #<?= htmlspecialchars($cat_id) ?>
        <a href="productos.php" style="margin-left:12px;color:#C0392B;font-weight:700;">✕ Quitar filtro</a>
      </div>
    <?php endif; ?>

    <div class="tabla-wrap">
      <table>
        <thead>
          <tr>
            <th>Imagen</th>
            <th>Producto</th>
            <th>Categoría</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($productos)): ?>
            <?php foreach ($productos as $p): ?>
              <tr>
                <td>
                  <div class="prod-img-wrap">
                    <?php if (!empty($p['imagen_principal'])): ?>
                      <img src="../<?= htmlspecialchars($p['imagen_principal']) ?>"
                        alt="<?= htmlspecialchars($p['nombre']) ?>"
                        onerror="this.src='../assets/imagenes/default.jpg';">
                    <?php else: ?>
                      <span style="font-size:1.5rem;">🎒</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <strong><?= htmlspecialchars($p['nombre']) ?></strong><br>
                  <small style="color:#999;"><?= htmlspecialchars($p['sku'] ?? '') ?></small>
                </td>
                <td>
                  <span style="font-size:0.72rem;background:#F2EBE0;padding:3px 10px;border-radius:20px;">
                    <?= htmlspecialchars($p['categoria_nombre'] ?? 'Sin categoría') ?>
                  </span>
                </td>
                <td><strong>$<?= number_format($p['precio'], 0, ',', '.') ?></strong></td>
                <td>
                  <span style="<?= (int)$p['stock'] < 5 ? 'color:#C0392B;font-weight:700;' : '' ?>">
                    <?= $p['stock'] ?> u.
                  </span>
                </td>
                <td>
                  <span class="badge <?= $p['activo'] ? 'badge-activo' : 'badge-inactivo' ?>">
                    <?= $p['activo'] ? 'Activo' : 'Pausado' ?>
                  </span>
                </td>
                <td style="white-space:nowrap;">
                  <a href="producto-editar.php?id=<?= $p['id'] ?>" class="btn-accion btn-editar">Editar</a>
                  <?php if ($p['activo']): ?>
                    <a href="cambiar-estado.php?id=<?= $p['id'] ?>&nuevo_estado=0"
                      class="btn-accion btn-pausar"
                      onclick="return confirm('¿Pausar este producto?')">Pausar</a>
                  <?php else: ?>
                    <a href="cambiar-estado.php?id=<?= $p['id'] ?>&nuevo_estado=1"
                      class="btn-accion btn-activar">Activar</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" style="text-align:center;padding:40px;color:#999;">No se encontraron productos.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>