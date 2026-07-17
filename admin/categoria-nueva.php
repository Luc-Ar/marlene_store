<?php
session_start();
require_once __DIR__ . '/../includes/error-handler.php';
require_once __DIR__ . '/../config/Database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$conexion = Database::getConexion();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $slug   = trim($_POST['slug'] ?? '');
    $desc   = trim($_POST['descripcion'] ?? '');
    $icono  = trim($_POST['icono'] ?? '');
    $orden  = (int)($_POST['orden_display'] ?? 0);

    if (!$nombre || !$slug) {
        $error = 'El nombre y el slug son obligatorios.';
    } else {
        $stmt = $conexion->prepare("INSERT INTO categorias (nombre, slug, descripcion, icono, orden_display, activo) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssssi", $nombre, $slug, $desc, $icono, $orden);
        if ($stmt->execute()) {
            header('Location: /admin/categorias.php?mensaje=creada');
            exit;
        } else {
            $error = 'Error al crear la categoría. El slug puede estar duplicado.';
        }
    }
}

// Variables para header-admin.php
$titulo_admin = 'Nueva Categoría';
$nav_activo   = 'categorias';

// CSS propio de esta página (formulario). Lo que ya viene de header-admin
// (panel, tabla, badges) no hace falta repetirlo acá.
$estilos_extra_admin = '
.form-wrap { max-width: 640px; }
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; }
.page-header h2 { font-family:"Cormorant Garamond",serif; font-size:2rem; color:var(--marlene); }
.page-header p { font-size:0.8rem; color:#999; margin-top:4px; }
.form-card { background:white; border-radius:8px; padding:28px; margin-bottom:20px; border:1px solid rgba(200,152,154,0.2); }
.form-card h3 { font-family:"Cormorant Garamond",serif; font-size:1.3rem; color:var(--marlene); margin-bottom:20px; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:0.6rem; font-weight:700; text-transform:uppercase; color:#999; margin-bottom:6px; letter-spacing:1px; }
.form-group input, .form-group textarea {
    width:100%; padding:10px 14px; border:1.5px solid rgba(200,152,154,0.3); border-radius:6px;
    font-family:"Montserrat",sans-serif; font-size:0.85rem; color:#3A2526; background:#FDFAF8; transition:border-color 0.2s;
}
.form-group input:focus, .form-group textarea:focus { outline:none; border-color:var(--dorado); }
.slug-preview { font-size:0.65rem; color:#999; margin-top:5px; }
.slug-preview span { color:var(--dorado); font-weight:700; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.btn-guardar {
    width:100%; background:var(--marlene); color:white; border:none; padding:16px; border-radius:6px;
    font-weight:700; font-size:0.7rem; letter-spacing:2px; text-transform:uppercase; cursor:pointer; transition:background 0.3s;
}
.btn-guardar:hover { background:var(--dorado); }
.btn-cancelar { display:block; text-align:center; margin-top:10px; padding:12px; background:#F2EBE0; color:var(--marlene); text-decoration:none; border-radius:6px; font-weight:700; font-size:0.7rem; text-transform:uppercase; }
.error-msg { background:#FEE2E2; color:#991B1B; padding:14px 18px; border-radius:6px; margin-bottom:20px; font-size:0.85rem; }
';

require_once __DIR__ . '/includes/header-admin.php';
?>

<div class="form-wrap">
    <div class="page-header">
        <div>
            <h2>Nueva Categoría</h2>
            <p>Agregá una nueva sección al catálogo</p>
        </div>
        <a href="/admin/categorias.php" style="color:#999;text-decoration:none;font-size:0.8rem;">← Volver</a>
    </div>

    <?php if ($error): ?>
        <div class="error-msg">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-card">
            <h3>Información de la categoría</h3>
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" id="nombre" required placeholder="Ej: Mochilas Infantiles">
            </div>
            <div class="form-group">
                <label>Slug (URL) *</label>
                <input type="text" name="slug" id="slug" required placeholder="mochilas-infantiles">
                <div class="slug-preview">marlene.store/catalogo?cat=<span id="slug-text">...</span></div>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3" placeholder="Breve descripción de la categoría..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Ícono (emoji)</label>
                    <input type="text" name="icono" placeholder="🎒" maxlength="10">
                </div>
                <div class="form-group">
                    <label>Orden de visualización</label>
                    <input type="number" name="orden_display" value="0" min="0">
                </div>
            </div>
        </div>

        <button type="submit" class="btn-guardar">CREAR CATEGORÍA</button>
        <a href="/admin/categorias.php" class="btn-cancelar">CANCELAR</a>
    </form>
</div>

<script>
    document.getElementById('nombre').addEventListener('input', function() {
        const slug = this.value.toLowerCase()
            .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
            .replace(/[^a-z0-9\s-]/g, "")
            .replace(/\s+/g, "-")
            .replace(/-+/g, "-");
        document.getElementById('slug').value = slug;
        document.getElementById('slug-text').textContent = slug || '...';
    });

    document.getElementById('slug').addEventListener('input', function() {
        document.getElementById('slug-text').textContent = this.value || '...';
    });
</script>

<?php require_once __DIR__ . '/includes/footer-admin.php'; ?>