<?php
require_once '../../includes/db.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT asistencia, nombre, fecha_imparticion, horas, lugar FROM cursos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();
echo json_encode($resultado->fetch_assoc());
