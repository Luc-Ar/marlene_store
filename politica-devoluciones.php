<?php
require_once __DIR__ . '/includes/error-handler.php';

$titulo          = 'Política de Devoluciones — Marlene STORE';
$meta_descripcion = 'Conocé cómo funcionan las devoluciones y cambios en Marlene STORE.';

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
.caso-card { background: var(--crema2); border-radius: 10px; padding: 20px 24px; margin: 16px 0 28px; }
.caso-card h3 { font-family: "Montserrat", sans-serif; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--dorado); margin-bottom: 10px; }
';

require_once __DIR__ . '/includes/header.php';
?>

<div class="legal-wrap">
    <div class="legal-nav">
        <a href="/terminos-condiciones.php">Términos y Condiciones</a>
        <a href="/politica-privacidad.php">Privacidad</a>
        <a href="/politica-devoluciones.php">Devoluciones</a>
    </div>

    <h1>Política de Devoluciones</h1>
    <p class="legal-fecha">Última actualización: <?= date('d/m/Y') ?></p>

    <p>
        En Marlene STORE distinguimos dos situaciones distintas a la hora de una
        devolución, cada una con sus propias condiciones:
    </p>

    <h2>1. Botón de arrepentimiento (10 días, sin necesidad de motivo)</h2>
    <div class="caso-card">
        <h3>Aplica cuando el cliente simplemente cambia de opinión</h3>
        <p>
            De acuerdo con la Ley de Defensa del Consumidor (24.240), tenés derecho
            a arrepentirte de tu compra dentro de los <strong>10 días corridos</strong>
            desde que recibiste el producto, sin necesidad de justificar el motivo.
        </p>
        <ul>
            <li>El costo de envío de devolución corre por cuenta del cliente.</li>
            <li>El producto debe devolverse sin uso, en su empaque original y con
                todas sus etiquetas.</li>
            <li>Una vez que recibimos y verificamos el producto, se realiza el
                <strong>reintegro del dinero</strong> abonado.
            </li>
        </ul>
        <p>
            Para iniciar este proceso, escribinos a
            <strong>pedidos@marlene-store.com.ar</strong> dentro del plazo indicado.
        </p>
    </div>

    <h2>2. Producto con fallas o error en el despacho</h2>
    <div class="caso-card">
        <h3>Aplica cuando el problema es responsabilidad de Marlene STORE</h3>
        <p>
            Si tu producto llegó roto, defectuoso, o si te enviamos algo distinto a
            lo que compraste, contactanos a la brevedad a
            <strong>pedidos@marlene-store.com.ar</strong> contándonos qué pasó,
            adjuntando fotos del producto recibido.
        </p>
        <ul>
            <li>El costo del envío de devolución corre por cuenta de
                <strong>Marlene STORE</strong>.
            </li>
            <li>Cada caso se analiza de forma individual antes de confirmar la
                solución.</li>
            <li>Una vez aprobado el reclamo, la solución es el
                <strong>cambio del producto</strong> por uno nuevo en buen estado
                (no se realiza reintegro de dinero en estos casos).
            </li>
        </ul>
    </div>

    <h2>3. Excepciones</h2>
    <p>
        No se aceptan devoluciones por arrepentimiento en productos que hayan sido
        usados, lavados, o que no conserven su empaque y etiquetas originales.
    </p>

    <h2>4. ¿Cómo iniciar una devolución o reclamo?</h2>
    <p>
        Escribinos a <strong>pedidos@marlene-store.com.ar</strong> indicando tu
        número de pedido y el motivo de la devolución. Te vamos a responder con los
        pasos a seguir.
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>