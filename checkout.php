<?php
session_start();
require_once __DIR__ . '/includes/error-handler.php';
require_once __DIR__ . '/config/Database.php';

if (empty($_SESSION['carrito'])) {
    header('Location: catalogo.php');
    exit;
}

$conexion = Database::getConexion();
$error = '';

// Cargar provincias
$stmt_provs = $conexion->prepare("SELECT id, nombre FROM provincias ORDER BY nombre ASC");
$stmt_provs->execute();
$provincias = $stmt_provs->get_result()->fetch_all(MYSQLI_ASSOC);

// Precargar datos del cliente logueado
$cliente_logueado = null;
if (isset($_SESSION['cliente_id'])) {
    $stmt = $conexion->prepare("SELECT * FROM clientes WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $_SESSION['cliente_id']);
    $stmt->execute();
    $cliente_logueado = $stmt->get_result()->fetch_assoc();
}

// ─── PROCESAR FORMULARIO ───
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $apellido    = trim($_POST['apellido'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $telefono    = trim($_POST['telefono'] ?? '');
    $calle       = trim($_POST['calle'] ?? '');
    $altura      = (int)($_POST['altura'] ?? 0);
    $cp          = trim($_POST['codigo_postal'] ?? '');
    $localidad   = trim($_POST['localidad'] ?? '');
    $id_provincia = (int)($_POST['id_provincia'] ?? 0);
    $notas       = trim($_POST['notas'] ?? '');
    $metodo_pago = trim($_POST['metodo_pago'] ?? 'transferencia');

    if (!$nombre || !$apellido || !$email || !$calle || !$localidad) {
        $error = 'Por favor completá todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido.';
    } else {
        // Buscar o crear cliente
        if (isset($_SESSION['cliente_id'])) {
            $id_cliente = $_SESSION['cliente_id'];
        } else {
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
        }

        // Buscar o crear localidad
        $id_localidad = null;
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

        // Guardar dirección
        $stmt = $conexion->prepare("INSERT INTO direcciones (id_cliente, calle, altura, codigo_postal, id_localidad) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isisi", $id_cliente, $calle, $altura, $cp, $id_localidad);
        $stmt->execute();
        $id_direccion = $conexion->insert_id;

        // Calcular totales
        $total = 0;
        $peso_total = 0;
        foreach ($_SESSION['carrito'] as $item) {
            $total += $item['precio'] * $item['cantidad'];
            $peso_total += ($item['peso'] ?? 0) * $item['cantidad'];
        }

        // Generar número de pedido
        $numero_pedido = 'PED-' . strtoupper(uniqid());

        // Insertar pedido
        $stmt = $conexion->prepare("
            INSERT INTO pedidos (id_cliente, numero_pedido, estado, total, peso_total, metodo_pago, notas, id_direccion)
            VALUES (?, ?, 'pendiente', ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isddssi", $id_cliente, $numero_pedido, $total, $peso_total, $metodo_pago, $notas, $id_direccion);
        $stmt->execute();
        $id_pedido = $conexion->insert_id;

        // Insertar items y descontar stock
        foreach ($_SESSION['carrito'] as $item) {
            $subtotal = $item['precio'] * $item['cantidad'];
            $stmt = $conexion->prepare("
                INSERT INTO pedido_items (id_pedido, id_producto, nombre_producto, precio_unitario, cantidad, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iisdid", $id_pedido, $item['id'], $item['nombre'], $item['precio'], $item['cantidad'], $subtotal);
            $stmt->execute();

            $stmt2 = $conexion->prepare("UPDATE productos SET stock = stock - ? WHERE id = ? AND stock > 0");
            $stmt2->bind_param("ii", $item['cantidad'], $item['id']);
            $stmt2->execute();
        }

        // Limpiar carrito
        $_SESSION['carrito'] = [];
        $_SESSION['ultimo_pedido'] = $numero_pedido;

        // Guardar para MP si corresponde
        $_SESSION['pedido_pendiente_pago'] = [
            'numero_pedido' => $numero_pedido,
            'id_pedido'     => $id_pedido,
            'total'         => $total,
        ];

        // Redirigir según método de pago
        if ($metodo_pago === 'mercadopago') {
            header("Location: pago.php");
        } else {
            header("Location: confirmacion.php?pedido=$numero_pedido");
        }
        exit;
    }
}

// Totales para mostrar
$items  = array_values($_SESSION['carrito']);
$total  = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $items));
$cantidad = array_sum(array_column($items, 'cantidad'));

// Variables para header.php
$titulo = 'Checkout — Marlene STORE';
$estilos_extra = '
* { box-sizing: border-box; }
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
    font-family: "Great Vibes", cursive;
    font-size: 3rem;
    color: var(--marron);
    margin-bottom: 32px;
}
.checkout-seccion {
    background: white;
    border: 1px solid rgba(200,152,154,0.2);
    border-radius: 8px;
    padding: 28px;
    margin-bottom: 24px;
}
.checkout-seccion h3 {
    font-family: "Montserrat", sans-serif;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--dorado);
    margin-bottom: 20px;
}
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group.full { grid-column: 1/-1; }
.form-group label {
    font-family: "Montserrat", sans-serif;
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
    border: 1.5px solid rgba(200,152,154,0.3);
    border-radius: 6px;
    font-family: "Montserrat", sans-serif;
    font-size: 0.85rem;
    color: var(--marron);
    background: white;
    transition: border-color 0.2s;
    width: 100%;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { outline: none; border-color: var(--dorado); }
.metodo-pago-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.metodo-pago-opt {
    border: 1.5px solid rgba(200,152,154,0.3);
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
    background: rgba(92,61,62,0.04);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(92,61,62,0.08);
}
.metodo-pago-opt.seleccionado {
    border-color: var(--marron);
    background: rgba(92,61,62,0.06);
    box-shadow: 0 0 0 3px rgba(92,61,62,0.1);
}
.metodo-pago-opt input[type="radio"] { display: none; }
.metodo-pago-opt span {
    font-family: "Montserrat", sans-serif;
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--marron);
}
.metodo-pago-opt.seleccionado span { font-weight: 700; }
.resumen-box {
    background: white;
    border: 1px solid rgba(200,152,154,0.2);
    border-radius: 8px;
    padding: 28px;
    position: sticky;
    top: 100px;
}
.resumen-box h3 {
    font-family: "Montserrat", sans-serif;
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
    border-bottom: 1px solid rgba(200,152,154,0.1);
}
.resumen-item img {
    width: 48px;
    height: 48px;
    object-fit: contain;
    border-radius: 4px;
    background: var(--crema2);
    padding: 4px;
}
.resumen-item-info { flex: 1; }
.resumen-item-info strong {
    display: block;
    font-family: "Montserrat", sans-serif;
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--marron);
}
.resumen-item-info span { font-size: 0.75rem; color: #999; }
.resumen-item-precio {
    font-family: "Montserrat", sans-serif;
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
    border-top: 1px solid rgba(200,152,154,0.2);
}
.resumen-total span {
    font-family: "Montserrat", sans-serif;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--marron);
}
.resumen-total strong {
    font-family: "Cormorant Garamond", serif;
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
    border-radius: 6px;
    font-family: "Montserrat", sans-serif;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    cursor: pointer;
    margin-top: 20px;
    transition: background 0.3s, transform 0.2s;
}
.btn-confirmar:hover { background: var(--dorado); transform: translateY(-1px); }
.btn-confirmar:disabled { background: #ccc; cursor: not-allowed; transform: none; }
.error-msg {
    background: #FEE2E2;
    color: #991B1B;
    padding: 14px 18px;
    border-radius: 6px;
    font-size: 0.85rem;
    margin-bottom: 20px;
}
@media (max-width: 768px) {
    .checkout-wrap { grid-template-columns: 1fr; margin-top: 80px; }
    .form-grid { grid-template-columns: 1fr; }
    .metodo-pago-grid { grid-template-columns: 1fr; }
}
';

// Sin panel de carrito en la página de checkout
$incluir_carrito = false;

require_once __DIR__ . '/includes/header.php';
?>

<div class="checkout-wrap">
    <div>
        <h1 class="checkout-titulo">Finalizar compra</h1>

        <?php if ($error): ?>
            <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="form-checkout">

            <!-- Datos personales -->
            <div class="checkout-seccion">
                <h3>📋 Datos personales</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" required
                            value="<?= htmlspecialchars($_POST['nombre'] ?? $cliente_logueado['nombre'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Apellido *</label>
                        <input type="text" name="apellido" required
                            value="<?= htmlspecialchars($_POST['apellido'] ?? $cliente_logueado['apellido'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required
                            value="<?= htmlspecialchars($_POST['email'] ?? $cliente_logueado['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="tel" name="telefono"
                            value="<?= htmlspecialchars($_POST['telefono'] ?? $cliente_logueado['telefono'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Dirección de envío -->
            <div class="checkout-seccion">
                <h3>📦 Dirección de envío</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Calle *</label>
                        <input type="text" name="calle" required
                            value="<?= htmlspecialchars($_POST['calle'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Altura / Número</label>
                        <input type="number" name="altura"
                            value="<?= htmlspecialchars($_POST['altura'] ?? '') ?>">
                    </div>
                    <div class="form-group full">
                        <label>Provincia *</label>
                        <select name="id_provincia" id="select-provincia-checkout" required
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
                    <div class="form-group full">
                        <label>Localidad *</label>
                        <select name="localidad" id="select-localidad-checkout" required disabled
                            onchange="buscarCPporLocalidad()">
                            <option value="">— Primero elegí la provincia —</option>
                        </select>
                        <span id="loading-localidades"
                            style="font-size:0.7rem;color:#C9A96E;margin-top:4px;display:none;">
                            ⏳ Cargando localidades...
                        </span>
                    </div>
                    <div class="form-group">
                        <label>Código Postal</label>
                        <input type="text" name="codigo_postal" id="input-cp-checkout"
                            placeholder="Se completa automático"
                            value="<?= htmlspecialchars($_POST['codigo_postal'] ?? '') ?>">
                        <span id="cp-status"
                            style="font-size:0.65rem;color:#999;margin-top:4px;display:block;"></span>
                    </div>
                    <div class="form-group full">
                        <label>Descripción adicional</label>
                        <textarea name="descripcion_adicional" rows="2"
                            placeholder="Piso, depto, referencias..."><?= htmlspecialchars($_POST['descripcion_adicional'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Método de pago -->
            <div class="checkout-seccion">
                <h3>💳 Método de pago</h3>
                <div class="metodo-pago-grid">
                    <label class="metodo-pago-opt seleccionado">
                        <input type="radio" name="metodo_pago" value="transferencia" checked>
                        <span>🏦 Transferencia bancaria</span>
                    </label>
                    <label class="metodo-pago-opt">
                        <input type="radio" name="metodo_pago" value="efectivo">
                        <span>💵 Efectivo al retirar</span>
                    </label>
                    <label class="metodo-pago-opt">
                        <input type="radio" name="metodo_pago" value="mercadopago">
                        <span>💙 MercadoPago</span>
                    </label>
                    <label class="metodo-pago-opt">
                        <input type="radio" name="metodo_pago" value="whatsapp">
                        <span>💬 Coordinar por WhatsApp</span>
                    </label>
                </div>
            </div>

            <!-- Notas -->
            <div class="checkout-seccion">
                <h3>📝 Notas del pedido</h3>
                <div class="form-group">
                    <textarea name="notas" rows="3"
                        placeholder="Instrucciones especiales, horarios de entrega..."><?= htmlspecialchars($_POST['notas'] ?? '') ?></textarea>
                </div>
            </div>

        </form>
    </div>

    <!-- RESUMEN -->
    <div class="resumen-box">
        <h3>🛒 Tu pedido (<?= $cantidad ?> producto<?= $cantidad != 1 ? 's' : '' ?>)</h3>

        <?php foreach ($items as $item): ?>
            <div class="resumen-item">
                <img src="<?= htmlspecialchars($item['imagen'] ?? '') ?>"
                    alt="<?= htmlspecialchars($item['nombre']) ?>">
                <div class="resumen-item-info">
                    <strong><?= htmlspecialchars($item['nombre']) ?></strong>
                    <span>Cant: <?= $item['cantidad'] ?></span>
                </div>
                <span class="resumen-item-precio">
                    $<?= number_format($item['precio'] * $item['cantidad'], 0, ',', '.') ?>
                </span>
            </div>
        <?php endforeach; ?>

        <div class="resumen-total">
            <span>Total</span>
            <strong>$<?= number_format($total, 0, ',', '.') ?></strong>
        </div>

        <button class="btn-confirmar" id="btn-confirmar" onclick="confirmarPedido()">
            CONFIRMAR PEDIDO →
        </button>

        <p style="font-size:0.65rem;color:#999;text-align:center;margin-top:12px;">
            Al confirmar aceptás nuestros términos y condiciones
        </p>
    </div>
</div>

<script>
    function confirmarPedido() {
        const metodo = document.querySelector('input[name="metodo_pago"]:checked')?.value;
        const btn = document.getElementById('btn-confirmar');
        if (metodo === 'mercadopago') {
            btn.textContent = '💙 Yendo a MercadoPago...';
            btn.style.background = '#009EE3';
        } else {
            btn.textContent = '⏳ Procesando...';
        }
        btn.disabled = true;
        document.getElementById('form-checkout').submit();
    }

    document.querySelectorAll('.metodo-pago-opt').forEach(opt => {
        opt.addEventListener('click', () => {
            document.querySelectorAll('.metodo-pago-opt').forEach(o => o.classList.remove('seleccionado'));
            opt.classList.add('seleccionado');
            const radio = opt.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    async function cargarLocalidadesCheckout() {
        const selectProv = document.getElementById('select-provincia-checkout');
        const selectLoc = document.getElementById('select-localidad-checkout');
        const loading = document.getElementById('loading-localidades');
        const nombreProv = selectProv.options[selectProv.selectedIndex]?.dataset.nombre?.trim() || '';
        if (!nombreProv) return;
        selectLoc.disabled = true;
        selectLoc.innerHTML = '<option value="">Cargando...</option>';
        loading.style.display = 'block';
        document.getElementById('input-cp-checkout').value = '';
        document.getElementById('cp-status').textContent = '';
        try {
            const nombre = nombreProv === 'Ciudad de Buenos Aires' ? 'Ciudad Autónoma de Buenos Aires' : nombreProv;
            const res = await fetch(`https://apis.datos.gob.ar/georef/api/localidades?provincia=${encodeURIComponent(nombre)}&max=500&orden=nombre&campos=nombre`);
            const data = await res.json();
            loading.style.display = 'none';
            if (data.localidades?.length > 0) {
                const nombres = [...new Set(data.localidades.map(l => l.nombre))].sort();
                selectLoc.innerHTML = '<option value="">— Seleccioná tu localidad —</option>';
                nombres.forEach(n => {
                    const opt = document.createElement('option');
                    opt.value = n;
                    opt.textContent = n;
                    selectLoc.appendChild(opt);
                });
                selectLoc.disabled = false;
            } else throw new Error('Sin localidades');
        } catch (e) {
            loading.style.display = 'none';
            document.getElementById('select-localidad-checkout').closest('.form-group').innerHTML = `
                    <label>Localidad *</label>
                    <input type="text" name="localidad" required placeholder="Escribí tu localidad"
                        style="padding:12px 14px;border:1.5px solid rgba(200,152,154,0.3);border-radius:6px;font-family:'Montserrat',sans-serif;font-size:0.85rem;width:100%;">
                `;
        }
    }

    async function buscarCPporLocalidad() {
        const localidad = document.getElementById('select-localidad-checkout')?.value;
        const inputCP = document.getElementById('input-cp-checkout');
        const cpStatus = document.getElementById('cp-status');
        if (!localidad) return;
        cpStatus.textContent = '⏳ Buscando código postal...';
        cpStatus.style.color = '#C9A96E';
        try {
            const res = await fetch(`https://api.zippopotam.us/ar/${encodeURIComponent(localidad)}`);
            if (res.ok) {
                const data = await res.json();
                if (data['post code']) {
                    inputCP.value = data['post code'];
                    cpStatus.textContent = '✓ Código postal encontrado';
                    cpStatus.style.color = '#16A34A';
                    return;
                }
            }
            cpStatus.textContent = 'Escribí el código postal si lo sabés';
            cpStatus.style.color = '#999';
        } catch (e) {
            cpStatus.textContent = 'Escribí el código postal manualmente';
            cpStatus.style.color = '#999';
        }
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>