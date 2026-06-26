<?php
session_start();
require_once __DIR__ . '/config/Database.php';
$conexion = Database::getConexion();

$id_cliente = $_SESSION['cliente_id'] ?? 0;
echo "cliente_id en sesión: $id_cliente<br>";

$stmt = $conexion->prepare("SELECT id FROM direcciones WHERE id_cliente = ? LIMIT 5");
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$dirs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo "Direcciones del cliente:<br>";
print_r($dirs);
