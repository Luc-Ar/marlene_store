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
    $stmt = $conexion->prepare("UPDATE categorias SET activo = NOT activo WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header('Location: /admin/categorias.php');
exit;
