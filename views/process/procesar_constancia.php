<?php
require_once(__DIR__ . '/../../includes/auth.php');
require_once(__DIR__ . '/../../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $curso_id = $_POST['curso_id'] ?? 0;

    if ($nombre && $correo && $curso_id) {
        $ultimo = $conn->query("SELECT MAX(id) AS max_id FROM constancias")->fetch_assoc()['max_id'] ?? 0;
        $nuevo_id = (int)$ultimo + 1;

        $numero_certificado = str_pad('100000000000000000' . $nuevo_id, 20, '0', STR_PAD_LEFT);

        $uuid = strtoupper(sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        ));

        $stmt = $conn->prepare("INSERT INTO constancias (nombre_completo, correo, curso_id, numero_certificado, uuid) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $nombre, $correo, $curso_id, $numero_certificado, $uuid);
        $stmt->execute();

        header("Location: /dashboard.php?seccion=generar_constancia&msg=const_creada");
        exit;
    } else {
        header("Location: /dashboard.php?seccion=generar_constancia&msg=const_vacio");
        exit;
    }
} else {
    header("Location: /dashboard.php");
    exit;
}