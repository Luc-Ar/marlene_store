<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.php');
  exit;
}

// 1. Cargamos el motor del sistema
require_once '../autoload.php';

try {
  // 2. Instanciamos herramientas
  $db = Database::getConexion();
  $clienteRepo = new ClienteRepository($db);

  // 3. Lógica de negocio simplificada
  $busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
  $clientes = $clienteRepo->listarClientes($busqueda);
} catch (Exception $e) {
  // Si algo falla, el blindaje ya se encarga, pero podemos loguear aquí también
  error_log("Error en clientes.php: " . $e->getMessage());
  $clientes = []; // Evita que el foreach de abajo rompa el HTML
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
    /* --- VARIABLES Y RESET --- */
    :root {
      --sidebar-width: 260px;
      --color-marlene: #5C3D3E;
      --color-dorado: #C9A96E;
    }

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

    /* --- SIDEBAR UNIFICADA --- */
    .sidebar {
      width: var(--sidebar-width);
      background: var(--color-marlene);
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      display: flex;
      flex-direction: column;
      box-shadow: 4px 0 15px rgba(0, 0, 0, 0.2);
      z-index: 1000;
    }

    .sidebar-header {
      padding: 40px 20px;
      text-align: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .logo-script {
      font-family: 'Great Vibes', cursive;
      font-size: 3rem;
      color: #FAF6F1;
      display: block;
      line-height: 1.1;
    }

    .logo-store-text {
      font-family: 'Montserrat', sans-serif;
      font-weight: 900;
      font-size: 0.7rem;
      letter-spacing: 0.8rem;
      color: var(--color-dorado);
      text-transform: uppercase;
      display: block;
      margin-top: 5px;
    }

    .sidebar-nav {
      padding: 20px 0;
      flex: 1;
    }

    .nav-section-title {
      font-size: 0.65rem;
      font-weight: 800;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.3);
      padding: 20px 25px 10px;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 15px 25px;
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      font-size: 0.95rem;
      font-weight: 700;
      transition: all 0.3s;
      border-left: 5px solid transparent;
    }

    .nav-item:hover,
    .nav-item.activo {
      background: rgba(255, 255, 255, 0.1);
      color: #FAF6F1;
      border-left: 5px solid var(--color-dorado);
    }

    .sidebar-footer {
      padding: 25px;
      background: rgba(0, 0, 0, 0.2);
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .user-name-footer {
      display: block;
      font-weight: 800;
      color: var(--color-dorado);
      font-size: 1.1rem;
      margin-bottom: 5px;
    }

    .logout-link-footer {
      color: #FFBABA;
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 700;
    }

    /* --- CONTENIDO PRINCIPAL --- */
    .main {
      margin-left: var(--sidebar-width);
      flex: 1;
      padding: 40px;
    }

    .page-header {
      margin-bottom: 36px;
    }

    .page-header h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2.2rem;
      color: #5C3D3E;
    }

    .search-container {
      margin-bottom: 25px;
      display: flex;
      gap: 10px;
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .search-input {
      flex: 1;
      max-width: 400px;
      padding: 12px;
      border: 1px solid rgba(200, 152, 154, 0.4);
      border-radius: 4px;
      font-family: 'Montserrat';
      outline: none;
    }

    .search-btn {
      background: #5C3D3E;
      color: white;
      border: none;
      padding: 0 25px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 700;
      font-size: 0.75rem;
      text-transform: uppercase;
    }

    .tabla-wrap {
      background: #fff;
      border-radius: 8px;
      border: 1px solid rgba(200, 152, 154, 0.2);
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead {
      background: #5C3D3E;
    }

    thead th {
      padding: 16px;
      text-align: left;
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: #C9A96E;
    }

    tbody tr {
      border-bottom: 1px solid rgba(200, 152, 154, 0.15);
      transition: 0.3s;
    }

    tbody tr:hover {
      background: #FAF6F1;
    }

    tbody td {
      padding: 16px;
      font-size: 0.85rem;
      color: #5C3D3E;
      vertical-align: middle;
    }

    .avatar {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: #5C3D3E;
      color: #FAF6F1;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.9rem;
      font-weight: 700;
    }

    .badge-activo {
      background: #eaf3de;
      color: #3B6D11;
      padding: 4px 12px;
      border-radius: 15px;
      font-size: 0.6rem;
      font-weight: 800;
      text-transform: uppercase;
    }

    .btn-wa {
      color: #25D366;
      text-decoration: none;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .vip-tag {
      color: #C9A96E;
      font-weight: 900;
    }
  </style>
</head>

<body>
  <aside class="sidebar">
    <div class="sidebar-header">
      <span class="logo-script">Marlene</span>
      <span class="logo-store-text">Store</span>
    </div>

    <nav class="sidebar-nav">
      <p class="nav-section-title">Principal</p>
      <a href="index.php" class="nav-item">
        <span class="icon">📊</span> Dashboard
      </a>

      <p class="nav-section-title">Catálogo</p>
      <a href="productos.php" class="nav-item">
        <span class="icon">🎒</span> Productos
      </a>
      <a href="categorias.php" class="nav-item">
        <span class="icon">📁</span> Categorías
      </a>

      <p class="nav-section-title">Ventas</p>
      <a href="pedidos.php" class="nav-item">
        <span class="icon">📦</span> Pedidos
      </a>
      <a href="clientes.php" class="nav-item activo">
        <span class="icon">👥</span> Clientes
      </a>
    </nav>

    <div class="sidebar-footer">
      <span class="user-name-footer"><?= $_SESSION['usuario_nombre'] ?> <?= $_SESSION['usuario_apellido'] ?></span>
      <a href="logout.php" class="logout-link-footer">Cerrar sesión</a>
    </div>
  </aside>

  <div class="main">
    <div class="page-header">
      <h2>Cartera de Clientes</h2>
      <p>Historial y gestión de contactos</p>
    </div>

    <div class="search-container">
      <form method="GET" style="display: flex; gap: 10px; width: 100%;">
        <input type="text" name="buscar" class="search-input"
          placeholder="Nombre, teléfono o email..."
          value="<?= htmlspecialchars($busqueda) ?>">
        <button type="submit" class="search-btn">Buscar</button>
        <?php if ($busqueda): ?>
          <a href="clientes.php" style="align-self:center; font-size:0.7rem; font-weight:700; color:#9E5F62; text-decoration:none;">LIMPIAR</a>
        <?php endif; ?>
      </form>
    </div>

    <div class="tabla-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Pedidos</th>
            <th>Total Gastado</th>
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
                    <?= strtoupper(substr($c['nombre'], 0, 1) . substr($c['apellido'], 0, 1)) ?>
                  </div>
                </td>
                <td><strong><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?></strong></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" style="text-align:center; padding:50px;">No se encontraron clientes</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>