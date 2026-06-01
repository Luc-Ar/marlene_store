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
    if (!$nombre || !$apellido || !$email || !$calle || !$cp || !$localidad) {
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

        // ─── Buscar o crear localidad ───
        $stmt = $conexion->prepare("SELECT id FROM localidades WHERE nombre = ? LIMIT 1");
        $stmt->bind_param("s", $localidad);
        $stmt->execute();
        $loc = $stmt->get_result()->fetch_assoc();

        if ($loc) {
            $id_localidad = $loc['id'];
        } else {
            $stmt = $conexion->prepare("INSERT INTO localidades (nombre) VALUES (?)");
            $stmt->bind_param("s", $localidad);
            $stmt->execute();
            $id_localidad = $conexion->insert_id;
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
        $stmt->bind_param("isddss i", $id_cliente, $numero_pedido, $total, $peso_total, $metodo_pago, $notas, $id_direccion);
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

        // ─── Limpiar carrito ───
        $_SESSION['carrito'] = [];
        $_SESSION['ultimo_pedido'] = $numero_pedido;

        // ─── Redirigir a confirmación ───
        header("Location: confirmacion.php?pedido=$numero_pedido");
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
            border: 1px solid rgba(200, 152, 154, 0.3);
            border-radius: 6px;
            padding: 14px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
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
                            <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Apellido *</label>
                            <input type="text" name="apellido" required value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="tel" name="telefono" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Dirección de envío -->
                <div class="checkout-seccion">
                    <h3>📦 Dirección de envío</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Calle *</label>
                            <input type="text" name="calle" required value="<?= htmlspecialchars($_POST['calle'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Altura / Número</label>
                            <input type="number" name="altura" value="<?= htmlspecialchars($_POST['altura'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Código Postal *</label>
                            <input type="text" name="codigo_postal" required id="cp-input" value="<?= htmlspecialchars($_POST['codigo_postal'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Localidad *</label>
                            <input type="text" name="localidad" required id="localidad-input" value="<?= htmlspecialchars($_POST['localidad'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Provincia</label>
                            <input type="text" name="provincia" id="provincia-input" value="<?= htmlspecialchars($_POST['provincia'] ?? '') ?>">
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
                CONFIRMAR PEDIDO →
            </button>

            <p style="font-size:0.65rem; color:#999; text-align:center; margin-top:12px;">
                Al confirmar aceptás nuestros términos y condiciones
            </p>
        </div>

    </div>

    <script>
        // Autocompletar localidad y provincia desde código postal
        document.getElementById('cp-input').addEventListener('blur', function() {
            const cp = this.value.trim();
            if (cp.length < 4) return;
            fetch(`https://api.zippopotam.us/ar/${cp}`)
                .then(r => r.json())
                .then(data => {
                    if (data.places && data.places[0]) {
                        document.getElementById('localidad-input').value = data.places[0]['place name'];
                        document.getElementById('provincia-input').value = data.places[0]['state'];
                    }
                })
                .catch(() => {});
        });

        // Resaltar método de pago seleccionado
        document.querySelectorAll('.metodo-pago-opt input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('.metodo-pago-opt').forEach(opt => opt.classList.remove('seleccionado'));
                radio.closest('.metodo-pago-opt').classList.add('seleccionado');
            });
        });
        document.querySelector('.metodo-pago-opt input[checked]')?.closest('.metodo-pago-opt')?.classList.add('seleccionado');
    </script>

</body>

</html>