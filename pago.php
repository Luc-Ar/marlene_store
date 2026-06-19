<?php
require_once __DIR__ . '/includes/error-handler.php';
session_start();
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

// Verificar que hay pedido pendiente
if (empty($_SESSION['pedido_pendiente_pago'])) {
    header('Location: catalogo.php');
    exit;
}

$pedido_data = $_SESSION['pedido_pendiente_pago'];

// Cargar credenciales
$envFile = __DIR__ . '/.env';
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
    if (strpos(trim($linea), '#') === 0) continue; // saltear comentarios
    if (strpos($linea, '=') === false) continue;
    [$key, $val] = explode('=', $linea, 2);
    $env[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
}

MercadoPagoConfig::setAccessToken($env['MP_ACCESS_TOKEN'] ?? '');

// Traer items del pedido desde BD
$conexion = Database::getConexion();
$stmt = $conexion->prepare("SELECT * FROM pedido_items WHERE id_pedido = ?");
$stmt->bind_param("i", $pedido_data['id_pedido']);
$stmt->execute();
$items_bd = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Armar items para MP
$items = [];
foreach ($items_bd as $item) {
    $items[] = [
        'id'          => (string)$item['id_producto'],
        'title'       => $item['nombre_producto'],
        'quantity'    => (int)$item['cantidad'],
        'unit_price'  => (float)$item['precio_unitario'],
        'currency_id' => 'ARS',
    ];
}

// URLs base
$base = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/marlene-store';

try {
    $client = new PreferenceClient();
    $preference = $client->create([
        'items'                => $items,
        'external_reference'   => $pedido_data['numero_pedido'],
        'back_urls'            => [
            'success' => "$base/pago-resultado.php?resultado=success",
            'failure' => "$base/pago-resultado.php?resultado=failure",
            'pending' => "$base/pago-resultado.php?resultado=pending",
        ],
        // 'auto_return'          => 'approved',
        'notification_url'     => "$base/webhook-mp.php",
        'statement_descriptor' => 'Marlene Store',
    ]);

    $_SESSION['mp_preference_id'] = $preference->id;

    // Sandbox para pruebas, init_point para producción
    $url_pago = $preference->sandbox_init_point;
    header('Location: ' . $url_pago);
    exit;
} catch (MPApiException $e) {
    error_log("MP Error: " . json_encode($e->getApiResponse()->getContent()));
    header('Location: checkout.php?error=pago');
    exit;
} catch (Exception $e) {
    error_log("MP Error general: " . $e->getMessage());
    header('Location: checkout.php?error=pago');
    exit;
}
