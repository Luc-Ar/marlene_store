<?php
session_start();
// Asegurate de que la ruta a Database.php sea la correcta según tu carpeta
require_once '../config/Database.php';

// 1. Verificación de seguridad
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header('Location: ../index.php?error=carrito_vacio');
    exit;
}

try {
    $conexion = Database::getConexion();
    $conexion->begin_transaction(); // Iniciamos la transacción atómica

    // 2. Preparación de datos
    $id_cliente = $_POST['id_cliente'] ?? 1; // Por ahora cliente 1 por defecto
    $total_final = 0;
    $peso_total = 0;

    // Calculamos totales recorriendo el carrito
    foreach ($_SESSION['carrito'] as $item) {
        $total_final += $item['precio'] * $item['cantidad'];
        $peso_total += ($item['peso'] ?? 0) * $item['cantidad'];
    }

    // Generamos un código de pedido legible (ej: PED-20260412-456)
    $nro_pedido = "PED-" . date('Ymd') . "-" . rand(100, 999);

    // 3. Insertar en la tabla 'pedidos'
    $sql_pedido = "INSERT INTO pedidos (numero_pedido, id_cliente, fecha_pedido, total, peso_total, estado) 
                   VALUES (?, ?, NOW(), ?, ?, 'pendiente')";

    $stmt = $conexion->prepare($sql_pedido);
    $stmt->bind_param("sidd", $nro_pedido, $id_cliente, $total_final, $peso_total);
    $stmt->execute();

    // Obtenemos el ID numérico que la base de datos le asignó a este pedido
    $id_pedido_generado = $conexion->insert_id;

    // 4. Insertar los productos en 'pedido_items'
    $sql_items = "INSERT INTO pedido_items (id_pedido, id_producto, nombre_producto, precio_unitario, cantidad, peso_unitario, subtotal) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_item = $conexion->prepare($sql_items);

    foreach ($_SESSION['carrito'] as $producto) {
        $subtotal = $producto['precio'] * $producto['cantidad'];
        $peso_u = $producto['peso'] ?? 0;

        $stmt_item->bind_param(
            "iisdidd",
            $id_pedido_generado,
            $producto['id'],
            $producto['nombre'],
            $producto['precio'],
            $producto['cantidad'],
            $peso_u,
            $subtotal
        );
        $stmt_item->execute();
    }

    // 5. Finalizar transacción
    $conexion->commit();

    // Limpiamos el carrito de la sesión porque la venta ya se concretó
    unset($_SESSION['carrito']);

    // Redirigimos a una página de confirmación
    header("Location: ../confirmacion.php?id=" . $id_pedido_generado);
} catch (Exception $e) {
    $conexion->rollback(); // Si algo falla, se borra lo que se intentó insertar
    die("Error crítico en la venta: " . $e->getMessage());
}
