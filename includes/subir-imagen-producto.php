<?php

/**
 * Procesa la subida de una imagen de producto.
 *
 * @param array  $archivo        El array de $_FILES['imagen']
 * @param string $carpetaDestino Ruta absoluta (con __DIR__) a assets/imagenes/
 * @return array{ok: bool, path: ?string, error: ?string}
 *         'path' es la ruta relativa a guardar en la DB (ej: "assets/imagenes/prod_xxx.jpg")
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

    $nombreFinal = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
    $rutaCompleta = rtrim($carpetaDestino, '/') . '/' . $nombreFinal;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        return ['ok' => false, 'path' => null, 'error' => 'No se pudo guardar la imagen en el servidor.'];
    }

    return ['ok' => true, 'path' => 'assets/imagenes/' . $nombreFinal, 'error' => null];
}
