<?php
// ... conexión ...

$conexion->begin_transaction();

try {
    // 1. Insertar el pedido general
    $sql_pedido = "INSERT INTO pedidos (id_cliente, fecha_pedido, estado, total) 
                   VALUES ($id_cliente, NOW(), 'pendiente', $total_carrito)";
    $conexion->query($sql_pedido);

    // 2. Recuperar el ID que se acaba de generar
    $id_generado = $conexion->insert_id;

    // 3. Insertar cada producto del carrito (recorremos el array de la sesión)
    foreach ($_SESSION['carrito'] as $producto) {
        $sql_item = "INSERT INTO pedido_items (id_pedido, id_producto, nombre_producto, precio_unitario, cantidad, peso_unitario, subtotal) 
                     VALUES ($id_generado, {$producto['id']}, '{$producto['nombre']}', {$producto['precio']}, {$producto['cantidad']}, {$producto['peso']}, {$producto['subtotal']})";
        $conexion->query($sql_item);
    }

    $conexion->commit();
    echo "Venta exitosa";
} catch (Exception $e) {
    $conexion->rollback();
    echo "Error al procesar: " . $e->getMessage();
}
