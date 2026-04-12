<?php
session_start();
// Si no tenés el login aún, podés comentar estas 3 líneas para probar
if (!isset($_SESSION['usuario_id'])) {
  // header('Location: login.php'); exit; 
}

require_once '../config/Database.php';
require_once '../models/PedidoRepository.php';

$db = Database::getConexion();
$pedidoRepo = new PedidoRepository($db);

// 1. Definir los colores y estados (Esto lo usás en los badges y filtros)
$colores = [
  'pendiente'   => '#E67E22', // Naranja
  'confirmado'   => '#27AE60', // Verde
  'en_preparacion' => '#2980B9', // Azul
  'enviado'     => '#8E44AD', // Violeta
  'demorado'       => '#F1C40F', // Amarillo
  'entregado'   => '#2C3E50', // Gris oscuro
  'cancelado'   => '#C0392B'  // Rojo
];

// 2. Procesar cambio de estado (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'])) {
  $pedidoRepo->actualizarEstado((int)$_POST['id_pedido'], $_POST['nuevo_estado']);
  header('Location: pedidos.php?mensaje=ok');
  exit;
}

// 3. Capturar filtros de la URL (GET)
$busqueda       = $_GET['buscar'] ?? '';
$filtro_estado  = $_GET['estado'] ?? '';
$fecha_desde    = $_GET['desde'] ?? '';
$fecha_hasta    = $_GET['hasta'] ?? '';

$filtros = [
  'buscar' => $busqueda,
  'estado' => $filtro_estado,
  'desde'  => $fecha_desde,
  'hasta'  => $fecha_hasta
];

// 4. Obtener los pedidos (Cambiamos el nombre para que coincida con tu HTML)
// Nota: Asegurate que listarPedidos devuelva un objeto mysqli_result o convertilo a array
$pedidos = $pedidoRepo->listarPedidos($filtros);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
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

    .sidebar {
      width: 240px;
      background: #5C3D3E;
      min-height: 100vh;
      position: fixed;
    }

    .sidebar-logo {
      padding: 28px 24px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      text-align: center;
    }

    .logo-script {
      font-family: 'Great Vibes', cursive;
      font-size: 2.8rem;
      color: #FAF6F1;
      display: block;
    }

    .sidebar-nav {
      padding: 24px 0;
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

    .main {
      margin-left: 240px;
      flex: 1;
      padding: 40px;
    }

    .page-header h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem;
      color: #5C3D3E;
    }

    /* Buscador e Interfaz */
    .search-bar {
      background: white;
      padding: 20px;
      border-radius: 4px;
      border: 1px solid #ddd;
      margin-bottom: 20px;
      display: flex;
      gap: 10px;
    }

    .search-bar input {
      flex: 1;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-family: 'Montserrat';
    }

    .btn-search {
      background: #5C3D3E;
      color: white;
      border: none;
      padding: 0 25px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 700;
    }

    .filtros {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-bottom: 25px;
    }

    .filtro-btn {
      padding: 8px 14px;
      border-radius: 4px;
      font-size: 0.6rem;
      font-weight: 700;
      text-transform: uppercase;
      text-decoration: none;
      border: 1px solid #ddd;
      color: #555;
      background: white;
    }

    .filtro-btn.activo {
      background: #5C3D3E;
      color: white;
      border-color: #5C3D3E;
    }

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
      padding: 15px;
      text-align: left;
      font-size: 0.6rem;
      text-transform: uppercase;
    }

    tbody td {
      padding: 15px;
      font-size: 0.85rem;
      border-bottom: 1px solid #eee;
    }

    .estado-badge {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.6rem;
      font-weight: 700;
      color: white;
      text-transform: uppercase;
    }

    .btn-wa {
      color: #25D366;
      text-decoration: none;
      font-weight: 700;
      font-size: 0.75rem;
    }

    /* Modal */
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
      padding: 30px;
      border-radius: 8px;
      width: 400px;
    }

    .btn-confirmar {
      background: #5C3D3E;
      color: white;
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 700;
      margin-top: 10px;
    }
  </style>
</head>

<body>
  <div class="sidebar">
    <div class="sidebar-logo"><span class="logo-script">Marlene</span></div>
    <nav class="sidebar-nav">
      <a href="index.php" class="nav-item">📊 Dashboard</a>
      <a href="productos.php" class="nav-item">🎒 Productos</a>
      <a href="pedidos.php" class="nav-item activo">📦 Pedidos</a>
      <a href="clientes.php" class="nav-item">👥 Clientes</a>
    </nav>
  </div>

  <div class="main">
    <div class="page-header">
      <h2>Búsqueda de Pedidos</h2>
      <p>Filtrá por cliente, número de pedido o estado</p>
    </div>
    <form method="GET" class="search-bar" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px; align-items: end;">
      <div>
        <label style="display:block; font-size:0.6rem; margin-bottom:5px; color:#9E5F62; font-weight:700;">BUSCAR CLIENTE / PEDIDO</label>
        <input type="text" name="buscar" placeholder="Escribí nombre o N°..." value="<?= htmlspecialchars($busqueda) ?>">
      </div>

      <div>
        <label style="display:block; font-size:0.6rem; margin-bottom:5px; color:#9E5F62; font-weight:700;">DESDE</label>
        <input type="date" name="desde" value="<?= htmlspecialchars($fecha_desde) ?>">
      </div>

      <div>
        <label style="display:block; font-size:0.6rem; margin-bottom:5px; color:#9E5F62; font-weight:700;">HASTA</label>
        <input type="date" name="hasta" value="<?= htmlspecialchars($fecha_hasta) ?>">
      </div>

      <div style="display: flex; gap: 10px; align-items: center;">
        <input type="hidden" name="estado" value="<?= htmlspecialchars($filtro_estado) ?>">
        <button type="submit" class="btn-search">FILTRAR</button>
        <?php if ($busqueda || $filtro_estado || $fecha_desde || $fecha_hasta): ?>
          <a href="pedidos.php" style="color:#9E5F62; text-decoration:none; font-size:0.7rem; font-weight:700;">LIMPIAR</a>
        <?php endif; ?>
      </div>
    </form>

    <div class="filtros">
      <?php foreach ($colores as $nom_estado => $color): ?>
        <a href="pedidos.php?estado=<?= $nom_estado ?>&buscar=<?= urlencode($busqueda) ?>"
          class="filtro-btn <?= $filtro_estado === $nom_estado ? 'activo' : '' ?>">
          <?= str_replace('_', ' ', $nom_estado) ?>
        </a>
      <?php endforeach; ?>
    </div>

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
          <?php if ($pedidos->num_rows > 0): ?>
            <?php while ($p = $pedidos->fetch_assoc()): ?>
              <tr>
                <td><strong>#<?= htmlspecialchars($p['numero_pedido']) ?></strong></td>
                <td>
                  <?= htmlspecialchars($p['c_nombre'] . ' ' . $p['c_apellido']) ?>
                  <br><a href="https://wa.me/54<?= preg_replace('/[^0-9]/', '', $p['cliente_telefono']) ?>" target="_blank" class="btn-wa">📱 WhatsApp</a>
                </td>
                <td><?= date('d/m/y H:i', strtotime($p['fecha_pedido'])) ?></td>
                <td><strong>$<?= number_format($p['total'], 2, ',', '.') ?></strong></td>
                <td>
                  <span class="estado-badge" style="background:<?= $colores[$p['estado']] ?>">
                    <?= str_replace('_', ' ', $p['estado']) ?>
                  </span>
                </td>
                <td>
                  <a href="pedido-detalle.php?id=<?= $p['id'] ?>"
                    class="btn-quick"
                    style="background: #C9A96E; margin-bottom: 5px; display: inline-block; text-decoration: none; padding: 5px 10px; font-size: 0.7rem;">
                    👁️ Ver Detalle
                  </a>
                  <button class="btn-search" style="padding:5px 10px; font-size:0.7rem;"
                    onclick="abrirModal(<?= $p['id'] ?>, '<?= $p['numero_pedido'] ?>', '<?= $p['estado'] ?>')">
                    Gestionar
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align:center; padding:40px;">No se encontraron resultados para tu búsqueda.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="modal-overlay" id="modal-overlay">
    <div class="modal">
      <h3 style="font-family:'Cormorant Garamond'; margin-bottom:15px;">Actualizar Pedido</h3>
      <form method="POST">
        <input type="hidden" name="cambiar_estado" value="1">
        <input type="hidden" name="id_pedido" id="in-id">
        <select name="nuevo_estado" id="sel-estado" style="width:100%; padding:10px; margin-bottom:10px;">
          <?php foreach ($colores as $estado_nombre => $color_hex): ?>
            <option value="<?= $estado_nombre ?>">
              <?= ucfirst(str_replace('_', ' ', $estado_nombre)) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <textarea name="notas" placeholder="Notas internas..." style="width:100%; padding:10px; height:80px;"></textarea>
        <button type="submit" class="btn-confirmar">GUARDAR CAMBIOS</button>
        <button type="button" onclick="cerrarModal()" style="width:100%; background:none; border:none; margin-top:10px; cursor:pointer; color:#888;">Cancelar</button>
      </form>
    </div>
  </div>

  <script>
    function abrirModal(id, numero, estado) {
      document.getElementById('in-id').value = id;
      document.getElementById('sel-estado').value = estado;
      document.getElementById('modal-overlay').classList.add('abierto');
    }

    function cerrarModal() {
      document.getElementById('modal-overlay').classList.remove('abierto');
    }
  </script>
</body>

</html>