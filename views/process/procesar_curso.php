<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/db.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asistencia = $_POST['asistencia'] ?? '';
    $nombre     = $_POST['nombre'] ?? '';
    $fecha      = $_POST['fecha'] ?? '';
    $horas      = $_POST['horas'] ?? '';
    $lugar      = $_POST['lugar'] ?? '';

    if ($asistencia && $nombre && $fecha && $lugar) {
        $stmt = $conn->prepare("INSERT INTO cursos (asistencia, nombre, fecha_imparticion, horas, lugar) VALUES (?, ?, ?, ?, ?)");

        if (!$stmt) {
            die("Error en prepare(): " . $conn->error);
        }

        $stmt->bind_param("sssss", $asistencia, $nombre, $fecha, $horas, $lugar);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Location: ../../dashboard.php?seccion=agregar_curso&msg=agregado");
            exit;
        } else {
            die("Error al insertar: " . $stmt->error);
        }
    } else {
        die("Faltan campos obligatorios.");
    }
}

die("Acceso no válido.");