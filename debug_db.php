<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Probando conexión...</h3>";

$host = 'localhost';
$user = 'lunay';
$pass = 'marlene123';
$db   = 'marlene_store';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("<b style='color:red'>Fallo total:</b> " . $conn->connect_error);
}

echo "<b style='color:green'>¡CONECTADO CON ÉXITO DESDE PHP!</b>";
$conn->close();
