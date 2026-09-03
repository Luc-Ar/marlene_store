<?php
session_start();
require_once __DIR__ . '/includes/error-handler.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/includes/csrf.php';

if (isset($_SESSION['cliente_id'])) {
    header('Location: mi-cuenta.php');
    exit;
}
$error = '';
$limpiarEmail = false;
$limpiarDni = false;
$limpiarTelefono = false;
$conexion = Database::getConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrfValidar()) {
    $error = 'La sesión expiró, recargá la página e intentá de nuevo.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email             = trim($_POST['email'] ?? '');
    $confirmar_email   = trim($_POST['confirmar_email'] ?? '');
    $password          = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    $dni = trim($_POST['dni'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if (!$email || !$confirmar_email || !$password || !$confirmar_password || !$dni || !$telefono) {
        $error = 'Completá todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido.';
        $limpiarEmail = true;
    } elseif ($email !== $confirmar_email) {
        $error = 'Los dos emails no coinciden.';
        $limpiarEmail = true;
    } elseif (!preg_match('/^\d{7,8}$/', $dni)) {
        $error = 'El DNI tiene que tener 7 u 8 números, sin puntos.';
        $limpiarDni = true;
    } elseif (!preg_match('/^\d{8,15}$/', preg_replace('/[\s\-]/', '', $telefono))) {
        $error = 'El teléfono no parece válido.';
        $limpiarTelefono = true;
    } elseif ($password !== $confirmar_password) {
        $error = 'Las dos contraseñas no coinciden.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña tiene que tener al menos 8 caracteres.';
    } else {
        // Chequear que el email no esté ya registrado
        $stmt = $conexion->prepare("SELECT id FROM clientes WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $existe = $stmt->get_result()->fetch_assoc();

        if ($existe) {
            $error = 'Ya existe una cuenta registrada con ese email.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conexion->prepare("INSERT INTO clientes (nombre, apellido, email, password, dni, telefono, activo, fecha_registro) VALUES ('', '', ?, ?, ?, ?, 1, NOW())");
            $stmt2->bind_param("ssss", $email, $passwordHash, $dni, $telefono);

            if ($stmt2->execute()) {
                $nuevoId = $conexion->insert_id;
                $_SESSION['cliente_id']     = $nuevoId;
                $_SESSION['cliente_nombre'] = '';
                $_SESSION['cliente_email']  = $email;
                header('Location: mi-cuenta.php');
                exit;
            } else {
                $error = 'No pudimos crear tu cuenta. Probá de nuevo en unos minutos.';
                error_log("Error al registrar cliente: " . $conexion->error);
            }
        }
    }
}

// Variables para header.php
$titulo = 'Registrate — Marlene STORE';
$sin_nav = true; // Página centrada sin nav
$estilos_extra = '
.auth-wrap {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--crema2);
    padding: 40px 24px;
}
.auth-card {
    background: white;
    border-radius: 12px;
    padding: 48px 40px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 20px 60px rgba(92,61,62,0.1);
}
.auth-logo { text-align: center; margin-bottom: 32px; }
.auth-logo a { text-decoration: none; }
.auth-logo .logo-script {
    font-family: "Great Vibes", cursive;
    font-size: 2.5rem;
    color: var(--marron);
}
.auth-logo .logo-store {
    font-family: "Montserrat", sans-serif;
    font-size: 0.8rem;
    font-weight: 900;
    letter-spacing: 4px;
    color: var(--dorado);
    display: block;
    margin-top: -8px;
}
.auth-titulo {
    font-family: "Cormorant Garamond", serif;
    font-size: 1.6rem;
    color: var(--marron);
    text-align: center;
    margin-bottom: 28px;
}
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.form-group label {
    font-family: "Montserrat", sans-serif;
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--marron);
}
.form-group input {
    padding: 12px 14px;
    border: 1px solid rgba(200,152,154,0.3);
    border-radius: 4px;
    font-family: "Montserrat", sans-serif;
    font-size: 0.85rem;
    color: var(--marron);
    transition: border-color 0.2s, background 0.2s;
    box-sizing: border-box;
    width: 100%;
}
.form-group input:focus { outline: none; border-color: var(--dorado); }
.form-group input:disabled {
    background: #F5F0EA;
    color: #aaa;
    cursor: not-allowed;
}
.form-hint {
    font-size: 0.7rem;
    color: #999;
    margin-top: 2px;
}
.btn-login {
    width: 100%;
    background: var(--marron);
    color: var(--crema);
    border: none;
    padding: 16px;
    border-radius: 4px;
    font-family: "Montserrat", sans-serif;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    cursor: pointer;
    margin-top: 8px;
    transition: background 0.3s;
}
.btn-login:hover { background: var(--dorado); }
.auth-footer { text-align: center; margin-top: 20px; font-size: 0.8rem; color: #999; }
.auth-footer a { color: var(--marron); font-weight: 600; text-decoration: none; }
.error-msg {
    background: #FEE2E2;
    color: #991B1B;
    padding: 16px 20px;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 24px;
    text-align: center;
    border: 2px solid #FCA5A5;
    animation: shake 0.4s ease;
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-6px); }
    40%, 80% { transform: translateX(6px); }
}
';

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <a href="/index.php">
                <span class="logo-script">Marlene</span>
                <span class="logo-store">STORE</span>
            </a>
        </div>
        <h1 class="auth-titulo">Registrate</h1>
        <?php if ($error): ?>
            <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" id="form-registro" autocomplete="off">
            <?= csrfField() ?>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" id="email" required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Confirmar email *</label>
                <input type="email" name="confirmar_email" id="confirmar_email" required disabled
                    onpaste="return false" ondrop="return false">
                <span class="form-hint">Volvé a escribirlo — no se puede pegar.</span>
            </div>
            <div class="form-group">
                <label>DNI *</label>
                <input type="text" name="dni" id="dni" required inputmode="numeric" maxlength="8"
                    placeholder="Sin puntos, ej: 30123456" disabled
                    value="<?= $limpiarDni ? '' : htmlspecialchars($_POST['dni'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Teléfono *</label>
                <input type="tel" name="telefono" id="telefono" required disabled
                    placeholder="Ej: 3704123456"
                    value="<?= $limpiarTelefono ? '' : htmlspecialchars($_POST['telefono'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Contraseña *</label>
                <input type="password" name="password" id="password" required minlength="8" disabled>
                <span class="form-hint">Mínimo 8 caracteres.</span>
            </div>
            <div class="form-group">
                <label>Confirmar contraseña *</label>
                <input type="password" name="confirmar_password" id="confirmar_password" required disabled
                    onpaste="return false" ondrop="return false">
                <span class="form-hint">Volvé a escribirla — no se puede pegar.</span>
            </div>
            <button type="submit" class="btn-login">Registrarme</button>
        </form>
        <div class="auth-footer">
            ¿Ya tenés cuenta? <a href="/login-cliente.php">Iniciar sesión</a>
        </div>
    </div>
</div>

<script>
    const campos = {
        email: document.getElementById('email'),
        confirmarEmail: document.getElementById('confirmar_email'),
        dni: document.getElementById('dni'),
        telefono: document.getElementById('telefono'),
        password: document.getElementById('password'),
        confirmarPassword: document.getElementById('confirmar_password'),
    };

    function validarFormatoEmail(v) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim());
    }

    function validarDni(v) {
        return /^\d{7,8}$/.test(v.trim());
    }

    function validarTelefono(v) {
        return /^\d{8,15}$/.test(v.trim().replace(/[\s\-]/g, ''));
    }

    function marcarEstado(input, esValido) {
        input.style.borderColor = input.value.trim() === '' ? '' : (esValido ? '#16A34A' : '#DC2626');
    }

    // Email → habilita Confirmar email
    campos.email.addEventListener('input', () => {
        const valido = validarFormatoEmail(campos.email.value);
        marcarEstado(campos.email, valido);
        campos.confirmarEmail.disabled = !valido;
        if (!valido) {
            campos.confirmarEmail.value = '';
            bloquearDesde('dni');
        }
    });

    // Confirmar email → habilita DNI (necesita formato válido Y que coincidan)
    campos.confirmarEmail.addEventListener('input', () => {
        const coincide = campos.confirmarEmail.value === campos.email.value;
        const valido = validarFormatoEmail(campos.confirmarEmail.value) && coincide;
        marcarEstado(campos.confirmarEmail, valido);
        campos.dni.disabled = !valido;
        if (!valido) bloquearDesde('telefono');
    });

    // DNI → habilita Teléfono
    campos.dni.addEventListener('input', () => {
        const valido = validarDni(campos.dni.value);
        marcarEstado(campos.dni, valido);
        campos.telefono.disabled = !valido;
        if (!valido) bloquearDesde('password');
    });

    // Teléfono → habilita Contraseña
    campos.telefono.addEventListener('input', () => {
        const valido = validarTelefono(campos.telefono.value);
        marcarEstado(campos.telefono, valido);
        campos.password.disabled = !valido;
        if (!valido) {
            campos.confirmarPassword.disabled = true;
        }
    });

    // Contraseña → habilita Confirmar contraseña
    campos.password.addEventListener('input', () => {
        campos.confirmarPassword.disabled = campos.password.value === '';
    });

    // Apaga y vacía todos los campos posteriores a uno que dejó de ser válido
    function bloquearDesde(idCampo) {
        const orden = ['dni', 'telefono', 'password', 'confirmarPassword'];
        const desde = orden.indexOf(idCampo);
        if (desde === -1) return;
        for (let i = desde; i < orden.length; i++) {
            const c = campos[orden[i]];
            c.disabled = true;
            if (orden[i] !== 'password') c.value = '';
        }
    }

    document.body.style.visibility = 'visible';
</script>
</body>

</html>