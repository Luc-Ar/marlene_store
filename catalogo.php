<?php
session_start();
require_once 'config/Database.php';

try {
  $conexion = Database::getConexion();
  // Traemos los productos activos
  $resultado = $conexion->query("SELECT * FROM productos WHERE activo = 1 ORDER BY id DESC");
  $productos = $resultado->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
  die("Error al conectar con el catálogo: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catálogo — Marlene Velazquez STORE</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Montserrat:wght@300;400;500;600;700;900&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="catalogo.css">
</head>

<body>

  <!-- ─── CARRITO FLOTANTE ─── -->
  <button class="carrito-btn-flotante" onclick="abrirCarrito()">
    🛒 Carrito
    <span class="carrito-badge" id="carrito-badge">0</span>
  </button>

  <!-- ─── OVERLAY + PANEL DEL CARRITO ─── -->
  <div class="carrito-overlay" id="carrito-overlay" onclick="cerrarCarrito()"></div>
  <div class="carrito-panel" id="carrito-panel">
    <div class="carrito-panel-header">
      <h2>Mi pedido</h2>
      <button class="carrito-cerrar" onclick="cerrarCarrito()">✕</button>
    </div>
    <div class="carrito-items" id="carrito-items">
      <div class="carrito-vacio">
        <p>🛒</p>
        <p>Tu carrito está vacío</p>
      </div>
    </div>
    <div class="carrito-panel-footer" id="carrito-footer" style="display:none;">
      <p class="carrito-resumen">Total de productos: <span id="carrito-total">0</span></p>
      <p class="carrito-resumen">Total: <span id="carrito-subtotal">$0</span></p>
      <button class="carrito-wa-btn" onclick="enviarPorWhatsapp()">
        💬 Enviar pedido por WhatsApp
      </button>
      <button class="btn-vaciar" onclick="vaciarCarrito()">Vaciar carrito</button>
    </div>
  </div>

  <!-- ─── NAV ─── -->
  <nav>
    <a href="index.html" class="logo-wrap">
      <span class="logo-script">Marlene Velazquez</span>
      <span class="logo-store">STORE</span>
    </a>
    <ul class="nav-links">
      <li><a href="index.html#categorias">Categorías</a></li>
      <li><a href="index.html#productos">Productos</a></li>
      <li><a href="index.html#contacto">Contacto</a></li>
      <li><a href="index.html#contacto" class="nav-cta">Consultar</a></li>
    </ul>
  </nav>

  <!-- ─── HEADER DE CATEGORÍA ─── -->
  <div class="cat-header">
    <div class="cat-header-inner">
      <div>
        <p class="cat-breadcrumb">
          <a href="index.html">Inicio</a>
          <span>›</span>
          <span>Mochilas</span>
        </p>
        <h1 class="cat-header-title">Mochilas</h1>
      </div>
    </div>
  </div>

  <!-- ─── FILTROS ─── -->
  <div class="filtros" id="filtros-container"></div>

  <!-- ─── MOCHILAS INFANTILES ─── -->
  <section class="catalogo-section" id="sec-infantiles">
    <div class="sub-titulo">
      <h2>🎒 Infantiles</h2>
    </div>
    <div class="catalogo-grid">

      <div class="cat-prod-card">
        <div class="cat-prod-img bg1">
          <img src="imagenes/mochila_infantil_frozen.avif" alt="Mochila Frozen">
          <span class="cat-prod-badge">Top ventas</span>
        </div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Infantil</p>
          <h3 class="cat-prod-name">Mochila Frozen</h3>
          <p class="cat-prod-desc">Diseño de Frozen con colores vibrantes. Ideal para jardín y primaria.</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$12.500</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Frozen', 'imagenes/mochila_infantil_frozen.avif', 'Infantil', 12500)">+
              Agregar</button>
          </div>
        </div>
      </div>

      <div class="cat-prod-card">
        <div class="cat-prod-img bg5">
          <img src="imagenes/mochila_infantil_kuromi.avif" alt="Mochila Kuromi">
          <span class="cat-prod-badge nuevo">Nuevo</span>
        </div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Infantil</p>
          <h3 class="cat-prod-name">Mochila Kuromi</h3>
          <p class="cat-prod-desc">La favorita de las fans de Kuromi. Diseño tierno y resistente.</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$12.500</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Kuromi', 'imagenes/mochila_infantil_kuromi.avif', 'Infantil', 12500)">+
              Agregar</button>
          </div>
        </div>
      </div>

      <div class="cat-prod-card">
        <div class="cat-prod-img bg2">
          <img src="imagenes/mochila_infantil_paw.avif" alt="Mochila Paw Patrol">
        </div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Infantil</p>
          <h3 class="cat-prod-name">Mochila Paw Patrol</h3>
          <p class="cat-prod-desc">Con los personajes de la Patrulla Canina. Perfecta para los más chicos.</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$12.500</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Paw Patrol', 'imagenes/mochila_infantil_paw.avif', 'Infantil', 12500)">+
              Agregar</button>
          </div>
        </div>
      </div>

      <div class="cat-prod-card">
        <div class="cat-prod-img bg3">
          <img src="imagenes/mochila_infantil_princesa_sofia.avif" alt="Mochila Princesa Sofía">
        </div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Infantil</p>
          <h3 class="cat-prod-name">Mochila Princesa Sofía</h3>
          <p class="cat-prod-desc">Diseño de Princesa Sofía, liviana y con compartimentos amplios.</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$12.500</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Princesa Sofía', 'imagenes/mochila_infantil_princesa_sofia.avif', 'Infantil', 12500)">+
              Agregar</button>
          </div>
        </div>
      </div>

      <div class="cat-prod-card">
        <div class="cat-prod-img bg2">
          <img src="imagenes/mochila_infantil_sm2.avif" alt="Mochila Spider-Man">
          <span class="cat-prod-badge">Top ventas</span>
        </div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Infantil</p>
          <h3 class="cat-prod-name">Mochila Spider-Man</h3>
          <p class="cat-prod-desc">El superhéroe favorito de los nenes. Resistente y espaciosa.</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$12.500</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Spider-Man', 'imagenes/mochila_infantil_sm2.avif', 'Infantil', 12500)">+
              Agregar</button>
          </div>
        </div>
      </div>

      <div class="cat-prod-card">
        <div class="cat-prod-img bg2">
          <img src="imagenes/mochila_infantill_sm.avif" alt="Mochila Spider-Man Mini">
        </div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Infantil</p>
          <h3 class="cat-prod-name">Mochila Spider-Man Mini</h3>
          <p class="cat-prod-desc">Versión más chica, ideal para los más pequeños del jardín.</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$12.500</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Spider-Man Mini', 'imagenes/mochila_infantill_sm.avif', 'Infantil', 12500)">+
              Agregar</button>
          </div>
        </div>
      </div>

      <div class="cat-prod-card">
        <div class="cat-prod-img bg1">
          <img src="imagenes/mochila_infantil_stich.avif" alt="Mochila Stitch">
          <span class="cat-prod-badge nuevo">Nuevo</span>
        </div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Infantil</p>
          <h3 class="cat-prod-name">Mochila Stitch</h3>
          <p class="cat-prod-desc">El adorable Stitch en una mochila súper tierna y resistente.</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$12.500</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Stitch', 'imagenes/mochila_infantil_stich.avif', 'Infantil', 12500)">+
              Agregar</button>
          </div>
        </div>
      </div>

      <div class="cat-prod-card">
        <div class="cat-prod-img bg5">
          <img src="imagenes/mochila_infantil_stichrosa.avif" alt="Mochila Stitch Rosa">
        </div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Infantil</p>
          <h3 class="cat-prod-name">Mochila Stitch Rosa</h3>
          <p class="cat-prod-desc">Stitch en versión rosa, perfecta para las nenas que aman este personaje.</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$12.500</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Stitch Rosa', 'imagenes/mochila_infantil_stichrosa.avif', 'Infantil', 12500)">+
              Agregar</button>
          </div>
        </div>
      </div>

      <div class="cat-prod-card">
        <div class="cat-prod-img bg1">
          <img src="imagenes/mochila_infantil_stitch2.avif" alt="Mochila Stitch Azul">
        </div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Infantil</p>
          <h3 class="cat-prod-name">Mochila Stitch Azul</h3>
          <p class="cat-prod-desc">Stitch en su versión clásica azul. Un clásico que nunca falla.</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$12.500</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Stitch Azul', 'imagenes/mochila_infantil_stitch2.avif', 'Infantil', 12500)">+
              Agregar</button>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ─── MOCHILAS ESCOLARES ─── -->
  <section class="catalogo-section" id="sec-escolares" style="background: var(--crema2);">
    <div class="sub-titulo">
      <h2>📚 Escolares</h2>
    </div>
    <div class="catalogo-grid">

      <div class="cat-prod-card">
        <div class="cat-prod-img bg3">🎒<span class="cat-prod-badge">Top ventas</span></div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Escolar</p>
          <h3 class="cat-prod-name">Mochila Escolar Clásica</h3>
          <p class="cat-prod-desc">Amplia, con refuerzo en la espalda y compartimento para notebook.</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$16.500</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Escolar Clásica', '🎒', 'Escolar', 16500)">+ Agregar</button>
          </div>
        </div>
      </div>

      <div class="cat-prod-card">
        <div class="cat-prod-img bg1">🌈<span class="cat-prod-badge nuevo">Nuevo</span></div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Escolar</p>
          <h3 class="cat-prod-name">Mochila Color Block</h3>
          <p class="cat-prod-desc">Diseño por bloques de colores, moderna y espaciosa para todo el material.</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$17.500</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Color Block', '🌈', 'Escolar', 17500)">+ Agregar</button>
          </div>
        </div>
      </div>

      <div class="cat-prod-card">
        <div class="cat-prod-img bg6">⚽</div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Escolar</p>
          <h3 class="cat-prod-name">Mochila Deportiva Escolar</h3>
          <p class="cat-prod-desc">Perfecta para el cole y el club. Bolsillo lateral para botella.</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$18.500</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Deportiva Escolar', '⚽', 'Escolar', 18500)">+ Agregar</button>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ─── MOCHILAS ADULTOS ─── -->
  <section class="catalogo-section" id="sec-adultos">
    <div class="sub-titulo">
      <h2>💼 Adultos</h2>
    </div>
    <div class="catalogo-grid">

      <div class="cat-prod-card">
        <div class="cat-prod-img bg2">💼<span class="cat-prod-badge">Top ventas</span></div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Adulto</p>
          <h3 class="cat-prod-name">Mochila Urbana</h3>
          <p class="cat-prod-desc">Estilo y funcionalidad. Compartimento acolchado para notebook de 15".</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$22.000</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Urbana', '💼', 'Adulto', 22000)">+ Agregar</button>
          </div>
        </div>
      </div>

      <div class="cat-prod-card">
        <div class="cat-prod-img bg4">🏔️<span class="cat-prod-badge nuevo">Nuevo</span></div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Adulto</p>
          <h3 class="cat-prod-name">Mochila Trekking</h3>
          <p class="cat-prod-desc">Resistente al agua, espalda ergonómica. Para el trabajo o el viaje.</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$24.000</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Trekking', '🏔️', 'Adulto', 24000)">+ Agregar</button>
          </div>
        </div>
      </div>

      <div class="cat-prod-card">
        <div class="cat-prod-img bg5">👜<span class="cat-prod-badge oferta">Oferta</span></div>
        <div class="cat-prod-body">
          <p class="cat-prod-sub">Adulto</p>
          <h3 class="cat-prod-name">Mochila Dama Casual</h3>
          <p class="cat-prod-desc">Elegante y práctica. Varios colores disponibles, perfecta para el día a día.</p>
          <div class="cat-prod-foot">
            <span class="cat-prod-precio">$22.000</span>
            <button class="cat-prod-btn-agregar"
              onclick="agregarAlCarrito(this, 'Mochila Dama Casual', '👜', 'Adulto', 22000)">+ Agregar</button>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ─── BANNER INFERIOR ─── -->
  <div class="banner-mid">
    <p class="s-eyebrow">✦ Siempre para vos</p>
    <h2 class="bm-title">¿No encontrás lo que buscás?</h2>
    <p class="bm-sub">Escribinos por WhatsApp y te ayudamos a encontrar exactamente lo que necesitás.</p>
    <a href="index.html#contacto" class="btn-main">Escribinos ahora</a>
  </div>

  <!-- ─── FOOTER ─── -->
  <footer>
    <div class="footer-top">
      <div class="footer-brand">
        <div class="logo-wrap">
          <span class="logo-script">Marlene Velazquez</span>
          <span class="logo-store">STORE</span>
        </div>
        <p>Tu tienda de confianza. Mochilas, termos, calzado, bazar y tecnología para toda la familia.</p>
      </div>
      <div class="footer-col">
        <h4>Categorías</h4>
        <ul>
          <li><a href="catalogo.html">Mochilas</a></li>
          <li><a href="#">Termos</a></li>
          <li><a href="#">Calzado</a></li>
          <li><a href="#">Bazar</a></li>
          <li><a href="#">Tecnología</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Info</h4>
        <ul>
          <li><a href="index.html#contacto">Contacto</a></li>
          <li><a href="index.html#contacto">Envíos</a></li>
          <li><a href="index.html#contacto">Formas de pago</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2025 Marlene Velazquez STORE — Todos los derechos reservados</p>
      <div class="socials">
        <a href="#" class="social-btn">📱</a>
        <a href="#" class="social-btn">📷</a>
        <a href="#" class="social-btn">💬</a>
      </div>
    </div>
  </footer>

  <!-- ─── WHATSAPP FLOTANTE ─── -->
  <a href="https://wa.me/5493704097831" class="wa-btn" target="_blank">
    <span class="wa-icon">💬</span>
    <span>WhatsApp</span>
  </a>

  <!-- ─── SCRIPTS ─── -->
  <script src="catalogo.js"></script>

</body>

</html>