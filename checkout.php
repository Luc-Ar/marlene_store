<?php
session_start();
require_once __DIR__ . '/config/Database.php';

// Si el carrito está vacío, redirigir al catálogo
if (empty($_SESSION['carrito'])) {
    header('Location: catalogo.php');
    exit;
}

$conexion = Database::getConexion();
$error = '';
$exito = false;
// Precargar datos del cliente si está logueado
$cliente_logueado = null;
if (isset($_SESSION['cliente_id'])) {
    $stmt = $conexion->prepare("SELECT * FROM clientes WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $_SESSION['cliente_id']);
    $stmt->execute();
    $cliente_logueado = $stmt->get_result()->fetch_assoc();
}
// Cargar provincias siempre
$stmt_provs = $conexion->prepare("SELECT id, nombre FROM provincias ORDER BY nombre ASC");
$stmt_provs->execute();
$provincias = $stmt_provs->get_result()->fetch_all(MYSQLI_ASSOC);
// ─── PROCESAR FORMULARIO ───
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del formulario
    $nombre     = trim($_POST['nombre'] ?? '');
    $apellido   = trim($_POST['apellido'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $telefono   = trim($_POST['telefono'] ?? '');
    $calle      = trim($_POST['calle'] ?? '');
    $altura     = (int)($_POST['altura'] ?? 0);
    $cp         = trim($_POST['codigo_postal'] ?? '');
    $localidad  = trim($_POST['localidad'] ?? '');
    $provincia  = trim($_POST['provincia'] ?? '');
    $notas      = trim($_POST['notas'] ?? '');
    $metodo_pago = trim($_POST['metodo_pago'] ?? 'transferencia');

    // Validaciones básicas
    if (!$nombre || !$apellido || !$email || !$calle || !$localidad) {
        $error = 'Por favor completá todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido.';
    } else {
        // ─── Buscar o crear cliente ───
        $stmt = $conexion->prepare("SELECT id FROM clientes WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $cliente = $stmt->get_result()->fetch_assoc();

        if ($cliente) {
            $id_cliente = $cliente['id'];
        } else {
            $stmt = $conexion->prepare("INSERT INTO clientes (nombre, apellido, email, telefono) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nombre, $apellido, $email, $telefono);
            $stmt->execute();
            $id_cliente = $conexion->insert_id;
        }
        // Cargar provincias
        $stmt_provs = $conexion->prepare("SELECT id, nombre FROM provincias ORDER BY nombre ASC");
        $stmt_provs->execute();
        $provincias = $stmt_provs->get_result()->fetch_all(MYSQLI_ASSOC);

        // Buscar o crear localidad con provincia
        $id_provincia = (int)($_POST['id_provincia'] ?? 0);
        $localidad    = trim($_POST['localidad'] ?? '');

        if ($localidad && $id_provincia) {
            $stmt = $conexion->prepare("SELECT id FROM localidades WHERE nombre = ? AND id_provincia = ? LIMIT 1");
            $stmt->bind_param("si", $localidad, $id_provincia);
            $stmt->execute();
            $loc = $stmt->get_result()->fetch_assoc();
            if ($loc) {
                $id_localidad = $loc['id'];
            } else {
                $stmt = $conexion->prepare("INSERT INTO localidades (nombre, id_provincia) VALUES (?, ?)");
                $stmt->bind_param("si", $localidad, $id_provincia);
                $stmt->execute();
                $id_localidad = $conexion->insert_id;
            }
        }
        // ─── Guardar dirección ───
        $stmt = $conexion->prepare("INSERT INTO direcciones (id_cliente, calle, altura, codigo_postal, id_localidad) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isisi", $id_cliente, $calle, $altura, $cp, $id_localidad);
        $stmt->execute();
        $id_direccion = $conexion->insert_id;

        // ─── Calcular total ───
        $total = 0;
        $peso_total = 0;
        foreach ($_SESSION['carrito'] as $item) {
            $total += $item['precio'] * $item['cantidad'];
            $peso_total += ($item['peso'] ?? 0) * $item['cantidad'];
        }

        // ─── Generar número de pedido ───
        $numero_pedido = 'PED-' . strtoupper(uniqid());

        // ─── Insertar pedido ───
        $stmt = $conexion->prepare("
            INSERT INTO pedidos (id_cliente, numero_pedido, estado, total, peso_total, metodo_pago, notas, id_direccion)
            VALUES (?, ?, 'pendiente', ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isddssi", $id_cliente, $numero_pedido, $total, $peso_total, $metodo_pago, $notas, $id_direccion);
        $stmt->execute();
        $id_pedido = $conexion->insert_id;

        // ─── Insertar items del pedido ───
        foreach ($_SESSION['carrito'] as $item) {
            $subtotal = $item['precio'] * $item['cantidad'];
            $stmt = $conexion->prepare("
                INSERT INTO pedido_items (id_pedido, id_producto, nombre_producto, precio_unitario, cantidad, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iisdid", $id_pedido, $item['id'], $item['nombre'], $item['precio'], $item['cantidad'], $subtotal);
            $stmt->execute();

            // ─── Descontar stock ───
            $stmt2 = $conexion->prepare("UPDATE productos SET stock = stock - ? WHERE id = ? AND stock > 0");
            $stmt2->bind_param("ii", $item['cantidad'], $item['id']);
            $stmt2->execute();
        }
        // ─── Limpiar carrito de sesión ───
        $_SESSION['carrito'] = [];
        $_SESSION['ultimo_pedido'] = $numero_pedido;

        // ─── Guardar pedido para MP ───
        $_SESSION['pedido_pendiente_pago'] = [
            'numero_pedido' => $numero_pedido,
            'id_pedido'     => $id_pedido,
            'total'         => $total,
            'items'         => array_values($_SESSION['carrito'] ?? []),
        ];

        // ─── Redirigir según método de pago ───
        if ($metodo_pago === 'mercadopago') {
            header("Location: pago.php");
        } else {
            header("Location: confirmacion.php?pedido=$numero_pedido");
        }
        exit;


        // Guardar pedido en sesión para MP
        $_SESSION['pedido_pendiente_pago'] = [
            'numero_pedido' => $numero_pedido,
            'id_pedido'     => $id_pedido,
            'total'         => $total,
        ];

        // Si eligió WhatsApp, ir directo a confirmación
        if (isset($_GET['metodo']) && $_GET['metodo'] === 'whatsapp') {
            header("Location: confirmacion.php?pedido=$numero_pedido");
            exit;
        }

        // Si eligió MP, ir a pago
        header("Location: pago.php");
        exit;
    }
}

// ─── Calcular totales para mostrar ───
$items = array_values($_SESSION['carrito']);
$total = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $items));
$cantidad = array_sum(array_column($items, 'cantidad'));
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — Marlene STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Montserrat:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .checkout-wrap {
            max-width: 1100px;
            margin: 120px auto 60px;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 48px;
            align-items: start;
        }

        .checkout-titulo {
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            color: var(--marron);
            margin-bottom: 32px;
        }

        .checkout-seccion {
            background: white;
            border: 1px solid rgba(200, 152, 154, 0.2);
            border-radius: 8px;
            padding: 28px;
            margin-bottom: 24px;
        }

        .checkout-seccion h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--dorado);
            margin-bottom: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--marron);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 14px;
            border: 1px solid rgba(200, 152, 154, 0.3);
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.85rem;
            color: var(--marron);
            background: white;
            transition: border-color 0.2s;
            width: 100%;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--dorado);
        }

        .metodo-pago-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .metodo-pago-opt {
            border: 1.5px solid rgba(200, 152, 154, 0.3);
            border-radius: 8px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            user-select: none;
        }

        .metodo-pago-opt:hover {
            border-color: var(--marron);
            background: rgba(92, 61, 62, 0.04);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(92, 61, 62, 0.08);
        }

        .metodo-pago-opt.seleccionado {
            border-color: var(--marron);
            background: rgba(92, 61, 62, 0.06);
            box-shadow: 0 0 0 3px rgba(92, 61, 62, 0.1);
        }

        .metodo-pago-opt.seleccionado span {
            color: var(--marron);
            font-weight: 700;
        }

        .metodo-pago-opt input[type="radio"] {
            display: none;
        }

        .metodo-pago-opt.seleccionado {
            border-color: var(--marron);
            background: rgba(92, 61, 62, 0.05);
        }

        .metodo-pago-opt span {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--marron);
        }

        /* Resumen */
        .resumen-box {
            background: white;
            border: 1px solid rgba(200, 152, 154, 0.2);
            border-radius: 8px;
            padding: 28px;
            position: sticky;
            top: 100px;
        }

        .resumen-box h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--dorado);
            margin-bottom: 20px;
        }

        .resumen-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(200, 152, 154, 0.1);
        }

        .resumen-item img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border-radius: 4px;
            background: var(--crema2);
            padding: 4px;
        }

        .resumen-item-info {
            flex: 1;
        }

        .resumen-item-info strong {
            display: block;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--marron);
        }

        .resumen-item-info span {
            font-size: 0.75rem;
            color: #999;
        }

        .resumen-item-precio {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--marron);
        }

        .resumen-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid rgba(200, 152, 154, 0.2);
        }

        .resumen-total span {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--marron);
        }

        .resumen-total strong {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--marron);
        }

        .btn-confirmar {
            width: 100%;
            background: var(--marron);
            color: var(--crema);
            border: none;
            padding: 18px;
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .btn-confirmar:hover {
            background: var(--dorado);
        }

        .error-msg {
            background: #FEE2E2;
            color: #991B1B;
            padding: 14px 18px;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .checkout-wrap {
                grid-template-columns: 1fr;
                margin-top: 80px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .metodo-pago-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>


    <nav>
        <a href="index.php" class="logo-wrap">
            <span class="logo-script">Marlene</span>
            <span class="logo-store">STORE</span>
        </a>
        <ul class="nav-links">
            <li><a href="catalogo.php">← Volver al catálogo</a></li>
        </ul>
    </nav>

    <div class="checkout-wrap">

        <!-- FORMULARIO -->
        <div>
            <h1 class="checkout-titulo">Finalizar compra</h1>

            <?php if ($error): ?>
                <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">

                <!-- Datos personales -->
                <div class="checkout-seccion">
                    <h3>📋 Datos personales</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nombre *</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? $cliente_logueado['nombre'] ?? '') ?>"
                                </div>
                            <div class="form-group">
                                <label>Apellido *</label>
                                <input type="text" name="apellido" value="<?= htmlspecialchars($_POST['apellido'] ?? $cliente_logueado['apellido'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $cliente_logueado['email'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="tel" name="telefono" value="<?= htmlspecialchars($_POST['telefono'] ?? $cliente_logueado['telefono'] ?? '') ?>"
                                    </div>
                            </div>
                        </div>

                        <!-- Dirección de envío -->
                        <div class="checkout-seccion">
                            <h3>📦 Dirección de envío</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Calle *</label>
                                    <input type="text" name="calle" value="<?= htmlspecialchars($_POST['calle'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Altura / Número</label>
                                    <input type="number" name="altura" value="<?= htmlspecialchars($_POST['altura'] ?? '') ?>">
                                </div>

                                <div class="form-group full">
                                    <label>Provincia *</label>
                                    <select name="id_provincia" id="select-provincia-checkout"
                                        onchange="cargarLocalidadesCheckout()">
                                        <option value="">— Seleccioná tu provincia —</option>
                                        <?php foreach ($provincias as $prov): ?>
                                            <option value="<?= $prov['id'] ?>"
                                                data-nombre="<?= htmlspecialchars($prov['nombre']) ?>"
                                                <?= ($_POST['id_provincia'] ?? '') == $prov['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($prov['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <!-- <div class="form-group full">
                            <label>Localidad *</label>
                            <select name="localidad" id="select-localidad-checkout" required disabled
                                onchange="buscarCPporLocalidad()">
                                <option value="">— Primero elegí la provincia —</option>
                            </select>
                            <span id="loading-localidades" style="font-size:0.7rem;color:#C9A96E;margin-top:4px;display:none;">
                                ⏳ Cargando localidades...
                            </span>
                        </div> -->
                                <div class="form-group full">
                                    <label>Localidad *</label>
                                    <input type="text" name="localidad" id="select-localidad-checkout"
                                        placeholder="Escribí tu localidad">
                                </div>
                                <div class="form-group">
                                    <label>Código Postal *</label>
                                    <input type="text" name="codigo_postal" id="input-cp-checkout" required
                                        placeholder="Se completa automático"
                                        value="<?= htmlspecialchars($_POST['codigo_postal'] ?? '') ?>">
                                    <span id="cp-status" style="font-size:0.65rem;color:#999;margin-top:4px;display:block;"></span>
                                </div>
                                <div class="form-group full">
                                    <label>Descripción adicional</label>
                                    <textarea name="descripcion_adicional" rows="2" placeholder="Piso, depto, referencias..."><?= htmlspecialchars($_POST['descripcion_adicional'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Método de pago -->
                        <div class="checkout-seccion">
                            <h3>💳 Método de pago</h3>
                            <div class="metodo-pago-grid">
                                <label class="metodo-pago-opt" id="opt-transferencia">
                                    <input type="radio" name="metodo_pago" value="transferencia" checked>
                                    <span>🏦 Transferencia bancaria</span>
                                </label>
                                <label class="metodo-pago-opt" id="opt-efectivo">
                                    <input type="radio" name="metodo_pago" value="efectivo">
                                    <span>💵 Efectivo al retirar</span>
                                </label>
                                <label class="metodo-pago-opt" id="opt-mercadopago">
                                    <input type="radio" name="metodo_pago" value="mercadopago">
                                    <span>💙 MercadoPago</span>
                                </label>
                                <label class="metodo-pago-opt" id="opt-whatsapp">
                                    <input type="radio" name="metodo_pago" value="whatsapp">
                                    <span>💬 Coordinar por WhatsApp</span>
                                </label>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="checkout-seccion">
                            <h3>📝 Notas del pedido</h3>
                            <div class="form-group">
                                <textarea name="notas" rows="3" placeholder="Instrucciones especiales, horarios de entrega..."><?= htmlspecialchars($_POST['notas'] ?? '') ?></textarea>
                            </div>
                        </div>

            </form>
        </div>

        <!-- RESUMEN DEL PEDIDO -->
        <div class="resumen-box">
            <h3>🛒 Tu pedido (<?= $cantidad ?> productos)</h3>

            <?php foreach ($items as $item): ?>
                <div class="resumen-item">
                    <img src="<?= htmlspecialchars($item['imagen'] ?? '') ?>" alt="<?= htmlspecialchars($item['nombre']) ?>">
                    <div class="resumen-item-info">
                        <strong><?= htmlspecialchars($item['nombre']) ?></strong>
                        <span>Cant: <?= $item['cantidad'] ?></span>
                    </div>
                    <span class="resumen-item-precio">$<?= number_format($item['precio'] * $item['cantidad'], 0, ',', '.') ?></span>
                </div>
            <?php endforeach; ?>

            <div class="resumen-total">
                <span>Total</span>
                <strong>$<?= number_format($total, 0, ',', '.') ?></strong>
            </div>

            <button class="btn-confirmar" onclick="document.querySelector('form').submit()">
                💳 Pagar con MercadoPago
            </button>
            <button class="btn-confirmar"
                style="background:#25D366; margin-top:10px;"
                onclick="document.querySelector('form').setAttribute('action','checkout.php?metodo=whatsapp'); document.querySelector('form').submit()">
                💬 Coordinar por WhatsApp
            </button>

            <p style="font-size:0.65rem; color:#999; text-align:center; margin-top:12px;">
                Al confirmar aceptás nuestros términos y condiciones
            </p>
        </div>

    </div>

    <script>
        async function cargarLocalidadesCheckout(id_provincia) {
            const select = document.getElementById('select-localidad-checkout');
            const loading = document.getElementById('loading-localidades');
            const nombreProv = document.querySelector('#select-provincia-checkout option:checked').textContent.trim();

            select.disabled = true;
            select.innerHTML = '<option value="">Cargando...</option>';
            loading.style.display = 'block';

            try {
                const nombre = nombreProv === 'Ciudad de Buenos Aires' ?
                    'Ciudad Autónoma de Buenos Aires' : nombreProv;
                const url = `https://apis.datos.gob.ar/georef/api/localidades?provincia=${encodeURIComponent(nombre)}&max=500&orden=nombre&campos=nombre`;
                const res = await fetch(url);
                const data = await res.json();
                loading.style.display = 'none';

                if (data.localidades && data.localidades.length > 0) {
                    const nombres = [...new Set(data.localidades.map(l => l.nombre))].sort();
                    select.innerHTML = '<option value="">— Seleccioná tu localidad —</option>';
                    nombres.forEach(nombre => {
                        const opt = document.createElement('option');
                        opt.value = nombre;
                        opt.textContent = nombre;
                        select.appendChild(opt);
                    });
                    select.disabled = false;
                }
            } catch (e) {
                loading.style.display = 'none';
                select.innerHTML = '<option value="">Error — escribí la localidad</option>';
                // Fallback a input de texto
                select.parentNode.innerHTML = `
            <label>Localidad *</label>
            <input type="text" name="localidad" required placeholder="Escribí tu localidad">
        `;
            }
        }
        async function buscarCPporLocalidad() {
            const localidad = document.getElementById('select-localidad-checkout').value;
            const selectProv = document.getElementById('select-provincia-checkout');
            const nombreProv = selectProv.options[selectProv.selectedIndex]?.dataset.nombre || '';
            const inputCP = document.getElementById('input-cp-checkout');
            const cpStatus = document.getElementById('cp-status');

            if (!localidad || !nombreProv) return;

            cpStatus.textContent = '⏳ Buscando código postal...';
            cpStatus.style.color = '#C9A96E';

            try {
                // Usar georef para buscar el municipio y obtener datos
                const nombre = nombreProv === 'Ciudad de Buenos Aires' ?
                    'Ciudad Autónoma de Buenos Aires' : nombreProv;

                const url = `https://apis.datos.gob.ar/georef/api/municipios?nombre=${encodeURIComponent(localidad)}&provincia=${encodeURIComponent(nombre)}&max=1&campos=id,nombre`;
                const res = await fetch(url);
                const data = await res.json();

                if (data.municipios?.length > 0) {
                    const id = data.municipios[0].id;
                    // Buscar CP con el ID del municipio en zippopotam
                    const res2 = await fetch(`https://api.zippopotam.us/ar/${id}`);
                    if (res2.ok) {
                        const data2 = await res2.json();
                        if (data2['post code']) {
                            inputCP.value = data2['post code'];
                            cpStatus.textContent = '✓ Código postal encontrado';
                            cpStatus.style.color = '#16A34A';
                            return;
                        }
                    }
                }
                // Si no encontró — el CP no es bloqueante
                cpStatus.textContent = 'No encontramos el CP — escribilo vos si lo sabés';
                cpStatus.style.color = '#999';
                inputCP.removeAttribute('required');
                inputCP.placeholder = 'Opcional — podés dejarlo vacío';
            } catch (e) {
                cpStatus.textContent = 'Escribí el código postal si lo sabés';
                cpStatus.style.color = '#999';
                inputCP.removeAttribute('required');
            }
        }
        // ─── Métodos de pago ───
        document.querySelectorAll('.metodo-pago-opt').forEach(opt => {
            opt.addEventListener('click', () => {
                // Desmarcar todos
                document.querySelectorAll('.metodo-pago-opt').forEach(o => o.classList.remove('seleccionado'));
                // Marcar el clickeado
                opt.classList.add('seleccionado');
                // Activar el radio interno
                const radio = opt.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
            });
        });

        // Marcar el primero por defecto
        const primero = document.querySelector('.metodo-pago-opt');
        if (primero) {
            primero.classList.add('seleccionado');
            const radio = primero.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        }

        function confirmarPedido() {
            const metodo = document.querySelector('input[name="metodo_pago"]:checked')?.value;
            const btn = document.getElementById('btn-confirmar');

            if (metodo === 'mercadopago') {
                btn.textContent = '💙 Ir a MercadoPago...';
                btn.style.background = '#009EE3';
            } else {
                btn.textContent = 'Procesando...';
            }
            btn.disabled = true;
            document.querySelector('form').submit();
        }
        c
    </script>

</body>

</html>