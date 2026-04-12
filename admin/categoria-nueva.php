<?php
session_start();
if (!isset($_SESSION['usuario_id'])) exit;
require_once '../config/Database.php';
$conexion = Database::getConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $slug   = trim($_POST['slug']);
    $desc   = trim($_POST['descripcion']);
    $orden  = (int)$_POST['orden_display'];

    // Lógica simple para la imagen (podes mejorarla con move_uploaded_file luego)
    $imagen = 'assets/imagenes/cat-default.jpg';

    $stmt = $conexion->prepare("INSERT INTO categorias (nombre, slug, descripcion, imagen, orden_display) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $nombre, $slug, $desc, $imagen, $orden);

    if ($stmt->execute()) {
        header("Location: categorias.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Nueva Categoría — Marlene Store</title>
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
            border: 1px solid rgba(200, 152, 154, 0.3);
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
            margin-top: 10px;
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
        <h2 style="font-family:'serif'; margin-bottom:20px;">Nueva Categoría</h2>
        <form method="POST">
            <div class="form-group">
                <label>Nombre de la Categoría</label>
                <input type="text" name="nombre" id="nombre" required placeholder="Ej: Mochilas Infantiles">
            </div>

            <div class="form-group">
                <label>Slug (URL)</label>
                <input type="text" name="slug" id="slug" required placeholder="mochilas-infantiles">
                <div class="slug-preview">Vista previa: marlene-store.com/categoria/<span id="slug-text">...</span></div>
            </div>

            <div class="form-group">
                <label>Descripción Breve</label>
                <textarea name="descripcion" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Orden de visualización</label>
                <input type="number" name="orden_display" value="0">
            </div>

            <button type="submit" class="btn-save">CREAR CATEGORÍA</button>
            <a href="categorias.php" style="display:block; text-align:center; margin-top:15px; color:#666; font-size:0.8rem; text-decoration:none;">Cancelar</a>
        </form>
    </div>

    <script>
        // Script para generar el slug automáticamente
        document.getElementById('nombre').addEventListener('input', function() {
            let nombre = this.value;
            let slug = nombre.toLowerCase()
                .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // Quita tildes
                .replace(/[^a-z0-9\s-]/g, "") // Quita caracteres raros
                .replace(/\s+/g, "-") // Espacios por guiones
                .replace(/-+/g, "-"); // Quita guiones dobles

            document.getElementById('slug').value = slug;
            document.getElementById('slug-text').innerText = slug;
        });
    </script>
</body>

</html>