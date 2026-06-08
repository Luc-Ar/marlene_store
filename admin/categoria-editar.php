<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/Database.php';
$conexion = Database::getConexion();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: categorias.php');
    exit;
}

$stmt = $conexion->prepare("SELECT * FROM categorias WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$cat = $stmt->get_result()->fetch_assoc();
if (!$cat) {
    header('Location: categorias.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $slug   = trim($_POST['slug'] ?? '');
    $desc   = trim($_POST['descripcion'] ?? '');
    $icono  = trim($_POST['icono'] ?? '');
    $orden  = (int)($_POST['orden_display'] ?? 0);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (!$nombre || !$slug) {
        $error = 'El nombre y el slug son obligatorios.';
    } else {
        $stmt_upd = $conexion->prepare("UPDATE categorias SET nombre=?, slug=?, descripcion=?, icono=?, orden_display=?, activo=? WHERE id=?");
        $stmt_upd->bind_param("ssssiis", $nombre, $slug, $desc, $icono, $orden, $activo, $id);
        if ($stmt_upd->execute()) {
            header("Location: categorias.php?mensaje=actualizada");
            exit;
        } else {
            $error = 'Error al actualizar. El slug puede estar duplicado.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoría — Marlene Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: #F2EBE0;
            color: #3A2526;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: #5C3D3E;
            min-height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo {
            padding: 28px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-script {
            font-family: 'Great Vibes', cursive;
            font-size: 2.6rem;
            color: #FAF6F1;
            display: block;
        }

        .logo-store {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 0.65rem;
            letter-spacing: 0.8rem;
            color: #C9A96E;
            text-transform: uppercase;
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
        }

        .nav-section {
            font-size: 0.55rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.3);
            padding: 16px 25px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 25px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.82rem;
            transition: 0.2s;
            border-left: 4px solid transparent;
        }

        .nav-item:hover,
        .nav-item.activo {
            background: rgba(255, 255, 255, 0.1);
            color: #FAF6F1;
            border-left-color: #C9A96E;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-logout-sidebar {
            display: block;
            text-align: center;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: 0.2s;
        }

        .btn-logout-sidebar:hover {
            background: rgba(192, 57, 43, 0.4);
            color: #fff;
        }

        .main {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
        }

        .form-wrap {
            max-width: 640px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .page-header h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            color: #5C3D3E;
        }

        .form-card {
            background: white;
            border-radius: 8px;
            padding: 28px;
            margin-bottom: 20px;
            border: 1px solid rgba(200, 152, 154, 0.2);
        }

        .form-card h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.3rem;
            color: #5C3D3E;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 6px;
            letter-spacing: 1px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid rgba(200, 152, 154, 0.3);
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.85rem;
            color: #3A2526;
            background: #FDFAF8;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #C9A96E;
        }

        .slug-preview {
            font-size: 0.65rem;
            color: #999;
            margin-top: 5px;
        }

        .slug-preview span {
            color: #C9A96E;
            font-weight: 700;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .check-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            color: #5C3D3E;
            cursor: pointer;
        }

        .btn-guardar {
            width: 100%;
            background: #5C3D3E;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.7rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-guardar:hover {
            background: #C9A96E;
        }

        .btn-cancelar {
            display: block;
            text-align: center;
            margin-top: 10px;
            padding: 12px;
            background: #F2EBE0;
            color: #5C3D3E;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
        }

        .error-msg {
            background: #FEE2E2;
            color: #991B1B;
            padding: 14px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.85rem;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-script">Marlene</span>
            <span class="logo-store">Store</span>
        </div>
        <nav class="sidebar-nav">
            <p class="nav-section">Principal</p>
            <a href="index.php" class="nav-item">📊 Dashboard</a>
            <p class="nav-section">Catálogo</p>
            <a href="productos.php" class="nav-item">🎒 Productos</a>
            <a href="categorias.php" class="nav-item activo">📁 Categorías</a>
            <p class="nav-section">Ventas</p>
            <a href="pedidos.php" class="nav-item">📦 Pedidos</a>
            <a href="clientes.php" class="nav-item">👥 Clientes</a>
            <p class="nav-section">Tienda</p>
            <a href="../index.php" class="nav-item" target="_blank">🌐 Ver tienda</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout-sidebar">🚪 Cerrar sesión</a>
        </div>
    </div>

    <div class="main">
        <div class="form-wrap">
            <div class="page-header">
                <div>
                    <h2>Editar Categoría</h2>
                    <p style="font-size:0.8rem;color:#999;margin-top:4px;"><?= htmlspecialchars($cat['nombre']) ?></p>
                </div>
                <a href="categorias.php" style="color:#999;text-decoration:none;font-size:0.8rem;">← Volver</a>
            </div>

            <?php if ($error): ?>
                <div class="error-msg">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-card">
                    <h3>Información de la categoría</h3>
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" id="nombre" required
                            value="<?= htmlspecialchars($cat['nombre']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Slug (URL) *</label>
                        <input type="text" name="slug" id="slug" required
                            value="<?= htmlspecialchars($cat['slug']) ?>">
                        <div class="slug-preview">
                            marlene.store/catalogo?cat=<span id="slug-text"><?= htmlspecialchars($cat['slug']) ?></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" rows="3"><?= htmlspecialchars($cat['descripcion'] ?? '') ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Ícono (emoji)</label>
                            <input type="text" name="icono"
                                value="<?= htmlspecialchars($cat['icono'] ?? '') ?>"
                                placeholder="🎒" maxlength="10">
                        </div>
                        <div class="form-group">
                            <label>Orden de visualización</label>
                            <input type="number" name="orden_display"
                                value="<?= $cat['orden_display'] ?>" min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="check-label">
                            <input type="checkbox" name="activo" style="width:auto;"
                                <?= $cat['activo'] ? 'checked' : '' ?>>
                            Categoría activa (visible en tienda)
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-guardar">ACTUALIZAR CATEGORÍA</button>
                <a href="categorias.php" class="btn-cancelar">CANCELAR</a>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('slug').addEventListener('input', function() {
            document.getElementById('slug-text').textContent = this.value || '...';
        });
    </script>
</body>

</html>