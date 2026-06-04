<?php
session_start();
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/includes/emails.php';

$numero_pedido = $_GET['pedido'] ?? $_SESSION['ultimo_pedido'] ?? '';
if (!$numero_pedido) {
    header('Location: index.php');
    exit;
}

$conexion = Database::getConexion();
$pedido = null;
$cliente = null;
$items = [];

$stmt = $conexion->prepare("SELECT * FROM pedidos WHERE numero_pedido = ? LIMIT 1");
$stmt->bind_param("s", $numero_pedido);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if ($pedido) {
    $stmt2 = $conexion->prepare("SELECT * FROM pedido_items WHERE id_pedido = ?");
    $stmt2->bind_param("i", $pedido['id']);
    $stmt2->execute();
    $items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt3 = $conexion->prepare("SELECT * FROM clientes WHERE id = ? LIMIT 1");
    $stmt3->bind_param("i", $pedido['id_cliente']);
    $stmt3->execute();
    $cliente = $stmt3->get_result()->fetch_assoc();

    // Enviar email solo una vez
    if ($cliente && !isset($_SESSION['email_enviado_' . $numero_pedido])) {
        emailConfirmacionPedido($pedido, $items, $cliente);
        $_SESSION['email_enviado_' . $numero_pedido] = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido confirmado — Marlene STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,400;0,600&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .confirm-wrap {
            max-width: 640px;
            margin: 140px auto 60px;
            padding: 0 24px;
            text-align: center;
        }

        .confirm-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .confirm-titulo {
            font-family: 'Great Vibes', cursive;
            font-size: 3.5rem;
            color: var(--marron);
            margin-bottom: 12px;
        }

        .confirm-numero {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--dorado);
            margin-bottom: 24px;
        }

        .confirm-msg {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.2rem;
            color: #666;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        .confirm-email-notice {
            background: #DCFCE7;
            border: 1px solid #BBF7D0;
            border-radius: 8px;
            padding: 14px 20px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.75rem;
            color: #166534;
            margin-bottom: 28px;
            display: inline-block;
        }

        /* Resumen del pedido */
        .confirm-resumen {
            background: white;
            border: 1px solid rgba(200, 152, 154, 0.2);
            border-radius: 10px;
            padding: 24px;
            text-align: left;
            margin-bottom: 28px;
        }

        .confirm-resumen h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--dorado);
            margin-bottom: 16px;
        }

        .confirm-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #FAF6F1;
            font-size: 0.85rem;
            color: #555;
        }

        .confirm-total {
            display: flex;
            justify-content: space-between;
            padding-top: 14px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            color: var(--marron);
        }

        .confirm-btns {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-seguir {
            padding: 14px 28px;
            background: var(--marron);
            color: var(--crema);
            border: none;
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-decoration: none;
            transition: background 0.3s;
        }

        .btn-seguir:hover {
            background: var(--dorado);
        }

        .btn-wsp-confirm {
            padding: 14px 28px;
            background: #25D366;
            color: white;
            border: none;
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-decoration: none;
            transition: background 0.3s;
        }

        .btn-wsp-confirm:hover {
            background: #128C7E;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/includes/nav.php'; ?>

    <div class="confirm-wrap">
        <div class="confirm-icon">🎉</div>
        <h1 class="confirm-titulo">¡Gracias!</h1>
        <p class="confirm-numero">Pedido #<?= htmlspecialchars($numero_pedido) ?></p>
        <p class="confirm-msg">
            Tu pedido fue recibido con éxito.<br>
            Nos pondremos en contacto a la brevedad para coordinar el pago y envío. 🌸
        </p>

        <?php if ($cliente): ?>
            <div class="confirm-email-notice">
                📧 Te enviamos un email de confirmación a <strong><?= htmlspecialchars($cliente['email']) ?></strong>
            </div>
        <?php endif; ?>

        <?php if (!empty($items)): ?>
            <div class="confirm-resumen">
                <h3>Resumen de tu pedido</h3>
                <?php foreach ($items as $item): ?>
                    <div class="confirm-item">
                        <span><?= htmlspecialchars($item['nombre_producto']) ?> × <?= $item['cantidad'] ?></span>
                        <span>$<?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if ($pedido): ?>
                    <div class="confirm-total">
                        <span>Total</span>
                        <span>$<?= number_format($pedido['total'], 0, ',', '.') ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="confirm-btns">
            <a href="catalogo.php" class="btn-seguir">Seguir comprando</a>
            <a href="https://wa.me/5493704097831?text=Hola!%20Hice%20el%20pedido%20<?= urlencode($numero_pedido) ?>%20y%20quería%20consultar%20sobre%20el%20estado."
                class="btn-wsp-confirm" target="_blank">
                💬 Consultar por WhatsApp
            </a>
        </div>
    </div>

    <script>
        localStorage.removeItem('marlene_carrito');
    </script>
</body>

</html>