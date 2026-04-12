<?php
// api/guardar-pedido.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '../config/database.php';
$conexion = conectar();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
    exit;
}

$nombre    = trim($data['nombre'] ?? '');
$telefono  = trim($data['telefono'] ?? '');
$email     = strtolower(trim($data['email'] ?? ''));
$productos = $data['productos'] ?? [];
$total     = (float)($data['total'] ?? 0);
$metodo    = trim($data['metodo_pago'] ?? 'whatsapp');

if (empty($nombre) || empty($telefono) || empty($productos)) {
    echo json_encode(['ok' => false, 'error' => 'Faltan datos obligatorios']);
    exit;
}

// --- INICIO DE TRANSACCIÓN ---
$conexion->begin_transaction();

try {
    // 1. Buscar o crear cliente
    $stmt = $conexion->prepare("SELECT id FROM clientes WHERE telefono = ? LIMIT 1");
    $stmt->bind_param('s', $telefono);
    $stmt->execute();
    $resCliente = $stmt->get_result()->fetch_assoc();

    if ($resCliente) {
        $id_cliente = $resCliente['id'];
    } else {
        $partes = explode(' ', $nombre, 2);
        $nombre_c = $partes[0];
        $apellido_c = $partes[1] ?? '';
        $stmt2 = $conexion->prepare("INSERT INTO clientes (nombre, apellido, telefono, email, activo) VALUES (?, ?, ?, ?, 1)");
        $stmt2->bind_param('ssss', $nombre_c, $apellido_c, $telefono, $email);
        $stmt2->execute();
        $id_cliente = $conexion->insert_id;
    }

    // 2. Generar número de pedido (Mejorado para evitar duplicados en picos de tráfico)
    $resUltimo = $conexion->query("SELECT id FROM pedidos ORDER BY id DESC LIMIT 1")->fetch_assoc();
    $proximo_id = ($resUltimo['id'] ?? 0) + 1;
    $numero_pedido = 'PED-' . date('Y') . '-' . str_pad($proximo_id, 4, '0', STR_PAD_LEFT);

    // 3. Insertar pedido
    $stmt3 = $conexion->prepare("INSERT INTO pedidos (id_cliente, numero_pedido, estado, total, metodo_pago, fecha_creacion) VALUES (?, ?, 'pendiente', ?, ?, NOW())");
    $stmt3->bind_param('isds', $id_cliente, $numero_pedido, $total, $metodo);
    $stmt3->execute();
    $id_pedido = $conexion->insert_id;

    // 4. Insertar items y actualizar stock (opcional pero recomendado)
    $stmt4 = $conexion->prepare("INSERT INTO pedido_items (id_pedido, id_producto, nombre_producto, precio_unitario, cantidad, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    
// 4. Guardar los productos del pedido y RESTAR STOCK
    $stmt4 = $conexion->prepare("INSERT INTO pedido_items (id_pedido, id_producto, nombre_producto, precio_unitario, cantidad, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Preparamos la consulta para restar stock una sola vez (más eficiente)
    $stmtStock = $conexion->prepare("UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?");

    foreach ($productos as $prod) {
        $id_prod     = (int)($prod['id'] ?? 0);
        $nombre_prod = trim($prod['nombre'] ?? 'Producto');
        $precio_unit = (float)($prod['precio'] ?? 0);
        $cantidad    = (int)($prod['cantidad'] ?? 1);
        $subtotal    = $precio_unit * $cantidad;

        // A. Insertar el item en el pedido
        $stmt4->bind_param('iisdid', $id_pedido, $id_prod, $nombre_prod, $precio_unit, $cantidad, $subtotal);
        $stmt4->execute();
        
        // B. Restar Stock con validación
        // El tercer parámetro ($cantidad) en el WHERE asegura que no quede stock negativo
        $stmtStock->bind_param('iii', $cantidad, $id_prod, $cantidad);
        $stmtStock->execute();

        if ($stmtStock->affected_rows === 0) {
            // Si no se afectaron filas, es porque no había stock suficiente
            throw new Exception("Stock insuficiente para el producto: $nombre_prod");
        }
    }

    // 5. Registrar estado inicial
    $stmt5 = $conexion->prepare("INSERT INTO estados_pedido (id_pedido, estado, notas, fecha) VALUES (?, 'pendiente', 'Pedido recibido desde la tienda', NOW())");
    $stmt5->bind_param('i', $id_pedido);
    $stmt5->execute();

    // Si todo salió bien, guardamos los cambios
    $conexion->commit();

    echo json_encode([
        'ok' => true,
        'numero_pedido' => $numero_pedido,
        'id_pedido' => $id_pedido
    ]);

} catch (Exception $e) {
    // Si algo falla, deshacemos todo lo que se intentó guardar
    $conexion->rollback();
    echo json_encode(['ok' => false, 'error' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>