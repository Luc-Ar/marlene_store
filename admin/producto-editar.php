<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/Database.php';
$conexion = Database::getConexion();

$error = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: productos.php'); exit; }

$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) { header('Location: productos.php'); exit; }

$categorias = $conexion->query("SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre            = trim($_POST['nombre']);
    $descripcion_corta = trim($_POST['descripcion_corta'] ?? '');
    $descripcion_larga = trim($_POST['descripcion_larga'] ?? '');
    $precio            = (float)$_POST['precio'];
    $precio_oferta     = !empty($_POST['precio_oferta']) ? (float)$_POST['precio_oferta'] : null;
    $categoria         = !empty($_POST['categoria']) ? (int)$_POST['categoria'] : null;
    $subcategoria      = trim($_POST['subcategoria'] ?? '');
    $peso_gramos       = (int)($_POST['peso_gramos'] ?? 0);
    $stock             = (int)($_POST['stock'] ?? 0);
    $activo            = isset($_POST['activo']) ? 1 : 0;
    $destacado         = isset($_POST['destacado']) ? 1 : 0;
    $imagen_db         = $p['imagen_principal'];

    if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','avif'])) {
            $nuevo_nombre = 'prod_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], '../assets/imagenes/' . $nuevo_nombre)) {
                if (!empty($p['imagen_principal']) && file_exists('../' . $p['imagen_principal'])) {
                    unlink('../' . $p['imagen_principal']);
                }
                $imagen_db = 'assets/imagenes/' . $nuevo_nombre;
            } else {
                $error = 'Error al subir la imagen.';
            }
        } else {
            $error = 'Formato no permitido. Usá JPG, PNG, WEBP o AVIF.';
        }
    }

    if (empty($error)) {
        $stmt2 = $conexion->prepare("
            UPDATE productos SET nombre=?, descripcion_corta=?, descripcion_larga=?, precio=?, precio_oferta=?,
            categoria=?, subcategoria=?, peso_gramos=?, stock=?, imagen_principal=?, activo=?, destacado=?
            WHERE id=?
        ");
        $stmt2->bind_param('sssddisiisiii', $nombre, $descripcion_corta, $descripcion_larga, $precio, $precio_oferta,
            $categoria, $subcategoria, $peso_gramos, $stock, $imagen_db, $activo, $destacado, $id);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto — Marlene Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Montserrat', sans-serif; background: #F2EBE0; color: #3A2526; display: flex; min-height: 100vh; }

        .sidebar { width: 260px; background: #5C3D3E; min-height: 100vh; position: fixed; display: flex; flex-direction: column; }
        .sidebar-logo { padding: 28px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .logo-script { font-family: 'Great Vibes', cursive; font-size: 2.6rem; color: #FAF6F1; display: block; }
        .logo-store { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 0.65rem; letter-spacing: 0.8rem; color: #C9A96E; text-transform: uppercase; }
        .sidebar-nav { flex: 1; padding: 20px 0; }
        .nav-section { font-size: 0.55rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: rgba(255,255,255,0.3); padding: 16px 25px 6px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 25px; color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.82rem; transition: 0.2s; border-left: 4px solid transparent; }
        .nav-item:hover, .nav-item.activo { background: rgba(255,255,255,0.1); color: #FAF6F1; border-left-color: #C9A96E; }
        .sidebar-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .btn-logout-sidebar { display: block; text-align: center; padding: 10px; background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 6px; font-size: 0.7rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; transition: 0.2s; }
        .btn-logout-sidebar:hover { background: rgba(192,57,43,0.4); color: #fff; }

        .main { margin-left: 260px; flex: 1; padding: 40px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; }
        .page-header h2 { font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: #5C3D3E; }

        .form-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        .form-card { background: white; border-radius: 8px; padding: 28px; margin-bottom: 20px; border: 1px solid rgba(200,152,154,0.2); }
        .form-card h3 { font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; color: #5C3D3E; margin-bottom: 20px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 0.6rem; font-weight: 700; text-transform: uppercase; color: #999; margin-bottom: 6px; letter-spacing: 1px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 14px; border: 1.5px solid rgba(200,152,154,0.3); border-radius: 6px; font-family: 'Montserrat', sans-serif; font-size: 0.85rem; color: #3A2526; background: #FDFAF8; transition: border-color 0.2s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #C9A96E; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .img-preview { width: 100%; height: 180px; background: #F2EBE0; border: 2px dashed rgba(200,152,154,0.4); border-radius: 6px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 12px; }
        .img-preview img { width: 100%; height: 100%; object-fit: contain; }

        .btn-guardar { width: 100%; background: #5C3D3E; color: white; border: none; padding: 16px; border-radius: 6px; font-weight: 700; font-size: 0.7rem; letter-spacing: 2px; text-transform: uppercase; cursor: pointer; transition: background 0.3s; }
        .btn-guardar:hover { background: #C9A96E; }
        .btn-cancelar { display: block; text-align: center; margin-top: 10px; padding: 12px; background: #F2EBE0; color: #5C3D3E; text-decoration: none; border-radius: 6px; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; }

        .error-msg { background: #FEE2E2; color: #991B1B; padding: 14px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 0.85rem; }
        .check-label { display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #5C3D3E; cursor: pointer; margin-bottom: 10px; }
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
        <a href="productos.php" class="nav-item activo">🎒 Productos</a>
        <a href="categorias.php" class="nav-item">📁 Categorías</a>
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
    <div class="page-header">
        <div>
            <h2>Editar Producto</h2>
            <p style="font-size:0.8rem;color:#999;margin-top:4px;"><?= htmlspecialchars($p['nombre']) ?> — ID #<?= $id ?></p>
        </div>
        <a href="productos.php" style="color:#999;text-decoration:none;font-size:0.8rem;">← Volver</a>
    </div>

    <?php if ($error): ?>
        <div class="error-msg">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-grid">
            <div>
                <div class="form-card">
                    <h3>Información general</h3>
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" required value="<?= htmlspecialchars($p['nombre']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Subcategoría</label>
                        <input type="text" name="subcategoria" value="<?= htmlspecialchars($p['subcategoria'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Descripción corta</label>
                        <input type="text" name="descripcion_corta" value="<?= htmlspecialchars($p['descripcion_corta'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Descripción larga</label>
                        <textarea name="descripcion_larga" rows="4"><?= htmlspecialchars($p['descripcion_larga'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-card">
                    <h3>Precios, stock y peso</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Precio *</label>
                            <input type="number" name="precio" step="0.01" required value="<?= $p['precio'] ?>">
                        </div>
                        <div class="form-group">
                            <label>Precio oferta</label>
                            <input type="number" name="precio_oferta" step="0.01" value="<?= $p['precio_oferta'] ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" name="stock" value="<?= $p['stock'] ?>">
                        </div>
                        <div class="form-group">
                            <label>Peso (gramos)</label>
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
                            🖼️
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Cambiar imagen</label>
                        <input type="file" name="imagen" accept="image/*" onchange="previewImg(this)">
                    </div>
                </div>

                <div class="form-card">
                    <h3>Categoría</h3>
                    <div class="form-group">
                        <select name="categoria">
                            <option value="">— Sin categoría —</option>
                            <?php while ($cat = $categorias->fetch_assoc()): ?>
                                <option value="<?= $cat['id'] ?>" <?= $p['categoria'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-card">
                    <h3>Opciones</h3>
                    <label class="check-label">
                        <input type="checkbox" name="activo" <?= $p['activo'] ? 'checked' : '' ?>> Visible en tienda
                    </label>
                    <label class="check-label">
                        <input type="checkbox" name="destacado" <?= $p['destacado'] ? 'checked' : '' ?>> Producto destacado
                    </label>
                    <button type="submit" name="guardar" class="btn-guardar">GUARDAR CAMBIOS</button>
                    <a href="productos.php" class="btn-cancelar">CANCELAR</a>
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