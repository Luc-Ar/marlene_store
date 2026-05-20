<?php
class Database
{
    private static ?mysqli $instancia = null;

    public static function getConexion(): mysqli
    {
        if (self::$instancia === null) {
            $env = parse_ini_file(__DIR__ . '/../.env');

            $host = $env['DB_HOST'] ?? 'localhost';
            $user = $env['DB_USER'] ?? '';
            $pass = $env['DB_PASS'] ?? '';
            $name = $env['DB_NAME'] ?? '';

            self::$instancia = new mysqli($host, $user, $pass, $name);

            if (self::$instancia->connect_error) {
                error_log("Error BD: " . self::$instancia->connect_error);
                die("Error de conexión.");
            }

            self::$instancia->set_charset('utf8mb4');
        }

        return self::$instancia;
    }
}
