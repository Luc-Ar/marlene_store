<?php

/**
 * Protección contra fuerza bruta en logins (admin y clientes).
 * Cuenta intentos fallidos por IP en una ventana de tiempo, y bloquea
 * temporalmente si se supera el máximo. Usa la tabla `intentos_login`
 * (columnas: ip, tipo, fecha, bloqueado_hasta) — cada intento fallido
 * inserta una fila nueva, en vez de llevar un contador en una sola fila.
 */

const BF_MAX_INTENTOS = 5;   // intentos fallidos permitidos antes de bloquear
const BF_VENTANA_MIN  = 15;  // minutos: ventana donde cuentan los intentos
const BF_BLOQUEO_MIN  = 15;  // minutos que dura el bloqueo una vez activado

function bfIp(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
}

/**
 * Devuelve los minutos que faltan para que se levante el bloqueo,
 * o null si no está bloqueado. Llamar ANTES de verificar la contraseña.
 */
function bfEstaBloqueado(mysqli $db, string $tipo): ?int
{
    $ip = bfIp();
    $stmt = $db->prepare("
        SELECT bloqueado_hasta FROM intentos_login
        WHERE ip = ? AND tipo = ? AND bloqueado_hasta IS NOT NULL
        ORDER BY bloqueado_hasta DESC LIMIT 1
    ");
    $stmt->bind_param("ss", $ip, $tipo);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();

    if ($fila && $fila['bloqueado_hasta'] && strtotime($fila['bloqueado_hasta']) > time()) {
        return (int) ceil((strtotime($fila['bloqueado_hasta']) - time()) / 60);
    }
    return null;
}

/**
 * Registrar un intento fallido. Llamar cuando el usuario/contraseña
 * no coinciden.
 */
function bfRegistrarFallo(mysqli $db, string $tipo): void
{
    $ip = bfIp();
    $ahora = date('Y-m-d H:i:s');
    $ventanaInicio = date('Y-m-d H:i:s', time() - BF_VENTANA_MIN * 60);

    $stmt = $db->prepare("
        SELECT COUNT(*) as total FROM intentos_login
        WHERE ip = ? AND tipo = ? AND fecha >= ?
    ");
    $stmt->bind_param("sss", $ip, $tipo, $ventanaInicio);
    $stmt->execute();
    $totalPrevios = (int) $stmt->get_result()->fetch_assoc()['total'];

    $nuevosIntentos = $totalPrevios + 1;
    $bloqueadoHasta = null;

    if ($nuevosIntentos >= BF_MAX_INTENTOS) {
        $bloqueadoHasta = date('Y-m-d H:i:s', time() + BF_BLOQUEO_MIN * 60);
    }

    $stmt2 = $db->prepare("INSERT INTO intentos_login (ip, tipo, fecha, bloqueado_hasta) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("ssss", $ip, $tipo, $ahora, $bloqueadoHasta);
    $stmt2->execute();
}

/**
 * Limpiar el contador. Llamar cuando el login sale bien.
 */
function bfLimpiar(mysqli $db, string $tipo): void
{
    $ip = bfIp();
    $stmt = $db->prepare("DELETE FROM intentos_login WHERE ip = ? AND tipo = ?");
    $stmt->bind_param("ss", $ip, $tipo);
    $stmt->execute();
}
