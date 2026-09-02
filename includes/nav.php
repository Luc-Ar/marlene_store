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
            <li class="nav-cuenta-wrap" id="nav-cuenta-wrap">
                <button type="button" class="nav-cuenta" id="btn-nav-cuenta" onclick="toggleMenuCuenta(event)">
                    👤 <?= htmlspecialchars($_SESSION['cliente_nombre']) ?>
                </button>
                <div class="nav-cuenta-dropdown" id="nav-cuenta-dropdown">
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
<script>
    function toggleMenuCuenta(e) {
        e.stopPropagation();
        document.getElementById('nav-cuenta-wrap').classList.toggle('abierto');
    }
    // Cerrar el menú si se hace click en cualquier otro lado de la página
    document.addEventListener('click', () => {
        const wrap = document.getElementById('nav-cuenta-wrap');
        if (wrap) wrap.classList.remove('abierto');
    });
</script>