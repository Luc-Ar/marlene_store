<?php
session_start();
require_once __DIR__ . '/../includes/error-handler.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /admin/login.php');
    exit;
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../includes/csrf.php';
$conexion = Database::getConexion();

if (!csrfValidarGet()) {
    header('Location: /admin/categorias.php?error=csrf');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Averiguamos el estado actual para saber si esto es una
    // activación o una desactivación.
    $check = $conexion->prepare("SELECT activo FROM categorias WHERE id = ?");
    $check->bind_param('i', $id);
    $check->execute();
    $cat = $check->get_result()->fetch_assoc();

    if ($cat) {
        $vaAQuedarInactiva = (int)$cat['activo'] === 1;

        if ($vaAQuedarInactiva) {
            // Solo chequeamos productos asociados cuando se está
            // desactivando. Activar una categoría nunca es riesgoso.
            $checkProd = $conexion->prepare("SELECT COUNT(*) as total FROM productos WHERE categoria = ?");
            $checkProd->bind_param('i', $id);
            $checkProd->execute();
            $fila = $checkProd->get_result()->fetch_assoc();

            if ($fila['total'] > 0) {
                header('Location: /admin/categorias.php?error=tiene_productos&cantidad=' . $fila['total']);
                exit;
            }
        }

        $stmt = $conexion->prepare("UPDATE categorias SET activo = NOT activo WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

header('Location: /admin/categorias.php');
exit;
