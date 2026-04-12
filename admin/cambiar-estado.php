<?php
session_start();
if (!isset($_SESSION['usuario_id'])) exit;

require_once '../config/Database.php';
$conexion = Database::getConexion();

if (isset($_GET['id']) && isset($_GET['nuevo_estado'])) {
    $id = (int)$_GET['id'];
    $estado = (int)$_GET['nuevo_estado'];

    $stmt = $conexion->prepare("UPDATE productos SET activo = ? WHERE id = ?");
    $stmt->bind_param("ii", $estado, $id);

    if ($stmt->execute()) {
        header("Location: productos.php?mensaje=estado_actualizado");
    } else {
        echo "Error al actualizar: " . $conexion->error;
    }
    $stmt->close();
}
exit;
