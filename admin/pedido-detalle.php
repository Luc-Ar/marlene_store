<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/Database.php';

try {
    $conexion = Database::getConexion();

    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header('Location: pedidos.php');
        exit;
    }

    $id_pedido = (int)$_GET['id'];

    $stmt = $conexion->prepare("
        SELECT p.*, CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre, c.telefono, c.email
        FROM pedidos p
        LEFT JOIN clientes c ON p.id_cliente = c.id
        WHERE p.id = ? LIMIT 1
    ");
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $pedido = $stmt->get_result()->fetch_assoc();

    if (!$pedido) {
        header('Location: pedidos.php');
        exit;
    }

    $stmt2 = $conexion->prepare("SELECT * FROM pedido_items WHERE id_pedido = ?");
    $stmt2->bind_param("i", $id_pedido);
    $stmt2->execute();
    $items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    $colores = [
        'pendiente'      => '#F59E0B',
        'confirmado'     => '#27AE60',
        'en_preparacion' => '#2980B9',
        'enviado'        => '#8E44AD',
        'demorado'       => '#EF4444',
        'entregado'      => '#2C3E50',
        'cancelado'      => '#6B7280',
    ];
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #<?= htmlspecialchars($pedido['numero_pedido']) ?> — Marlene Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: #F2EBE0;
            color: #3A2526;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: #5C3D3E;
            min-height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo {
            padding: 28px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-script {
            font-family: 'Great Vibes', cursive;
            font-size: 2.6rem;
            color: #FAF6F1;
            display: block;
        }

        .logo-store {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 0.65rem;
            letter-spacing: 0.8rem;
            color: #C9A96E;
            text-transform: uppercase;
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
        }

        .nav-section {
            font-size: 0.55rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.3);
            padding: 16px 25px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 25px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.82rem;
            transition: 0.2s;
            border-left: 4px solid transparent;
        }

        .nav-item:hover,
        .nav-item.activo {
            background: rgba(255, 255, 255, 0.1);
            color: #FAF6F1;
            border-left-color: #C9A96E;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-logout-sidebar {
            display: block;
            text-align: center;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: 0.2s;
        }

        .btn-logout-sidebar:hover {
            background: rgba(192, 57, 43, 0.4);
            color: #fff;
        }

        .main {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
        }

        .detalle-wrap {
            max-width: 900px;
        }

        .detalle-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .detalle-header h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            color: #5C3D3E;
        }

        .detalle-header p {
            font-size: 0.78rem;
            color: #999;
            margin-top: 4px;
        }

        .badge {
            padding: 6px 14px;
            border-radius: 20px;
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid rgba(200, 152, 154, 0.2);
        }

        .info-box .label {
            font-size: 0.58rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #C9A96E;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .info-box p {
            font-size: 0.85rem;
            color: #3A2526;
            margin-bottom: 4px;
        }

        .btn-wa {
            color: #25D366;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.78rem;
        }

        .tabla-wrap {
            background: white;
            border-radius: 8px;
            border: 1px solid rgba(200, 152, 154, 0.2);
            overflow: hidden;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #5C3D3E;
        }

        thead th {
            padding: 14px 16px;
            text-align: left;
            font-size: 0.6rem;
            text-transform: uppercase;
            color: #C9A96E;
            letter-spacing: 1px;
        }

        tbody td {
            padding: 14px 16px;
            font-size: 0.82rem;
            border-bottom: 1px solid #F9F5F0;
        }

        .totales-box {
            background: white;
            border-radius: 8px;
            border: 1px solid rgba(200, 152, 154, 0.2);
            padding: 20px 24px;
            text-align: right;
        }

        .totales-box .peso {
            font-size: 0.78rem;
            color: #999;
            margin-bottom: 8px;
        }

        .totales-box .total {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: #5C3D3E;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-script">Marlene</span>
            <span class="logo-store">Store</span>
        </div>
        <nav class="sidebar-nav">
            <p class="nav-section">Principal</p>
            <a href="index.php" class="nav-item">📊 Dashboard</a>
            <p class="nav-section">Catálogo</p>
            <a href="productos.php" class="nav-item">🎒 Productos</a>
            <a href="categorias.php" class="nav-item">📁 Categorías</a>
            <p class="nav-section">Ventas</p>
            <a href="pedidos.php" class="nav-item activo">📦 Pedidos</a>
            <a href="clientes.php" class="nav-item">👥 Clientes</a>
            <p class="nav-section">Tienda</p>
            <a href="../index.php" class="nav-item" target="_blank">🌐 Ver tienda</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout-sidebar">🚪 Cerrar sesión</a>
        </div>
    </div>

    <div class="main">
        <div class="detalle-wrap">
            <a href="pedidos.php" style="color:#999;text-decoration:none;font-size:0.8rem;display:inline-block;margin-bottom:20px;">← Volver a pedidos</a>

            <div class="detalle-header">
                <div>
                    <h2>Pedido #<?= htmlspecialchars($pedido['numero_pedido']) ?></h2>
                    <p><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?> · Método: <?= htmlspecialchars($pedido['metodo_pago'] ?? '—') ?></p>
                </div>
                <span class="badge" style="background:<?= $colores[$pedido['estado']] ?? '#ccc' ?>">
                    <?= strtoupper(str_replace('_', ' ', $pedido['estado'])) ?>
                </span>
            </div>

            <div class="info-grid">
                <div class="info-box">
                    <p class="label">Cliente</p>
                    <p><strong><?= htmlspecialchars($pedido['cliente_nombre']) ?></strong></p>
                    <p><?= htmlspecialchars($pedido['email'] ?? '—') ?></p>
                </div>
                <div class="info-box">
                    <p class="label">Contacto</p>
                    <p><?= htmlspecialchars($pedido['telefono'] ?? '—') ?></p>
                    <?php if (!empty($pedido['telefono'])): ?>
                        <a href="https://wa.me/54<?= preg_replace('/[^0-9]/', '', $pedido['telefono']) ?>"
                            target="_blank" class="btn-wa">💬 Enviar WhatsApp</a>
                    <?php endif; ?>
                </div>
                <?php if ($pedido['notas']): ?>
                    <div class="info-box" style="grid-column:1/-1;">
                        <p class="label">Notas del pedido</p>
                        <p><?= htmlspecialchars($pedido['notas']) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tabla-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio unit.</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)):
                            $total_dinero = 0;
                            $total_peso = 0;
                            foreach ($items as $item):
                                $total_dinero += $item['subtotal'];
                                $total_peso += ($item['peso_unitario'] ?? 0) * $item['cantidad'];
                        ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($item['nombre_producto']) ?></strong></td>
                                    <td>$<?= number_format($item['precio_unitario'], 0, ',', '.') ?></td>
                                    <td><?= $item['cantidad'] ?> un.</td>
                                    <td><strong>$<?= number_format($item['subtotal'], 0, ',', '.') ?></strong></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center;padding:30px;color:#999;">
                                    No hay productos registrados en este pedido.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($items)): ?>
                <div class="totales-box">
                    <?php if ($total_peso > 0): ?>
                        <p class="peso">Peso total estimado: <?= number_format($total_peso, 3) ?> kg</p>
                    <?php endif; ?>
                    <p class="total">Total: $<?= number_format($total_dinero, 0, ',', '.') ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>