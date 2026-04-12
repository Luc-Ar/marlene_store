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

  $filtros = [
    'buscar'    => $busqueda,
    'ver'       => $ver,
    'categoria' => $cat_id
  ];

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
    /* Estilos base */
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

    /* Sidebar */
    .sidebar {
      width: 240px;
      background: #5C3D3E;
      min-height: 100vh;
      position: fixed;
    }

    .sidebar-logo {
      padding: 28px 24px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .logo-script {
      font-family: 'Great Vibes', cursive;
      font-size: 2.8rem;
      color: #FAF6F1;
      display: block;
      line-height: 1.1;
    }

    .logo-store {
      font-family: 'Montserrat', sans-serif;
      font-weight: 900;
      font-size: 0.65rem;
      letter-spacing: 1.6rem;
      color: #C9A96E;
      text-transform: uppercase;
      display: block;
      margin-top: 2px;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 24px;
      color: rgba(255, 252, 246, 0.7);
      text-decoration: none;
      font-size: 0.75rem;
      transition: 0.2s;
    }

    .nav-item:hover,
    .nav-item.activo {
      background: rgba(255, 255, 255, 0.08);
      color: #FAF6F1;
      border-left: 3px solid #C9A96E;
    }

    /* Main Content */
    .main {
      margin-left: 240px;
      flex: 1;
      padding: 40px;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }

    .page-header h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem;
      color: #5C3D3E;
    }

    /* Buscador y Filtros */
    .search-area {
      background: white;
      padding: 20px;
      border-radius: 4px;
      border: 1px solid rgba(200, 152, 154, 0.2);
      margin-bottom: 25px;
      display: flex;
      gap: 15px;
      align-items: center;
    }

    .search-input {
      flex: 1;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-family: 'Montserrat';
    }

    .btn-filtro {
      padding: 10px 20px;
      border-radius: 4px;
      text-decoration: none;
      font-size: 0.65rem;
      font-weight: 700;
      text-transform: uppercase;
      border: 1px solid #ddd;
      color: #555;
      background: white;
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
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      font-weight: 700;
      font-size: 0.65rem;
      text-transform: uppercase;
    }

    /* Tabla */
    .tabla-wrap {
      background: #fff;
      border-radius: 4px;
      border: 1px solid rgba(200, 152, 154, 0.2);
      overflow: hidden;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead {
      background: #5C3D3E;
      color: #C9A96E;
    }

    thead th {
      padding: 15px 20px;
      text-align: left;
      font-size: 0.6rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    tbody td {
      padding: 15px 20px;
      font-size: 0.85rem;
      border-bottom: 1px solid #f2ebe0;
      vertical-align: middle;
    }

    /* Imagenes */
    .prod-img-container {
      width: 55px;
      height: 55px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f9f9f9;
      border-radius: 4px;
      border: 1px solid #eee;
      overflow: hidden;
    }

    .prod-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* Badges y Botones */
    .badge {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.6rem;
      font-weight: 700;
      text-transform: uppercase;
      display: inline-block;
    }

    .badge-activo {
      background: #eaf3de;
      color: #3B6D11;
      border: 1px solid #d4e5bc;
    }

    .badge-inactivo {
      background: #fef0f0;
      color: #c0392b;
      border: 1px solid #f5c6c6;
    }

    .btn-accion {
      padding: 8px 12px;
      font-size: 0.65rem;
      font-weight: 700;
      text-decoration: none;
      border-radius: 3px;
      text-transform: uppercase;
      display: inline-block;
      transition: 0.2s;
      margin-right: 5px;
    }

    .edit {
      background: #F2EBE0;
      color: #5C3D3E;
      border: 1px solid #ddd;
    }

    .edit:hover {
      background: #e5ddd1;
    }

    .pausar {
      background: #fef0f0;
      color: #c0392b;
      border: 1px solid #f5c6c6;
    }

    .activar {
      background: #eaf3de;
      color: #3B6D11;
      border: 1px solid #d4e5bc;
    }
  </style>
</head>

<body>
  <div class="sidebar">
    <div class="sidebar-logo"><span class="logo-script">Marlene</span><span class="logo-store">Store</span></div>
    <nav class="sidebar-nav">
      <a href="index.php" class="nav-item">📊 Dashboard</a>
      <a href="productos.php" class="nav-item activo">🎒 Productos</a>
      <a href="categorias.php" class="nav-item">📁 Categorías</a>
      <a href="pedidos.php" class="nav-item">📦 Pedidos</a>
      <a href="clientes.php" class="nav-item">👥 Clientes</a>
    </nav>
  </div>

  <div class="main">
    <div class="page-header">
      <div>
        <h2>Catálogo de Productos</h2>
        <p>Administrá el inventario y formatos de imagen</p>
      </div>
      <a href="producto-nuevo.php" class="btn-nuevo">+ Agregar producto</a>
    </div>

    <form method="GET" class="search-area">
      <input type="text" name="buscar" class="search-input" placeholder="Buscar producto o SKU..." value="<?= htmlspecialchars($busqueda) ?>">
      <a href="productos.php?ver=activos" class="btn-filtro <?= $mostrar ? 'activo' : '' ?>">Activos</a>
      <a href="productos.php?ver=inactivos" class="btn-filtro <?= !$mostrar ? 'activo' : '' ?>">Inactivos</a>
      <button type="submit" style="display:none;"></button>
    </form>
    <?php if (!empty($cat_filtro)): ?>
      <div style="margin-bottom: 20px; padding: 10px; background: #FAF6F1; border-left: 4px solid #C9A96E;">
        Filtro activo: <strong>Categoría ID #<?= htmlspecialchars($cat_filtro) ?></strong>
        <a href="productos.php" style="margin-left: 10px; color: #c0392b; font-size: 0.8rem;">(Quitar filtro)</a>
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
                  <div class="prod-img-container">
                    <?php if (!empty($p['imagen_principal'])): ?>
                      <img src="../<?= $p['imagen_principal'] ?>?v=<?= time() ?>" class="prod-img" onerror="this.src='../assets/imagenes/default.jpg';">
                    <?php else: ?>
                      <span style="font-size: 1.5rem;">🎒</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <strong><?= htmlspecialchars($p['nombre']) ?></strong><br>
                  <small style="color:#888"><?= htmlspecialchars($p['sku']) ?></small>
                </td>
                <td>
                  <span style="font-size: 0.75rem; background: #eee; padding: 2px 8px; border-radius: 10px;">
                    <?= htmlspecialchars($p['categoria_nombre'] ?? 'General') ?>
                  </span>
                </td>
                <td><strong>$<?= number_format($p['precio'], 2, ',', '.') ?></strong></td>
                <td>
                  <span style="<?= $p['stock'] < 5 ? 'color:red; font-weight:bold;' : '' ?>">
                    <?= $p['stock'] ?> u.
                  </span>
                </td>
                <td>
                  <span class="badge <?= $p['activo'] ? 'badge-activo' : 'badge-inactivo' ?>">
                    <?= $p['activo'] ? 'Activo' : 'Pausado' ?>
                  </span>
                </td>
                <td style="white-space: nowrap;">
                  <a href="producto-editar.php?id=<?= $p['id'] ?>" class="btn-accion edit">Editar</a>

                  <?php if ($p['activo']): ?>
                    <a href="cambiar-estado.php?id=<?= $p['id'] ?>&nuevo_estado=0"
                      class="btn-accion pausar"
                      onclick="return confirm('¿Pausar producto?')">Pausar</a>
                  <?php else: ?>
                    <a href="cambiar-estado.php?id=<?= $p['id'] ?>&nuevo_estado=1"
                      class="btn-accion activar">Activar</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" style="text-align:center; padding:40px;">No se encontraron productos.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>