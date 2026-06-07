<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.php');
  exit;
}

require_once '../autoload.php';

try {
  $db = Database::getConexion();
  $clienteRepo = new ClienteRepository($db);
  $busqueda = trim($_GET['buscar'] ?? '');
  $clientes = $clienteRepo->listarClientes($busqueda);
} catch (Exception $e) {
  error_log("Error en clientes.php: " . $e->getMessage());
  $clientes = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clientes — Marlene Store</title>
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
      max-width: 400px;
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

    .avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #5C3D3E;
      color: #FAF6F1;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.85rem;
      font-weight: 700;
    }

    .badge-activo {
      background: #DCFCE7;
      color: #166534;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.58rem;
      font-weight: 700;
      text-transform: uppercase;
    }

    .btn-wa {
      color: #25D366;
      text-decoration: none;
      font-weight: 700;
      font-size: 0.75rem;
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
      <a href="productos.php" class="nav-item">🎒 Productos</a>
      <a href="categorias.php" class="nav-item">📁 Categorías</a>
      <p class="nav-section">Ventas</p>
      <a href="pedidos.php" class="nav-item">📦 Pedidos</a>
      <a href="clientes.php" class="nav-item activo">👥 Clientes</a>
      <p class="nav-section">Tienda</p>
      <a href="../index.php" class="nav-item" target="_blank">🌐 Ver tienda</a>
    </nav>
    <div class="sidebar-footer">
      <a href="logout.php" class="btn-logout-sidebar">🚪 Cerrar sesión</a>
    </div>
  </div>

  <div class="main">
    <div class="page-header">
      <h2>Clientes</h2>
      <p>Historial y gestión de contactos</p>
    </div>

    <form method="GET" class="search-area">
      <input type="text" name="buscar" class="search-input"
        placeholder="Nombre, teléfono o email..."
        value="<?= htmlspecialchars($busqueda) ?>">
      <button type="submit" class="btn-buscar">Buscar</button>
      <?php if ($busqueda): ?>
        <a href="clientes.php" style="color:#999;text-decoration:none;font-size:0.7rem;font-weight:700;">LIMPIAR</a>
      <?php endif; ?>
    </form>

    <div class="tabla-wrap">
      <table>
        <thead>
          <tr>
            <th></th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Pedidos</th>
            <th>Total gastado</th>
            <th>Estado</th>
            <th>Registro</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($clientes)): ?>
            <?php foreach ($clientes as $c): ?>
              <tr>
                <td>
                  <div class="avatar">
                    <?= strtoupper(substr($c['nombre'] ?? '?', 0, 1) . substr($c['apellido'] ?? '', 0, 1)) ?>
                  </div>
                </td>
                <td><strong><?= htmlspecialchars(trim($c['nombre'] . ' ' . $c['apellido'])) ?></strong></td>
                <td style="font-size:0.78rem;"><?= htmlspecialchars($c['email'] ?? '') ?></td>
                <td>
                  <?php if (!empty($c['telefono'])): ?>
                    <a href="https://wa.me/54<?= preg_replace('/[^0-9]/', '', $c['telefono']) ?>"
                      target="_blank" class="btn-wa">
                      📱 <?= htmlspecialchars($c['telefono']) ?>
                    </a>
                  <?php else: ?>
                    <span style="color:#ccc;">—</span>
                  <?php endif; ?>
                </td>
                <td style="text-align:center;"><?= (int)($c['total_pedidos'] ?? 0) ?></td>
                <td><strong>$<?= number_format($c['total_gastado'] ?? 0, 0, ',', '.') ?></strong></td>
                <td>
                  <span class="badge-activo">
                    <?= $c['activo'] ? 'Activo' : 'Inactivo' ?>
                  </span>
                </td>
                <td style="font-size:0.75rem;color:#999;">
                  <?= isset($c['fecha_registro']) ? date('d/m/Y', strtotime($c['fecha_registro'])) : '—' ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" style="text-align:center;padding:40px;color:#999;">No se encontraron clientes.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>