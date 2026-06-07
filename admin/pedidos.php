<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.php');
  exit;
}

require_once '../config/Database.php';
require_once '../models/PedidoRepository.php';

$db = Database::getConexion();
$pedidoRepo = new PedidoRepository($db);

$colores = [
  'pendiente'      => '#E67E22',
  'confirmado'     => '#27AE60',
  'en_preparacion' => '#2980B9',
  'enviado'        => '#8E44AD',
  'demorado'       => '#F1C40F',
  'entregado'      => '#2C3E50',
  'cancelado'      => '#C0392B',
];

// Procesar cambio de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'])) {
  $id_pedido    = (int)$_POST['id_pedido'];
  $nuevo_estado = trim($_POST['nuevo_estado']);

  $pedidoRepo->actualizarEstado($id_pedido, $nuevo_estado);

  // Email al cliente
  require_once __DIR__ . '/../includes/emails.php';
  $stmt = $db->prepare("
        SELECT p.*, c.nombre as cliente_nombre, c.email as cliente_email
        FROM pedidos p
        LEFT JOIN clientes c ON p.id_cliente = c.id
        WHERE p.id = ? LIMIT 1
    ");
  $stmt->bind_param("i", $id_pedido);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_assoc();
  if ($data && !empty($data['cliente_email'])) {
    emailCambioEstado(
      $data,
      ['nombre' => $data['cliente_nombre'], 'email' => $data['cliente_email']],
      $nuevo_estado
    );
  }

  header('Location: pedidos.php?mensaje=ok');
  exit;
}

// Filtros
$busqueda      = $_GET['buscar'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';
$fecha_desde   = $_GET['desde'] ?? '';
$fecha_hasta   = $_GET['hasta'] ?? '';

$filtros = [
  'buscar' => $busqueda,
  'estado' => $filtro_estado,
  'desde'  => $fecha_desde,
  'hasta'  => $fecha_hasta,
];

$pedidos = $pedidoRepo->listarPedidos($filtros);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pedidos — Marlene Store</title>
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

    /* SIDEBAR */
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

    /* MAIN */
    .main {
      margin-left: 260px;
      flex: 1;
      padding: 40px;
    }

    .page-header {
      margin-bottom: 24px;
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

    /* ALERTA ÉXITO */
    .alerta-ok {
      background: #DCFCE7;
      color: #166534;
      padding: 12px 20px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    /* FILTROS */
    .search-bar {
      background: white;
      padding: 20px;
      border-radius: 8px;
      border: 1px solid rgba(200, 152, 154, 0.2);
      margin-bottom: 16px;
      display: grid;
      grid-template-columns: 2fr 1fr 1fr auto;
      gap: 12px;
      align-items: end;
    }

    .search-bar label {
      display: block;
      font-size: 0.58rem;
      font-weight: 700;
      color: #999;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 5px;
    }

    .search-bar input {
      width: 100%;
      padding: 10px 14px;
      border: 1.5px solid rgba(200, 152, 154, 0.3);
      border-radius: 6px;
      font-family: 'Montserrat', sans-serif;
      font-size: 0.82rem;
      color: #3A2526;
    }

    .btn-search {
      background: #5C3D3E;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 700;
      font-size: 0.7rem;
      letter-spacing: 1px;
      transition: 0.2s;
    }

    .btn-search:hover {
      background: #C9A96E;
    }

    .filtros-estado {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }

    .filtro-btn {
      padding: 7px 14px;
      border-radius: 4px;
      font-size: 0.6rem;
      font-weight: 700;
      text-transform: uppercase;
      text-decoration: none;
      border: 1px solid rgba(200, 152, 154, 0.3);
      color: #666;
      background: white;
      transition: 0.2s;
    }

    .filtro-btn:hover {
      border-color: #5C3D3E;
      color: #5C3D3E;
    }

    .filtro-btn.activo {
      background: #5C3D3E;
      color: white;
      border-color: #5C3D3E;
    }

    /* TABLA */
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
    }

    .estado-badge {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.58rem;
      font-weight: 700;
      color: white;
      text-transform: uppercase;
    }

    .btn-ver {
      display: inline-block;
      padding: 6px 12px;
      background: #C9A96E;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-size: 0.65rem;
      font-weight: 700;
      margin-right: 4px;
      transition: 0.2s;
    }

    .btn-ver:hover {
      background: #5C3D3E;
    }

    .btn-gestionar {
      padding: 6px 12px;
      background: #5C3D3E;
      color: white;
      border: none;
      border-radius: 4px;
      font-size: 0.65rem;
      font-weight: 700;
      cursor: pointer;
      transition: 0.2s;
    }

    .btn-gestionar:hover {
      background: #C9A96E;
    }

    .btn-wa {
      color: #25D366;
      text-decoration: none;
      font-weight: 700;
      font-size: 0.72rem;
    }

    /* MODAL */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }

    .modal-overlay.abierto {
      display: flex;
    }

    .modal {
      background: white;
      padding: 32px;
      border-radius: 10px;
      width: 420px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    }

    .modal h3 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.5rem;
      color: #5C3D3E;
      margin-bottom: 20px;
    }

    .modal select,
    .modal textarea {
      width: 100%;
      padding: 10px 14px;
      border: 1.5px solid rgba(200, 152, 154, 0.3);
      border-radius: 6px;
      font-family: 'Montserrat', sans-serif;
      font-size: 0.82rem;
      margin-bottom: 12px;
    }

    .btn-confirmar {
      width: 100%;
      background: #5C3D3E;
      color: white;
      border: none;
      padding: 14px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 700;
      font-size: 0.7rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      transition: 0.2s;
    }

    .btn-confirmar:hover {
      background: #C9A96E;
    }

    .btn-cancelar {
      width: 100%;
      background: none;
      border: none;
      margin-top: 10px;
      cursor: pointer;
      color: #999;
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
      <a href="pedidos.php" class="nav-item activo">📦 Pedidos</a>
      <a href="clientes.php" class="nav-item">👥 Clientes</a>
      <p class="nav-section">Tienda</p>
      <a href="../index.php" class="nav-item" target="_blank">🌐 Ver tienda</a>
    </nav>
    <div class="sidebar-footer">
      <a href="logout.php" class="btn-logout-sidebar">🚪 Cerrar sesión</a>
    </div>
  </div>

  <div class="main">
    <div class="page-header">
      <h2>Pedidos</h2>
      <p>Gestioná y filtrá todos los pedidos de la tienda</p>
    </div>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'ok'): ?>
      <div class="alerta-ok">✅ Estado del pedido actualizado correctamente.</div>
    <?php endif; ?>

    <!-- Buscador -->
    <form method="GET" class="search-bar">
      <div>
        <label>Buscar cliente / pedido</label>
        <input type="text" name="buscar" placeholder="Nombre o N° de pedido..." value="<?= htmlspecialchars($busqueda) ?>">
      </div>
      <div>
        <label>Desde</label>
        <input type="date" name="desde" value="<?= htmlspecialchars($fecha_desde) ?>">
      </div>
      <div>
        <label>Hasta</label>
        <input type="date" name="hasta" value="<?= htmlspecialchars($fecha_hasta) ?>">
      </div>
      <div style="display:flex;gap:8px;align-items:center;">
        <input type="hidden" name="estado" value="<?= htmlspecialchars($filtro_estado) ?>">
        <button type="submit" class="btn-search">FILTRAR</button>
        <?php if ($busqueda || $filtro_estado || $fecha_desde || $fecha_hasta): ?>
          <a href="pedidos.php" style="color:#999;text-decoration:none;font-size:0.7rem;font-weight:700;">LIMPIAR</a>
        <?php endif; ?>
      </div>
    </form>

    <!-- Filtros por estado -->
    <div class="filtros-estado">
      <a href="pedidos.php?buscar=<?= urlencode($busqueda) ?>" class="filtro-btn <?= !$filtro_estado ? 'activo' : '' ?>">Todos</a>
      <?php foreach ($colores as $nom_estado => $color): ?>
        <a href="pedidos.php?estado=<?= $nom_estado ?>&buscar=<?= urlencode($busqueda) ?>"
          class="filtro-btn <?= $filtro_estado === $nom_estado ? 'activo' : '' ?>"
          style="<?= $filtro_estado === $nom_estado ? "background:$color;border-color:$color;" : '' ?>">
          <?= str_replace('_', ' ', $nom_estado) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Tabla -->
    <div class="tabla-wrap">
      <table>
        <thead>
          <tr>
            <th>N° Pedido</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($pedidos && $pedidos->num_rows > 0): ?>
            <?php while ($p = $pedidos->fetch_assoc()): ?>
              <tr>
                <td><strong>#<?= htmlspecialchars($p['numero_pedido']) ?></strong></td>
                <td>
                  <?= htmlspecialchars(trim(($p['c_nombre'] ?? '') . ' ' . ($p['c_apellido'] ?? ''))) ?: 'Invitado' ?>
                  <?php if (!empty($p['cliente_telefono'])): ?>
                    <br>
                    <a href="https://wa.me/54<?= preg_replace('/[^0-9]/', '', $p['cliente_telefono']) ?>"
                      target="_blank" class="btn-wa">📱 WhatsApp</a>
                  <?php endif; ?>
                </td>
                <td><?= date('d/m/y H:i', strtotime($p['fecha_pedido'])) ?></td>
                <td><strong>$<?= number_format($p['total'], 0, ',', '.') ?></strong></td>
                <td>
                  <span class="estado-badge" style="background:<?= $colores[$p['estado']] ?? '#ccc' ?>">
                    <?= str_replace('_', ' ', $p['estado']) ?>
                  </span>
                </td>
                <td>
                  <a href="pedido-detalle.php?id=<?= $p['id'] ?>" class="btn-ver">👁 Ver</a>
                  <button class="btn-gestionar"
                    onclick="abrirModal(<?= $p['id'] ?>, '<?= htmlspecialchars($p['numero_pedido']) ?>', '<?= $p['estado'] ?>')">
                    Gestionar
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align:center;padding:40px;color:#999;">
                No se encontraron pedidos.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal-overlay" id="modal-overlay">
    <div class="modal">
      <h3>Actualizar Estado</h3>
      <form method="POST">
        <input type="hidden" name="id_pedido" id="in-id">
        <p style="font-size:0.75rem;color:#999;margin-bottom:12px;">Pedido: <strong id="modal-numero"></strong></p>
        <select name="nuevo_estado" id="sel-estado">
          <?php foreach ($colores as $estado_nombre => $color_hex): ?>
            <option value="<?= $estado_nombre ?>">
              <?= ucfirst(str_replace('_', ' ', $estado_nombre)) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-confirmar">GUARDAR CAMBIOS</button>
        <button type="button" onclick="cerrarModal()" class="btn-cancelar">Cancelar</button>
      </form>
    </div>
  </div>

  <script>
    function abrirModal(id, numero, estado) {
      document.getElementById('in-id').value = id;
      document.getElementById('modal-numero').textContent = '#' + numero;
      document.getElementById('sel-estado').value = estado;
      document.getElementById('modal-overlay').classList.add('abierto');
    }

    function cerrarModal() {
      document.getElementById('modal-overlay').classList.remove('abierto');
    }
    document.getElementById('modal-overlay').addEventListener('click', function(e) {
      if (e.target === this) cerrarModal();
    });
  </script>
</body>

</html>