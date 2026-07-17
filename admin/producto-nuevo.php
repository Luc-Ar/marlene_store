<?php
session_start();
require_once __DIR__ . '/../includes/error-handler.php';
require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../includes/subir-imagen-producto.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$db = Database::getConexion();
$productoRepo = new ProductoRepository($db);

// La categoría todavía no tiene su propio Repository (igual que categorias.php),
// así que este SELECT queda directo, sin inventar una clase que no existe.
$res_cats = $db->query("SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre ASC");
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
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

    // SKU automático si no se completó a mano
    $sku = !empty($_POST['sku']) ? trim($_POST['sku']) : '';
    if (empty($sku)) {
        $prefijo_cat = 'GEN';
        if ($categoria) {
            $res_cat = $db->prepare("SELECT nombre FROM categorias WHERE id = ?");
            $res_cat->bind_param("i", $categoria);
            $res_cat->execute();
            if ($cat_data = $res_cat->get_result()->fetch_assoc()) {
                $prefijo_cat = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $cat_data['nombre']), 0, 3));
            }
        }
        $prefijo_sub = !empty($subcategoria) ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $subcategoria), 0, 3)) : 'GEN';
        $ultimo_id = $productoRepo->obtenerUltimoId();
        $sku = $prefijo_cat . '-' . $prefijo_sub . '-' . str_pad($ultimo_id + 1, 3, '0', STR_PAD_LEFT);
    }

    if ($productoRepo->existeSku($sku)) {
        $error = "El SKU ($sku) ya existe.";
    } else {
        $imagen_principal = '';
        if (isset($_FILES['imagen'])) {
            $subida = procesarImagenProducto($_FILES['imagen'], __DIR__ . '/../assets/imagenes');
            if (!$subida['ok']) {
                $error = $subida['error'];
            } elseif ($subida['path']) {
                $imagen_principal = $subida['path'];
            }
        }

        if (empty($error)) {
            $nuevoId = $productoRepo->crear([
                'sku'               => $sku,
                'nombre'            => $nombre,
                'descripcion_corta' => $descripcion_corta,
                'descripcion_larga' => $descripcion_larga,
                'precio'            => $precio,
                'precio_oferta'     => $precio_oferta,
                'categoria'         => $categoria,
                'subcategoria'      => $subcategoria,
                'peso_gramos'       => $peso_gramos,
                'stock'             => $stock,
                'imagen_principal'  => $imagen_principal,
                'activo'            => $activo,
                'destacado'         => $destacado,
            ]);

            if ($nuevoId) {
                header('Location: /admin/productos.php?mensaje=agregado');
                exit;
            } else {
                $error = 'Error al guardar el producto en la base de datos.';
            }
        }
    }
}

// Variables para header-admin.php
$titulo_admin = 'Nuevo Producto';
$nav_activo   = 'productos';

$estilos_extra_admin = '
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; }
.page-header h2 { font-family:"Cormorant Garamond",serif; font-size:2rem; color:var(--marlene); }
.page-header p { font-size:0.8rem; color:#999; margin-top:4px; }
.form-grid { display:grid; grid-template-columns:2fr 1fr; gap:24px; }
.form-card { background:white; border-radius:8px; padding:28px; margin-bottom:20px; border:1px solid rgba(200,152,154,0.2); }
.form-card h3 { font-family:"Cormorant Garamond",serif; font-size:1.3rem; color:var(--marlene); margin-bottom:20px; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:0.6rem; font-weight:700; text-transform:uppercase; color:#999; margin-bottom:6px; letter-spacing:1px; }
.form-group input, .form-group select, .form-group textarea {
    width:100%; padding:10px 14px; border:1.5px solid rgba(200,152,154,0.3); border-radius:6px;
    font-family:"Montserrat",sans-serif; font-size:0.85rem; color:#3A2526; background:#FDFAF8; transition:border-color 0.2s;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline:none; border-color:var(--dorado); }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.img-preview { width:100%; height:180px; background:#F2EBE0; border:2px dashed rgba(200,152,154,0.4); border-radius:6px; display:flex; align-items:center; justify-content:center; overflow:hidden; margin-bottom:12px; font-size:2rem; }
.img-preview img { width:100%; height:100%; object-fit:contain; }
.btn-guardar { width:100%; background:var(--marlene); color:white; border:none; padding:16px; border-radius:6px; font-weight:700; font-size:0.7rem; letter-spacing:2px; text-transform:uppercase; cursor:pointer; transition:background 0.3s; }
.btn-guardar:hover { background:var(--dorado); }
.btn-cancelar { display:block; text-align:center; margin-top:10px; padding:12px; background:#F2EBE0; color:var(--marlene); text-decoration:none; border-radius:6px; font-weight:700; font-size:0.7rem; text-transform:uppercase; transition:background 0.2s; }
.btn-cancelar:hover { background:#e5ddd1; }
.error-msg { background:#FEE2E2; color:#991B1B; padding:14px 18px; border-radius:6px; margin-bottom:20px; font-size:0.85rem; }
.check-label { display:flex; align-items:center; gap:8px; font-size:0.8rem; color:var(--marlene); cursor:pointer; margin-bottom:10px; }
';

require_once __DIR__ . '/includes/header-admin.php';
?>

<div class="page-header">
    <div>
        <h2>Nuevo Producto</h2>
        <p>Completá los datos para agregar un producto al catálogo</p>
    </div>
    <a href="/admin/productos.php" style="color:#999;text-decoration:none;font-size:0.8rem;">← Volver</a>
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
                    <label>Nombre del producto *</label>
                    <input type="text" name="nombre" required placeholder="Ej: Mochila Escolar Azul">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>SKU (automático si se deja vacío)</label>
                        <input type="text" name="sku" placeholder="Se generará solo">
                    </div>
                    <div class="form-group">
                        <label>Subcategoría</label>
                        <input type="text" name="subcategoria" placeholder="Ej: Infantil">
                    </div>
                </div>
                <div class="form-group">
                    <label>Descripción corta</label>
                    <input type="text" name="descripcion_corta" placeholder="Resumen breve del producto">
                </div>
                <div class="form-group">
                    <label>Descripción larga</label>
                    <textarea name="descripcion_larga" rows="4" placeholder="Descripción detallada..."></textarea>
                </div>
            </div>

            <div class="form-card">
                <h3>Precios, stock y peso</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Precio *</label>
                        <input type="number" name="precio" step="0.01" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Precio oferta</label>
                        <input type="number" name="precio_oferta" step="0.01" placeholder="Opcional">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Stock inicial</label>
                        <input type="number" name="stock" value="0" min="0">
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
                <h3>Imagen</h3>
                <div class="img-preview" id="preview">🖼️</div>
                <div class="form-group">
                    <label>Seleccionar imagen</label>
                    <input type="file" name="imagen" accept="image/*" onchange="previewImg(this)">
                </div>
            </div>

            <div class="form-card">
                <h3>Categoría</h3>
                <div class="form-group">
                    <label>Categoría *</label>
                    <select name="categoria" required>
                        <option value="">— Seleccioná —</option>
                        <?php while ($cat = $res_cats->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-card">
                <h3>Opciones</h3>
                <label class="check-label">
                    <input type="checkbox" name="activo" checked> Visible en tienda
                </label>
                <label class="check-label">
                    <input type="checkbox" name="destacado"> Producto destacado
                </label>
                <button type="submit" name="guardar" class="btn-guardar">GUARDAR PRODUCTO</button>
                <a href="/admin/productos.php" class="btn-cancelar">CANCELAR</a>
            </div>
        </div>
    </div>
</form>

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

<?php require_once __DIR__ . '/includes/footer-admin.php'; ?>