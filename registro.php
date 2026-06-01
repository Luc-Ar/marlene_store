<?php
session_start();
require_once __DIR__ . '/config/Database.php';

// Si ya está logueado, redirigir
if (isset($_SESSION['cliente_id'])) {
    header('Location: mi-cuenta.php');
    exit;
}

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (!$nombre || !$apellido || !$email || !$password) {
        $error = 'Completá todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $conexion = Database::getConexion();

        // Verificar si el email ya existe
        $stmt = $conexion->prepare("SELECT id FROM clientes WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Ya existe una cuenta con ese email.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare("INSERT INTO clientes (nombre, apellido, email, password, telefono) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nombre, $apellido, $email, $hash, $telefono);
            $stmt->execute();
            $id = $conexion->insert_id;

            // Login automático
            $_SESSION['cliente_id'] = $id;
            $_SESSION['cliente_nombre'] = $nombre;
            $_SESSION['cliente_email'] = $email;

            header('Location: mi-cuenta.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta — Marlene STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,400;0,600&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
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
            max-width: 480px;
            box-shadow: 0 20px 60px rgba(92, 61, 62, 0.1);
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .auth-logo .logo-script {
            font-family: 'Great Vibes', cursive;
            font-size: 2.5rem;
            color: var(--marron);
        }

        .auth-logo .logo-store {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.8rem;
            font-weight: 900;
            letter-spacing: 4px;
            color: var(--dorado);
            display: block;
            margin-top: -8px;
        }

        .auth-titulo {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.6rem;
            color: var(--marron);
            text-align: center;
            margin-bottom: 28px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
        }

        .form-group label {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--marron);
        }

        .form-group input {
            padding: 12px 14px;
            border: 1px solid rgba(200, 152, 154, 0.3);
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.85rem;
            color: var(--marron);
            transition: border-color 0.2s;
            box-sizing: border-box;
            width: 100%;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--dorado);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .btn-registrar {
            width: 100%;
            background: var(--marron);
            color: var(--crema);
            border: none;
            padding: 16px;
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.3s;
        }

        .btn-registrar:hover {
            background: var(--dorado);
        }

        .auth-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.8rem;
            color: #999;
        }

        .auth-footer a {
            color: var(--marron);
            font-weight: 600;
            text-decoration: none;
        }

        .error-msg {
            background: #FEE2E2;
            color: #991B1B;
            padding: 12px 16px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-logo">
                <a href="index.php">
                    <span class="logo-script">Marlene</span>
                    <span class="logo-store">STORE</span>
                </a>
            </div>

            <h1 class="auth-titulo">Crear cuenta</h1>

            <?php if ($error): ?>
                <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Apellido *</label>
                        <input type="text" name="apellido" required value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Contraseña *</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Repetir contraseña *</label>
                    <input type="password" name="password2" required>
                </div>
                <button type="submit" class="btn-registrar">Crear cuenta</button>
            </form>

            <div class="auth-footer">
                ¿Ya tenés cuenta? <a href="login-cliente.php">Iniciá sesión</a>
            </div>
        </div>
    </div>
</body>

</html>