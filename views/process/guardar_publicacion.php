<?php
require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $curso_id = $_POST['curso_id'];
    $estado = $_POST['estado'];

    if (!isset($_FILES['flyer']) || $_FILES['flyer']['error'] !== UPLOAD_ERR_OK) {
        die('Error al subir el flyer.');
    }

    $directorio = '../../uploads/flyers/';
    if (!is_dir($directorio)) mkdir($directorio, 0755, true);

    $nombre_archivo = uniqid('flyer_') . '.' . pathinfo($_FILES['flyer']['name'], PATHINFO_EXTENSION);
    $ruta_completa = $directorio . $nombre_archivo;

    if (!move_uploaded_file($_FILES['flyer']['tmp_name'], $ruta_completa)) {
        die('No se pudo guardar el archivo.');
    }

    $flyer_path = 'uploads/flyers/' . $nombre_archivo;

    $stmt = $conn->prepare("INSERT INTO pub_cursos (curso_id, flyer, estado) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $curso_id, $flyer_path, $estado);
    $stmt->execute();

    header("Location: /dashboard.php?seccion=publicar_curso&msg=pub_creada");
    exit;
}