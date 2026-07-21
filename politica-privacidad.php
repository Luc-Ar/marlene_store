<?php
require_once __DIR__ . '/includes/error-handler.php';

$titulo          = 'Política de Privacidad — Marlene STORE';
$meta_descripcion = 'Cómo Marlene STORE recolecta, usa y protege tus datos personales.';

$estilos_extra = '
.legal-wrap { max-width: 820px; margin: 60px auto; padding: 0 24px; }
.legal-wrap h1 { font-family: "Cormorant Garamond", serif; font-size: 2.4rem; color: var(--marron); margin-bottom: 8px; }
.legal-wrap .legal-fecha { font-size: 0.8rem; color: #999; margin-bottom: 40px; }
.legal-wrap h2 { font-family: "Cormorant Garamond", serif; font-size: 1.4rem; color: var(--marron); margin-top: 36px; margin-bottom: 12px; border-bottom: 1px solid var(--crema2); padding-bottom: 8px; }
.legal-wrap p, .legal-wrap li { font-size: 0.92rem; line-height: 1.7; color: var(--texto); margin-bottom: 12px; }
.legal-wrap ul { padding-left: 22px; }
.legal-wrap strong { color: var(--marron); }
.legal-nav { display: flex; gap: 20px; margin-bottom: 30px; font-size: 0.8rem; }
.legal-nav a { color: var(--dorado); text-decoration: none; font-weight: 600; }
.legal-nav a:hover { text-decoration: underline; }
';

require_once __DIR__ . '/includes/header.php';
?>

<div class="legal-wrap">
    <div class="legal-nav">
        <a href="/terminos-condiciones.php">Términos y Condiciones</a>
        <a href="/politica-privacidad.php">Privacidad</a>
        <a href="/politica-devoluciones.php">Devoluciones</a>
    </div>

    <h1>Política de Privacidad</h1>
    <p class="legal-fecha">Última actualización: <?= date('d/m/Y') ?></p>

    <h2>1. Qué datos recolectamos</h2>
    <p>Cuando comprás o creás una cuenta en Marlene STORE, recolectamos:</p>
    <ul>
        <li>Nombre y apellido</li>
        <li>Email y teléfono</li>
        <li>Dirección de envío</li>
    </ul>
    <p>
        No recolectamos datos de tarjetas de crédito ni información financiera —
        eso lo procesa directamente MercadoPago (ver punto 3).
    </p>

    <h2>2. Para qué usamos tus datos</h2>
    <ul>
        <li>Procesar y despachar tus pedidos</li>
        <li>Comunicarnos con vos sobre el estado de tu compra</li>
        <li>Responder consultas o reclamos</li>
    </ul>
    <p>
        Actualmente no enviamos newsletters ni comunicaciones promocionales, y no
        utilizamos herramientas de análisis o cookies de seguimiento publicitario en
        el sitio. Si esto cambia en el futuro, vamos a actualizar esta política.
    </p>

    <h2>3. Terceros con los que compartimos información</h2>
    <p>
        Tus datos se comparten únicamente con los proveedores necesarios para
        completar tu compra:
    </p>
    <ul>
        <li><strong>MercadoPago</strong>, para procesar el pago</li>
        <li>La <strong>empresa de transporte/correo</strong> elegida, para realizar la entrega</li>
    </ul>
    <p>
        Marlene STORE no vende ni cede tus datos personales a terceros con fines
        comerciales o publicitarios.
    </p>

    <h2>4. Cómo protegemos tus datos</h2>
    <p>
        El sitio utiliza conexión segura (HTTPS) en todas sus páginas. El acceso a
        la base de datos está restringido y las contraseñas de usuarios se almacenan
        de forma encriptada, nunca en texto plano.
    </p>

    <h2>5. Tus derechos</h2>
    <p>
        Podés solicitar acceder, corregir o eliminar tus datos personales en
        cualquier momento escribiéndonos a
        <strong>pedidos@marlene-store.com.ar</strong>.
    </p>

    <h2>6. Cookies</h2>
    <p>
        El sitio utiliza almacenamiento local del navegador (localStorage) para
        recordar el contenido de tu carrito de compras mientras navegás, y una
        cookie de sesión para mantener tu inicio de sesión activo. No se usan
        cookies de terceros ni de seguimiento publicitario.
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>