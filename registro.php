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
$conexion = Database::getConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrfValidar()) {
    $error = 'La sesión expiró, recargá la página e intentá de nuevo.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email             = trim($_POST['email'] ?? '');
    $confirmar_email   = trim($_POST['confirmar_email'] ?? '');
    $password          = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    if (!$email || !$confirmar_email || !$password || !$confirmar_password) {
        $error = 'Completá todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido.';
    } elseif ($email !== $confirmar_email) {
        $error = 'Los dos emails no coinciden.';
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
            $stmt2 = $conexion->prepare("INSERT INTO clientes (nombre, apellido, email, password, activo, fecha_registro) VALUES ('', '', ?, ?, 1, NOW())");
            $stmt2->bind_param("ss", $email, $passwordHash);

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
    padding: 12px 16px;
    border-radius: 4px;
    font-size: 0.8rem;
    margin-bottom: 20px;
    text-align: center;
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
                <label>Contraseña *</label>
                <input type="password" name="password" id="password" required minlength="8">
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
    // Los campos de confirmación se destraban recién cuando el
    // campo original tiene contenido — así el usuario primero
    // carga el dato "real" antes de poder confirmarlo.
    const email = document.getElementById('email');
    const confirmarEmail = document.getElementById('confirmar_email');
    const password = document.getElementById('password');
    const confirmarPassword = document.getElementById('confirmar_password');

    email.addEventListener('input', () => {
        confirmarEmail.disabled = email.value.trim() === '';
    });

    password.addEventListener('input', () => {
        confirmarPassword.disabled = password.value === '';
    });

    document.body.style.visibility = 'visible';
</script>
</body>

</html>