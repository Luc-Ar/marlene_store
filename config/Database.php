<?php
class Database
{
    public static function getConexion()
    {
        $db = new mysqli('localhost', 'lunay', 'marlene123', 'marlene_store');
        if ($db->connect_error) {
            die("Error de conexión: " . $db->connect_error);
        }
        $db->set_charset("utf8mb4");
        return $db;
    }
}
