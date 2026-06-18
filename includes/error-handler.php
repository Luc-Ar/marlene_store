<?php
function mostrar_error_500(): void
{
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }
    require __DIR__ . '/../500.php';
    exit;
}

set_exception_handler(function (\Throwable $e) {
    error_log('Excepción no capturada: ' . $e->getMessage());
    mostrar_error_500();
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('Error fatal: ' . $error['message']);
        mostrar_error_500();
    }
});
