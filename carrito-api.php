<?php
require_once __DIR__ . '/includes/error-handler-json.php';
session_start();
require_once __DIR__ . '/config/Database.php';

header('Content-Type: application/json');

// Inicializar carrito en sesión
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$data = json_decode(file_get_contents('php://input'), true);
$accion = $data['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {

    case 'agregar':
        $id = (int)($data['producto_id'] ?? 0);
        $cantidad = (int)($data['cantidad'] ?? 1);

        if ($id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'ID inválido']);
            exit;
        }

        // Verificar que el producto existe y tiene stock
        $conexion = Database::getConexion();
        $stmt = $conexion->prepare("SELECT id, nombre, precio, imagen_principal, stock FROM productos WHERE id = ? AND activo = 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();

        if (!$producto) {
            echo json_encode(['ok' => false, 'error' => 'Producto no encontrado']);
            exit;
        }

        if ($producto['stock'] <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Sin stock disponible']);
            exit;
        }

        // Agregar o incrementar
        if (isset($_SESSION['carrito'][$id])) {
            $nueva_cantidad = $_SESSION['carrito'][$id]['cantidad'] + $cantidad;
            // No superar el stock
            $nueva_cantidad = min($nueva_cantidad, $producto['stock']);
            $_SESSION['carrito'][$id]['cantidad'] = $nueva_cantidad;
        } else {
            $_SESSION['carrito'][$id] = [
                'id'       => $id,
                'nombre'   => $producto['nombre'],
                'precio'   => (float)$producto['precio'],
                'imagen'   => $producto['imagen_principal'],
                'cantidad' => $cantidad,
                'stock'    => (int)$producto['stock'],
            ];
        }

        echo json_encode([
            'ok'    => true,
            'total' => array_sum(array_column($_SESSION['carrito'], 'cantidad')),
        ]);
        break;

    case 'quitar':
        $id = (int)($data['producto_id'] ?? 0);
        unset($_SESSION['carrito'][$id]);
        echo json_encode(['ok' => true]);
        break;

    case 'actualizar':
        $id = (int)($data['producto_id'] ?? 0);
        $cantidad = (int)($data['cantidad'] ?? 0);

        if ($cantidad <= 0) {
            unset($_SESSION['carrito'][$id]);
        } elseif (isset($_SESSION['carrito'][$id])) {
            $stock = $_SESSION['carrito'][$id]['stock'];
            $_SESSION['carrito'][$id]['cantidad'] = min($cantidad, $stock);
        }

        echo json_encode(['ok' => true]);
        break;

    case 'obtener':
        $carrito = array_values($_SESSION['carrito']);
        $total = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $carrito));
        $cantidad = array_sum(array_column($carrito, 'cantidad'));
        echo json_encode([
            'ok'       => true,
            'items'    => $carrito,
            'total'    => $total,
            'cantidad' => $cantidad,
        ]);
        break;

    case 'vaciar':
        $_SESSION['carrito'] = [];
        echo json_encode(['ok' => true]);
        break;

    case 'sincronizar':
        $conexion = Database::getConexion();
        $items = $data['items'] ?? [];
        $_SESSION['carrito'] = [];
        foreach ($items as $item) {
            $id = (int)($item['id'] ?? 0);
            $cantidad = max(1, (int)($item['cantidad'] ?? 1));

            // Buscamos el producto SIEMPRE en la base de datos — nunca
            // confiamos en el precio o el stock que mande el navegador,
            // solo usamos el id/nombre para identificar QUÉ producto es.
            if ($id > 0) {
                $stmt = $conexion->prepare("SELECT id, nombre, precio, imagen_principal, stock FROM productos WHERE id = ? AND activo = 1");
                $stmt->bind_param("i", $id);
            } else {
                $nombre = trim($item['nombre'] ?? '');
                if (!$nombre) continue;
                $stmt = $conexion->prepare("SELECT id, nombre, precio, imagen_principal, stock FROM productos WHERE nombre = ? AND activo = 1 LIMIT 1");
                $stmt->bind_param("s", $nombre);
            }
            $stmt->execute();
            $prod = $stmt->get_result()->fetch_assoc();

            // Si el producto ya no existe o está inactivo, lo salteamos.
            if (!$prod) continue;

            $cantidadFinal = min($cantidad, max(0, (int)$prod['stock']));
            if ($cantidadFinal <= 0) continue; // sin stock, no entra

            $_SESSION['carrito'][$prod['id']] = [
                'id'       => (int)$prod['id'],
                'nombre'   => $prod['nombre'],
                'precio'   => (float)$prod['precio'],
                'imagen'   => $prod['imagen_principal'],
                'cantidad' => $cantidadFinal,
                'stock'    => (int)$prod['stock'],
            ];
        }
        echo json_encode(['ok' => true, 'items' => count($_SESSION['carrito'])]);
        break;
        echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
}
