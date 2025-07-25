<?php
require_once 'includes/db.php';
session_start();

$token = $_GET['token'] ?? '';

if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
    $_SESSION['toast_error'] = "Token no válido.";
    header("Location: /inicio");
    exit;
}

// Verificar si es un bot (ej. WhatsApp, Facebook, etc.)
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isBot = preg_match('/(WhatsApp|facebookexternalhit|Facebot|Twitterbot|Slackbot|Discordbot|TelegramBot|Googlebot)/i', $userAgent);

if ($isBot) {
    // Evitar que bots consuman el token
    http_response_code(204); // No Content
    exit;
}

// Buscar el token
$stmt = $conn->prepare("SELECT * FROM tokens_temporales WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    $_SESSION['toast_error'] = "Token no encontrado.";
    header("Location: /inicio");
    exit;
}

if (strtotime($data['expira_en']) < time()) {
    $_SESSION['toast_error'] = "Este enlace ha expirado.";
    header("Location: /inicio");
    exit;
}

if ($data['usado']) {
    $_SESSION['toast_error'] = "Este enlace ya fue utilizado.";
    header("Location: /inicio");
    exit;
}

$stmt2 = $conn->prepare("SELECT * FROM constancias WHERE uuid = ? AND telefono = ?");
$stmt2->bind_param("ss", $data['uuid'], $data['telefono']);
$stmt2->execute();
$constancia = $stmt2->get_result()->fetch_assoc();

if (!$constancia) {
    $_SESSION['toast_error'] = "No se encontró la constancia.";
    header("Location: /inicio");
    exit;
}

// No marcar como usado aquí aún
// Redirigir al cotejo (donde SÍ se marca usado al final del render)
header("Location: /cotejo/" . $data['uuid']);
exit;
