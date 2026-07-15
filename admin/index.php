<?php
session_start();
require_once __DIR__ . '/../includes/error-handler.php';
require_once __DIR__ . '/../config/Database.php';

if (!isset($_SESSION['usuario_id'])) {
  header('Location: /admin/login.php');
  exit;
}

try {
  $conexion = Database::getConexion();

  $total_productos  = $conexion->query("SELECT COUNT(*) as t FROM productos WHERE activo = 1")->fetch_assoc()['t'];
  $total_clientes   = $conexion->query("SELECT COUNT(*) as t FROM clientes WHERE activo = 1")->fetch_assoc()['t'];
  $total_pedidos    = $conexion->query("SELECT COUNT(*) as t FROM pedidos")->fetch_assoc()['t'];
  $pedidos_hoy      = $conexion->query("SELECT COUNT(*) as t FROM pedidos WHERE DATE(fecha_pedido) = CURDATE()")->fetch_assoc()['t'];
  $total_pendientes = $conexion->query("SELECT COUNT(*) as t FROM pedidos WHERE estado = 'pendiente'")->fetch_assoc()['t'];

  $ventas_hoy = $conexion->query("
        SELECT COALESCE(SUM(total), 0) as t FROM pedidos
        WHERE DATE(fecha_pedido) = CURDATE()
        AND estado IN ('confirmado','enviado','entregado')
    ")->fetch_assoc()['t'];

  $ventas_mes = $conexion->query("
        SELECT COALESCE(SUM(total), 0) as t FROM pedidos
        WHERE MONTH(fecha_pedido) = MONTH(CURDATE())
        AND YEAR(fecha_pedido) = YEAR(CURDATE())
        AND estado IN ('confirmado','enviado','entregado')
    ")->fetch_assoc()['t'];

  $dinero_total = $conexion->query("
        SELECT COALESCE(SUM(total), 0) as t FROM pedidos
        WHERE estado IN ('confirmado','enviado','entregado')
    ")->fetch_assoc()['t'];

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

  $ultimos_pedidos = $conexion->query("
        SELECT p.*, CONCAT(c.nombre, ' ', c.apellido) as cliente
        FROM pedidos p
        LEFT JOIN clientes c ON p.id_cliente = c.id
        ORDER BY p.fecha_pedido DESC LIMIT 8
    ");

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
  error_log("Dashboard error: " . $e->getMessage());
  die("Error crítico al cargar el dashboard.");
}

// Variables para header-admin.php
$titulo_admin   = 'Dashboard';
$nav_activo     = 'dashboard';
$scripts_head   = ['https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js'];
$estilos_extra_admin = '
.welcome {
    background: var(--marlene);
    border-radius: 12px;
    padding: 28px 32px;
    color: #FAF6F1;
    margin-bottom: 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 30px rgba(92,61,62,0.2);
}
.welcome h3 { font-family: "Cormorant Garamond", serif; font-size: 1.8rem; margin-top: 4px; }
.welcome-tag { font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: var(--dorado); }
.welcome-sub { opacity: 0.8; font-size: 0.82rem; margin-top: 6px; }
.welcome-right { text-align: right; }
.welcome-right .label { font-size: 0.62rem; text-transform: uppercase; letter-spacing: 2px; color: var(--dorado); }
.welcome-right .monto { font-family: "Cormorant Garamond", serif; font-size: 2.6rem; font-weight: 600; line-height: 1; }
.cards-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 18px; margin-bottom: 28px; }
.cards-grid-2 { display: grid; grid-template-columns: repeat(3,1fr); gap: 18px; margin-bottom: 28px; }
.card {
    background: #fff;
    border-radius: 10px;
    padding: 20px 22px;
    border-left: 4px solid var(--dorado);
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.card-label { font-size: 0.6rem; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
.card-number { font-family: "Cormorant Garamond", serif; font-size: 2rem; font-weight: 700; color: var(--marlene); }
.card-sub { font-size: 0.65rem; color: #bbb; margin-top: 4px; }
.dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 28px; }
.grafico-wrap { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.04); margin-bottom: 28px; }
.grafico-wrap canvas { max-height: 220px; }
';

require_once __DIR__ . '/includes/header-admin.php';
?>

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
  <div class="panel-title">📈 Ventas últimos 30 días</div>
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

<?php
$scripts_extra_admin = [];
// El script del gráfico va inline porque necesita las variables PHP
require_once __DIR__ . '/includes/footer-admin.php';
?>

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