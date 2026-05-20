<?php
// 1. Conexión (Ajustá con tus datos de MariaDB en Kali)
// $conexion = mysqli_connect("localhost", "admin_marlene", "marlene123", "marlene_store");
require_once __DIR__ . '/config/Database.php';
$conexion = Database::getConexion();
if (!$conexion) {
  die("Error de conexión: " . mysqli_connect_error());
}

// 2. Traer los productos
require_once __DIR__ . '/config/Database.php';
$conexion = Database::getConexion();

if (isset($_GET['cat'])) {
  $stmt = $conexion->prepare("SELECT * FROM productos WHERE categoria = ? AND activo = 1");
  $stmt->bind_param("s", $_GET['cat']);
} else {
  $stmt = $conexion->prepare("SELECT * FROM productos WHERE activo = 1");
}

$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Marlene STORE</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Montserrat:wght@300;400;500;600;700;900&display=swap"
    rel="stylesheet">

  <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>

  <nav>
    <a href="index.php" class="logo-wrap">
      <span class="logo-script">Marlene</span>
      <span class="logo-store">STORE</span>
    </a>
    <ul class="nav-links">
      <li><a href="#categorias">Categorías</a></li>
      <li><a href="catalogo.php">Productos</a></li>
      <li><a href="#envios">Envíos</a></li>
      <li><a href="#contacto">Contacto</a></li>
      <li><a href="#contacto" class="nav-cta">Consultar</a></li>
    </ul>
  </nav>

  <!-- ─── HERO ─── -->
  <section class="hero">
    <div class="hero-left">
      <p class="hero-tag">✦ Bienvenida a nuestra tienda</p>
      <h1 class="hero-title">Marlene</h1>
      <p class="hero-store">STORE</p>
      <div class="hero-divider"></div>
      <p class="hero-desc">Mochilas, termos, calzado, bazar y tecnología.<br>Todo lo que necesitás, con la calidad y el
        estilo que merecés.</p>
      <div class="hero-btns">
        <a href="#productos" class="btn-main">Ver Productos</a>
        <a href="#contacto" class="btn-outline">Consultanos</a>
      </div>
    </div>
    <div class="hero-right">
      <span class="hero-script-bg">MV</span>
      <div class="hero-emoji-showcase">
        <div class="showcase-row">

          <a href="catalogo.html#sec-infantiles" class="showcase-item" style="text-decoration:none;">
            <span class="si-emoji">🎒</span>
            <span class="si-label">Mochilas</span>
          </a>

          <div class="showcase-item"><span class="si-emoji">🍶</span><span class="si-label">Termos</span></div>
          <div class="showcase-item"><span class="si-emoji">👟</span><span class="si-label">Calzado</span></div>
        </div>
        <div class="showcase-row">
          <div class="showcase-item"><span class="si-emoji">🍴</span><span class="si-label">Bazar</span></div>
          <div class="showcase-item"><span class="si-emoji">🔋</span><span class="si-label">Tecnología</span></div>
          <div class="showcase-item"><span class="si-emoji">🔧</span><span class="si-label">Herramientas</span></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ─── RIBBON ─── -->
  <div class="ribbon">
    <div class="ribbon-item"><span class="dot"></span>🚚 Envíos por OCA y Correo Argentino</div>
    <div class="ribbon-item"><span class="dot"></span>📦 A todo el país</div>
    <div class="ribbon-item"><span class="dot"></span>🔍 Seguimiento de tu pedido</div>
    <div class="ribbon-item"><span class="dot"></span>💳 Efectivo · Transferencia · Tarjeta</div>
  </div>
  <!-- ─── CATEGORÍAS ─── -->
  <section class="cats-section" id="categorias">
    <p class="s-eyebrow">✦ Lo que encontrás</p>
    <h2 class="s-title">Nuestras Categorías</h2>
    <p class="s-sub">De todo para la familia, el hogar y el día a día.</p>

    <div class="cats-grid">

      <a href="catalogo.php?cat=mochilas-infantiles" class="cat-card" style="text-decoration:none;">
        <span class="cat-emoji">🎒</span>
        <p class="cat-name">Mochilas Infantiles</p>
        <p class="cat-desc">Diseños divertidos y resistentes</p>
      </a>

      <a href="catalogo.php?cat=mochilas-escolares" class="cat-card" style="text-decoration:none;">
        <span class="cat-emoji">📚</span>
        <p class="cat-name">Mochilas Escolares</p>
        <p class="cat-desc">Amplias para todo el material</p>
      </a>

      <a href="catalogo.php?cat=mochilas-adultos" class="cat-card" style="text-decoration:none;">
        <span class="cat-emoji">💼</span>
        <p class="cat-name">Mochilas Adultos</p>
        <p class="cat-desc">Estilo y funcionalidad</p>
      </a>

      <a href="catalogo.php?cat=termos_infantiles" class="cat-card" style="text-decoration:none;">
        <span class="cat-emoji">🍶</span>
        <p class="cat-name">Termos Infantiles</p>
        <p class="cat-desc">Coloridos y seguros para nenes</p>
      </a>

      <a href="catalogo.php?cat=termos_adultos" class="cat-card" style="text-decoration:none;">
        <span class="cat-emoji">♨️</span>
        <p class="cat-name">Termos Adultos</p>
        <p class="cat-desc">Varios tamaños, máxima temperatura</p>
      </a>

      <a href="catalogo.php?cat=zapatillas" class="cat-card" style="text-decoration:none;">
        <span class="cat-emoji">👟</span>
        <p class="cat-name">Zapatos y Zapatillas</p>
        <p class="cat-desc">Para toda la familia</p>
      </a>

      <a href="catalogo.php?cat=bazar" class="cat-card" style="text-decoration:none;">
        <span class="cat-emoji">🍴</span>
        <p class="cat-name">Bazar</p>
        <p class="cat-desc">Cubiertos, fuentes y más</p>
      </a>

      <a href="catalogo.php?cat=cargadores" class="cat-card" style="text-decoration:none;">
        <span class="cat-emoji">🔋</span>
        <p class="cat-name">Cargadores Portátiles</p>
        <p class="cat-desc">Power banks para todos los días</p>
      </a>

      <a href="catalogo.php?cat=herramientas" class="cat-card" style="text-decoration:none;">
        <span class="cat-emoji">🔧</span>
        <p class="cat-name">Cajitas de Herramientas</p>
        <p class="cat-desc">Sets completos para el hogar</p>
      </a>

      <a href="catalogo.php?cat=parlantes" class="cat-card" style="text-decoration:none;">
        <span class="cat-emoji">🔊</span>
        <p class="cat-name">Parlantes</p>
        <p class="cat-desc">Bluetooth y portátiles</p>
      </a>

    </div>
  </section>

  <!-- ─── PRODUCTOS ─── -->
  <section class="prods-section" id="productos">
    <p class="s-eyebrow">✦ Lo más pedido</p>
    <h2 class="s-title">Productos Destacados</h2>
    <p class="s-sub">Una selección especial de lo que más nos piden. ¡Consultanos por disponibilidad!</p>
    <div class="prod-grid">

      <div class="prod-card">
        <div class="prod-visual pv1">🎒<span class="prod-badge">Top ventas</span></div>
        <div class="prod-body">
          <p class="prod-cat-label">Mochilas</p>
          <h3 class="prod-name">Mochila Infantil Estampada</h3>
          <p class="prod-desc">Diseños únicos, compartimentos amplios y correas acolchadas para los más chicos.</p>
          <div class="prod-foot">
            <span class="prod-price">CONSULTAR PRECIO</span>
            <button class="prod-btn">Lo quiero →</button>
          </div>
        </div>
      </div>

      <div class="prod-card">
        <div class="prod-visual pv2">🍶<span class="prod-badge">Nuevo</span></div>
        <div class="prod-body">
          <p class="prod-cat-label">Termos</p>
          <h3 class="prod-name">Termo Acero Inoxidable</h3>
          <p class="prod-desc">Mantiene temperatura 24 hs. Disponible en varios colores y tamaños para toda la familia.
          </p>
          <div class="prod-foot">
            <span class="prod-price">CONSULTAR PRECIO</span>
            <button class="prod-btn">Lo quiero →</button>
          </div>
        </div>
      </div>

      <div class="prod-card">
        <div class="prod-visual pv3">👟</div>
        <div class="prod-body">
          <p class="prod-cat-label">Calzado</p>
          <h3 class="prod-name">Zapatillas Urbanas</h3>
          <p class="prod-desc">Cómodas y resistentes para el día a día. Varios talles disponibles para niños y adultos.
          </p>
          <div class="prod-foot">
            <span class="prod-price">CONSULTAR PRECIO</span>
            <button class="prod-btn">Lo quiero →</button>
          </div>
        </div>
      </div>

      <div class="prod-card">
        <div class="prod-visual pv4">🔊<span class="prod-badge">Oferta</span></div>
        <div class="prod-body">
          <p class="prod-cat-label">Tecnología</p>
          <h3 class="prod-name">Parlante Bluetooth</h3>
          <p class="prod-desc">Sonido potente y resistente. Ideal para llevar a todos lados y disfrutar la música.</p>
          <div class="prod-foot">
            <span class="prod-price">CONSULTAR PRECIO</span>
            <button class="prod-btn">Lo quiero →</button>
          </div>
        </div>
      </div>

      <div class="prod-card">
        <div class="prod-visual pv5">🍴</div>
        <div class="prod-body">
          <p class="prod-cat-label">Bazar</p>
          <h3 class="prod-name">Set de Cubiertos Premium</h3>
          <p class="prod-desc">Acero inoxidable, set completo. Ideal como regalo o para renovar la mesa del hogar.</p>
          <div class="prod-foot">
            <span class="prod-price">CONSULTAR PRECIO</span>
            <button class="prod-btn">Lo quiero →</button>
          </div>
        </div>
      </div>

      <div class="prod-card">
        <div class="prod-visual pv6">🔋<span class="prod-badge">Top ventas</span></div>
        <div class="prod-body">
          <p class="prod-cat-label">Tecnología</p>
          <h3 class="prod-name">Power Bank 10.000 mAh</h3>
          <p class="prod-desc">Cargá tu celular 2 a 3 veces. Compacto, liviano y compatible con todos los dispositivos.
          </p>
          <div class="prod-foot">
            <span class="prod-price">CONSULTAR PRECIO</span>
            <button class="prod-btn">Lo quiero →</button>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ─── BANNER MID ─── -->
  <div class="banner-mid">
    <p class="s-eyebrow">✦ Siempre para vos</p>
    <h2 class="bm-title">¿No encontrás lo que buscás?</h2>
    <p class="bm-sub">Escribinos por WhatsApp y te ayudamos a encontrar exactamente lo que necesitás. ¡Respondemos
      rápido!</p>
    <a href="#contacto" class="btn-main">Escribinos ahora</a>
  </div>

  <!-- ─── CONTACTO ─── -->
  <section class="contacto-section" id="contacto">
    <p class="s-eyebrow">✦ Hablemos</p>
    <h2 class="s-title">Contacto</h2>
    <p class="s-sub">Estamos para ayudarte. Respondemos rápido y con mucho cariño.</p>

    <div class="contact-layout">
      <div class="contact-left">
        <h3>Estamos para vos</h3>
        <p>Cualquier consulta, pedido especial o duda que tengas, no dudes en escribirnos. Atención personalizada
          siempre.</p>
        <div class="c-info-item">
          <div class="c-icon">📱</div>
          <div><strong>WhatsApp</strong><span>La forma más rápida de comunicarte con nosotras</span></div>
        </div>
        <div class="c-info-item">
          <div class="c-icon">📷</div>
          <div><strong>Instagram</strong><span>Seguinos para ver novedades y ofertas especiales</span></div>
        </div>
        <div class="c-info-item">
          <div class="c-icon">🚚</div>
          <div><strong>Envíos</strong><span>Enviamos a todo el país, consultá costos y tiempos</span></div>
        </div>
        <div class="c-info-item">
          <div class="c-icon">💳</div>
          <div><strong>Formas de pago</strong><span>Efectivo, transferencia, tarjeta y más opciones</span></div>
        </div>
      </div>

      <div class="contact-form">
        <h3 class="cf-title">Envianos un mensaje</h3>
        <div class="cf-row">
          <div class="cf-group">
            <label>Nombre</label>
            <input type="text" placeholder="Tu nombre">
          </div>
          <div class="cf-group">
            <label>Teléfono / WhatsApp</label>
            <input type="tel" placeholder="Tu número">
          </div>
        </div>
        <div class="cf-group">
          <label>¿Qué te interesa?</label>
          <select>
            <option value="">Seleccioná una categoría</option>
            <option>Mochilas infantiles</option>
            <option>Mochilas escolares</option>
            <option>Mochilas para adultos</option>
            <option>Termos infantiles</option>
            <option>Termos para adultos</option>
            <option>Zapatos y zapatillas</option>
            <option>Bazar (cubiertos, fuentes...)</option>
            <option>Cargadores portátiles</option>
            <option>Cajitas de herramientas</option>
            <option>Parlantes</option>
            <option>Otro / Varios productos</option>
          </select>
        </div>
        <div class="cf-group">
          <label>Mensaje</label>
          <textarea placeholder="Contanos qué necesitás, qué talle, color o cualquier detalle..."></textarea>
        </div>
        <button class="cf-submit">Enviar mensaje →</button>
      </div>
    </div>
  </section>

  <!-- ─── ENVÍOS ─── -->
  <section class="envios-section" id="envios">
    <p class="s-eyebrow">✦ Enviamos a todo el país</p>
    <h2 class="s-title">Información de Envíos</h2>
    <p class="s-sub">Trabajamos con los mejores servicios de logística para que tu pedido llegue seguro y a tiempo.</p>

    <div class="envios-grid">

      <div class="envio-card">
        <div class="envio-icon">📦</div>
        <h3>OCA</h3>
        <p>Servicio rápido y confiable. Cobertura en todo el país con seguimiento en tiempo real.</p>
        <ul>
          <li>✓ Entrega en 2 a 5 días hábiles</li>
          <li>✓ Seguimiento online</li>
          <li>✓ Retiro en sucursal disponible</li>
        </ul>
      </div>

      <div class="envio-card">
        <div class="envio-icon">🏣</div>
        <h3>Correo Argentino</h3>
        <p>Llega hasta los rincones más remotos del país. Ideal para zonas donde OCA no cubre.</p>
        <ul>
          <li>✓ Cobertura nacional total</li>
          <li>✓ Entrega en 3 a 7 días hábiles</li>
          <li>✓ Retiro en sucursal disponible</li>
        </ul>
      </div>

      <div class="envio-card">
        <div class="envio-icon">💬</div>
        <h3>¿Cómo comprar?</h3>
        <p>El proceso es simple y rápido. Te acompañamos en cada paso.</p>
        <ul>
          <li>1️⃣ Elegís tu producto</li>
          <li>2️⃣ Nos escribís por WhatsApp</li>
          <li>3️⃣ Coordinamos el envío y el pago</li>
          <li>4️⃣ ¡Tu pedido en camino!</li>
        </ul>
      </div>

    </div>

    <div class="envios-faq">
      <h3>Preguntas frecuentes</h3>
      <div class="faq-grid">
        <div class="faq-item">
          <strong>¿Cuánto cuesta el envío?</strong>
          <p>El costo varía según tu provincia y el peso del paquete. Consultanos por WhatsApp y te damos el precio exacto.</p>
        </div>
        <div class="faq-item">
          <strong>¿Cuándo sale mi pedido?</strong>
          <p>Despachamos dentro de las 48 hs hábiles de confirmado el pago.</p>
        </div>
        <div class="faq-item">
          <strong>¿Cómo hago el seguimiento?</strong>
          <p>Una vez despachado te mandamos el número de tracking para que puedas seguir tu paquete online.</p>
        </div>
        <div class="faq-item">
          <strong>¿Hacen entrega en mano?</strong>
          <p>Sí, dependiendo de la zona podemos coordinar entrega en mano. Consultanos.</p>
        </div>
      </div>
    </div>

    <div style="text-align:center; margin-top: 48px;">
      <a href="https://wa.me/5493704097831" class="btn-main" target="_blank">Consultar envío por WhatsApp</a>
    </div>
  </section>

  <!-- ─── FOOTER ─── -->
  <footer>
    <div class="footer-top">
      <div class="footer-brand">
        <div class="logo-wrap">
          <span class="logo-script">Marlene</span>
          <span class="logo-store">STORE</span>
        </div>
        <p>Tu tienda de confianza. Mochilas, termos, calzado, bazar y tecnología para toda la familia.</p>
      </div>
      <div class="footer-col">
        <h4>Categorías</h4>
        <ul>
          <li><a href="#categorias">Mochilas</a></li>
          <li><a href="#categorias">Termos</a></li>
          <li><a href="#categorias">Calzado</a></li>
          <li><a href="#categorias">Bazar</a></li>
          <li><a href="#categorias">Tecnología</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Info</h4>
        <ul>
          <li><a href="#contacto">Contacto</a></li>
          <li><a href="#contacto">Envíos</a></li>
          <li><a href="#contacto">Formas de pago</a></li>
          <li><a href="#productos">Productos</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2026 Marlene STORE — Todos los derechos reservados</p>
      <div class="socials">
        <a href="#" class="social-btn">📱</a>
        <a href="#" class="social-btn">📷</a>
        <a href="#" class="social-btn">💬</a>
      </div>
    </div>
  </footer>

  <!-- ─── WHATSAPP FLOTANTE ─── -->
  <a href="https://wa.me/54911XXXXXXXX" class="wa-btn" target="_blank">
    <span class="wa-icon">💬</span>
    <span>WhatsApp</span>
  </a>


  <script src="assets/js/script.js"></script>
</body>

</html>