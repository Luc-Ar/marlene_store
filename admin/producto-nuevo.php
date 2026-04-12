<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.php');
  exit;
}
require_once '../config/Database.php';
$conexion = Database::getConexion();

// 1. Cargar categorías para el select


$res_cats = $conexion->query("SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre ASC");
$mensaje = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
}
?>
<?php
// 2. Procesar formulario al recibir POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $nombre = trim($_POST['nombre']);
  $descripcion_corta = trim($_POST['descripcion_corta']);
  $descripcion_larga = trim($_POST['descripcion_larga']);
  $precio = $_POST['precio'];
  $precio_oferta = !empty($_POST['precio_oferta']) ? $_POST['precio_oferta'] : null;
  $categoria = !empty($_POST['categoria']) ? $_POST['categoria'] : null;
  $subcategoria = trim($_POST['subcategoria']);
  $peso_gramos = !empty($_POST['peso_gramos']) ? $_POST['peso_gramos'] : 0;
  $stock = !empty($_POST['stock']) ? $_POST['stock'] : 0;
  $activo = isset($_POST['activo']) ? 1 : 0;
  $destacado = isset($_POST['destacado']) ? 1 : 0;
  $imagen_principal = '';

  // --- LÓGICA DE SKU AUTOMÁTICO ---
  $sku = !empty($_POST['sku']) ? trim($_POST['sku']) : '';

  if (empty($sku)) {
    $id_cat = (int)$categoria;
    $prefijo_cat = 'GEN';

    if ($id_cat > 0) {
      $res_cat = $conexion->query("SELECT nombre FROM categorias WHERE id = $id_cat");
      if ($cat_data = $res_cat->fetch_assoc()) {
        $nombre_limpio = preg_replace('/[^A-Za-z0-9]/', '', $cat_data['nombre']);
        $prefijo_cat = strtoupper(substr($nombre_limpio, 0, 3));
      }
    }

    $sub_limpia = preg_replace('/[^A-Za-z0-9]/', '', $subcategoria);
    $prefijo_sub = !empty($sub_limpia) ? strtoupper(substr($sub_limpia, 0, 3)) : 'GEN';

    $res_ultimo = $conexion->query("SELECT id FROM productos ORDER BY id DESC LIMIT 1");
    $ultimo_id = ($res_ultimo && $res_ultimo->num_rows > 0) ? $res_ultimo->fetch_assoc()['id'] : 0;

    $sku = $prefijo_cat . '-' . $prefijo_sub . '-' . str_pad($ultimo_id + 1, 3, '0', STR_PAD_LEFT);
  }

  // --- VALIDACIÓN DE DUPLICADOS ---
  $check_sku = $conexion->prepare("SELECT id FROM productos WHERE sku = ?");
  $check_sku->bind_param("s", $sku);
  $check_sku->execute();
  if ($check_sku->get_result()->num_rows > 0) {
    $error = "El SKU ($sku) ya existe.";
  } else {

    // --- PROCESAMIENTO DE IMAGEN ---
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
      $nombre_temp = $_FILES['imagen']['tmp_name'];
      $nombre_orig = $_FILES['imagen']['name'];
      $ext = strtolower(pathinfo($nombre_orig, PATHINFO_EXTENSION));
      $nombre_final = 'prod_' . time() . '.' . $ext;
      $ruta_destino = '../assets/imagenes/' . $nombre_final;

      if (move_uploaded_file($nombre_temp, $ruta_destino)) {
        $imagen_principal = 'assets/imagenes/' . $nombre_final;
      } else {
        $error = "Error al mover la imagen. Revisá permisos en Kali.";
      }
    }

    if (empty($error)) {
      // --- INSERT FINAL ---
      $sql = "INSERT INTO productos (sku, nombre, descripcion_corta, descripcion_larga, precio, precio_oferta, categoria, subcategoria, peso_gramos, stock, imagen_principal, activo, destacado)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

      $stmt = $conexion->prepare($sql);
      if ($stmt) {
        $stmt->bind_param('ssssddssiisii', $sku, $nombre, $descripcion_corta, $descripcion_larga, $precio, $precio_oferta, $categoria, $subcategoria, $peso_gramos, $stock, $imagen_principal, $activo, $destacado);

        if ($stmt->execute()) {
          header('Location: productos.php?mensaje=agregado');
          exit;
        } else {
          $error = 'Error en la base de datos: ' . $stmt->error;
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Nuevo Producto — Marlene Store</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
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
    }

    .sidebar {
      width: 240px;
      background: #5C3D3E;
      min-height: 100vh;
      position: fixed;
      color: white;
      padding: 20px;
    }

    .main {
      margin-left: 240px;
      flex: 1;
      padding: 40px;
    }

    .form-card {
      background: #fff;
      padding: 25px;
      border-radius: 4px;
      margin-bottom: 20px;
      border: 1px solid #ddd;
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      display: block;
      font-weight: 700;
      font-size: 0.7rem;
      text-transform: uppercase;
      margin-bottom: 5px;
      color: #9E5F62;
    }

    input,
    select,
    textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 2px;
    }

    .btn-guardar {
      background: #5C3D3E;
      color: white;
      padding: 15px;
      width: 100%;
      border: none;
      cursor: pointer;
      font-weight: 700;
      letter-spacing: 2px;
      margin-top: 10px;
    }

    .error-msg {
      background: #f8d7da;
      color: #721c24;
      padding: 15px;
      margin-bottom: 20px;
      border: 1px solid #f5c6cb;
    }

    .img-preview {
      width: 100%;
      height: 150px;
      background: #eee;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 10px;
      overflow: hidden;
      border: 1px dashed #ccc;
    }

    .img-preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <h2>Marlene Store</h2>
    <p style="font-size: 0.6rem; opacity: 0.5;">ADMIN PANEL</p><br>
    <a href="productos.php" style="color: white; text-decoration: none; display: block; padding: 10px 0;">← Volver a Productos</a>
  </div>

  <div class="main">
    <h2>Nuevo Producto</h2><br>

    <?php if ($error): ?>
      <div class="error-msg">❌ <?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">

        <div>
          <div class="form-card">
            <h3>Información General</h3><br>
            <div class="form-group">
              <label>Nombre del producto *</label>
              <input type="text" name="nombre" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
              <div class="form-group">
                <label>SKU (Automático)</label>
                <input type="text" name="sku" readonly placeholder="Se generará solo" style="background: #f9f9f9;">
              </div>
              <div class="form-group">
                <label>Subcategoría</label>
                <input type="text" name="subcategoria" placeholder="Ej: Infantil">
              </div>
            </div>
            <div class="form-group">
              <label>Descripción corta</label>
              <input type="text" name="descripcion_corta">
            </div>
            <div class="form-group">
              <label>Descripción larga</label>
              <textarea name="descripcion_larga" rows="4"></textarea>
            </div>
          </div>

          <div class="form-card">
            <h3>Precios, Stock y Peso</h3><br>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
              <div class="form-group">
                <label>Precio *</label>
                <input type="number" name="precio" step="0.01" required>
              </div>
              <div class="form-group">
                <label>Precio Oferta</label>
                <input type="number" name="precio_oferta" step="0.01">
              </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
              <div class="form-group">
                <label>Stock Inicial *</label>
                <input type="number" name="stock" value="0" min="0" required>
              </div>
              <div class="form-group">
                <label>Peso (gramos)</label>
                <input type="number" name="peso_gramos" value="0" min="0">
              </div>
            </div>
          </div>
        </div>

        <div>
          <div class="form-card">
            <label>Imagen del Producto</label>
            <div class="img-preview" id="preview"><span>🖼️</span></div>
            <input type="file" name="imagen" accept="image/*" onchange="previewImg(this)">
          </div>

          <div class="form-card">
            <h3>Categoría</h3>
            <div class="form-group">
              <label>Seleccionar Categoría *</label>
              <select name="categoria" required>
                <option value="">— Seleccione una categoría —</option>
                <?php
                if ($res_cats && $res_cats->num_rows > 0):
                  $res_cats->data_seek(0);
                  while ($cat = $res_cats->fetch_assoc()):
                ?>
                    <option value="<?= $cat['id'] ?>">
                      <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php
                  endwhile;
                endif;
                ?>
              </select>
            </div>
          </div>

          <div class="form-card">
            <div class="form-group">
              <label><input type="checkbox" name="activo" checked> Visible en tienda</label>
            </div>
            <div class="form-group">
              <label><input type="checkbox" name="destacado"> Producto destacado</label>
            </div>
            <button type="submit" name="guardar" class="btn-guardar">GUARDAR PRODUCTO</button>
            <div style="display: flex; gap: 10px; margin-top: 10px;">
              <a href="productos.php" style="flex: 1; background: #eee; color: #333; text-decoration: none; padding: 15px; text-align: center; font-weight: 700; font-size: 0.7rem; border-radius: 2px;">CANCELAR</a>

            </div>
          </div>
        </div>
      </div>

    </form>
  </div>

  <script>
    function previewImg(input) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('preview').innerHTML = '<img src="' + e.target.result + '">';
        }
        reader.readAsDataURL(input.files[0]);
      }
    }
  </script>
</body>

</html>