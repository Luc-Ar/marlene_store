<?php
session_start();

// 1. Errores para debuggear
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Verificación de sesión
if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.php');
  exit;
}

// 3. Conexión
require_once __DIR__ . '/../config/Database.php';

try {
  $conexion = Database::getConexion();

  // --- CONSULTAS DEL DASHBOARD ---

  // Total Productos
  $res_p = $conexion->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
  $total_productos = $res_p ? $res_p->fetch_assoc()['total'] : 0;

  // Total Clientes
  $res_c = $conexion->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1");
  $total_clientes = $res_c ? $res_c->fetch_assoc()['total'] : 0;

  // Total Pedidos y Pedidos Hoy
  $res_ped = $conexion->query("SELECT COUNT(*) as total FROM pedidos");
  $total_pedidos = $res_ped ? $res_ped->fetch_assoc()['total'] : 0;

  $res_hoy = $conexion->query("SELECT COUNT(*) as total FROM pedidos WHERE DATE(fecha_pedido) = CURDATE()");
  $pedidos_hoy = $res_hoy ? $res_hoy->fetch_assoc()['total'] : 0;

  // Recaudación real (Solo lo confirmado/pagado/entregado)
  $res_ventas_real = $conexion->query("SELECT SUM(total) as total FROM pedidos WHERE estado IN ('confirmado', 'enviado', 'entregado')");
  $dinero_total = $res_ventas_real ? ($res_ventas_real->fetch_assoc()['total'] ?? 0) : 0;

  // Pedidos Pendientes (Para la tarjeta de alerta)
  $res_pen = $conexion->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'");
  $total_pendientes = $res_pen ? $res_pen->fetch_assoc()['total'] : 0;

  // Últimos Pedidos con nombre de cliente
  $ultimos_pedidos = $conexion->query("
        SELECT p.*, CONCAT(c.nombre, ' ', c.apellido) as cliente 
        FROM pedidos p 
        LEFT JOIN clientes c ON p.id_cliente = c.id 
        ORDER BY p.fecha_pedido DESC LIMIT 5
    ");

  // Stock Bajo
  $stock_bajo = $conexion->query("SELECT nombre, stock FROM productos WHERE stock < 5 AND activo = 1 ORDER BY stock ASC LIMIT 5");

  $colores = [
    'pendiente'   => '#E67E22', // Naranja
    'confirmado'   => '#27AE60', // Verde
    'en_preparacion' => '#2980B9', // Azul
    'enviado'     => '#8E44AD', // Violeta
    'demorado'       => '#F1C40F', // Amarillo
    'entregado'   => '#2C3E50', // Gris oscuro
    'cancelado'   => '#C0392B'  // Rojo
  ];
} catch (Exception $e) {
  die("Error crítico en Base de Datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Marlene Store</title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
  <style>
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

    /* SIDEBAR */
    .sidebar {
      width: var(--sidebar-width);
      background: var(--color-marlene);
      height: 100vh;
      position: fixed;
      display: flex;
      flex-direction: column;
    }

    .sidebar-logo {
      padding: 30px 20px;
      text-align: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .logo-script {
      font-family: 'Great Vibes', cursive;
      font-size: 2.8rem;
      color: #FAF6F1;
      display: block;
    }

    .logo-store {
      font-family: 'Montserrat', sans-serif;
      font-weight: 900;
      font-size: 0.7rem;
      letter-spacing: 1rem;
      color: var(--color-dorado);
      text-transform: uppercase;
    }

    .sidebar-nav {
      flex: 1;
      padding: 20px 0;
    }

    .nav-section {
      font-size: 0.6rem;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.3);
      padding: 15px 25px 5px;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 25px;
      color: rgba(255, 255, 255, 0.7);
      text-decoration: none;
      font-size: 0.85rem;
      transition: 0.3s;
    }

    .nav-item:hover,
    .nav-item.activo {
      background: rgba(255, 255, 255, 0.1);
      color: #FAF6F1;
      border-left: 4px solid var(--color-dorado);
    }

    /* MAIN */
    .main {
      margin-left: var(--sidebar-width);
      flex: 1;
      padding: 40px;
    }

    .welcome {
      background: var(--color-marlene);
      border-radius: 12px;
      padding: 30px;
      color: #FAF6F1;
      margin-bottom: 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 10px 30px rgba(92, 61, 62, 0.2);
    }

    .welcome h3 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem;
    }

    /* GRID */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      margin-bottom: 30px;
    }

    .card {
      background: #fff;
      border-radius: 8px;
      padding: 20px;
      border-left: 4px solid var(--color-dorado);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }

    .card-label {
      font-size: 0.65rem;
      font-weight: 700;
      color: #888;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .card-number {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem;
      font-weight: 700;
      color: var(--color-marlene);
      margin-top: 5px;
    }

    .dashboard-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 30px;
    }

    .panel {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    }

    .panel h4 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.5rem;
      margin-bottom: 20px;
      color: var(--color-marlene);
      display: flex;
      justify-content: space-between;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      text-align: left;
      font-size: 0.65rem;
      text-transform: uppercase;
      color: var(--color-dorado);
      padding-bottom: 15px;
      border-bottom: 1px solid #F2EBE0;
    }

    td {
      padding: 15px 0;
      font-size: 0.85rem;
      border-bottom: 1px solid #F9F9F9;
    }

    .badge {
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.6rem;
      font-weight: 800;
      color: #fff;
      text-transform: uppercase;
    }

    .btn-quick {
      display: inline-block;
      padding: 8px 15px;
      background: var(--color-marlene);
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-size: 0.7rem;
      font-weight: 700;
      transition: 0.3s;
    }

    .btn-quick:hover {
      background: var(--color-dorado);
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
      <a href="index.php" class="nav-item activo">📊 Dashboard</a>
      <p class="nav-section">Catálogo</p>
      <a href="productos.php" class="nav-item">🎒 Productos</a>
      <a href="categorias.php" class="nav-item">📁 Categorías</a>
      <p class="nav-section">Ventas</p>
      <a href="pedidos.php" class="nav-item">📦 Pedidos</a>
      <a href="clientes.php" class="nav-item">👥 Clientes</a>
    </nav>
  </div>

  <div class="main">
    <div class="welcome">
      <div>
        <p style="color: var(--color-dorado); font-weight: 700; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 2px;">Panel de Administración</p>
        <h3>Bienvenido de nuevo, Lucas</h3>
        <p style="opacity: 0.8; font-size: 0.9rem;">Hoy es <?= date('d/m/Y') ?>. Tenés <strong><?= $total_pendientes ?> pedidos</strong> esperando acción.</p>
      </div>
      <div style="text-align: right;">
        <p style="font-size: 0.7rem; text-transform: uppercase; color: var(--color-dorado);">Recaudación Confirmada</p>
        <p style="font-family: 'Cormorant Garamond'; font-size: 2.8rem; font-weight: 600;">$<?= number_format($dinero_total, 2, ',', '.') ?></p>
      </div>
    </div>

    <div class="cards-grid">
      <div class="card">
        <p class="card-label">Productos Activos</p>
        <p class="card-number"><?= $total_productos ?></p>
      </div>
      <div class="card" style="border-left-color: #5DCAA5;">
        <p class="card-label">Clientes Registrados</p>
        <p class="card-number"><?= $total_clientes ?></p>
      </div>
      <div class="card" style="border-left-color: #85B7EB;">
        <p class="card-label">Ventas Totales</p>
        <p class="card-number"><?= $total_pedidos ?></p>
      </div>
      <div class="card" style="border-left-color: #FAC775;">
        <p class="card-label">Nuevos Hoy</p>
        <p class="card-number"><?= $pedidos_hoy ?></p>
      </div>
    </div>

    <div class="dashboard-grid">
      <div class="panel">
        <h4>Últimos Pedidos <a href="pedidos.php" class="btn-quick">VER TODOS</a></h4>
        <table>
          <thead>
            <tr>
              <th>N°</th>
              <th>Cliente</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Detalle</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($ultimos_pedidos && $ultimos_pedidos->num_rows > 0): ?>
              <?php while ($p = $ultimos_pedidos->fetch_assoc()): ?>
                <tr>
                  <td><strong>#<?= $p['numero_pedido'] ?? $p['id'] ?></strong></td>
                  <td><?= htmlspecialchars($p['cliente'] ?? 'Invitado') ?></td>
                  <td style="color: var(--color-marlene); font-weight: 700;">$<?= number_format($p['total'], 2, ',', '.') ?></td>
                  <td><span class="badge" style="background:<?= $colores[$p['estado']] ?? '#ccc' ?>"><?= str_replace('_', ' ', $p['estado']) ?></span></td>

                  <td>
                    <a href="pedido-detalle.php?id=<?= $p['id'] ?>"
                      class="btn-quick"
                      style="background: #C9A96E; margin-bottom: 5px; display: inline-block; text-decoration: none; padding: 5px 10px; font-size: 0.7rem;">
                      👁️ Ver
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="4 style=" text-align: center; padding: 30px;">No hay pedidos registrados aún.</td>
              </tr>
            <?php endif; ?>

          </tbody>
        </table>
      </div>

      <div class="panel">
        <h4 style="color: #C0392B;">⚠️ Stock Bajo</h4>
        <table>
          <thead>
            <tr>
              <th>Producto</th>
              <th style="text-align: right;">Stock</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($stock_bajo && $stock_bajo->num_rows > 0): ?>
              <?php while ($s = $stock_bajo->fetch_assoc()): ?>
                <tr>
                  <td style="font-size: 0.75rem;"><?= htmlspecialchars($s['nombre']) ?></td>
                  <td style="text-align: right; color: #C0392B; font-weight: 900;"><?= $s['stock'] ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="2" style="text-align: center; padding: 20px; color: #5DCAA5;">Todo el stock está al día. ✅</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        <a href="productos.php" class="btn-quick" style="width: 100%; text-align: center; margin-top: 20px; background: #C0392B;">REPONER STOCK</a>
      </div>
    </div>
  </div>
</body>

</html>