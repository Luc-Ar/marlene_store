<?php
session_start();
require_once __DIR__ . '/../includes/error-handler.php';
require_once __DIR__ . '/../config/Database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$conexion = Database::getConexion();

if (empty($_GET['id'])) {
    header('Location: /admin/pedidos.php');
    exit;
}

$id_pedido = (int)$_GET['id'];

$stmt = $conexion->prepare("
    SELECT p.*, CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
           c.telefono, c.email
    FROM pedidos p
    LEFT JOIN clientes c ON p.id_cliente = c.id
    WHERE p.id = ? LIMIT 1
");
$stmt->bind_param("i", $id_pedido);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    header('Location: /admin/pedidos.php');
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

// Variables para header-admin.php
$titulo_admin = 'Pedido #' . htmlspecialchars($pedido['numero_pedido']);
$nav_activo   = 'pedidos';
$estilos_extra_admin = '
.detalle-wrap { max-width:900px; }
.detalle-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; flex-wrap:wrap; gap:16px; }
.detalle-header h2 { font-family:"Cormorant Garamond",serif; font-size:2rem; color:var(--marlene); }
.detalle-header p { font-size:0.78rem; color:#999; margin-top:4px; }
.estado-badge { padding:6px 14px; border-radius:20px; color:white; font-size:0.65rem; font-weight:700; text-transform:uppercase; }
.info-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px; }
.info-box { background:white; padding:20px; border-radius:8px; border:1px solid rgba(200,152,154,0.2); }
.info-box .label { font-size:0.58rem; font-weight:700; text-transform:uppercase; color:var(--dorado); letter-spacing:1px; margin-bottom:8px; }
.info-box p { font-size:0.85rem; color:#3A2526; margin-bottom:4px; }
.btn-wa { color:#25D366; text-decoration:none; font-weight:700; font-size:0.78rem; }
.tabla-wrap { background:white; border-radius:8px; border:1px solid rgba(200,152,154,0.2); overflow:hidden; margin-bottom:16px; }
.tabla-wrap table thead { background:var(--marlene); }
.tabla-wrap table thead th { padding:14px 16px; text-align:left; font-size:0.6rem; text-transform:uppercase; color:var(--dorado); letter-spacing:1px; }
.tabla-wrap table tbody td { padding:14px 16px; font-size:0.82rem; border-bottom:1px solid #F9F5F0; }
.totales-box { background:white; border-radius:8px; border:1px solid rgba(200,152,154,0.2); padding:20px 24px; text-align:right; }
.totales-box .peso  { font-size:0.78rem; color:#999; margin-bottom:8px; }
.totales-box .total { font-family:"Cormorant Garamond",serif; font-size:1.8rem; font-weight:700; color:var(--marlene); }
.btn-volver { color:#999; text-decoration:none; font-size:0.8rem; display:inline-block; margin-bottom:20px; }
.btn-volver:hover { color:var(--marlene); }
';

require_once __DIR__ . '/includes/header-admin.php';
?>

<div class="detalle-wrap">
    <a href="/admin/pedidos.php" class="btn-volver">← Volver a pedidos</a>

    <div class="detalle-header">
        <div>
            <h2>Pedido #<?= htmlspecialchars($pedido['numero_pedido']) ?></h2>
            <p>
                <?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?>
                · Método: <?= htmlspecialchars($pedido['metodo_pago'] ?? '—') ?>
            </p>
        </div>
        <span class="estado-badge" style="background:<?= $colores[$pedido['estado']] ?? '#ccc' ?>">
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
        <?php if (!empty($pedido['notas'])): ?>
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
                    $total_peso   = 0;
                    foreach ($items as $item):
                        $total_dinero += $item['subtotal'];
                        $total_peso   += ($item['peso_unitario'] ?? 0) * $item['cantidad'];
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
                <p class="peso">Peso total estimado: <?= number_format($total_peso / 1000, 2) ?> kg</p>
            <?php endif; ?>
            <p class="total">Total: $<?= number_format($total_dinero, 0, ',', '.') ?></p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer-admin.php'; ?>