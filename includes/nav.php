<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<nav>
    <a href="index.php" class="logo-wrap">
        <span class="logo-script">Marlene</span>
        <span class="logo-store">STORE</span>
    </a>
    <ul class="nav-links">
        <li><a href="index.php#categorias">Categorías</a></li>
        <li><a href="catalogo.php">Productos</a></li>
        <li><a href="index.php#envios">Envíos</a></li>
        <li><a href="index.php#contacto">Contacto</a></li>
        <?php if (isset($_SESSION['cliente_id'])): ?>
            <li class="nav-cuenta-wrap">
                <a href="mi-cuenta.php" class="nav-cuenta">
                    👤 <?= htmlspecialchars($_SESSION['cliente_nombre']) ?>
                </a>
                <div class="nav-cuenta-dropdown">
                    <a href="mi-cuenta.php">Mi cuenta</a>
                    <a href="mi-cuenta.php?tab=pedidos">Mis pedidos</a>
                    <a href="logout-cliente.php">Cerrar sesión</a>
                </div>
            </li>
        <?php else: ?>
            <li><a href="login-cliente.php" class="nav-cta">Ingresar</a></li>
        <?php endif; ?>
    </ul>
</nav>