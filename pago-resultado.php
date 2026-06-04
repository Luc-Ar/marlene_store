<?php
session_start();
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/includes/emails.php';

$resultado    = $_GET['resultado'] ?? '';
$payment_id   = $_GET['payment_id'] ?? '';
$status       = $_GET['status'] ?? '';
$ext_ref      = $_GET['external_reference'] ?? '';

$conexion = Database::getConexic();
// if ($resultado === 'success') {
//     // Aquí podrías actualizar el estado del pedido en tu base de datos usando $ext_ref
//     // y enviar un email de confirmación al cliente.
//     $stmt = $conexion->prepare("UPDATE pedidos SET estado = 'aprobado' WHERE referencia_externa = ?");
//     $stmt->execute([$ext_ref]);

//     // Enviar email de confirmación
//     $pedido = $conexion->prepare("SELECT * FROM pedidos WHERE referencia_externa = ?");
//     $pedido->execute([$ext_ref]);
//     $pedidoData = $pedido->fetch(PDO::FETCH_ASSOC);
//     if ($pedidoData) {
//         sendConfirmationEmail($pedidoData['email'], $pedidoData['nombre']);
//     }
// } elseif ($resultado === 'failure') {
//     // Actualizar estado a 'rechazado'
//     $stmt = $conexion->prepare("UPDATE pedidos SET estado = 'rechazado' WHERE referencia_externa = ?");
//     $stmt->execute([$ext_ref]);
// } elseif ($resultado === 'pending') {
//     // Actualizar estado a 'pendiente'
//     $stmt = $conexion->prepare("UPDATE pedidos SET estado = 'pendiente' WHERE referencia_externa = ?");
//     $stmt->execute([$ext_ref]);
// }                   