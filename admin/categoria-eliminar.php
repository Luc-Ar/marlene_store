<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/database.php';
$conexion = conectar();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // 1. PRIMERO: Contamos si hay productos en esta categoría
    // Usamos una consulta preparada para seguridad
    $check = $conexion->prepare("SELECT COUNT(*) as total FROM productos WHERE categoria = ?");
    $check->bind_param('i', $id);
    $check->execute();
    $resCount = $check->get_result();
    $fila = $resCount->fetch_assoc();

    if ($fila['total'] > 0) {
        // 2. SI HAY PRODUCTOS: No eliminamos y mandamos un error
        header('Location: categorias.php?error=tiene_productos&cantidad=' . $fila['total']);
        exit;
    } else {
        // 3. SI ESTÁ VACÍA: Procedemos con el borrado lógico
        $stmt = $conexion->prepare("UPDATE categorias SET activo = 0 WHERE id = ?");
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            header('Location: categorias.php?mensaje=eliminada');
        } else {
            header('Location: categorias.php?error=db');
        }
        exit;
    }
} else {
    // Si no hay ID válido, volvemos sin hacer nada
    header('Location: categorias.php');
    exit;
}
?>