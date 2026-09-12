<?php
require_once __DIR__ . '/config/Database.php';
header('Content-Type: application/json; charset=utf-8');

$conexion = Database::getConexion();
$provincia = trim($_GET['provincia'] ?? '');
$busqueda  = trim($_GET['q'] ?? '');

if (!$provincia || mb_strlen($busqueda) < 3) {
    echo json_encode(['ok' => false, 'localidades' => []]);
    exit;
}

function normalizar(string $texto): string
{
    $texto = mb_strtolower($texto, 'UTF-8');
    return strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);
}

$provinciaNormalizada = normalizar($provincia);
$busquedaNormalizada  = normalizar($busqueda);

// Traemos solo las localidades de la provincia elegida que empiezan a
// coincidir con lo que se está escribiendo. Como el volumen por
// provincia ya es mucho más chico, filtrar en PHP acá es rápido.
$stmt = $conexion->prepare("SELECT DISTINCT localidad, provincia FROM codigos_postales");
$stmt->execute();
$todas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$coincidencias = [];
foreach ($todas as $fila) {
    if (normalizar($fila['provincia']) !== $provinciaNormalizada) continue;
    if (strpos(normalizar($fila['localidad']), $busquedaNormalizada) === false) continue;
    $coincidencias[] = $fila['localidad'];
}

$coincidencias = array_values(array_unique($coincidencias));
sort($coincidencias);
$coincidencias = array_slice($coincidencias, 0, 20); // tope de resultados

echo json_encode(['ok' => true, 'localidades' => $coincidencias]);
