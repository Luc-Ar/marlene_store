<?php
class Database
{
    private static ?mysqli $instancia = null;

    public static function getConexion(): mysqli
    {
        if (self::$instancia === null) {

            // Ruta absoluta al .env
            $envFile = '/var/www/html/marlene-store/.env';
            $env = file_exists($envFile) ? parse_ini_file($envFile) : [];

            $host = $env['DB_HOST'] ?? 'localhost';
            $user = $env['DB_USER'] ?? '';
            $pass = $env['DB_PASS'] ?? '';
            $name = $env['DB_NAME'] ?? 'marlene_store';

            self::$instancia = new mysqli($host, $user, $pass, $name);

            if (self::$instancia->connect_error) {
                error_log("Error BD: " . self::$instancia->connect_error);
                die("Error de conexión: " . self::$instancia->connect_error);
            }

            self::$instancia->set_charset('utf8mb4');
        }

        return self::$instancia;
    }
}
