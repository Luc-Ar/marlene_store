<?php
session_start();
if (!isset($_SESSION['usuario_id'])) exit;

require_once '../config/Database.php';
$conexion = Database::getConexion();

if (isset($_GET['id']) && isset($_GET['nuevo_estado'])) {
    $id = (int)$_GET['id'];
    $nuevo_estado = (int)$_GET['nuevo_estado'];

    $stmt = $conexion->prepare("UPDATE productos SET activo = ? WHERE id = ?");
    $stmt->bind_param("ii", $nuevo_estado, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: productos.php?mensaje=estado_actualizado");
}
exit;
