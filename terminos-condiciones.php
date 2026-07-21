<?php
require_once __DIR__ . '/includes/error-handler.php';

$titulo          = 'Términos y Condiciones — Marlene STORE';
$meta_descripcion = 'Términos y condiciones de uso y compra en Marlene STORE.';

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

    <h1>Términos y Condiciones</h1>
    <p class="legal-fecha">Última actualización: <?= date('d/m/Y') ?></p>

    <h2>1. Identificación</h2>
    <p>
        El presente sitio web (<strong>marlene-store.com.ar</strong>) es operado por
        <strong>Lucas Armoa</strong>, CUIT 23-33481235-9, con domicilio en Formosa,
        provincia de Formosa, Argentina. Para consultas, reclamos o cualquier tipo de
        comunicación, podés escribir a <strong>pedidos@marlene-store.com.ar</strong>.
    </p>

    <h2>2. Objeto</h2>
    <p>
        Marlene STORE ofrece la venta online de mochilas, termos, calzado, artículos
        de bazar y tecnología. La navegación y/o compra en este sitio implica la
        aceptación de los presentes Términos y Condiciones.
    </p>

    <h2>3. Productos, precios y disponibilidad</h2>
    <p>
        Los precios publicados están expresados en pesos argentinos (ARS) e incluyen
        los impuestos correspondientes. Marlene STORE se reserva el derecho de
        modificar precios y stock sin previo aviso. Las imágenes de los productos son
        ilustrativas y pueden presentar leves diferencias respecto del producto real.
    </p>
    <p>
        En caso de un error de precio o descripción evidente en el sitio, Marlene
        STORE se reserva el derecho de cancelar el pedido, notificando al cliente
        y reintegrando el importe abonado.
    </p>

    <h2>4. Proceso de compra y pago</h2>
    <p>
        Las compras se procesan a través de la plataforma MercadoPago. Marlene STORE
        no almacena datos de tarjetas de crédito o débito — esa información es
        procesada directamente por MercadoPago bajo sus propios estándares de
        seguridad.
    </p>
    <p>
        El pedido se considera confirmado una vez que el pago fue aprobado. En caso
        de rechazo o pago pendiente, el pedido no se despachará hasta su acreditación.
    </p>

    <h2>5. Envíos</h2>
    <p>
        El costo y tiempo estimado de envío se calculan e informan al finalizar la
        compra, antes de confirmar el pago. Marlene STORE no se responsabiliza por
        demoras atribuibles a la empresa de transporte una vez que el paquete fue
        despachado.
    </p>

    <h2>6. Devoluciones y arrepentimiento de compra</h2>
    <p>
        Consultá el detalle completo en nuestra
        <a href="/politica-devoluciones.php" style="color:var(--dorado);">Política de Devoluciones</a>,
        que forma parte integral de estos Términos y Condiciones.
    </p>

    <h2>7. Derechos del consumidor</h2>
    <p>
        Esta relación comercial se rige por la Ley de Defensa del Consumidor N°
        24.240 de la República Argentina. Ante cualquier reclamo, podés contactarte
        primero con nosotros a <strong>pedidos@marlene-store.com.ar</strong>; también
        podés recurrir a los organismos oficiales de defensa del consumidor de tu
        jurisdicción.
    </p>

    <h2>8. Modificaciones</h2>
    <p>
        Marlene STORE podrá modificar estos Términos y Condiciones en cualquier
        momento. La versión vigente es siempre la publicada en esta página.
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>