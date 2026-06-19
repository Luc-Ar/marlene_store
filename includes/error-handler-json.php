<?php
require_once __DIR__ . '/includes/error-handler-json.php';
/**
 * Manejador de errores para endpoints tipo API (responden JSON),
 * como carrito-api.php y webhook-mp.php.
 * En vez de mostrar la página 500 en HTML, devuelve un JSON
 * con el error, para no romper al consumidor (JS o MercadoPago).
 */

function responder_error_json(): void
{
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=UTF-8');
    }
    echo json_encode([
        'ok' => false,
        'error' => 'Ocurrió un error interno. Intentá de nuevo en unos minutos.',
    ]);
    exit;
}

set_exception_handler(function (\Throwable $e) {
    error_log('Excepción no capturada (API): ' . $e->getMessage());
    responder_error_json();
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('Error fatal (API): ' . $error['message']);
        responder_error_json();
    }
});
