<?php
require_once 'includes/db.php';
session_start();

$token = $_GET['token'] ?? '';

// Validar formato del token (hex 32 caracteres)
if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
    $_SESSION['toast_error'] = "Token no válido.";
    header("Location: /inicio");
    exit;
}

// Buscar el token
$stmt = $conn->prepare("SELECT * FROM tokens_temporales WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// Validar existencia del token
if (!$data) {
    $_SESSION['toast_error'] = "Token no encontrado.";
    header("Location: /inicio");
    exit;
}

// Validar expiración
if (strtotime($data['expira_en']) < time()) {
    $_SESSION['toast_error'] = "Este enlace ha expirado.";
    header("Location: /inicio");
    exit;
}

// Validar si ya fue usado
if ($data['usado']) {
    $_SESSION['toast_error'] = "Este enlace ya fue utilizado.";
    header("Location: /inicio");
    exit;
}

// Buscar constancia con UUID y teléfono del token
$stmt2 = $conn->prepare("SELECT * FROM constancias WHERE uuid = ? AND telefono = ?");
$stmt2->bind_param("ss", $data['uuid'], $data['telefono']);
$stmt2->execute();
$constancia = $stmt2->get_result()->fetch_assoc();

if (!$constancia) {
    $_SESSION['toast_error'] = "No se encontró la constancia.";
    header("Location: /inicio");
    exit;
}

// ✅ SOLO AHORA marcamos el token como usado
$update = $conn->prepare("UPDATE tokens_temporales SET usado = 1 WHERE id = ?");
$update->bind_param("i", $data['id']);
$update->execute();

// Redirigir al cotejo
header("Location: /cotejo/" . $data['uuid']);
exit;
