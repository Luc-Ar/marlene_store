<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/Database.php'; // Ojo con la mayúscula en Database.php
$conexion = Database::getConexion(); // CORRECCIÓN 1: Usar el método correcto de tu clase

$error = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: productos.php');
    exit;
}

// 1. Obtener datos del producto actual
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) {
    header('Location: productos.php');
    exit;
}

// 2. Obtener categorías activas para el select
$categorias = $conexion->query("SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre");

// 3. Procesar Formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre            = trim($_POST['nombre']);
    $descripcion_corta = trim($_POST['descripcion_corta'] ?? '');
    $descripcion_larga = trim($_POST['descripcion_larga']);
    $precio            = (float)$_POST['precio'];
    $precio_oferta     = !empty($_POST['precio_oferta']) ? (float)$_POST['precio_oferta'] : null;
    $categoria         = !empty($_POST['categoria']) ? (int)$_POST['categoria'] : null;
    $subcategoria      = trim($_POST['subcategoria']);
    $peso_gramos       = (int)($_POST['peso_gramos'] ?? 0);
    $stock             = (int)($_POST['stock'] ?? 0);
    $activo            = isset($_POST['activo']) ? 1 : 0;
    $destacado         = isset($_POST['destacado']) ? 1 : 0;

    // CORRECCIÓN 2: Ruta alineada con producto-nuevo.php
    $imagen_db = $p['imagen_principal'];

    if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        // Agregamos AVIF que es lo que tenías en la carpeta
        $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

        if (in_array($ext, $permitidos)) {
            $nuevo_nombre = 'prod_' . time() . '.' . $ext;
            // IMPORTANTE: Usar la misma carpeta que en el alta
            $ruta_destino = "../assets/imagenes/" . $nuevo_nombre;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                // Borrar imagen anterior si existe y no es la default
                if (!empty($p['imagen_principal']) && file_exists("../" . $p['imagen_principal'])) {
                    unlink("../" . $p['imagen_principal']);
                }
                $imagen_db = "assets/imagenes/" . $nuevo_nombre;
            } else {
                $error = 'Error al subir la imagen. Revisá permisos de carpeta en Kali.';
            }
        } else {
            $error = 'Formato no permitido. Usá JPG, PNG, WEBP o AVIF.';
        }
    }

    if (empty($error)) {
        // CORRECCIÓN 3: El SQL debe tener 13 parámetros para que coincida con el bind_param
        $sql = "UPDATE productos SET nombre=?, descripcion_corta=?, descripcion_larga=?, precio=?, precio_oferta=?, categoria=?, subcategoria=?, peso_gramos=?, stock=?, imagen_principal=?, activo=?, destacado=? WHERE id=?";
        $stmt2 = $conexion->prepare($sql);
        $stmt2->bind_param(
            'sssddisiisiii',
            $nombre,
            $descripcion_corta,
            $descripcion_larga,
            $precio,
            $precio_oferta,
            $categoria,
            $subcategoria,
            $peso_gramos,
            $stock,
            $imagen_db,
            $activo,
            $destacado,
            $id
        );

        if ($stmt2->execute()) {
            header('Location: productos.php?mensaje=actualizado');
            exit;
        } else {
            $error = 'Error en la DB: ' . $conexion->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Producto — Marlene Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        /* Mismos estilos que tenías, agregando ajuste para la preview */
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
            width: 240px;
            background: #5C3D3E;
            min-height: 100vh;
            position: fixed;
        }

        .sidebar-logo {
            padding: 28px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-script {
            font-family: 'Great Vibes', cursive;
            font-size: 2.8rem;
            color: #FAF6F1;
            display: block;
            line-height: 1.1;
        }

        .logo-store {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 0.65rem;
            letter-spacing: 1.8rem;
            color: #C9A96E;
            text-transform: uppercase;
            display: block;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: rgba(255, 252, 246, 0.7);
            text-decoration: none;
            font-size: 0.75rem;
        }

        .nav-item.activo {
            background: rgba(255, 255, 255, 0.08);
            color: #FAF6F1;
            border-left: 3px solid #C9A96E;
        }

        .main {
            margin-left: 240px;
            flex: 1;
            padding: 40px;
        }

        .page-header h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            color: #5C3D3E;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        .form-card {
            background: #fff;
            border-radius: 4px;
            padding: 32px;
            margin-bottom: 24px;
            border: 1px solid rgba(200, 152, 154, 0.2);
        }

        .form-card h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.3rem;
            color: #5C3D3E;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #9E5F62;
            margin-bottom: 8px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            background: #FAF6F1;
        }

        .img-preview {
            width: 100%;
            height: 180px;
            background: #F2EBE0;
            border: 2px dashed #C8989A;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .img-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .btn-guardar {
            width: 100%;
            background: #5C3D3E;
            color: #FAF6F1;
            border: none;
            padding: 16px;
            font-weight: 700;
            cursor: pointer;
        }

        .error-msg {
            background: #fef0f0;
            color: #c0392b;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #f5c6c6;
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
            <a href="productos.php" class="nav-item activo">🎒 Productos</a>
            <a href="categorias.php" class="nav-item">📁 Categorías</a>
        </nav>
    </div>

    <div class="main">
        <div class="page-header" style="margin-bottom:30px;">
            <h2>Editar Producto</h2>
            <p>ID: <?= $id ?> — <?= htmlspecialchars($p['nombre']) ?></p>
        </div>

        <?php if ($error): ?>
            <div class="error-msg">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div>
                    <div class="form-card">
                        <h3>Información básica</h3>
                        <div class="form-group">
                            <label>Nombre del producto *</label>
                            <input type="text" name="nombre" required value="<?= htmlspecialchars($p['nombre']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Subcategoría</label>
                            <input type="text" name="subcategoria" value="<?= htmlspecialchars($p['subcategoria'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Descripción completa</label>
                            <textarea name="descripcion_larga"><?= htmlspecialchars($p['descripcion_larga'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="form-card">
                        <h3>Precios y stock</h3>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                            <div class="form-group">
                                <label>Precio *</label>
                                <input type="number" name="precio" step="0.01" required value="<?= $p['precio'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Precio oferta</label>
                                <input type="number" name="precio_oferta" step="0.01" value="<?= $p['precio_oferta'] ?>">
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                            <div class="form-group">
                                <label>Stock</label>
                                <input type="number" name="stock" value="<?= $p['stock'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Peso (g)</label>
                                <input type="number" name="peso_gramos" value="<?= $p['peso_gramos'] ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="form-card">
                        <h3>Imagen</h3>
                        <div class="img-preview" id="preview">
                            <?php if (!empty($p['imagen_principal'])): ?>
                                <img src="../<?= htmlspecialchars($p['imagen_principal']) ?>" alt="Vista previa">
                            <?php else: ?>
                                <span>🖼️</span>
                            <?php endif; ?>
                        </div>
                        <div class="form-group" style="margin-top:15px;">
                            <label>Cambiar imagen</label>
                            <input type="file" name="imagen" accept="image/*" onchange="previewImg(this)">
                        </div>
                    </div>
                    <div class="form-card">
                        <h3>Categoría</h3>
                        <select name="categoria">
                            <option value="">— Sin categoría —</option>
                            <?php while ($cat = $categorias->fetch_assoc()): ?>
                                <option value="<?= $cat['id'] ?>" <?= $p['categoria'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-card">
                        <div class="form-group">
                            <label><input type="checkbox" name="activo" <?= $p['activo'] ? 'checked' : '' ?>> Activo</label>
                        </div>
                        <div class="form-group">
                            <label><input type="checkbox" name="destacado" <?= $p['destacado'] ? 'checked' : '' ?>> Destacado</label>
                        </div>

                        <button type="submit" name="guardar" class="btn-guardar">GUARDAR CAMBIOS</button>
                        <div style="display: flex; gap: 10px; margin-top: 10px;">
                            <a href="productos.php" style="flex: 1; background: #eee; color: #333; text-decoration: none; padding: 15px; text-align: center; font-weight: 700; font-size: 0.7rem; border-radius: 2px;">CANCELAR</a>

                        </div>
                    </div>
                </div>
        </form>
    </div>

    <script>
        function previewImg(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('preview').innerHTML = '<img src="' + e.target.result + '">';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>