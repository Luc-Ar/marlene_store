<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/Database.php';
require_once __DIR__ . '/../includes/emails.php';

$conexion = Database::getConexion();

if (isset($_GET['id']) && isset($_GET['estado'])) {
    $id_pedido    = (int)$_GET['id'];
    $nuevo_estado = trim($_GET['estado']);

    $estados_validos = ['pendiente', 'confirmado', 'en_preparacion', 'demorado', 'enviado', 'entregado', 'cancelado'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        header('Location: pedidos.php');
        exit;
    }

    // Actualizar estado
    $stmt = $conexion->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $id_pedido);
    $stmt->execute();

    // Traer pedido y cliente para email
    $stmt2 = $conexion->prepare("
        SELECT p.*, c.nombre as cliente_nombre, c.email as cliente_email
        FROM pedidos p
        LEFT JOIN clientes c ON p.id_cliente = c.id
        WHERE p.id = ? LIMIT 1
    ");
    $stmt2->bind_param("i", $id_pedido);
    $stmt2->execute();
    $data = $stmt2->get_result()->fetch_assoc();

    if ($data && !empty($data['cliente_email'])) {
        emailCambioEstado(
            $data,
            ['nombre' => $data['cliente_nombre'], 'email' => $data['cliente_email']],
            $nuevo_estado
        );
    }

    header("Location: pedidos.php?mensaje=estado_actualizado&id=$id_pedido");
}
exit;
