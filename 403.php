<?php
http_response_code(403);
$titulo = 'Acceso denegado — Marlene STORE';
require_once __DIR__ . '/includes/header.php';
?>

<section style="
  min-height: 60vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 80px 24px;
">
  <p style="font-size: 6rem; margin: 0; line-height: 1;">🔒</p>
  <p class="s-eyebrow" style="margin-top: 24px;">✦ Error 403</p>
  <h1 class="s-title" style="margin-top: 8px;">Acceso denegado</h1>
  <p class="s-sub" style="max-width: 480px; margin: 16px auto 40px;">
    No tenés permiso para ver esta página. Si creés que es un error, por favor contactanos.
  </p>
  <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center;">
    <a href="/index.php" class="btn-main">Volver al inicio</a>
    <a href="/index.php#contacto" class="btn-outline">Contactanos</a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
