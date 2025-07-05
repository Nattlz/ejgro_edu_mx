<?php
require_once(__DIR__ . '/../../includes/db.php');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT asistencia, nombre, fecha_imparticion, horas, lugar FROM cursos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($curso = $resultado->fetch_assoc()) {
        echo json_encode($curso);
    } else {
        echo json_encode([]);
    }
}