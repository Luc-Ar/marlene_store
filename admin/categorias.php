<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/Database.php';
$conexion = Database::getConexion();

// Traemos las categorías ordenadas por el campo orden_display
$query = "SELECT * FROM categorias ORDER BY orden_display ASC, nombre ASC";
$resultado = $conexion->query($query);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Categorías — Marlene Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat';
            background: #F2EBE0;
            display: flex;
            color: #3A2526;
        }

        .sidebar {
            width: 240px;
            background: #5C3D3E;
            min-height: 100vh;
            position: fixed;
            padding: 20px;
        }

        .main {
            margin-left: 240px;
            flex: 1;
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .btn-nuevo {
            background: #C9A96E;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.65rem;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .tabla-wrap {
            background: white;
            border-radius: 4px;
            border: 1px solid rgba(200, 152, 154, 0.2);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #5C3D3E;
            color: #C9A96E;
        }

        th {
            padding: 15px;
            text-align: left;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 10px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 0.85rem;
        }



        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: bold;
        }

        .activo {
            background: #eaf3de;
            color: #3B6D11;
        }

        .inactivo {
            background: #fef0f0;
            color: #c0392b;
        }

        .btn-edit {
            color: #5C3D3E;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.75rem;
            border: 1px solid #ddd;
            padding: 5px 10px;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2 style="color:white; font-family:serif; margin-bottom:30px;">Marlene Store</h2>
        <a href="productos.php" style="color:rgba(255,255,255,0.7); text-decoration:none; display:block; margin-bottom:15px; font-size:0.8rem;">🎒 Productos</a>
        <a href="categorias.php" style="color:#C9A96E; text-decoration:none; display:block; margin-bottom:15px; font-size:0.8rem; font-weight:700;">📁 Categorías</a>
    </div>

    <div class="main">
        <div class="header">
            <div>
                <h2 style="font-family:serif; font-size:2rem;">Categorías</h2>
                <p style="font-size:0.8rem; color:#888;">Gestioná las secciones de tu tienda</p>
            </div>
            <a href="categoria-nueva.php" class="btn-nuevo">+ Nueva Categoría</a>
        </div>

        <div class="tabla-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nombre / Slug</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($cat = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($cat['nombre']) ?></strong><br>
                                <small style="color:#aaa;">/<?= htmlspecialchars($cat['slug']) ?></small>
                            </td>
                            <td><span style="color:#00000; font-weight:bold">#<?= $cat['orden_display'] ?></span></td>
                            <td>
                                <span class="badge <?= $cat['activo'] ? 'activo' : 'inactivo' ?>">
                                    <?= $cat['activo'] ? 'ACTIVA' : 'PAUSADA' ?>
                                </span>
                            </td>
                            <td style="white-space: nowrap;">
                                <a href="productos.php?categoria=<?= $cat['id'] ?>&ver=activos" class="btn-edit" style="background: #C9A96E; color: white; border: none;">Ver Productos</a>
                                <a href="categoria-editar.php?id=<?= $cat['id'] ?>" class="btn-edit">Editar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>