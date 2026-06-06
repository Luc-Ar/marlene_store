<?php
session_start();
require_once __DIR__ . '/config/Database.php';

if (!isset($_SESSION['cliente_id'])) {
    header('Location: login-cliente.php?redirect=mi-cuenta.php');
    exit;
}

$conexion = Database::getConexion();
$id_cliente = (int)$_SESSION['cliente_id'];

// Datos del cliente
$stmt = $conexion->prepare("SELECT * FROM clientes WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

// Pedidos del cliente
$stmt = $conexion->prepare("
    SELECT p.id, p.numero_pedido, p.estado, p.total, p.fecha_pedido,
           COUNT(pi.id) as cant_items
    FROM pedidos p
    LEFT JOIN pedido_items pi ON p.id = pi.id_pedido
    WHERE p.id_cliente = ?
    GROUP BY p.id
    ORDER BY p.fecha_pedido DESC
");
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Nueva dirección
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_direccion'])) {
    $calle     = trim($_POST['calle'] ?? '');
    $altura    = (int)($_POST['altura'] ?? 0);
    $cp        = trim($_POST['codigo_postal'] ?? '');
    $localidad = trim($_POST['localidad'] ?? '');
    $id_prov   = (int)($_POST['id_provincia'] ?? 0);
    $desc      = trim($_POST['descripcion_adicional'] ?? '');
    $principal = isset($_POST['principal']) ? 1 : 0;

    if ($calle && $localidad && $id_prov) {
        $stmt = $conexion->prepare("SELECT id FROM localidades WHERE nombre = ? AND id_provincia = ? LIMIT 1");
        $stmt->bind_param("si", $localidad, $id_prov);
        $stmt->execute();
        $loc = $stmt->get_result()->fetch_assoc();
        if ($loc) {
            $id_loc = $loc['id'];
        } else {
            $stmt = $conexion->prepare("INSERT INTO localidades (nombre, id_provincia) VALUES (?, ?)");
            $stmt->bind_param("si", $localidad, $id_prov);
            $stmt->execute();
            $id_loc = $conexion->insert_id;
        }

        if ($principal) {
            $stmt2 = $conexion->prepare("UPDATE direcciones SET principal = 0 WHERE id_cliente = ?");
            $stmt2->bind_param("i", $id_cliente);
            $stmt2->execute();
        }

        $stmt = $conexion->prepare("INSERT INTO direcciones (id_cliente, calle, altura, codigo_postal, id_localidad, descripcion_adicional, principal) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isisisi", $id_cliente, $calle, $altura, $cp, $id_loc, $desc, $principal);
        $stmt->execute();
    }
    header('Location: mi-cuenta.php?tab=direcciones');
    exit;
}

// Eliminar dirección
if (isset($_GET['eliminar_direccion'])) {
    $id_dir = (int)$_GET['eliminar_direccion'];
    $stmt = $conexion->prepare("DELETE FROM direcciones WHERE id = ? AND id_cliente = ?");
    $stmt->bind_param("ii", $id_dir, $id_cliente);
    $stmt->execute();
    header('Location: mi-cuenta.php?tab=direcciones');
    exit;
}

// Actualizar datos
$exito_datos = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_datos'])) {
    $nuevo_nombre   = trim($_POST['nombre'] ?? '');
    $nuevo_apellido = trim($_POST['apellido'] ?? '');
    $nuevo_telefono = trim($_POST['telefono'] ?? '');
    if ($nuevo_nombre && $nuevo_apellido) {
        $stmt = $conexion->prepare("UPDATE clientes SET nombre=?, apellido=?, telefono=? WHERE id=?");
        $stmt->bind_param("sssi", $nuevo_nombre, $nuevo_apellido, $nuevo_telefono, $id_cliente);
        $stmt->execute();
        $_SESSION['cliente_nombre'] = $nuevo_nombre;
        $cliente['nombre']   = $nuevo_nombre;
        $cliente['apellido'] = $nuevo_apellido;
        $cliente['telefono'] = $nuevo_telefono;
        $exito_datos = 'Datos actualizados correctamente.';
    }
}

// Provincias para el formulario de dirección
$stmt = $conexion->prepare("SELECT id, nombre FROM provincias ORDER BY nombre ASC");
$stmt->execute();
$provincias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$estados = [
    'pendiente'      => ['label' => 'Pendiente',      'color' => '#F59E0B', 'bg' => '#FEF3C7'],
    'confirmado'     => ['label' => 'Confirmado',     'color' => '#3B82F6', 'bg' => '#EFF6FF'],
    'en_preparacion' => ['label' => 'En preparación', 'color' => '#8B5CF6', 'bg' => '#F5F3FF'],
    'demorado'       => ['label' => 'Demorado',       'color' => '#EF4444', 'bg' => '#FEF2F2'],
    'enviado'        => ['label' => 'Enviado',        'color' => '#06B6D4', 'bg' => '#ECFEFF'],
    'entregado'      => ['label' => 'Entregado',      'color' => '#16A34A', 'bg' => '#DCFCE7'],
    'cancelado'      => ['label' => 'Cancelado',      'color' => '#6B7280', 'bg' => '#F3F4F6'],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi cuenta — Marlene STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,400;0,600&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        * {
            box-sizing: border-box;
        }

        .cuenta-wrap {
            max-width: 1000px;
            margin: 120px auto 60px;
            padding: 0 24px;
        }

        .cuenta-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 40px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .cuenta-bienvenida {
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            color: var(--marron);
            line-height: 1;
        }

        .cuenta-email {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            color: #999;
            margin-top: 4px;
        }

        .btn-logout {
            padding: 10px 20px;
            background: none;
            border: 1px solid rgba(200, 152, 154, 0.4);
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--marron);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: #FEE2E2;
            border-color: #DC2626;
            color: #DC2626;
        }

        .cuenta-tabs {
            display: flex;
            border-bottom: 2px solid rgba(200, 152, 154, 0.2);
            margin-bottom: 32px;
        }

        .tab-btn {
            padding: 12px 24px;
            background: none;
            border: none;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #999;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
        }

        .tab-btn.activo {
            color: var(--marron);
            border-bottom-color: var(--marron);
        }

        .tab-btn:hover {
            color: var(--marron);
        }

        .tab-content {
            display: none;
        }

        .tab-content.activo {
            display: block;
        }

        .pedidos-lista {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .pedido-card {
            background: white;
            border: 1px solid rgba(200, 152, 154, 0.2);
            border-radius: 10px;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            transition: box-shadow 0.2s;
        }

        .pedido-card:hover {
            box-shadow: 0 8px 24px rgba(92, 61, 62, 0.08);
        }

        .pedido-numero {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--marron);
        }

        .pedido-fecha {
            font-size: 0.75rem;
            color: #999;
            margin-top: 3px;
        }

        .pedido-estado {
            padding: 5px 12px;
            border-radius: 20px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .pedido-total {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--marron);
            text-align: right;
        }

        .pedido-total-label {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.55rem;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sin-pedidos {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .sin-pedidos p:first-child {
            font-size: 3rem;
            margin-bottom: 12px;
        }

        .datos-card {
            background: white;
            border: 1px solid rgba(200, 152, 154, 0.2);
            border-radius: 10px;
            padding: 28px;
        }

        .datos-titulo {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--dorado);
            margin-bottom: 20px;
        }

        .datos-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .dato-item label {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #999;
            display: block;
            margin-bottom: 4px;
        }

        .dato-item input,
        .dato-item select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid rgba(200, 152, 154, 0.3);
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.85rem;
            color: var(--marron);
            background: #FDFAF8;
            transition: border-color 0.2s;
        }

        .dato-item input:focus,
        .dato-item select:focus {
            outline: none;
            border-color: var(--dorado);
        }

        .btn-guardar {
            margin-top: 20px;
            padding: 12px 28px;
            background: var(--marron);
            color: var(--crema);
            border: none;
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-guardar:hover {
            background: var(--dorado);
        }

        .exito-msg {
            background: #DCFCE7;
            color: #166534;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 0.8rem;
            margin-bottom: 16px;
        }

        @media (max-width: 600px) {
            .cuenta-wrap {
                margin-top: 80px;
            }

            .datos-grid {
                grid-template-columns: 1fr;
            }

            .pedido-card {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/includes/nav.php'; ?>

    <div class="cuenta-wrap">

        <div class="cuenta-header">
            <div>
                <h1 class="cuenta-bienvenida">Hola, <?= htmlspecialchars($cliente['nombre']) ?>!</h1>
                <p class="cuenta-email"><?= htmlspecialchars($cliente['email']) ?></p>
            </div>
            <a href="logout-cliente.php" class="btn-logout">Cerrar sesión</a>
        </div>

        <div class="cuenta-tabs">
            <button class="tab-btn activo" onclick="mostrarTab('pedidos', this)">📦 Mis pedidos</button>
            <button class="tab-btn" onclick="mostrarTab('datos', this)">👤 Mis datos</button>
            <button class="tab-btn" onclick="mostrarTab('direcciones', this)">📍 Direcciones</button>
        </div>

        <!-- TAB PEDIDOS -->
        <div class="tab-content activo" id="tab-pedidos">
            <?php if (empty($pedidos)): ?>
                <div class="sin-pedidos">
                    <p>📦</p>
                    <p>Todavía no realizaste ningún pedido</p>
                    <a href="catalogo.php" style="display:inline-block;margin-top:20px;padding:12px 24px;background:var(--marron);color:var(--crema);text-decoration:none;border-radius:4px;font-family:'Montserrat',sans-serif;font-size:0.65rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;">
                        Ver productos
                    </a>
                </div>
            <?php else: ?>
                <div class="pedidos-lista">
                    <?php foreach ($pedidos as $pedido):
                        $estado = $estados[$pedido['estado']] ?? ['label' => $pedido['estado'], 'color' => '#999', 'bg' => '#F3F4F6'];
                    ?>
                        <div class="pedido-card">
                            <div>
                                <div class="pedido-numero"># <?= htmlspecialchars($pedido['numero_pedido']) ?></div>
                                <div class="pedido-fecha"><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></div>
                                <div style="font-size:0.65rem;color:#999;margin-top:3px;">
                                    <?= $pedido['cant_items'] ?> producto<?= $pedido['cant_items'] != 1 ? 's' : '' ?>
                                </div>
                            </div>
                            <span class="pedido-estado" style="background:<?= $estado['bg'] ?>;color:<?= $estado['color'] ?>">
                                <?= $estado['label'] ?>
                            </span>
                            <div style="text-align:right;">
                                <div class="pedido-total-label">Total</div>
                                <div class="pedido-total">$<?= number_format($pedido['total'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- TAB DATOS -->
        <div class="tab-content" id="tab-datos">
            <div class="datos-card">
                <div class="datos-titulo">📋 Datos personales</div>
                <?php if ($exito_datos): ?>
                    <div class="exito-msg">✅ <?= $exito_datos ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="actualizar_datos" value="1">
                    <div class="datos-grid">
                        <div class="dato-item">
                            <label>Nombre</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($cliente['nombre']) ?>" required>
                        </div>
                        <div class="dato-item">
                            <label>Apellido</label>
                            <input type="text" name="apellido" value="<?= htmlspecialchars($cliente['apellido']) ?>" required>
                        </div>
                        <div class="dato-item">
                            <label>Email</label>
                            <input type="email" value="<?= htmlspecialchars($cliente['email']) ?>" disabled style="opacity:0.6;cursor:not-allowed;">
                        </div>
                        <div class="dato-item">
                            <label>Teléfono</label>
                            <input type="tel" name="telefono" value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn-guardar">Guardar cambios</button>
                </form>
            </div>
        </div>

        <!-- TAB DIRECCIONES -->
        <div class="tab-content" id="tab-direcciones">
            <?php
            $stmt = $conexion->prepare("
            SELECT d.*, l.nombre as localidad_nombre
            FROM direcciones d
            LEFT JOIN localidades l ON d.id_localidad = l.id
            WHERE d.id_cliente = ?
            ORDER BY d.principal DESC, d.fecha_creacion DESC
        ");
            $stmt->bind_param("i", $id_cliente);
            $stmt->execute();
            $direcciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            ?>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <span style="font-family:'Montserrat',sans-serif;font-size:0.65rem;color:#999;text-transform:uppercase;letter-spacing:2px;">
                    <?= count($direcciones) ?> dirección<?= count($direcciones) != 1 ? 'es' : '' ?> guardada<?= count($direcciones) != 1 ? 's' : '' ?>
                </span>
                <button onclick="toggleFormDireccion()" class="btn-guardar" style="margin:0;">+ Nueva dirección</button>
            </div>

            <!-- Formulario nueva dirección -->
            <div id="form-direccion" style="display:none;margin-bottom:20px;">
                <div class="datos-card">
                    <div class="datos-titulo">📍 Nueva dirección</div>
                    <form method="POST">
                        <input type="hidden" name="nueva_direccion" value="1">
                        <div class="datos-grid">
                            <div class="dato-item">
                                <label>Calle *</label>
                                <input type="text" name="calle" required placeholder="Av. San Martín">
                            </div>
                            <div class="dato-item">
                                <label>Altura</label>
                                <input type="number" name="altura" placeholder="1234">
                            </div>
                            <div class="dato-item">
                                <label>Provincia *</label>
                                <select name="id_provincia" id="prov-nueva" required onchange="cargarLocMiCuenta()">
                                    <option value="">— Seleccioná —</option>
                                    <?php foreach ($provincias as $prov): ?>
                                        <option value="<?= $prov['id'] ?>" data-nombre="<?= htmlspecialchars($prov['nombre']) ?>">
                                            <?= htmlspecialchars($prov['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="dato-item">
                                <label>Localidad *</label>
                                <select name="localidad" id="loc-nueva" required disabled>
                                    <option value="">— Primero elegí provincia —</option>
                                </select>
                            </div>
                            <div class="dato-item">
                                <label>Código Postal</label>
                                <input type="text" name="codigo_postal" id="cp-nueva" placeholder="Se completa automático">
                            </div>
                            <div class="dato-item">
                                <label>Descripción adicional</label>
                                <input type="text" name="descripcion_adicional" placeholder="Piso, depto...">
                            </div>
                        </div>
                        <label style="display:flex;align-items:center;gap:8px;margin-top:12px;font-family:'Montserrat',sans-serif;font-size:0.65rem;color:var(--marron);cursor:pointer;">
                            <input type="checkbox" name="principal" value="1"> Marcar como dirección principal
                        </label>
                        <div style="display:flex;gap:12px;margin-top:16px;">
                            <button type="submit" class="btn-guardar" style="margin:0;">Guardar</button>
                            <button type="button" onclick="toggleFormDireccion()" style="padding:12px 20px;background:none;border:1px solid rgba(200,152,154,0.4);border-radius:6px;cursor:pointer;font-family:'Montserrat',sans-serif;font-size:0.62rem;color:#999;">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de direcciones -->
            <?php if (empty($direcciones)): ?>
                <div class="sin-pedidos">
                    <p>📍</p>
                    <p>No tenés direcciones guardadas</p>
                </div>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <?php foreach ($direcciones as $dir): ?>
                        <div class="datos-card" style="padding:20px 24px;">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                                <div>
                                    <?php if ($dir['principal']): ?>
                                        <span style="background:#FEF3C7;color:#92400E;font-family:'Montserrat',sans-serif;font-size:0.55rem;font-weight:700;padding:3px 10px;border-radius:20px;display:inline-block;margin-bottom:8px;">⭐ Principal</span>
                                    <?php endif; ?>
                                    <p style="font-family:'Montserrat',sans-serif;font-weight:600;color:var(--marron);font-size:0.9rem;margin-bottom:4px;">
                                        <?= htmlspecialchars($dir['calle']) ?> <?= $dir['altura'] ?: '' ?>
                                    </p>
                                    <p style="font-size:0.8rem;color:#999;">
                                        <?= htmlspecialchars($dir['localidad_nombre'] ?? '') ?>
                                        <?= $dir['codigo_postal'] ? '(CP: ' . htmlspecialchars($dir['codigo_postal']) . ')' : '' ?>
                                    </p>
                                    <?php if ($dir['descripcion_adicional']): ?>
                                        <p style="font-size:0.75rem;color:#bbb;margin-top:4px;"><?= htmlspecialchars($dir['descripcion_adicional']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <a href="?eliminar_direccion=<?= $dir['id'] ?>"
                                    onclick="return confirm('¿Eliminar esta dirección?')"
                                    style="color:#DC2626;font-size:0.7rem;font-family:'Montserrat',sans-serif;text-decoration:none;white-space:nowrap;">
                                    🗑 Eliminar
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        function mostrarTab(tab, btn) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('activo'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('activo'));
            document.getElementById('tab-' + tab).classList.add('activo');
            btn.classList.add('activo');
        }

        // Abrir tab desde URL
        const urlTab = new URLSearchParams(window.location.search).get('tab');
        if (urlTab) {
            const btn = document.querySelector(`.tab-btn[onclick*="${urlTab}"]`);
            if (btn) btn.click();
        }

        function toggleFormDireccion() {
            const form = document.getElementById('form-direccion');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        async function cargarLocMiCuenta() {
            const selectProv = document.getElementById('prov-nueva');
            const selectLoc = document.getElementById('loc-nueva');
            const nombreProv = selectProv.options[selectProv.selectedIndex]?.dataset.nombre?.trim() || '';
            if (!nombreProv) return;
            selectLoc.disabled = true;
            selectLoc.innerHTML = '<option value="">Cargando...</option>';
            try {
                const nombre = nombreProv === 'Ciudad de Buenos Aires' ? 'Ciudad Autónoma de Buenos Aires' : nombreProv;
                const res = await fetch(`https://apis.datos.gob.ar/georef/api/localidades?provincia=${encodeURIComponent(nombre)}&max=500&orden=nombre&campos=nombre`);
                const data = await res.json();
                if (data.localidades?.length > 0) {
                    const nombres = [...new Set(data.localidades.map(l => l.nombre))].sort();
                    selectLoc.innerHTML = '<option value="">— Seleccioná —</option>';
                    nombres.forEach(n => {
                        const opt = document.createElement('option');
                        opt.value = n;
                        opt.textContent = n;
                        selectLoc.appendChild(opt);
                    });
                    selectLoc.disabled = false;
                    selectLoc.addEventListener('change', async () => {
                        const cpInput = document.getElementById('cp-nueva');
                        try {
                            const r = await fetch(`https://api.zippopotam.us/ar/${encodeURIComponent(selectLoc.value)}`);
                            if (r.ok) {
                                const d = await r.json();
                                if (d['post code']) cpInput.value = d['post code'];
                            }
                        } catch (e) {}
                    });
                }
            } catch (e) {
                selectLoc.innerHTML = '<option value="">Error — escribí la localidad</option>';
                const parent = selectLoc.closest('.dato-item');
                parent.innerHTML = '<label>Localidad *</label><input type="text" name="localidad" required placeholder="Escribí tu localidad" style="width:100%;padding:10px 14px;border:1.5px solid rgba(200,152,154,0.3);border-radius:6px;font-family:\'Montserrat\',sans-serif;">';
            }
        }
    </script>
</body>

</html>