<?php

/**
 * Procesa la subida de una imagen de producto y la convierte a WebP
 * automáticamente (confirmado que este hosting soporta WebP vía GD,
 * pero no AVIF — ver check-gd.php, ya borrado del servidor).
 *
 * @param array  $archivo        El array de $_FILES['imagen']
 * @param string $carpetaDestino Ruta absoluta (con __DIR__) a assets/imagenes/
 * @return array{ok: bool, path: ?string, error: ?string}
 *         'path' es la ruta relativa a guardar en la DB (ej: "assets/imagenes/prod_xxx.webp")
 */
function procesarImagenProducto(array $archivo, string $carpetaDestino): array
{
    // Si no se seleccionó ningún archivo, no es un error — simplemente no hay nada que subir.
    if (empty($archivo['name']) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'path' => null, 'error' => null];
    }

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'path' => null, 'error' => 'Error al subir la imagen (código ' . $archivo['error'] . ').'];
    }

    // Límite de tamaño: 5MB
    if ($archivo['size'] > 5 * 1024 * 1024) {
        return ['ok' => false, 'path' => null, 'error' => 'La imagen no puede pesar más de 5MB.'];
    }

    $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
    if (!in_array($ext, $extensionesPermitidas)) {
        return ['ok' => false, 'path' => null, 'error' => 'Formato no permitido. Usá JPG, PNG, WEBP o AVIF.'];
    }

    // Validación real: confirmamos que el archivo sea efectivamente una imagen
    // (getimagesize() falla si es, por ejemplo, un .php disfrazado con extensión .jpg).
    // AVIF a veces no lo reconoce getimagesize() en versiones viejas de GD, así que lo dejamos pasar sin este chequeo.
    if ($ext !== 'avif' && @getimagesize($archivo['tmp_name']) === false) {
        return ['ok' => false, 'path' => null, 'error' => 'El archivo no es una imagen válida.'];
    }

    $nombreBase = 'prod_' . time() . '_' . uniqid();
    $nombreOriginal = $nombreBase . '.' . $ext;
    $rutaOriginal = rtrim($carpetaDestino, '/') . '/' . $nombreOriginal;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaOriginal)) {
        return ['ok' => false, 'path' => null, 'error' => 'No se pudo guardar la imagen en el servidor.'];
    }

    // Si ya es WebP o es AVIF (que GD no puede tocar en este servidor),
    // no hay nada que convertir — se guarda tal cual.
    if (in_array($ext, ['webp', 'avif'])) {
        return ['ok' => true, 'path' => 'assets/imagenes/' . $nombreOriginal, 'error' => null];
    }

    $rutaWebp = rtrim($carpetaDestino, '/') . '/' . $nombreBase . '.webp';
    $convertido = convertirAWebp($rutaOriginal, $ext, $rutaWebp);

    if ($convertido) {
        // La conversión salió bien: borramos el original (jpg/png) para
        // no duplicar espacio en disco, y nos quedamos con el WebP.
        @unlink($rutaOriginal);
        return ['ok' => true, 'path' => 'assets/imagenes/' . $nombreBase . '.webp', 'error' => null];
    }

    // Si por algún motivo la conversión falla, no rompemos la subida:
    // nos quedamos con el archivo original tal cual se subió.
    return ['ok' => true, 'path' => 'assets/imagenes/' . $nombreOriginal, 'error' => null];
}

/**
 * Convierte una imagen (jpg/png) a WebP usando GD.
 * Devuelve true si pudo convertir y guardar, false si no.
 */
function convertirAWebp(string $rutaOrigen, string $extOrigen, string $rutaDestino): bool
{
    if (!function_exists('imagewebp')) {
        return false;
    }

    $imagen = match ($extOrigen) {
        'jpg', 'jpeg' => @imagecreatefromjpeg($rutaOrigen),
        'png' => @imagecreatefrompng($rutaOrigen),
        default => false,
    };

    if (!$imagen) {
        return false;
    }

    // Los PNG pueden tener transparencia — la preservamos al convertir.
    imagepalettetotruecolor($imagen);
    imagealphablending($imagen, true);
    imagesavealpha($imagen, true);

    // Calidad 82: buen equilibrio entre peso de archivo y nitidez visual.
    $resultado = imagewebp($imagen, $rutaDestino, 82);
    imagedestroy($imagen);

    return $resultado;
}
