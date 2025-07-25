<?php
session_start();
require_once 'includes/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'imjgro.edu.mx@gmail.com');
define('SMTP_PASSWORD', 'vmsd nksi blts lcao');
define('CORREO_DESTINO', 'ejgro.constanciasc@gmail.com');
define('NOMBRE_REMITENTE', 'Instituto para el Mejoramiento Judicial');

function generarFolio() {
    $fecha = date('Ymd');
    $hora = date('His');
    $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
    return "IMJ-{$fecha}-{$hora}-{$random}";
}

function enviarCorreoContacto($nombre, $correo, $mensaje) {
    $mail = new PHPMailer(true);
    
    try {
        $folio = generarFolio();
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->setFrom(SMTP_USERNAME, NOMBRE_REMITENTE);
        $mail->addAddress(CORREO_DESTINO, 'Constancias IMJ');
        $mail->addReplyTo($correo, $nombre);
        $mail->isHTML(true);
        $mail->Subject = "Contacto #{$folio} - {$nombre}";
        $mail->Body = generarHtmlCorreo($nombre, $correo, $mensaje, $folio);
        $mail->AltBody = generarTextoPlano($nombre, $correo, $mensaje, $folio);
        $mail->send();
        return ['success' => true, 'folio' => $folio];
        
    } catch (Exception $e) {
        error_log("Error al enviar correo de contacto: " . $mail->ErrorInfo);
        error_log("Excepción PHPMailer: " . $e->getMessage());
        return ['success' => false, 'folio' => null];
    }
}

function generarHtmlCorreo($nombre, $correo, $mensaje, $folio) {
    $fechaActual = date('d/m/Y H:i:s');
    $mensajeSeguro = nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'));
    
    return "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Mensaje de Contacto #{$folio} - IMJ</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f5f5f5; }
            .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; box-shadow: 0 0 20px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden; }
            .header { background: linear-gradient(135deg, #1a5f3f 0%, #2d7a56 100%); color: white; padding: 30px 20px; text-align: center; }
            .header h1 { font-size: 24px; margin-bottom: 5px; }
            .header p { font-size: 14px; opacity: 0.9; }
            .folio-badge { background: rgba(255,255,255,0.2); color: white; padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: bold; margin-top: 10px; display: inline-block; }
            .content { padding: 30px; }
            .info-row { margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #1a5f3f; }
            .label { font-weight: 600; color: #1a5f3f; margin-bottom: 5px; display: block; }
            .value { color: #333; word-wrap: break-word; }
            .mensaje-box { background-color: white; padding: 20px; border: 1px solid #e9ecef; border-radius: 8px; margin-top: 10px; }
            .footer { background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6; }
            .footer p { font-size: 12px; color: #6c757d; }
            .icon { display: inline-block; width: 20px; height: 20px; margin-right: 8px; vertical-align: middle; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>📧 Nuevo Mensaje de Contacto</h1>
                <p>Instituto para el Mejoramiento Judicial</p>
                <div class='folio-badge'>📋 Folio: {$folio}</div>
            </div>
            <div class='content'>
                <div class='info-row'>
                    <span class='label'>👤 Remitente:</span>
                    <span class='value'>{$nombre}</span>
                </div>
                
                <div class='info-row'>
                    <span class='label'>📧 Correo electrónico:</span>
                    <span class='value'><a href='mailto:{$correo}' style='color: #1a5f3f; text-decoration: none;'>{$correo}</a></span>
                </div>
                
                <div class='info-row'>
                    <span class='label'>📅 Fecha y hora:</span>
                    <span class='value'>{$fechaActual}</span>
                </div>
                
                <div class='info-row'>
                    <span class='label'>🆔 Número de folio:</span>
                    <span class='value' style='font-family: monospace; background: #e9ecef; padding: 4px 8px; border-radius: 4px;'>{$folio}</span>
                </div>
                
                <div class='info-row'>
                    <span class='label'>💬 Mensaje:</span>
                    <div class='mensaje-box'>
                        {$mensajeSeguro}
                    </div>
                </div>
            </div>
            <div class='footer'>
                <p>Este mensaje fue enviado desde el formulario de contacto del sitio web del Instituto para el Mejoramiento Judicial.</p>
                <p style='margin-top: 10px;'><strong>Importante:</strong> Para responder, utiliza la dirección de correo del remitente.</p>
                <p style='margin-top: 10px; font-family: monospace; color: #1a5f3f;'><strong>Folio de referencia:</strong> {$folio}</p>
            </div>
        </div>
    </body>
    </html>";
}

function generarTextoPlano($nombre, $correo, $mensaje, $folio) {
    $fechaActual = date('d/m/Y H:i:s');
    
    return "
NUEVO MENSAJE DE CONTACTO
Instituto para el Mejoramiento Judicial
Folio: {$folio}

======================================

Remitente: {$nombre}
Correo: {$correo}
Fecha: {$fechaActual}
Folio: {$folio}

MENSAJE:
--------------------------------------
{$mensaje}
--------------------------------------

Este mensaje fue enviado desde el formulario de contacto del sitio web del IMJ.
Para responder, utiliza la dirección de correo del remitente: {$correo}

Folio de referencia: {$folio}
";
}

function validarDatos($nombre, $correo, $mensaje) {
    $errores = [];
    if (empty(trim($nombre))) {
        $errores[] = 'El nombre es obligatorio';
    } elseif (strlen(trim($nombre)) < 2) {
        $errores[] = 'El nombre debe tener al menos 2 caracteres';
    } elseif (strlen(trim($nombre)) > 100) {
        $errores[] = 'El nombre no puede tener más de 100 caracteres';
    }
    if (empty(trim($correo))) {
        $errores[] = 'El correo electrónico es obligatorio';
    } elseif (!filter_var(trim($correo), FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El formato del correo electrónico no es válido';
    } elseif (strlen(trim($correo)) > 254) {
        $errores[] = 'El correo electrónico es demasiado largo';
    }
    if (empty(trim($mensaje))) {
        $errores[] = 'El mensaje es obligatorio';
    } elseif (strlen(trim($mensaje)) < 10) {
        $errores[] = 'El mensaje debe tener al menos 10 caracteres';
    } elseif (strlen(trim($mensaje)) > 2000) {
        $errores[] = 'El mensaje no puede tener más de 2000 caracteres';
    }
    
    return $errores;
}

function esSpam($mensaje) {
    $palabrasSpam = [
        'viagra', 'casino', 'lottery', 'winner', 'congratulations',
        'click here', 'urgent', 'limited time', 'act now', 'free money'
    ];
    
    $mensajeLower = strtolower($mensaje);
    
    foreach ($palabrasSpam as $palabra) {
        if (strpos($mensajeLower, $palabra) !== false) {
            return true;
        }
    }
    $numLinks = substr_count($mensajeLower, 'http');
    if ($numLinks > 2) {
        return true;
    }
    
    return false;
}

// Verificar método de petición
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['toast_error'] = 'Método de petición no válido';
    header('Location: /inicio');
    exit;
}

// Obtener y limpiar datos
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

// Validar datos
$errores = validarDatos($nombre, $correo, $mensaje);

if (!empty($errores)) {
    $_SESSION['toast_error'] = implode('. ', $errores);
    header('Location: /inicio');
    exit;
}

// Verificar spam
if (esSpam($mensaje)) {
    error_log("Mensaje de spam detectado de: {$correo}");
    $_SESSION['toast_error'] = 'Mensaje no válido';
    header('Location: /inicio');
    exit;
}

// Control de frecuencia de envío
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$sessionKey = "last_contact_" . md5($ip);

if (isset($_SESSION[$sessionKey])) {
    $tiempoTranscurrido = time() - $_SESSION[$sessionKey];
    if ($tiempoTranscurrido < 60) {
        $_SESSION['toast_error'] = 'Espera un momento antes de enviar otro mensaje';
        header('Location: /inicio');
        exit;
    }
}

// Enviar correo
$resultadoEnvio = enviarCorreoContacto($nombre, $correo, $mensaje);

if ($resultadoEnvio['success']) {
    $_SESSION[$sessionKey] = time();
    $_SESSION['toast_success'] = "Mensaje enviado correctamente. Folio de referencia: {$resultadoEnvio['folio']}";

    error_log("Mensaje de contacto enviado exitosamente - Folio: {$resultadoEnvio['folio']}, De: {$correo}, Nombre: {$nombre}");
} else {
    $_SESSION['toast_error'] = 'Error al enviar el mensaje. Por favor, inténtalo nuevamente.';

    error_log("Error al enviar mensaje de contacto - De: {$correo}, Nombre: {$nombre}");
}

// Redirigir a inicio
header('Location: /inicio');
exit;
?>