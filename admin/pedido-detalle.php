<?php
session_start();
// 1. Verificación de sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/Database.php';

try {
    $conexion = Database::getConexion();

    // 2. Validar que exista el ID en la URL
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header('Location: pedidos.php');
        exit;
    }

    $id_pedido = (int)$_GET['id'];

    // 3. Traer datos generales del PEDIDO y del CLIENTE
    $sql_pedido = "SELECT p.*, CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre, c.telefono, c.email
                   FROM pedidos p 
                   LEFT JOIN clientes c ON p.id_cliente = c.id 
                   WHERE p.id = $id_pedido";

    $res_p = $conexion->query($sql_pedido);
    if ($res_p->num_rows === 0) {
        die("El pedido no existe.");
    }
    $pedido = $res_p->fetch_assoc();

    // 4. Traer los PRODUCTOS del pedido
    $sql_items = "SELECT * FROM pedido_items WHERE id_pedido = $id_pedido";
    $res_items = $conexion->query($sql_items);

    $total_dinero = 0;
    $total_peso_kg = 0;

    $colores = [
        'pendiente' => '#FAC775',
        'confirmado' => '#5DCAA5',
        'en_preparacion' => '#85B7EB',
        'enviado' => '#C9A96E',
        'entregado' => '#97C459',
        'cancelado' => '#B4B2A9'
    ];
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Detalle Pedido #<?= $id_pedido ?> — Marlene Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #F2EBE0;
            padding: 40px;
            color: #3A2526;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .header-detalle {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #F2EBE0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.2rem;
            color: #5C3D3E;
        }

        .info-cliente {
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .dato-box {
            background: #FDFBF8;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #EEE;
        }

        .dato-label {
            font-size: 0.65rem;
            font-weight: 700;
            color: #C9A96E;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            text-align: left;
            padding: 15px;
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #C9A96E;
            border-bottom: 2px solid #F2EBE0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #F9F9F9;
            font-size: 0.9rem;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .btn-volver {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #5C3D3E;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .totales {
            margin-top: 30px;
            text-align: right;
            padding: 20px;
            background: #FDFBF8;
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <div class="container">
        <a href="pedidos.php" class="btn-volver">← Volver a Pedidos</a>

        <div class="header-detalle">
            <div>
                <h2>Pedido #<?= $pedido['numero_pedido'] ?? $pedido['id'] ?></h2>
                <p>Fecha: <?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></p>
            </div>
            <div style="text-align: right;">
                <span class="badge" style="background: <?= $colores[$pedido['estado']] ?>">
                    <?= strtoupper(str_replace('_', ' ', $pedido['estado'])) ?>
                </span>
            </div>
        </div>

        <div class="info-cliente">
            <div class="dato-box">
                <p class="dato-label">Cliente</p>
                <p><strong><?= htmlspecialchars($pedido['cliente_nombre']) ?></strong></p>
                <p><?= $pedido['email'] ?></p>
            </div>
            <div class="dato-box">
                <p class="dato-label">Contacto</p>
                <p>Tel: <?= $pedido['telefono'] ?></p>
                <a href="https://wa.me/54<?= $pedido['telefono'] ?>" target="_blank" style="color: #27AE60; font-size: 0.8rem; text-decoration: none; font-weight: 700;">Enviar WhatsApp</a>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Peso</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_dinero = 0;
                $total_peso_kg = 0;

                // Si res_items tiene filas, las recorremos
                if ($res_items && $res_items->num_rows > 0):
                    while ($item = $res_items->fetch_assoc()):
                        // Sumamos a los totales generales
                        $total_dinero += $item['subtotal'];
                        $total_peso_kg += ($item['peso_unitario'] * $item['cantidad']);
                ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nombre_producto']) ?></td>
                            <td>$<?= number_format($item['precio_unitario'], 2, ',', '.') ?></td>
                            <td><?= $item['cantidad'] ?> un.</td>
                            <td><?= number_format($item['peso_unitario'], 3) ?> kg</td>
                            <td><strong>$<?= number_format($item['subtotal'], 2, ',', '.') ?></strong></td>
                        </tr>
                    <?php
                    endwhile;
                else:
                    ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 20px;">
                            No hay productos registrados para este pedido.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="totales">
            <p style="font-size: 0.8rem; color: #888;">Peso Total estimado: <strong><?= number_format($total_peso_kg, 3) ?> kg</strong></p>
            <p style="font-size: 1.5rem; color: #5C3D3E; margin-top: 10px;">Total a Cobrar: <strong>$<?= number_format($total_dinero, 2, ',', '.') ?></strong></p>
        </div>
    </div>

</body>

</html>