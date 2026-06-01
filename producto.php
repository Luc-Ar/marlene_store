<?php
require_once __DIR__ . '/config/Database.php';
$conexion = Database::getConexion();

// Verificar que viene un ID válido
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: catalogo.php');
    exit;
}

// Traer el producto con su categoría
$stmt = $conexion->prepare("
    SELECT p.*, c.nombre as categoria_nombre, c.slug as categoria_slug
    FROM productos p
    LEFT JOIN categorias c ON p.categoria = c.id
    WHERE p.id = ? AND p.activo = 1
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();

// Si no existe redirigir al catálogo
if (!$producto) {
    header('Location: catalogo.php');
    exit;
}

// Traer productos relacionados (misma subcategoría)
$stmt2 = $conexion->prepare("
    SELECT id, nombre, precio, imagen_principal, descripcion_corta
    FROM productos
    WHERE subcategoria = ? AND id != ? AND activo = 1
    LIMIT 4
");
$stmt2->bind_param("si", $producto['subcategoria'], $id);
$stmt2->execute();
$relacionados = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <style>
        body {
            visibility: hidden;
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($producto['nombre']) ?> — Marlene STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Montserrat:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/catalogo.css">
    <style>
        .prod-detalle {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: start;
        }

        .prod-img-wrap {
            background: #FAF6F1;
            border-radius: 16px;
            overflow: hidden;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .prod-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 20px;
        }

        .prod-info {
            padding: 10px 0;
        }

        .prod-breadcrumb {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        .prod-breadcrumb a {
            color: #999;
            text-decoration: none;
        }

        .prod-breadcrumb a:hover {
            color: var(--color-marlene, #5C3D3E);
        }

        .prod-subcategoria {
            font-size: 11px;
            font-weight: 700;
            color: #C9A96E;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .prod-nombre {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.4rem;
            font-weight: 600;
            color: #3A2526;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .prod-precio-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
        }

        .prod-precio {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #5C3D3E;
        }

        .prod-precio-oferta {
            font-size: 1.1rem;
            color: #999;
            text-decoration: line-through;
        }

        .prod-badge-oferta {
            background: #C0392B;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .prod-desc {
            font-size: 0.95rem;
            color: #666;
            line-height: 1.7;
            margin-bottom: 24px;
        }

        .prod-stock {
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 24px;
            padding: 8px 14px;
            border-radius: 6px;
            display: inline-block;
        }

        .stock-ok {
            background: #DCFCE7;
            color: #166534;
        }

        .stock-bajo {
            background: #FEF3C7;
            color: #92400E;
        }

        .stock-agotado {
            background: #FEE2E2;
            color: #991B1B;
        }

        .prod-acciones {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-carrito {
            flex: 1;
            padding: 16px 24px;
            background: #5C3D3E;
            color: white;
            border: none;
            border-radius: 10px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.3s;
            text-align: center;
        }

        .btn-carrito:hover {
            background: #C9A96E;
        }

        .btn-carrito:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .btn-wsp {
            padding: 16px 20px;
            background: #25D366;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }

        .btn-wsp:hover {
            background: #128C7E;
        }

        .prod-meta {
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid #F2EBE0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .prod-meta span {
            font-size: 12px;
            color: #999;
        }

        .prod-meta strong {
            color: #5C3D3E;
        }

        /* Relacionados */
        .relacionados {
            max-width: 1100px;
            margin: 60px auto;
            padding: 0 24px;
        }

        .relacionados h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            color: #3A2526;
            margin-bottom: 24px;
        }

        .rel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .rel-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #F2EBE0;
        }

        .rel-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .rel-card img {
            width: 100%;
            aspect-ratio: 1;
            object-fit: contain;
            background: #FAF6F1;
            padding: 16px;
        }

        .rel-card-body {
            padding: 14px;
        }

        .rel-card-nombre {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 4px;
        }

        .rel-card-precio {
            color: #5C3D3E;
            font-weight: 700;
            font-size: 1rem;
        }

        /* Breadcrumb nav */
        .page-breadcrumb {
            max-width: 1100px;
            margin: 24px auto 0;
            padding: 0 24px;
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .page-breadcrumb a {
            color: #999;
            text-decoration: none;
        }

        .page-breadcrumb a:hover {
            color: #5C3D3E;
        }

        @media (max-width: 768px) {
            .prod-detalle {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .prod-nombre {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>

    <!-- NAV igual al index.php -->
    <nav>
        <a href="index.php" class="logo-wrap">
            <span class="logo-script">Marlene</span>
            <span class="logo-store">STORE</span>
        </a>
        <ul class="nav-links">
            <li><a href="index.php#categorias">Categorías</a></li>
            <li><a href="catalogo.php">Productos</a></li>
            <li><a href="index.php#envios">Envíos</a></li>
            <li><a href="index.php#contacto">Contacto</a></li>
            <li><a href="index.php#contacto" class="nav-cta">Consultar</a></li>
        </ul>
    </nav>

    <!-- Breadcrumb -->
    <div class="page-breadcrumb">
        <a href="index.php">Inicio</a> ›
        <a href="catalogo.php">Catálogo</a> ›
        <?php if ($producto['categoria_slug']): ?>
            <a href="catalogo.php?cat=<?= $producto['categoria_slug'] ?>"><?= htmlspecialchars($producto['categoria_nombre']) ?></a> ›
        <?php endif; ?>
        <?= htmlspecialchars($producto['nombre']) ?>
    </div>

    <!-- Detalle -->
    <div class="prod-detalle">
        <!-- Imagen -->
        <div class="prod-img-wrap">
            <?php if ($producto['imagen_principal']): ?>
                <img src="<?= htmlspecialchars($producto['imagen_principal']) ?>"
                    alt="<?= htmlspecialchars($producto['nombre']) ?>">
            <?php else: ?>
                <span style="font-size: 5rem;">🎒</span>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="prod-info">
            <p class="prod-subcategoria"><?= htmlspecialchars($producto['subcategoria'] ?? '') ?></p>
            <h1 class="prod-nombre"><?= htmlspecialchars($producto['nombre']) ?></h1>

            <!-- Precio -->
            <div class="prod-precio-wrap">
                <span class="prod-precio">$<?= number_format($producto['precio'], 0, ',', '.') ?></span>
                <?php if ($producto['precio_oferta']): ?>
                    <span class="prod-precio-oferta">$<?= number_format($producto['precio_oferta'], 0, ',', '.') ?></span>
                    <span class="prod-badge-oferta">OFERTA</span>
                <?php endif; ?>
            </div>

            <!-- Descripción -->
            <?php if ($producto['descripcion_corta']): ?>
                <p class="prod-desc"><?= htmlspecialchars($producto['descripcion_corta']) ?></p>
            <?php endif; ?>
            <?php if ($producto['descripcion_larga']): ?>
                <p class="prod-desc"><?= nl2br(htmlspecialchars($producto['descripcion_larga'])) ?></p>
            <?php endif; ?>

            <!-- Stock -->
            <?php
            $stock = (int)$producto['stock'];
            if ($stock <= 0):
            ?>
                <span class="prod-stock stock-agotado">❌ Sin stock</span>
            <?php elseif ($stock <= 5): ?>
                <span class="prod-stock stock-bajo">⚠️ Últimas <?= $stock ?> unidades</span>
            <?php else: ?>
                <span class="prod-stock stock-ok">✅ En stock (<?= $stock ?> disponibles)</span>
            <?php endif; ?>

            <!-- Acciones -->
            <div class="prod-acciones">
                <?php if ($stock > 0): ?>
                    <button class="btn-carrito" onclick="agregarAlCarrito(this, '<?= addslashes($producto['nombre']) ?>', '<?= $producto['imagen_principal'] ?>', '<?= addslashes($producto['subcategoria'] ?? '') ?>', <?= $producto['precio'] ?>)">
                        🛒 Agregar al carrito
                    </button>
                <?php else: ?>
                    <button class="btn-carrito" disabled>Sin stock</button>
                <?php endif; ?>
                <a href="https://wa.me/5493704097831?text=Hola!%20Me%20interesa%20<?= urlencode($producto['nombre']) ?>"
                    class="btn-wsp" target="_blank">
                    💬 Consultar
                </a>
            </div>

            <!-- Meta info -->
            <div class="prod-meta">
                <?php if ($producto['sku']): ?>
                    <span>SKU: <strong><?= htmlspecialchars($producto['sku']) ?></strong></span>
                <?php endif; ?>
                <?php if ($producto['peso_gramos']): ?>
                    <span>Peso: <strong><?= $producto['peso_gramos'] ?>g</strong></span>
                <?php endif; ?>
                <?php if ($producto['categoria_nombre']): ?>
                    <span>Categoría: <strong><?= htmlspecialchars($producto['categoria_nombre']) ?></strong></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Productos relacionados -->
    <?php if (!empty($relacionados)): ?>
        <div class="relacionados">
            <h2>También te puede interesar</h2>
            <div class="rel-grid">
                <?php foreach ($relacionados as $rel): ?>
                    <a href="producto.php?id=<?= $rel['id'] ?>" class="rel-card">
                        <img src="<?= htmlspecialchars($rel['imagen_principal'] ?? '') ?>"
                            alt="<?= htmlspecialchars($rel['nombre']) ?>">
                        <div class="rel-card-body">
                            <p class="rel-card-nombre"><?= htmlspecialchars($rel['nombre']) ?></p>
                            <p class="rel-card-precio">$<?= number_format($rel['precio'], 0, ',', '.') ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php include __DIR__ . '/includes/carrito-panel.php'; ?>
    <script src="assets/js/catalogo.js"></script>
    <script>
        document.body.style.visibility = 'visible';
    </script>
</body>


</body>

</html>