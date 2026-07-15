<?php
session_start();
require_once __DIR__ . '/../includes/error-handler.php';

// Chequeo temprano: si no hay sesión, cortamos antes de tocar la DB.
// header-admin.php también protege esto, pero salir acá evita hacer
// consultas de más si ni siquiera está logueado.
if (!isset($_SESSION['usuario_id'])) {
  header('Location: /admin/login.php');
  exit;
}

require_once __DIR__ . '/../autoload.php';

try {
  $db          = Database::getConexion();
  $clienteRepo = new ClienteRepository($db);
  $busqueda    = trim($_GET['buscar'] ?? '');
  $clientes    = $clienteRepo->listarClientes($busqueda);
} catch (Exception $e) {
  error_log("Error en clientes.php: " . $e->getMessage());
  $clientes = [];
}

// Variables que espera header-admin.php
$titulo_admin = 'Clientes';
$nav_activo   = 'clientes';

// CSS propio de esta página (avatar, buscador, badges, link de WhatsApp).
// header-admin.php ya trae estilos genéricos (.panel, table, .badge),
// esto son solo los que le faltan a esta pantalla en particular.
$estilos_extra_admin = <<<CSS
.page-header { margin-bottom: 28px; }
.page-header h2 { font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: var(--marlene); }
.page-header p { font-size: 0.8rem; color: #999; margin-top: 4px; }

.search-area {
    background: white;
    padding: 16px 20px;
    border-radius: 8px;
    border: 1px solid rgba(200, 152, 154, 0.2);
    margin-bottom: 20px;
    display: flex;
    gap: 12px;
    align-items: center;
}
.search-input {
    flex: 1;
    max-width: 400px;
    padding: 10px 14px;
    border: 1.5px solid rgba(200, 152, 154, 0.3);
    border-radius: 6px;
    font-family: 'Montserrat', sans-serif;
    font-size: 0.82rem;
}
.btn-buscar {
    background: var(--marlene);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 700;
    font-size: 0.7rem;
    transition: 0.2s;
}
.btn-buscar:hover { background: var(--dorado); }

.tabla-wrap {
    background: white;
    border-radius: 8px;
    border: 1px solid rgba(200, 152, 154, 0.2);
    overflow: hidden;
}
.tabla-wrap table th { padding: 14px 16px; }
.tabla-wrap table td { padding: 14px 16px; vertical-align: middle; }
.tabla-wrap tbody tr:hover { background: #FDFAF8; }

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--marlene);
    color: #FAF6F1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 700;
}
.badge-activo {
    background: #DCFCE7;
    color: #166534;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.58rem;
    font-weight: 700;
    text-transform: uppercase;
}
.btn-wa {
    color: #25D366;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.75rem;
}
CSS;

require_once __DIR__ . '/includes/header-admin.php';
?>

<div class="page-header">
  <h2>Clientes</h2>
  <p>Historial y gestión de contactos</p>
</div>

<form method="GET" class="search-area">
  <input type="text" name="buscar" class="search-input"
    placeholder="Nombre, teléfono o email..."
    value="<?= htmlspecialchars($busqueda) ?>">
  <button type="submit" class="btn-buscar">Buscar</button>
  <?php if ($busqueda): ?>
    <a href="/admin/clientes.php" style="color:#999;text-decoration:none;font-size:0.7rem;font-weight:700;">LIMPIAR</a>
  <?php endif; ?>
</form>

<div class="tabla-wrap">
  <table>
    <thead>
      <tr>
        <th></th>
        <th>Nombre</th>
        <th>Email</th>
        <th>Teléfono</th>
        <th>Pedidos</th>
        <th>Total gastado</th>
        <th>Estado</th>
        <th>Registro</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($clientes)): ?>
        <?php foreach ($clientes as $c): ?>
          <tr>
            <td>
              <div class="avatar">
                <?= strtoupper(substr($c['nombre'] ?? '?', 0, 1) . substr($c['apellido'] ?? '', 0, 1)) ?>
              </div>
            </td>
            <td><strong><?= htmlspecialchars(trim($c['nombre'] . ' ' . $c['apellido'])) ?></strong></td>
            <td style="font-size:0.78rem;"><?= htmlspecialchars($c['email'] ?? '') ?></td>
            <td>
              <?php if (!empty($c['telefono'])): ?>
                <a href="https://wa.me/54<?= preg_replace('/[^0-9]/', '', $c['telefono']) ?>"
                  target="_blank" class="btn-wa">
                  📱 <?= htmlspecialchars($c['telefono']) ?>
                </a>
              <?php else: ?>
                <span style="color:#ccc;">—</span>
              <?php endif; ?>
            </td>
            <td style="text-align:center;"><?= (int)($c['total_pedidos'] ?? 0) ?></td>
            <td><strong>$<?= number_format($c['total_gastado'] ?? 0, 0, ',', '.') ?></strong></td>
            <td>
              <span class="badge-activo">
                <?= $c['activo'] ? 'Activo' : 'Inactivo' ?>
              </span>
            </td>
            <td style="font-size:0.75rem;color:#999;">
              <?= isset($c['fecha_registro']) ? date('d/m/Y', strtotime($c['fecha_registro'])) : '—' ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="8" style="text-align:center;padding:40px;color:#999;">No se encontraron clientes.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer-admin.php'; ?>