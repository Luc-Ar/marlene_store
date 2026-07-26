<?php

/**
 * Protección CSRF (Cross-Site Request Forgery).
 * Requiere que la sesión ya esté iniciada (session_start()) antes de usar estas funciones.
 */

/**
 * Devuelve el token CSRF de la sesión actual, generándolo si todavía no existe.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Imprime el campo oculto listo para pegar dentro de un <form>.
 * Uso: <?= csrfField() ?>
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

/**
 * Valida el token CSRF cuando viene por GET (para links de acción
 * como "Eliminar" o "Activar", que no son formularios).
 */
function csrfValidarGet(): bool
{
    $tokenRecibido = $_GET['csrf_token'] ?? '';
    $tokenSesion    = $_SESSION['csrf_token'] ?? '';

    if (empty($tokenSesion) || empty($tokenRecibido)) {
        return false;
    }

    return hash_equals($tokenSesion, $tokenRecibido);
}
