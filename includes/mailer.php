<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function getMailCredentials(): array
{
    $envFile = __DIR__ . '/../.env';
    if (!file_exists($envFile)) return ['user' => '', 'pass' => ''];

    $lineas = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lineas as $linea) {
        if (strpos($linea, '=') === false) continue;
        [$key, $val] = explode('=', $linea, 2);
        $env[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
    }
    return [
        'user' => $env['MAIL_USER'] ?? '',
        'pass' => $env['MAIL_PASS'] ?? '',
    ];
}

function enviarEmail(string $para, string $nombre, string $asunto, string $cuerpoHTML): bool
{
    $creds = getMailCredentials();

    if (empty($creds['user'])) {
        error_log("Email: MAIL_USER vacío");
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $creds['user'];
        $mail->Password   = $creds['pass'];
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($creds['user'], 'Marlene Store');
        $mail->addAddress($para, $nombre);
        $mail->addReplyTo($creds['user'], 'Marlene Store');

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpoHTML;
        $mail->AltBody = strip_tags($cuerpoHTML);

        $mail->send();
        error_log("Email enviado OK a: $para");
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar email: " . $mail->ErrorInfo);
        return false;
    }
}

function templateEmail(string $titulo, string $contenido): string
{
    return "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Georgia, serif; background: #F2EBE0; margin: 0; padding: 0; }
            .wrap { max-width: 600px; margin: 0 auto; background: white; }
            .header { background: #5C3D3E; padding: 32px; text-align: center; }
            .header h1 { color: #FAF6F1; font-size: 2rem; margin: 0; }
            .header p { color: #C9A96E; font-size: 0.75rem; letter-spacing: 3px; text-transform: uppercase; margin: 6px 0 0; }
            .body { padding: 36px 40px; }
            .body h2 { color: #5C3D3E; font-size: 1.4rem; margin-bottom: 16px; }
            .body p { color: #555; line-height: 1.7; font-size: 0.95rem; }
            .btn { display: inline-block; background: #5C3D3E; color: white !important; padding: 14px 28px; border-radius: 4px; text-decoration: none; font-size: 0.75rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin: 20px 0; }
            .footer { background: #F2EBE0; padding: 20px; text-align: center; font-size: 0.75rem; color: #999; }
            .divider { border: none; border-top: 1px solid #F2EBE0; margin: 20px 0; }
            .badge { display: inline-block; background: #F2EBE0; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; color: #5C3D3E; font-weight: 700; }
            table { width: 100%; border-collapse: collapse; margin: 16px 0; }
            th { text-align: left; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px; color: #C9A96E; padding-bottom: 8px; border-bottom: 1px solid #F2EBE0; }
            td { padding: 10px 0; font-size: 0.85rem; color: #555; border-bottom: 1px solid #FAF6F1; }
            .total-row td { font-weight: 700; color: #5C3D3E; font-size: 1rem; border-bottom: none; padding-top: 14px; }
        </style>
    </head>
    <body>
        <div class='wrap'>
            <div class='header'>
                <h1>Marlene Store</h1>
                <p>Tu tienda de confianza</p>
            </div>
            <div class='body'>
                <h2>$titulo</h2>
                $contenido
            </div>
            <div class='footer'>
                <p>© " . date('Y') . " Marlene Store — Todos los derechos reservados</p>
            </div>
        </div>
    </body>
    </html>";
}
