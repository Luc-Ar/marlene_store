<?php
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
      <!-- Redes sociales en el brand -->
      <div class="footer-socials">
        <a href="https://www.instagram.com/lucas__arm/" target="_blank" rel="noopener" class="social-btn" aria-label="Instagram">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
          </svg>
        </a>
        <a href="https://www.facebook.com/lic.lucas.ar" target="_blank" rel="noopener" class="social-btn" aria-label="Facebook">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
          </svg>
        </a>
        <a href="https://wa.me/5493704097831" target="_blank" rel="noopener" class="social-btn" aria-label="WhatsApp">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.555 4.116 1.528 5.845L.057 23.428a.5.5 0 0 0 .609.61l5.652-1.48A11.95 11.95 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.88 0-3.638-.51-5.145-1.396l-.368-.217-3.812.998 1.015-3.707-.24-.382A9.96 9.96 0 0 1 2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
          </svg>
        </a>
      </div>
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
    <div class="socials" style="margin-right: 260px;">
      <a href="https://www.instagram.com/lucas__arm/" target="_blank" rel="noopener" class="social-btn" aria-label="Instagram">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
          <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
          <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
        </svg>
      </a>
      <a href="https://www.facebook.com/lic.lucas.ar" target="_blank" rel="noopener" class="social-btn" aria-label="Facebook">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
        </svg>
      </a>
      <a href="https://wa.me/5493704097831" target="_blank" rel="noopener" class="social-btn" aria-label="WhatsApp">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
          <path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.555 4.116 1.528 5.845L.057 23.428a.5.5 0 0 0 .609.61l5.652-1.48A11.95 11.95 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.88 0-3.638-.51-5.145-1.396l-.368-.217-3.812.998 1.015-3.707-.24-.382A9.96 9.96 0 0 1 2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
        </svg>
      </a>
    </div>
  </div>
</footer>

<!-- ─── WHATSAPP FLOTANTE ─── -->
<a href="https://wa.me/5493704097831" class="wa-btn" target="_blank" rel="noopener" aria-label="Contactar por WhatsApp">
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