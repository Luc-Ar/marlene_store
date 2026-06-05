<?php
$envFile = '/var/www/html/marlene-store/.env';
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
    if (strpos(trim($linea), '#') === 0) continue;
    if (strpos($linea, '=') === false) continue;
    [$key, $val] = explode('=', $linea, 2);
    $env[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
}
echo "TOKEN: " . $env['MP_ACCESS_TOKEN'];
