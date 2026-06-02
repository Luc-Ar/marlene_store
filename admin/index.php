<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.php');
  exit;
}
require_once __DIR__ . '/../config/Database.php';

try {
  $conexion = Database::getConexion();

  // Métricas generales
  $total_productos = $conexion->query("SELECT COUNT(*) as t FROM productos WHERE activo = 1")->fetch_assoc()['t'];
  $total_clientes  = $conexion->query("SELECT COUNT(*) as t FROM clientes WHERE activo = 1")->fetch_assoc()['t'];
  $total_pedidos   = $conexion->query("SELECT COUNT(*) as t FROM pedidos")->fetch_assoc()['t'];
  $pedidos_hoy     = $conexion->query("SELECT COUNT(*) as t FROM pedidos WHERE DATE(fecha_pedido) = CURDATE()")->fetch_assoc()['t'];
  $total_pendientes = $conexion->query("SELECT COUNT(*) as t FROM pedidos WHERE estado = 'pendiente'")->fetch_assoc()['t'];

  // Ventas del día
  $ventas_hoy = $conexion->query("
        SELECT COALESCE(SUM(total), 0) as t FROM pedidos
        WHERE DATE(fecha_pedido) = CURDATE()
        AND estado IN ('confirmado','enviado','entregado')
    ")->fetch_assoc()['t'];

  // Ventas del mes
  $ventas_mes = $conexion->query("
        SELECT COALESCE(SUM(total), 0) as t FROM pedidos
        WHERE MONTH(fecha_pedido) = MONTH(CURDATE())
        AND YEAR(fecha_pedido) = YEAR(CURDATE())
        AND estado IN ('confirmado','enviado','entregado')
    ")->fetch_assoc()['t'];

  // Recaudación total
  $dinero_total = $conexion->query("
        SELECT COALESCE(SUM(total), 0) as t FROM pedidos
        WHERE estado IN ('confirmado','enviado','entregado')
    ")->fetch_assoc()['t'];

  // Ventas últimos 30 días para el gráfico
  $res_grafico = $conexion->query("
        SELECT DATE(fecha_pedido) as fecha, COALESCE(SUM(total), 0) as total
        FROM pedidos
        WHERE fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        AND estado IN ('confirmado','enviado','entregado')
        GROUP BY DATE(fecha_pedido)
        ORDER BY fecha ASC
    ");
  $grafico_labels = [];
  $grafico_datos  = [];
  while ($row = $res_grafico->fetch_assoc()) {
    $grafico_labels[] = date('d/m', strtotime($row['fecha']));
    $grafico_datos[]  = (float)$row['total'];
  }

  // Últimos pedidos
  $ultimos_pedidos = $conexion->query("
        SELECT p.*, CONCAT(c.nombre, ' ', c.apellido) as cliente
        FROM pedidos p
        LEFT JOIN clientes c ON p.id_cliente = c.id
        ORDER BY p.fecha_pedido DESC LIMIT 8
    ");

  // Stock bajo
  $stock_bajo = $conexion->query("
        SELECT nombre, stock FROM productos
        WHERE stock < 5 AND activo = 1
        ORDER BY stock ASC LIMIT 6
    ");

  $colores_estado = [
    'pendiente'      => '#E67E22',
    'confirmado'     => '#27AE60',
    'en_preparacion' => '#2980B9',
    'enviado'        => '#8E44AD',
    'demorado'       => '#E74C3C',
    'entregado'      => '#2C3E50',
    'cancelado'      => '#95A5A6',
  ];
} catch (Exception $e) {
  die("Error crítico: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Marlene Store</title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
  <style>
    :root {
      --sidebar-width: 260px;
      --marlene: #5C3D3E;
      --dorado: #C9A96E;
      --crema: #F2EBE0;
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
      background: var(--marlene);
      height: 100vh;
      position: fixed;
      display: flex;
      flex-direction: column;
      z-index: 100;
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
      color: var(--dorado);
      text-transform: uppercase;
    }

    .sidebar-nav {
      flex: 1;
      padding: 20px 0;
      overflow-y: auto;
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
      border-left-color: var(--dorado);
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

    /* MAIN */
    .main {
      margin-left: var(--sidebar-width);
      flex: 1;
      padding: 36px 40px;
    }

    /* WELCOME */
    .welcome {
      background: var(--marlene);
      border-radius: 12px;
      padding: 28px 32px;
      color: #FAF6F1;
      margin-bottom: 28px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 10px 30px rgba(92, 61, 62, 0.2);
    }

    .welcome h3 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.8rem;
      margin-top: 4px;
    }

    .welcome-tag {
      font-size: 0.62rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: var(--dorado);
    }

    .welcome-sub {
      opacity: 0.8;
      font-size: 0.82rem;
      margin-top: 6px;
    }

    .welcome-right {
      text-align: right;
    }

    .welcome-right .label {
      font-size: 0.62rem;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: var(--dorado);
    }

    .welcome-right .monto {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2.6rem;
      font-weight: 600;
      line-height: 1;
    }

    /* CARDS */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
      margin-bottom: 28px;
    }

    .card {
      background: #fff;
      border-radius: 10px;
      padding: 20px 22px;
      border-left: 4px solid var(--dorado);
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    }

    .card-label {
      font-size: 0.6rem;
      font-weight: 700;
      color: #999;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 6px;
    }

    .card-number {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem;
      font-weight: 700;
      color: var(--marlene);
    }

    .card-sub {
      font-size: 0.65rem;
      color: #bbb;
      margin-top: 4px;
    }

    /* SEGUNDA FILA DE CARDS */
    .cards-grid-2 {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
      margin-bottom: 28px;
    }

    /* DASHBOARD GRID */
    .dashboard-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 24px;
      margin-bottom: 28px;
    }

    .panel {
      background: #fff;
      padding: 24px;
      border-radius: 12px;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    }

    .panel-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.4rem;
      color: var(--marlene);
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    /* TABLE */
    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      text-align: left;
      font-size: 0.6rem;
      text-transform: uppercase;
      color: var(--dorado);
      padding-bottom: 12px;
      border-bottom: 1px solid #F2EBE0;
      letter-spacing: 1px;
    }

    td {
      padding: 12px 0;
      font-size: 0.82rem;
      border-bottom: 1px solid #F9F9F9;
    }

    .badge {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.58rem;
      font-weight: 800;
      color: #fff;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .btn-quick {
      display: inline-block;
      padding: 6px 12px;
      background: var(--marlene);
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-size: 0.65rem;
      font-weight: 700;
      transition: 0.2s;
    }

    .btn-quick:hover {
      background: var(--dorado);
    }

    /* GRAFICO */
    .grafico-wrap {
      background: #fff;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
      margin-bottom: 28px;
    }

    .grafico-wrap canvas {
      max-height: 220px;
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
      <p class="nav-section">Tienda</p>
      <a href="../index.php" class="nav-item" target="_blank">🌐 Ver tienda</a>
    </nav>
    <div class="sidebar-footer">
      <a href="logout.php" class="btn-logout-sidebar">🚪 Cerrar sesión</a>
    </div>
  </div>

  <div class="main">

    <!-- WELCOME -->
    <div class="welcome">
      <div>
        <p class="welcome-tag">Panel de Administración</p>
        <h3>Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Admin') ?> 👋</h3>
        <p class="welcome-sub">
          Hoy es <?= date('d/m/Y') ?>.
          <?php if ($total_pendientes > 0): ?>
            Tenés <strong><?= $total_pendientes ?> pedido<?= $total_pendientes != 1 ? 's' : '' ?> pendiente<?= $total_pendientes != 1 ? 's' : '' ?></strong> esperando acción.
          <?php else: ?>
            Todo al día. ✅
          <?php endif; ?>
        </p>
      </div>
      <div class="welcome-right">
        <p class="label">Recaudación total</p>
        <p class="monto">$<?= number_format($dinero_total, 0, ',', '.') ?></p>
      </div>
    </div>

    <!-- CARDS FILA 1 -->
    <div class="cards-grid">
      <div class="card">
        <p class="card-label">Productos Activos</p>
        <p class="card-number"><?= $total_productos ?></p>
        <p class="card-sub"><a href="productos.php" style="color:var(--dorado);text-decoration:none;">Ver todos →</a></p>
      </div>
      <div class="card" style="border-left-color:#5DCAA5;">
        <p class="card-label">Clientes Registrados</p>
        <p class="card-number"><?= $total_clientes ?></p>
        <p class="card-sub"><a href="clientes.php" style="color:#5DCAA5;text-decoration:none;">Ver todos →</a></p>
      </div>
      <div class="card" style="border-left-color:#85B7EB;">
        <p class="card-label">Pedidos Totales</p>
        <p class="card-number"><?= $total_pedidos ?></p>
        <p class="card-sub"><a href="pedidos.php" style="color:#85B7EB;text-decoration:none;">Ver todos →</a></p>
      </div>
      <div class="card" style="border-left-color:#FAC775;">
        <p class="card-label">Pedidos Hoy</p>
        <p class="card-number"><?= $pedidos_hoy ?></p>
        <p class="card-sub">Nuevos hoy</p>
      </div>
    </div>

    <!-- CARDS FILA 2 -->
    <div class="cards-grid-2">
      <div class="card" style="border-left-color:#27AE60;">
        <p class="card-label">💰 Ventas Hoy</p>
        <p class="card-number">$<?= number_format($ventas_hoy, 0, ',', '.') ?></p>
        <p class="card-sub">Pedidos confirmados hoy</p>
      </div>
      <div class="card" style="border-left-color:#8E44AD;">
        <p class="card-label">📅 Ventas Este Mes</p>
        <p class="card-number">$<?= number_format($ventas_mes, 0, ',', '.') ?></p>
        <p class="card-sub"><?= date('F Y') ?></p>
      </div>
      <div class="card" style="border-left-color:#E67E22;">
        <p class="card-label">⏳ Pendientes</p>
        <p class="card-number"><?= $total_pendientes ?></p>
        <p class="card-sub"><a href="pedidos.php?estado=pendiente" style="color:#E67E22;text-decoration:none;">Atender ahora →</a></p>
      </div>
    </div>

    <!-- GRÁFICO -->
    <div class="grafico-wrap">
      <div class="panel-title">
        📈 Ventas últimos 30 días
      </div>
      <canvas id="grafico-ventas"></canvas>
    </div>

    <!-- TABLA + STOCK -->
    <div class="dashboard-grid">
      <div class="panel">
        <div class="panel-title">
          Últimos Pedidos
          <a href="pedidos.php" class="btn-quick">VER TODOS</a>
        </div>
        <table>
          <thead>
            <tr>
              <th>N°</th>
              <th>Cliente</th>
              <th>Total</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if ($ultimos_pedidos && $ultimos_pedidos->num_rows > 0): ?>
              <?php while ($p = $ultimos_pedidos->fetch_assoc()): ?>
                <tr>
                  <td><strong>#<?= htmlspecialchars($p['numero_pedido']) ?></strong></td>
                  <td><?= htmlspecialchars($p['cliente'] ?? 'Invitado') ?></td>
                  <td style="color:var(--marlene);font-weight:700;">$<?= number_format($p['total'], 0, ',', '.') ?></td>
                  <td>
                    <span class="badge" style="background:<?= $colores_estado[$p['estado']] ?? '#ccc' ?>">
                      <?= str_replace('_', ' ', $p['estado']) ?>
                    </span>
                  </td>
                  <td>
                    <a href="pedido-detalle.php?id=<?= $p['id'] ?>" class="btn-quick" style="background:var(--dorado);">👁 Ver</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" style="text-align:center;padding:30px;color:#999;">No hay pedidos aún.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="panel">
        <div class="panel-title" style="color:#C0392B;">⚠️ Stock Bajo</div>
        <table>
          <thead>
            <tr>
              <th>Producto</th>
              <th style="text-align:right;">Stock</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($stock_bajo && $stock_bajo->num_rows > 0): ?>
              <?php while ($s = $stock_bajo->fetch_assoc()): ?>
                <tr>
                  <td style="font-size:0.75rem;"><?= htmlspecialchars($s['nombre']) ?></td>
                  <td style="text-align:right;color:#C0392B;font-weight:900;"><?= $s['stock'] ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="2" style="text-align:center;padding:20px;color:#5DCAA5;">✅ Todo el stock al día</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        <a href="productos.php" class="btn-quick" style="display:block;text-align:center;margin-top:16px;background:#C0392B;">
          GESTIONAR STOCK
        </a>
      </div>
    </div>

  </div>

  <script>
    const labels = <?= json_encode($grafico_labels) ?>;
    const datos = <?= json_encode($grafico_datos) ?>;

    new Chart(document.getElementById('grafico-ventas'), {
      type: 'line',
      data: {
        labels: labels.length ? labels : ['Sin datos'],
        datasets: [{
          label: 'Ventas ($)',
          data: datos.length ? datos : [0],
          borderColor: '#C9A96E',
          backgroundColor: 'rgba(201,169,110,0.1)',
          borderWidth: 2.5,
          pointBackgroundColor: '#5C3D3E',
          pointRadius: 4,
          tension: 0.4,
          fill: true,
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: ctx => ' $' + ctx.parsed.y.toLocaleString('es-AR')
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: val => '$' + val.toLocaleString('es-AR'),
              font: {
                family: 'Montserrat',
                size: 10
              }
            },
            grid: {
              color: 'rgba(0,0,0,0.04)'
            }
          },
          x: {
            ticks: {
              font: {
                family: 'Montserrat',
                size: 10
              }
            },
            grid: {
              display: false
            }
          }
        }
      }
    });
  </script>

</body>

</html>