<?php
require_once __DIR__ . '/mailer.php';

// ─── Email de confirmación de pedido al cliente ───
function emailConfirmacionPedido(array $pedido, array $items, array $cliente): bool
{
    $itemsHTML = '';
    foreach ($items as $item) {
        $itemsHTML .= "
        <tr>
            <td>{$item['nombre_producto']}</td>
            <td style='text-align:center'>{$item['cantidad']}</td>
            <td style='text-align:right'>$" . number_format($item['precio_unitario'], 0, ',', '.') . "</td>
            <td style='text-align:right'>$" . number_format($item['subtotal'], 0, ',', '.') . "</td>
        </tr>";
    }

    $contenido = "
        <p>Hola <strong>{$cliente['nombre']}</strong>, ¡gracias por tu compra! 🎉</p>
        <p>Recibimos tu pedido correctamente. Te avisaremos cuando esté en camino.</p>
        <hr class='divider'>
        <p><strong>N° de pedido:</strong> <span class='badge'>#{$pedido['numero_pedido']}</span></p>
        <p><strong>Método de pago:</strong> {$pedido['metodo_pago']}</p>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th style='text-align:center'>Cant.</th>
                    <th style='text-align:right'>Precio</th>
                    <th style='text-align:right'>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                {$itemsHTML}
                <tr class='total-row'>
                    <td colspan='3'>Total</td>
                    <td style='text-align:right'>$" . number_format($pedido['total'], 0, ',', '.') . "</td>
                </tr>
            </tbody>
        </table>

        <hr class='divider'>
        <p style='font-size:0.85rem;color:#999;'>¿Tenés alguna duda? Escribinos por WhatsApp y te respondemos rápido. 🌸</p>
        <a href='https://wa.me/5493704097831' class='btn'>Consultar por WhatsApp</a>
    ";

    $html = templateEmail('¡Tu pedido fue recibido!', $contenido);
    return enviarEmail($cliente['email'], $cliente['nombre'], "Pedido #{$pedido['numero_pedido']} confirmado — Marlene Store", $html);
}

// ─── Email de cambio de estado al cliente ───
function emailCambioEstado(array $pedido, array $cliente, string $nuevoEstado): bool
{
    $estados = [
        'confirmado'     => ['titulo' => '¡Tu pedido fue confirmado!',     'emoji' => '✅', 'msg' => 'Ya estamos preparando tu pedido con mucho cariño.'],
        'en_preparacion' => ['titulo' => 'Tu pedido está en preparación',   'emoji' => '📦', 'msg' => 'Estamos empacando tu pedido. Pronto saldrá.'],
        'enviado'        => ['titulo' => '¡Tu pedido está en camino!',      'emoji' => '🚚', 'msg' => 'Tu pedido fue despachado. Pronto llegará a vos.'],
        'entregado'      => ['titulo' => '¡Tu pedido fue entregado!',       'emoji' => '🎉', 'msg' => '¡Esperamos que lo disfrutes mucho!'],
        'cancelado'      => ['titulo' => 'Tu pedido fue cancelado',         'emoji' => '❌', 'msg' => 'Tu pedido fue cancelado. Contactanos si tenés dudas.'],
    ];

    $info = $estados[$nuevoEstado] ?? ['titulo' => 'Actualización de tu pedido', 'emoji' => '📋', 'msg' => 'Hubo una actualización en tu pedido.'];

    $contenido = "
        <p>Hola <strong>{$cliente['nombre']}</strong>,</p>
        <p>{$info['emoji']} {$info['msg']}</p>
        <hr class='divider'>
        <p><strong>N° de pedido:</strong> <span class='badge'>#{$pedido['numero_pedido']}</span></p>
        <p><strong>Estado actual:</strong> {$info['titulo']}</p>
        <p><strong>Total:</strong> $" . number_format($pedido['total'], 0, ',', '.') . "</p>
        <hr class='divider'>
        <p style='font-size:0.85rem;color:#999;'>¿Tenés consultas sobre tu envío? Escribinos. 🌸</p>
        <a href='https://wa.me/5493704097831' class='btn'>Consultar por WhatsApp</a>
    ";

    $html = templateEmail($info['titulo'], $contenido);
    return enviarEmail($cliente['email'], $cliente['nombre'], "{$info['emoji']} Pedido #{$pedido['numero_pedido']} — {$info['titulo']}", $html);
}

// ─── Email de bienvenida al registrarse ───
function emailBienvenida(array $cliente): bool
{
    $contenido = "
        <p>Hola <strong>{$cliente['nombre']}</strong>, ¡bienvenida a Marlene Store! 🌸</p>
        <p>Tu cuenta fue creada correctamente. Ya podés explorar nuestro catálogo y hacer tus pedidos.</p>
        <hr class='divider'>
        <a href='http://marlene.store/catalogo.php' class='btn'>Ver catálogo</a>
        <hr class='divider'>
        <p style='font-size:0.85rem;color:#999;'>Si no creaste esta cuenta, ignorá este mensaje.</p>
    ";

    $html = templateEmail('¡Bienvenida a Marlene Store!', $contenido);
    return enviarEmail($cliente['email'], $cliente['nombre'], '¡Bienvenida a Marlene Store! 🌸', $html);
}
