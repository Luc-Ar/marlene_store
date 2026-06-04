<?php
session_start();
if (!isset($_SESSION['usuario_id'])) exit;

require_once '../config/Database.php';
$conexion = Database::getConexion();

if (isset($_GET['id']) && isset($_GET['nuevo_estado'])) {
    $id = (int)$_GET['id'];
    $nuevo_estado = (int)$_GET['nuevo_estado'];

    $stmt_update = $conexion->prepare("UPDATE productos SET activo = ? WHERE id = ?");
    $stmt_update->bind_param("ii", $nuevo_estado, $id);

    if (!$stmt_update->execute()) {
        echo "Error al actualizar: " . $conexion->error;
        $stmt_update->close();
        exit;
    }

    require_once __DIR__ . '/../includes/emails.php';
    // Traer pedido y cliente
    $id_pedido = $id;
    $stmt = $conexion->prepare("SELECT p.*, c.nombre, c.email FROM pedidos p LEFT JOIN clientes c ON p.id_cliente = c.id WHERE p.id = ?");
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    if ($data && $data['email']) {
        emailCambioEstado($data, ['nombre' => $data['nombre'], 'email' => $data['email']], $nuevo_estado);
    }

    $stmt->close();
    $stmt_update->close();

    header("Location: productos.php?mensaje=estado_actualizado");
}
exit;
