<?php if (session_status() === PHP_SESSION_NONE) session_start();

// Proteger todas las páginas del admin — redirigir si no está logueado
// Se puede desactivar en login.php definiendo $sin_auth = true antes del require
if (empty($sin_auth) && !isset($_SESSION['usuario_id'])) {
    header('Location: /admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_admin ?? 'Panel Admin') ?> — Marlene Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <?php if (!empty($scripts_head)): foreach ($scripts_head as $src): ?>
            <script src="<?= htmlspecialchars($src) ?>"></script>
    <?php endforeach;
    endif; ?>
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

        /* ─── SIDEBAR ─── */
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

        /* ─── MAIN ─── */
        .main {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 36px 40px;
        }

        /* ─── UTILITARIOS COMUNES ─── */
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

        .alert-ok {
            background: #DCFCE7;
            color: #166534;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 0.82rem;
            margin-bottom: 20px;
        }

        .alert-err {
            background: #FEE2E2;
            color: #991B1B;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 0.82rem;
            margin-bottom: 20px;
        }
    </style>
    <?php if (!empty($estilos_extra_admin)): ?>
        <style>
            <?= $estilos_extra_admin ?>
        </style>
    <?php endif; ?>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-script">Marlene</span>
            <span class="logo-store">Store</span>
        </div>
        <nav class="sidebar-nav">
            <p class="nav-section">Principal</p>
            <a href="/admin/index.php" class="nav-item <?= ($nav_activo ?? '') === 'dashboard' ? 'activo' : '' ?>">📊 Dashboard</a>
            <p class="nav-section">Catálogo</p>
            <a href="/admin/productos.php" class="nav-item <?= ($nav_activo ?? '') === 'productos' ? 'activo' : '' ?>">🎒 Productos</a>
            <a href="/admin/categorias.php" class="nav-item <?= ($nav_activo ?? '') === 'categorias' ? 'activo' : '' ?>">📁 Categorías</a>
            <p class="nav-section">Ventas</p>
            <a href="/admin/pedidos.php" class="nav-item <?= ($nav_activo ?? '') === 'pedidos' ? 'activo' : '' ?>">📦 Pedidos</a>
            <a href="/admin/clientes.php" class="nav-item <?= ($nav_activo ?? '') === 'clientes' ? 'activo' : '' ?>">👥 Clientes</a>
            <p class="nav-section">Tienda</p>
            <a href="/index.php" class="nav-item" target="_blank">🌐 Ver tienda</a>
        </nav>
        <div class="sidebar-footer">
            <a href="/admin/logout.php" class="btn-logout-sidebar">🚪 Cerrar sesión</a>
        </div>
    </div>

    <div class="main">