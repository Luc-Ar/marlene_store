<?php
// El footer busca sus propias categorías, así no depende de que
// la página que lo incluye ya las haya cargado.
require_once __DIR__ . '/../config/Database.php';
$conexionFooter = Database::getConexion();
$catsFooter = $conexionFooter
  ->query("SELECT nombre, slug FROM categorias WHERE activo = 1 ORDER BY orden_display ASC LIMIT 5")
  ->fetch_all(MYSQLI_ASSOC);
?>
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
        <?php foreach ($catsFooter as $cat): ?>
          <li><a href="/catalogo.php?cat=<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['nombre']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Info</h4>
      <ul>
        <li><a href="/index.php#contacto">Contacto</a></li>
        <li><a href="/index.php#envios">Envíos</a></li>
        <li><a href="/index.php#contacto">Formas de pago</a></li>
        <li><a href="/catalogo.php">Productos</a></li>
        <li><a href="/mi-cuenta.php">Mi cuenta</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© <?= date('Y') ?> Marlene STORE — Todos los derechos reservados</p>
    <div class="socials">
      <a href="#" class="social-btn">📱</a>
      <a href="#" class="social-btn">📷</a>
      <a href="https://wa.me/5493704097831" class="social-btn" target="_blank">💬</a>
    </div>
  </div>
</footer>

<!-- ─── WHATSAPP FLOTANTE ─── -->
<a href="https://wa.me/5493704097831" class="wa-btn" target="_blank">
  <span class="wa-icon">💬</span>
  <span>WhatsApp</span>
</a>

<?php if (!isset($incluir_carrito) || $incluir_carrito !== false): ?>
  <?php require_once __DIR__ . '/carrito-panel.php'; ?>
<?php endif; ?>

<?php if (!empty($scripts_extra)): foreach ($scripts_extra as $script): ?>
    <script src="<?= htmlspecialchars($script) ?>"></script>
<?php endforeach;
endif; ?>
<script src="/assets/js/script.js"></script>
<script>
  document.body.style.visibility = 'visible';
</script>
</body>

</html>