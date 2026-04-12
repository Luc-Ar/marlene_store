<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/database.php';
$conexion = conectar();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    $stmt = $conexion->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

header('Location: productos.php?mensaje=eliminado');
exit;
