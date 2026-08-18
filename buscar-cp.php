<?php
session_start();
require_once __DIR__ . '/includes/error-handler.php';
require_once __DIR__ . '/config/Database.php';

header('Content-Type: application/json; charset=utf-8');

$provincia = trim($_GET['provincia'] ?? '');
$localidad = trim($_GET['localidad'] ?? '');

if (!$provincia || !$localidad) {
    echo json_encode(['ok' => false, 'cps' => []]);
    exit;
}

$conexion = Database::getConexion();

// Normalizamos sacando tildes, porque georef y nuestra tabla no
// siempre coinciden exactamente en cómo escriben los acentos.
function normalizarSql(string $columna): string
{
    $reemplazos = [
        ['á', 'a'],
        ['é', 'e'],
        ['í', 'i'],
        ['ó', 'o'],
        ['ú', 'u'],
        ['ñ', 'n'],
    ];
    $expr = "LOWER($columna)";
    foreach ($reemplazos as [$con, $sin]) {
        $expr = "REPLACE($expr, '$con', '$sin')";
    }
    return $expr;
}

function normalizarPhp(string $s): string
{
    $s = mb_strtolower(trim($s), 'UTF-8');
    return strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']);
}

$provinciaNorm = normalizarPhp($provincia);
$localidadNorm = normalizarPhp($localidad);

$provCol = normalizarSql('provincia');
$locCol  = normalizarSql('localidad');

$sql = "SELECT DISTINCT cp FROM codigos_postales WHERE $provCol LIKE ? AND $locCol = ? ORDER BY cp ASC";
$stmt = $conexion->prepare($sql);
$provinciaLike = '%' . $provinciaNorm . '%';
$stmt->bind_param("ss", $provinciaLike, $localidadNorm);
$stmt->execute();
$cps = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'cp');

echo json_encode(['ok' => true, 'cps' => $cps]);
