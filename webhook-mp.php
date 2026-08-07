<?php
require_once __DIR__ . '/includes/error-handler-json.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/emails.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;

// Cargar credenciales
$envFile = __DIR__ . '/../.env';
$env = [];
foreach (file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
    if (strpos($linea, '=') === false) continue;
    [$key, $val] = explode('=', $linea, 2);
    $env[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
}

MercadoPagoConfig::setAccessToken($env['MP_ACCESS_TOKEN'] ?? '');

$payload = file_get_contents('php://input');
$data    = json_decode($payload, true);

if (isset($data['type']) && $data['type'] === 'payment') {
    $payment_id = $data['data']['id'];

    try {
        $client  = new PaymentClient();
        $payment = $client->get($payment_id);

        $ext_ref = $payment->external_reference;
        $status  = $payment->status;

        $conexion = Database::getConexion();
        $nuevo_estado = match ($status) {
            'approved' => 'confirmado',
            'pending'  => 'pendiente',
            'rejected' => 'cancelado',
            default    => 'pendiente',
        };

        $stmtPedido = $conexion->prepare("SELECT id FROM pedidos WHERE numero_pedido = ? LIMIT 1");
        $stmtPedido->bind_param("s", $ext_ref);
        $stmtPedido->execute();
        $pedidoActual = $stmtPedido->get_result()->fetch_assoc();

        if ($pedidoActual) {
            require_once __DIR__ . '/models/PedidoRepository.php';
            $pedidoRepo = new PedidoRepository($conexion);
            $resultado = $pedidoRepo->cambiarEstado($pedidoActual['id'], $nuevo_estado);

            if (!$resultado['ok']) {
                error_log("Webhook MP: error al cambiar estado del pedido $ext_ref — " . $resultado['error']);
            }
        }

        error_log("Webhook MP: pedido $ext_ref → $nuevo_estado");
    } catch (Exception $e) {
        error_log("Webhook MP error: " . $e->getMessage());
        http_response_code(500);
        exit;
    }
}

http_response_code(200);
echo json_encode(['status' => 'ok']);
