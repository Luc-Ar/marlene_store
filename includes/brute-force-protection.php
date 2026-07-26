<?php

/**
 * Protección contra fuerza bruta en logins (admin y clientes).
 * Cuenta intentos fallidos por IP en una ventana de tiempo, y bloquea
 * temporalmente si se supera el máximo. Usa la tabla `intentos_login`.
 */

const BF_MAX_INTENTOS = 5;   // intentos fallidos permitidos antes de bloquear
const BF_VENTANA_MIN  = 15;  // minutos: ventana donde cuentan los intentos
const BF_BLOQUEO_MIN  = 15;  // minutos que dura el bloqueo una vez activado

function bfIdentificador(string $tipo): string
{
    // Separamos por tipo (admin/cliente) para que un bloqueo en un
    // formulario no afecte al otro, aunque sea la misma IP.
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    return $tipo . '_' . $ip;
}

/**
 * Devuelve los minutos que faltan para que se levante el bloqueo,
 * o null si no está bloqueado. Llamar ANTES de verificar la contraseña.
 */
function bfEstaBloqueado(mysqli $db, string $tipo): ?int
{
    $id = bfIdentificador($tipo);
    $stmt = $db->prepare("SELECT bloqueado_hasta FROM intentos_login WHERE identificador = ? LIMIT 1");
    $stmt->bind_param("s", $id);
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
    $id = bfIdentificador($tipo);
    $ahora = date('Y-m-d H:i:s');
    $ventanaInicio = date('Y-m-d H:i:s', time() - BF_VENTANA_MIN * 60);

    $stmt = $db->prepare("SELECT id, intentos, primer_intento FROM intentos_login WHERE identificador = ? LIMIT 1");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();

    if (!$fila) {
        $stmt2 = $db->prepare("INSERT INTO intentos_login (identificador, intentos, primer_intento) VALUES (?, 1, ?)");
        $stmt2->bind_param("ss", $id, $ahora);
        $stmt2->execute();
        return;
    }

    // Si el primer intento registrado quedó afuera de la ventana de
    // tiempo, arrancamos a contar de nuevo desde 1.
    if ($fila['primer_intento'] < $ventanaInicio) {
        $stmt2 = $db->prepare("UPDATE intentos_login SET intentos = 1, primer_intento = ?, bloqueado_hasta = NULL WHERE id = ?");
        $stmt2->bind_param("si", $ahora, $fila['id']);
        $stmt2->execute();
        return;
    }

    $nuevosIntentos = $fila['intentos'] + 1;

    if ($nuevosIntentos >= BF_MAX_INTENTOS) {
        $bloqueoHasta = date('Y-m-d H:i:s', time() + BF_BLOQUEO_MIN * 60);
        $stmt2 = $db->prepare("UPDATE intentos_login SET intentos = ?, bloqueado_hasta = ? WHERE id = ?");
        $stmt2->bind_param("isi", $nuevosIntentos, $bloqueoHasta, $fila['id']);
        $stmt2->execute();
    } else {
        $stmt2 = $db->prepare("UPDATE intentos_login SET intentos = ? WHERE id = ?");
        $stmt2->bind_param("ii", $nuevosIntentos, $fila['id']);
        $stmt2->execute();
    }
}

/**
 * Limpiar el contador. Llamar cuando el login sale bien.
 */
function bfLimpiar(mysqli $db, string $tipo): void
{
    $id = bfIdentificador($tipo);
    $stmt = $db->prepare("DELETE FROM intentos_login WHERE identificador = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
}
