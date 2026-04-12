<?php
// 1. Cargamos ÚNICAMENTE el autoloader
require_once 'autoload.php';

try {
    // 2. Intentamos usar la Database (sin require manual)
    $db = Database::getConexion();
    echo "✅ Conexión a DB: Exitosa.<br>";

    // 3. Intentamos usar un Repositorio (sin require manual)
    $repo = new ClienteRepository($db);
    echo "✅ Autoload de ClienteRepository: Funciona.<br>";

    // 4. Probamos una consulta
    $clientes = $repo->listarClientes();
    echo "✅ Consulta de datos: Trajo " . count($clientes) . " clientes.";
} catch (Exception $e) {
    echo "❌ Error en el test: " . $e->getMessage();
}
