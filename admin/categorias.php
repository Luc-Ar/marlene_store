<?php
session_start();
require_once __DIR__ . '/../includes/error-handler.php';
require_once __DIR__ . '/../config/Database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$conexion  = Database::getConexion();
$resultado = $conexion->query("SELECT * FROM categorias ORDER BY orden_display ASC, nombre ASC");

// Variables para header-admin.php
$titulo_admin = 'Categorías';
$nav_activo   = 'categorias';
$estilos_extra_admin = '
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; }
.page-header h2 { font-family:"Cormorant Garamond",serif; font-size:2rem; color:var(--marlene); }
.page-header p  { font-size:0.8rem; color:#999; margin-top:4px; }
.btn-nuevo { background:var(--dorado); color:white; padding:10px 20px; border-radius:6px; text-decoration:none; font-weight:700; font-size:0.65rem; text-transform:uppercase; transition:0.2s; }
.btn-nuevo:hover { background:var(--marlene); }
.tabla-wrap { background:white; border-radius:8px; border:1px solid rgba(200,152,154,0.2); overflow:hidden; }
.tabla-wrap table thead { background:var(--marlene); }
.tabla-wrap table thead th { padding:14px 16px; text-align:left; font-size:0.6rem; text-transform:uppercase; color:var(--dorado); letter-spacing:1px; }
.tabla-wrap table tbody td { padding:14px 16px; font-size:0.82rem; border-bottom:1px solid #F9F5F0; vertical-align:middle; }
.tabla-wrap table tbody tr:hover { background:#FDFAF8; }
.badge-activo   { background:#DCFCE7; color:#166534; padding:4px 10px; border-radius:20px; font-size:0.58rem; font-weight:700; text-transform:uppercase; }
.badge-inactivo { background:#FEE2E2; color:#991B1B; padding:4px 10px; border-radius:20px; font-size:0.58rem; font-weight:700; text-transform:uppercase; }
.btn-accion { padding:6px 12px; font-size:0.62rem; font-weight:700; text-decoration:none; border-radius:4px; text-transform:uppercase; display:inline-block; transition:0.2s; margin-right:4px; border:1px solid transparent; }
.btn-ver     { background:var(--dorado); color:white; }
.btn-ver:hover { background:var(--marlene); }
.btn-editar  { background:#F2EBE0; color:var(--marlene); border-color:rgba(200,152,154,0.3); }
.btn-editar:hover { background:#e5ddd1; }
';

require_once __DIR__ . '/includes/header-admin.php';
?>

<div class="page-header">
    <div>
        <h2>Categorías</h2>
        <p>Gestioná las secciones de tu tienda</p>
    </div>
    <a href="/admin/categoria-nueva.php" class="btn-nuevo">+ Nueva categoría</a>
</div>

<div class="tabla-wrap">
    <table>
        <thead>
            <tr>
                <th>Nombre / Slug</th>
                <th>Ícono</th>
                <th>Orden</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($cat = $resultado->fetch_assoc()): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($cat['nombre']) ?></strong><br>
                        <small style="color:#aaa;">/<?= htmlspecialchars($cat['slug']) ?></small>
                    </td>
                    <td style="font-size:1.4rem;"><?= $cat['icono'] ?? '—' ?></td>
                    <td><strong>#<?= $cat['orden_display'] ?></strong></td>
                    <td>
                        <span class="<?= $cat['activo'] ? 'badge-activo' : 'badge-inactivo' ?>">
                            <?= $cat['activo'] ? 'Activa' : 'Pausada' ?>
                        </span>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="/admin/productos.php?categoria=<?= $cat['id'] ?>&ver=activos" class="btn-accion btn-ver">
                            Ver productos
                        </a>
                        <a href="/admin/categoria-editar.php?id=<?= $cat['id'] ?>" class="btn-accion btn-editar">
                            Editar
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer-admin.php'; ?>