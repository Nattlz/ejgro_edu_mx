<?php
require_once '../../includes/db.php';

$id = $_POST['id'] ?? null;
$estado = $_POST['estado'] ?? '';

if (!$id || !$estado) {
    die('Datos incompletos.');
}

if (isset($_FILES['flyer']) && $_FILES['flyer']['error'] === UPLOAD_ERR_OK) {
    $nombre_archivo = uniqid('flyer_') . '.' . pathinfo($_FILES['flyer']['name'], PATHINFO_EXTENSION);
    $directorio = '../../uploads/flyers/';
    if (!is_dir($directorio)) mkdir($directorio, 0755, true);
    $ruta = $directorio . $nombre_archivo;

    if (!move_uploaded_file($_FILES['flyer']['tmp_name'], $ruta)) {
        die('Error al guardar el nuevo flyer.');
    }

    $flyer = 'uploads/flyers/' . $nombre_archivo;

    $stmt = $conn->prepare("UPDATE pub_cursos SET estado = ?, flyer = ? WHERE id = ?");
    $stmt->bind_param("ssi", $estado, $flyer, $id);
} else {
    $stmt = $conn->prepare("UPDATE pub_cursos SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $estado, $id);
}

$stmt->execute();

header("Location: /dashboard.php?seccion=ver_publicaciones&msg=pub_actualizada");
exit;
