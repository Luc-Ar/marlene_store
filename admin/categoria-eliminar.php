<?php
session_start();
require_once __DIR__ . '/../includes/error-handler.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /admin/login.php');
    exit;
}

require_once __DIR__ . '/../config/Database.php';
$conexion = Database::getConexion();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // 1. Contamos si hay productos en esta categoría
    $check = $conexion->prepare("SELECT COUNT(*) as total FROM productos WHERE categoria = ?");
    $check->bind_param('i', $id);
    $check->execute();
    $fila = $check->get_result()->fetch_assoc();

    if ($fila['total'] > 0) {
        // 2. Si hay productos: no eliminamos, avisamos por qué
        header('Location: /admin/categorias.php?error=tiene_productos&cantidad=' . $fila['total']);
        exit;
    }

    // 3. Si está vacía: borrado lógico (soft delete)
    $stmt = $conexion->prepare("UPDATE categorias SET activo = 0 WHERE id = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        header('Location: /admin/categorias.php?mensaje=eliminada');
    } else {
        header('Location: /admin/categorias.php?error=db');
    }
    exit;
}

header('Location: /admin/categorias.php');
exit;
