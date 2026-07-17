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

if ($id) {
    $stmt = $conexion->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

header('Location: /admin/productos.php?mensaje=eliminado');
exit;
