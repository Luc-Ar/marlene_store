<?php
session_start();
require_once __DIR__ . '/includes/error-handler.php';
require_once __DIR__ . '/config/Database.php';

if (isset($_SESSION['cliente_id'])) {
    header('Location: mi-cuenta.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Completá todos los campos.';
    } else {
        $conexion = Database::getConexion();
        $stmt = $conexion->prepare("SELECT id, nombre, email, password FROM clientes WHERE email = ? AND activo = 1 LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $cliente = $stmt->get_result()->fetch_assoc();

        if ($cliente && password_verify($password, $cliente['password'])) {
            $_SESSION['cliente_id']     = $cliente['id'];
            $_SESSION['cliente_nombre'] = $cliente['nombre'];
            $_SESSION['cliente_email']  = $cliente['email'];
            header('Location: mi-cuenta.php');
            exit;
        } else {
            $error = 'Email o contraseña incorrectos.';
        }
    }
}

// Variables para header.php
$titulo = 'Iniciar sesión — Marlene STORE';
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
    transition: border-color 0.2s;
    box-sizing: border-box;
    width: 100%;
}
.form-group input:focus { outline: none; border-color: var(--dorado); }
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
        <h1 class="auth-titulo">Iniciar sesión</h1>
        <?php if ($error): ?>
            <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Contraseña *</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Ingresar</button>
        </form>
        <div class="auth-footer">
            ¿No tenés cuenta? <a href="/registro.php">Registrate</a>
        </div>
    </div>
</div>

<script>
    document.body.style.visibility = 'visible';
</script>
</body>

</html>