<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/Database.php';
$conexion = Database::getConexion();

$resultado = $conexion->query("SELECT * FROM categorias ORDER BY orden_display ASC, nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías — Marlene Store</title>
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

        .btn-nuevo {
            background: #C9A96E;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
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

        .btn-ver {
            background: #C9A96E;
            color: white;
        }

        .btn-ver:hover {
            background: #5C3D3E;
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
            <a href="productos.php" class="nav-item">🎒 Productos</a>
            <a href="categorias.php" class="nav-item activo">📁 Categorías</a>
            <p class="nav-section">Ventas</p>
            <a href="pedidos.php" class="nav-item">📦 Pedidos</a>
            <a href="clientes.php" class="nav-item">👥 Clientes</a>
            <p class="nav-section">Tienda</p>
            <a href="../index.php" class="nav-item" target="_blank" rel="noopener noreferrer">
                🌐 Ver tienda
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout-sidebar">🚪 Cerrar sesión</a>
        </div>
    </div>

    <div class="main">
        <div class="page-header">
            <div>
                <h2>Categorías</h2>
                <p>Gestioná las secciones de tu tienda</p>
            </div>
            <a href="categoria-nueva.php" class="btn-nuevo">+ Nueva categoría</a>
        </div>

        <div class="tabla-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Icono</th>
                        <th>Nombre / Slug</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($cat = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td style="font-size:1.5rem;"><?= $cat['icono'] ?? '📁' ?></td>
                            <td>
                                <strong><?= htmlspecialchars($cat['nombre']) ?></strong><br>
                                <small style="color:#999;">/<?= htmlspecialchars($cat['slug']) ?></small>
                            </td>
                            <td><span style="font-weight:700;">#<?= $cat['orden_display'] ?></span></td>
                            <td>
                                <span class="badge <?= $cat['activo'] ? 'badge-activo' : 'badge-inactivo' ?>">
                                    <?= $cat['activo'] ? 'Activa' : 'Pausada' ?>
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="productos.php?categoria=<?= $cat['id'] ?>" class="btn-accion btn-ver">Ver productos</a>
                                <a href="categoria-editar.php?id=<?= $cat['id'] ?>" class="btn-accion btn-editar">Editar</a>
                                <a href="categoria-toggle.php?id=<?= $cat['id'] ?>"
                                    class="btn-accion <?= $cat['activo'] ? 'btn-pausar' : 'btn-activar' ?>"
                                    onclick="return confirm('¿Confirmar cambio de estado?')">
                                    <?= $cat['activo'] ? '⏸ Pausar' : '▶ Activar' ?>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>