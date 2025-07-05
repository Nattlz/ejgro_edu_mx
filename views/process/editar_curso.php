<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $asistencia = $_POST['asistencia'];
    $nombre = $_POST['nombre'];
    $fecha = $_POST['fecha'];
    $horas = $_POST['horas'];
    $lugar = $_POST['lugar'];

    $stmt = $conn->prepare("UPDATE cursos SET asistencia = ?, nombre = ?, fecha_imparticion = ?, horas = ?, lugar = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $asistencia, $nombre, $fecha, $horas, $lugar, $id);
    $stmt->execute();
}

header("Location: ../../dashboard.php?seccion=agregar_curso&msg=editado");
exit;
