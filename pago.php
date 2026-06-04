<?php
session_start();
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

// Verificar que hay carrito
if (empty($_SESSION['carrito'])) {
    header('Location: catalogo.php');
    exit;
}

// Cargar credenciales
$envFile = '/var/www/html/marlene-store/.env';
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
    if (strpos($linea, '=') === false) continue;
    [$key, $val] = explode('=', $linea, 2);
    $env[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
}

MercadoPagoConfig::setAccessToken($env['MP_ACCESS_TOKEN'] ?? '');

// Armar items del carrito
$items = [];
foreach ($_SESSION['carrito'] as $item) {
    $items[] = [
        'id'          => (string)$item['id'],
        'title'       => $item['nombre'],
        'quantity'    => (int)$item['cantidad'],
        'unit_price'  => (float)$item['precio'],
        'currency_id' => 'ARS',
        'picture_url' => 'http://localhost/marlene-store/' . $item['imagen'],
    ];
}

$total = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $_SESSION['carrito']));

// URLs de retorno
$base = 'http://localhost/marlene-store';

try {
    $client = new PreferenceClient();
    $preference = $client->create([
        'items'               => $items,
        'back_urls'           => [
            'success' => "$base/pago-resultado.php?resultado=success",
            'failure' => "$base/pago-resultado.php?resultado=failure",
            'pending' => "$base/pago-resultado.php?resultado=pending",
        ],
        'auto_return'         => 'approved',
        'notification_url'    => "$base/webhook-mp.php",
        'statement_descriptor' => 'Marlene Store',
        'external_reference'  => 'PEDIDO-' . time(),
    ]);

    // Guardar preference_id en sesión
    $_SESSION['mp_preference_id'] = $preference->id;
    $_SESSION['mp_external_ref']  = 'PEDIDO-' . time();

    // Redirigir al checkout de MercadoPago (sandbox)
    header('Location: ' . $preference->sandbox_init_point);
    exit;
} catch (MPApiException $e) {
    error_log("MP Error: " . $e->getMessage());
    header('Location: checkout.php?error=pago');
    exit;
}
