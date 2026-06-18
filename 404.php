<?php
http_response_code(404);
$titulo = 'Página no encontrada — Marlene STORE';
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
  <p style="font-size: 6rem; margin: 0; line-height: 1;">🔍</p>
  <p class="s-eyebrow" style="margin-top: 24px;">✦ Error 404</p>
  <h1 class="s-title" style="margin-top: 8px;">Página no encontrada</h1>
  <p class="s-sub" style="max-width: 480px; margin: 16px auto 40px;">
    La página que buscás no existe o fue movida. Podés volver al inicio o explorar nuestro catálogo.
  </p>
  <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center;">
    <a href="/index.php" class="btn-main">Volver al inicio</a>
    <a href="/catalogo.php" class="btn-outline">Ver productos</a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
