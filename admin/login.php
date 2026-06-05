<?php
session_start();
ini_set('display_errors', 0); // Lo prendemos un segundo para ver si hay algún otro fallo
error_reporting(E_ALL);

require_once __DIR__ . '/../config/Database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $usuario = $_POST['usuario'] ?? '';
  $clave = $_POST['password'] ?? ''; // Usamos la variable $clave para no confundir

  try {
    $db = Database::getConexion();
    // Pedimos nombre y apellido (Asegurate de haber corrido el comando de MariaDB antes)
    $stmt = $db->prepare("SELECT id, password, nombre, apellido FROM usuarios WHERE usuario = ? LIMIT 1");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($user = $resultado->fetch_assoc()) {
      // Verificamos la contraseña
      if (password_verify($clave, $user['password'])) {
        // if ($clave === 'admin123') {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'] ?? 'Admin';
        $_SESSION['usuario_apellido'] = $user['apellido'] ?? '';

        header('Location: index.php');
        exit;
      } else {
        $error = "Contraseña incorrecta.";
      }
    } else {
      $error = "Usuario no encontrado.";
    }
  } catch (Exception $e) {
    $error = "Error de conexión: " . $e->getMessage();
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Login — Marlene Store</title>
  <style>
    body {
      font-family: sans-serif;
      background: #F2EBE0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .card {
      background: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 350px;
      text-align: center;
    }

    input {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ddd;
      border-radius: 8px;
      box-sizing: border-box;
    }

    button {
      width: 100%;
      padding: 12px;
      background: #5C3D3E;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
    }

    .err {
      color: #d9534f;
      background: #f2dede;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 15px;
      font-size: 14px;
    }
  </style>
</head>

<body>
  <div class="card">
    <h1>Marlene Store</h1>
    <?php if ($error) echo "<div class='err'>$error</div>"; ?>
    <form method="POST">
      <input type="text" name="usuario" placeholder="Usuario" required>
      <input type="password" name="password" placeholder="Contraseña">
      <button type="submit">INGRESAR</button>
    </form>
  </div>
</body>

</html>