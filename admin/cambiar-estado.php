<?php
session_start();
require_once __DIR__ . '/../includes/error-handler.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /admin/login.php');
    exit;
}

require_once __DIR__ . '/../config/Database.php';
$conexion = Database::getConexion();

if (isset($_GET['id']) && isset($_GET['nuevo_estado'])) {
    $id           = (int)$_GET['id'];
    $nuevo_estado = (int)$_GET['nuevo_estado'];

    $stmt = $conexion->prepare("UPDATE productos SET activo = ? WHERE id = ?");
    $stmt->bind_param("ii", $nuevo_estado, $id);
    $stmt->execute();
}

header('Location: /admin/productos.php?mensaje=estado_actualizado');
exit;
