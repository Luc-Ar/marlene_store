<?php
session_start();
require_once __DIR__ . '/../includes/error-handler.php';
require_once __DIR__ . '/../config/Database.php';

if (isset($_SESSION['usuario_id'])) {
  header('Location: /admin/index.php');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $usuario = trim($_POST['usuario'] ?? '');
  $clave   = $_POST['password'] ?? '';

  try {
    $db   = Database::getConexion();
    $stmt = $db->prepare("SELECT id, password, nombre, apellido FROM usuarios WHERE usuario = ? LIMIT 1");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($clave, $user['password'])) {
      $_SESSION['usuario_id']       = $user['id'];
      $_SESSION['usuario_nombre']   = $user['nombre'] ?? 'Admin';
      $_SESSION['usuario_apellido'] = $user['apellido'] ?? '';
      header('Location: /admin/index.php');
      exit;
    } else {
      $error = 'Usuario o contraseña incorrectos.';
    }
  } catch (Exception $e) {
    $error = 'Error de conexión.';
    error_log("Login admin error: " . $e->getMessage());
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Marlene Store</title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Montserrat', sans-serif;
      background: #5C3D3E;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .login-card {
      background: #FAF6F1;
      padding: 48px 40px;
      border-radius: 16px;
      width: 100%;
      max-width: 380px;
      box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3);
    }

    .login-logo {
      text-align: center;
      margin-bottom: 32px;
    }

    .login-logo .script {
      font-family: 'Great Vibes', cursive;
      font-size: 3rem;
      color: #5C3D3E;
      display: block;
      line-height: 1;
    }

    .login-logo .store {
      font-family: 'Montserrat', sans-serif;
      font-size: 0.65rem;
      font-weight: 900;
      letter-spacing: 5px;
      color: #C9A96E;
      text-transform: uppercase;
      display: block;
      margin-top: -4px;
    }

    .login-logo .panel {
      font-size: 0.6rem;
      color: #999;
      letter-spacing: 3px;
      text-transform: uppercase;
      margin-top: 8px;
    }

    .form-group {
      margin-bottom: 16px;
    }

    .form-group label {
      display: block;
      font-size: 0.58rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: #999;
      margin-bottom: 6px;
    }

    .form-group input {
      width: 100%;
      padding: 12px 14px;
      border: 1.5px solid rgba(200, 152, 154, 0.3);
      border-radius: 6px;
      font-family: 'Montserrat', sans-serif;
      font-size: 0.85rem;
      color: #3A2526;
      background: white;
      transition: border-color 0.2s;
    }

    .form-group input:focus {
      outline: none;
      border-color: #C9A96E;
    }

    .btn-ingresar {
      width: 100%;
      background: #5C3D3E;
      color: #FAF6F1;
      border: none;
      padding: 16px;
      border-radius: 6px;
      font-family: 'Montserrat', sans-serif;
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 3px;
      text-transform: uppercase;
      cursor: pointer;
      margin-top: 8px;
      transition: background 0.3s;
    }

    .btn-ingresar:hover {
      background: #C9A96E;
    }

    .error-msg {
      background: #FEE2E2;
      color: #991B1B;
      padding: 12px 16px;
      border-radius: 6px;
      font-size: 0.78rem;
      margin-bottom: 20px;
      text-align: center;
    }

    .volver {
      text-align: center;
      margin-top: 20px;
    }

    .volver a {
      font-size: 0.7rem;
      color: #999;
      text-decoration: none;
    }

    .volver a:hover {
      color: #5C3D3E;
    }
  </style>
</head>

<body>
  <div class="login-card">
    <div class="login-logo">
      <span class="script">Marlene</span>
      <span class="store">STORE</span>
      <p class="panel">Panel de administración</p>
    </div>

    <?php if ($error): ?>
      <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Usuario</label>
        <input type="text" name="usuario" required autocomplete="username"
          value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Contraseña</label>
        <input type="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn-ingresar">Ingresar</button>
    </form>

    <div class="volver">
      <a href="/index.php">← Volver a la tienda</a>
    </div>
  </div>
</body>

</html>