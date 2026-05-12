<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$conexion = conectar();

$total_productos = $conexion->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1")->fetch_assoc()['total'];
$total_clientes  = $conexion->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1")->fetch_assoc()['total'];
$total_pedidos   = $conexion->query("SELECT COUNT(*) as total FROM pedidos")->fetch_assoc()['total'];
$pedidos_hoy     = $conexion->query("SELECT COUNT(*) as total FROM pedidos WHERE DATE(fecha_pedido) = CURDATE()")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Marlene Store</title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Montserrat',sans-serif; background:#F2EBE0; color:#3A2526; display:flex; min-height:100vh; }

    /* ─── SIDEBAR ─── */
    .sidebar {
      width: 240px; background:#5C3D3E; min-height:100vh;
      display:flex; flex-direction:column; position:fixed; top:0; left:0;
    }
    .sidebar-logo {
      padding: 28px 24px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
.logo-script {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.6rem;
  font-weight: 600;
  color: #FAF6F1;
  display: block;
  letter-spacing: 2px;
  text-transform: uppercase;
}
    .logo-store {
      font-family: 'Montserrat', sans-serif;
      font-weight: 900;
      font-size: 0.65rem;
      letter-spacing: 5px;
      color: #C9A96E;
      text-transform: uppercase;
      display: block;
      margin-top: 2px;
    }
    .logo-admin {
      font-size: 0.55rem;
      font-weight: 600;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: rgba(255,255,255,0.3);
      display: block;
      margin-top: 8px;
    }

    .sidebar-nav { padding:24px 0; flex:1; }
    .nav-section {
      font-size:0.55rem; font-weight:700; letter-spacing:3px;
      text-transform:uppercase; color:rgba(255,255,255,0.3);
      padding:16px 24px 8px;
    }
    .nav-item {
      display:flex; align-items:center; gap:12px;
      padding:12px 24px; color:rgba(255,252,246,0.7);
      text-decoration:none; font-size:0.75rem; font-weight:500;
      letter-spacing:1px; transition:all 0.2s;
    }
    .nav-item:hover, .nav-item.activo {
      background:rgba(255,255,255,0.08); color:#FAF6F1;
      border-left:3px solid #C9A96E; padding-left:21px;
    }
    .nav-item .icon { font-size:1rem; width:20px; text-align:center; }

    .sidebar-footer {
      padding:20px 24px;
      border-top:1px solid rgba(255,255,255,0.1);
    }
    .sidebar-user {
      font-size:0.7rem; color:rgba(255,255,255,0.6);
      margin-bottom:8px; font-weight:500;
    }
    .sidebar-footer a {
      font-size:0.62rem; font-weight:600; letter-spacing:1px;
      text-transform:uppercase; color:rgba(255,255,255,0.35);
      text-decoration:none; transition:color 0.2s;
    }
    .sidebar-footer a:hover { color:#FAF6F1; }

    /* ─── MAIN ─── */
    .main { margin-left:240px; flex:1; padding:40px; }

    .page-header { margin-bottom:36px; }
    .page-header h2 {
      font-family:'Cormorant Garamond',serif;
      font-size:2rem; font-weight:400; color:#5C3D3E;
    }
    .page-header p { font-size:0.8rem; color:#9a7070; margin-top:4px; }

    /* ─── CARDS ─── */
    .cards-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:40px; }
    .card {
      background:#fff; border-radius:4px; padding:28px 24px;
      border:1px solid rgba(200,152,154,0.2);
      transition:transform 0.2s, box-shadow 0.2s;
    }
    .card:hover { transform:translateY(-3px); box-shadow:0 12px 30px rgba(92,61,62,0.1); }
    .card-label {
      font-size:0.58rem; font-weight:700; letter-spacing:3px;
      text-transform:uppercase; color:#C9A96E; margin-bottom:12px;
    }
    .card-number {
      font-family:'Cormorant Garamond',serif;
      font-size:2.8rem; font-weight:600; color:#5C3D3E; line-height:1;
    }
    .card-sub { font-size:0.7rem; color:#9a7070; margin-top:8px; }

    /* ─── BIENVENIDA ─── */
    .welcome {
      background:#5C3D3E; border-radius:4px; padding:32px;
      color:#FAF6F1; margin-bottom:40px;
    }
    .welcome h3 {
      font-family:'Cormorant Garamond',serif;
      font-size:1.8rem; font-weight:400; margin-bottom:8px;
    }
    .welcome p { font-size:0.8rem; opacity:0.7; line-height:1.6; }
  </style>
</head>
<body>

  <!-- ─── SIDEBAR ─── -->
  <div class="sidebar">
    <div class="sidebar-logo">
      <span class="logo-script">Marlene</span>
      <span class="logo-store">Store</span>
      <span class="logo-admin">Panel de administración</span>
    </div>
    <nav class="sidebar-nav">
      <p class="nav-section">Principal</p>
      <a href="index.php" class="nav-item activo"><span class="icon">📊</span> Dashboard</a>
      <p class="nav-section">Catálogo</p>
      <a href="productos.php" class="nav-item"><span class="icon">🎒</span> Productos</a>
      <a href="categorias.php" class="nav-item"><span class="icon">📁</span> Categorías</a>
      <p class="nav-section">Ventas</p>
      <a href="pedidos.php" class="nav-item"><span class="icon">📦</span> Pedidos</a>
      <a href="clientes.php" class="nav-item"><span class="icon">👥</span> Clientes</a>
    </nav>
    <div class="sidebar-footer">
      <p class="sidebar-user"><?= $_SESSION['usuario_nombre'] ?> <?= $_SESSION['usuario_apellido'] ?></p>
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </div>

  <!-- ─── MAIN ─── -->
  <div class="main">
    <div class="page-header">
      <h2>Dashboard</h2>
      <p>Resumen general de tu tienda</p>
    </div>

    <div class="welcome">
      <h3>Bienvenido, <?= $_SESSION['usuario_nombre'] ?>! 👋</h3>
      <p>Desde acá podés gestionar todos los aspectos de Marlene Store.</p>
    </div>

    <div class="cards-grid">
      <div class="card">
        <p class="card-label">Productos activos</p>
        <p class="card-number"><?= $total_productos ?></p>
        <p class="card-sub">En el catálogo</p>
      </div>
      <div class="card">
        <p class="card-label">Clientes</p>
        <p class="card-number"><?= $total_clientes ?></p>
        <p class="card-sub">Registrados</p>
      </div>
      <div class="card">
        <p class="card-label">Pedidos totales</p>
        <p class="card-number"><?= $total_pedidos ?></p>
        <p class="card-sub">Desde el inicio</p>
      </div>
      <div class="card">
        <p class="card-label">Pedidos hoy</p>
        <p class="card-number"><?= $pedidos_hoy ?></p>
        <p class="card-sub"><?= date('d/m/Y') ?></p>
      </div>
    </div>
  </div>

</body>
</html>
