<?php
session_start();
if (!isset($_SESSION['usuario_id'])) exit;
require_once '../config/Database.php';
$conexion = Database::getConexion();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conexion->prepare("SELECT * FROM categorias WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$cat = $stmt->get_result()->fetch_assoc();

if (!$cat) {
    header("Location: categorias.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $slug   = trim($_POST['slug']);
    $desc   = trim($_POST['descripcion']);
    $orden  = (int)$_POST['orden_display'];
    $activo = isset($_POST['activo']) ? 1 : 0;

    $stmt_upd = $conexion->prepare("UPDATE categorias SET nombre=?, slug=?, descripcion=?, orden_display=?, activo=? WHERE id=?");
    $stmt_upd->bind_param("sssiii", $nombre, $slug, $desc, $orden, $activo, $id);

    if ($stmt_upd->execute()) {
        header("Location: categorias.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Categoría — Marlene Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat';
            background: #F2EBE0;
            padding: 40px;
            color: #3A2526;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 4px;
            max-width: 600px;
            margin: auto;
            border: 1px solid #ddd;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-size: 0.65rem;
            font-weight: 700;
            color: #9E5F62;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            background: #FAF6F1;
            font-family: 'Montserrat';
        }

        .btn-save {
            background: #5C3D3E;
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            cursor: pointer;
            font-weight: 700;
        }

        .slug-preview {
            font-size: 0.7rem;
            color: #888;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2 style="margin-bottom:20px;">Editar: <?= htmlspecialchars($cat['nombre']) ?></h2>
        <form method="POST">
            <div class="form-group">
                <label>Nombre de la Categoría</label>
                <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($cat['nombre']) ?>" required>
            </div>

            <div class="form-group">
                <label>Slug (URL)</label>
                <input type="text" name="slug" id="slug" value="<?= htmlspecialchars($cat['slug']) ?>" required>
                <div class="slug-preview">marlene-store.com/categoria/<span id="slug-text"><?= $cat['slug'] ?></span></div>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3"><?= htmlspecialchars($cat['descripcion']) ?></textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label>Orden</label>
                    <input type="number" name="orden_display" value="<?= $cat['orden_display'] ?>">
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <div style="margin-top:10px;">
                        <input type="checkbox" name="activo" style="width:auto;" <?= $cat['activo'] ? 'checked' : '' ?>> Activa
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-save">ACTUALIZAR CATEGORÍA</button>
            <a href="categorias.php" style="display:block; text-align:center; margin-top:15px; color:#666; font-size:0.8rem; text-decoration:none;">Cancelar</a>
        </form>
    </div>

    <script>
        document.getElementById('nombre').addEventListener('input', function() {
            let nombre = this.value;
            let slug = nombre.toLowerCase()
                .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
                .replace(/[^a-z0-9\s-]/g, "")
                .replace(/\s+/g, "-")
                .replace(/-+/g, "-");

            document.getElementById('slug').value = slug;
            document.getElementById('slug-text').innerText = slug;
        });
    </script>
</body>

</html>