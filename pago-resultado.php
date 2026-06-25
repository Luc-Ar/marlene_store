<?php
session_start();
require_once __DIR__ . '/includes/error-handler.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/includes/emails.php';

$resultado  = $_GET['resultado'] ?? '';
$payment_id = $_GET['payment_id'] ?? '';
$ext_ref    = $_GET['external_reference'] ?? '';
$status     = $_GET['status'] ?? '';

$conexion = Database::getConexion();
$pedido   = null;

if ($ext_ref) {
    $stmt = $conexion->prepare("
        SELECT p.*, c.nombre as cliente_nombre, c.email as cliente_email,
               c.apellido as cliente_apellido
        FROM pedidos p
        LEFT JOIN clientes c ON p.id_cliente = c.id
        WHERE p.numero_pedido = ? LIMIT 1
    ");
    $stmt->bind_param("s", $ext_ref);
    $stmt->execute();
    $pedido = $stmt->get_result()->fetch_assoc();
}

// Actualizar estado según resultado
if ($pedido) {
    $nuevo_estado = match ($resultado) {
        'success' => 'confirmado',
        'pending' => 'pendiente',
        'failure' => 'cancelado',
        default   => 'pendiente',
    };

    $stmt = $conexion->prepare("UPDATE pedidos SET estado = ?, metodo_pago = 'mercadopago' WHERE numero_pedido = ?");
    $stmt->bind_param("ss", $nuevo_estado, $ext_ref);
    $stmt->execute();

    if ($resultado === 'success' && !isset($_SESSION['email_mp_' . $ext_ref])) {
        $items_stmt = $conexion->prepare("SELECT * FROM pedido_items WHERE id_pedido = ?");
        $items_stmt->bind_param("i", $pedido['id']);
        $items_stmt->execute();
        $items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        emailConfirmacionPedido(
            $pedido,
            $items,
            ['nombre' => $pedido['cliente_nombre'], 'email' => $pedido['cliente_email']]
        );
        $_SESSION['email_mp_' . $ext_ref] = true;
    }
}

// Limpiar sesión de pago
unset($_SESSION['pedido_pendiente_pago']);
unset($_SESSION['mp_preference_id']);

// Variables para header.php
$titulo = 'Resultado del pago — Marlene STORE';
$estilos_extra = '
.resultado-wrap {
    max-width: 560px;
    margin: 140px auto 60px;
    padding: 0 24px;
    text-align: center;
}
.resultado-icon { font-size: 4rem; margin-bottom: 20px; }
.resultado-titulo {
    font-family: "Great Vibes", cursive;
    font-size: 3rem;
    margin-bottom: 12px;
}
.resultado-msg {
    font-family: "Cormorant Garamond", serif;
    font-size: 1.1rem;
    color: #666;
    line-height: 1.7;
    margin-bottom: 28px;
}
.resultado-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.btn-primary {
    padding: 14px 28px;
    background: var(--marron);
    color: var(--crema);
    text-decoration: none;
    border-radius: 4px;
    font-family: "Montserrat", sans-serif;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
}
.btn-wsp {
    padding: 14px 28px;
    background: #25D366;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-family: "Montserrat", sans-serif;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
}
';

$incluir_carrito = false;

require_once __DIR__ . '/includes/header.php';
?>

<div class="resultado-wrap">
    <?php if ($resultado === 'success'): ?>
        <div class="resultado-icon">🎉</div>
        <h1 class="resultado-titulo" style="color:var(--marron);">¡Pago exitoso!</h1>
        <p class="resultado-msg">
            Tu pago fue procesado correctamente.<br>
            <?php if ($pedido): ?>
                Pedido <strong>#<?= htmlspecialchars($pedido['numero_pedido']) ?></strong> confirmado.
            <?php endif; ?>
            Te enviamos un email con el resumen. 🌸
        </p>

    <?php elseif ($resultado === 'pending'): ?>
        <div class="resultado-icon">⏳</div>
        <h1 class="resultado-titulo" style="color:#D97706;">Pago pendiente</h1>
        <p class="resultado-msg">
            Tu pago está siendo procesado.<br>
            Te avisaremos cuando se confirme. Podés consultarnos por WhatsApp.
        </p>

    <?php else: ?>
        <div class="resultado-icon">❌</div>
        <h1 class="resultado-titulo" style="color:#DC2626;">Pago no completado</h1>
        <p class="resultado-msg">
            Hubo un problema con el pago.<br>
            Podés intentarlo de nuevo o elegir otro método de pago.
        </p>
    <?php endif; ?>

    <div class="resultado-btns">
        <?php if ($resultado === 'failure'): ?>
            <a href="/checkout.php" class="btn-primary">Intentar de nuevo</a>
        <?php else: ?>
            <a href="/catalogo.php" class="btn-primary">Seguir comprando</a>
        <?php endif; ?>
        <a href="https://wa.me/5493704097831" class="btn-wsp" target="_blank">
            💬 Consultar por WhatsApp
        </a>
    </div>
</div>

<!-- Limpiar carrito del localStorage al completar el pago -->
<script>
    localStorage.removeItem('marlene_carrito');
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>