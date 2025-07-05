<?php
require_once(__DIR__ . '/../../includes/db.php');

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_completo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $curso_id = (int)($_POST['curso_id'] ?? 0);

    if (!$nombre || !$telefono || !$curso_id) {
        $_SESSION['toast_error'] = "Faltan datos requeridos.";
        header("Location: /index.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM constancias WHERE nombre_completo = ? AND curso_id = ?");
    $stmt->bind_param("si", $nombre, $curso_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $constancia = $res->fetch_assoc();

    if (!$constancia) {
        $_SESSION['toast_error'] = "No se encontro una constancia con ese nombre para este curso.";
        header("Location: /index.php");
        exit;
    }

    if (empty($constancia['telefono'])) {
        $up = $conn->prepare("UPDATE constancias SET telefono = ? WHERE id = ?");
        $up->bind_param("si", $telefono, $constancia['id']);
        $up->execute();
    } elseif ($constancia['telefono'] !== $telefono) {
        $_SESSION['toast_error'] = "Este nombre ya esta vinculado a otro numero.";
        header("Location: /index.php");
        exit;
    }

    $token = bin2hex(random_bytes(16));
    $expira = date("Y-m-d H:i:s", strtotime("+15 minutes"));
    $uuid = $constancia['uuid'];

    // Insertar el token con 'usado' fijo en 0 directamente en la consulta
    $ins = $conn->prepare("INSERT INTO tokens_temporales (token, uuid, telefono, expira_en, usado) VALUES (?, ?, ?, ?, 0)");
    $ins->bind_param("ssss", $token, $uuid, $telefono, $expira);
    $ins->execute();

    // Enviar mensaje por WhatsApp
    $url = "https://ejgro.edu.mx/descargar?token=$token";
    $msg = "*Estimado(a)* $nombre:\n\nLa solicitud para obtener su constancia ha sido procesada exitosamente. Puede descargarla a través del siguiente enlace:\n\n$url\n\nAgradecemos su interés y participación.\n\n*Atentamente*,\n*Instituto para el Mejoramiento Judicial*";
    $msg_encoded = urlencode($msg);

    header("Location: https://wa.me/52$telefono?text=$msg_encoded");
    exit;
}

$_SESSION['toast_error'] = "Acceso invalido.";
header("Location: /index.php");
die("Acceso inválido.");
