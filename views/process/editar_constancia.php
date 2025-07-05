<?php
require_once(__DIR__ . '/../../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $curso_id = isset($_POST['curso_id']) ? (int) $_POST['curso_id'] : 0;

    if ($id > 0 && $nombre !== '' && $correo !== '' && $curso_id > 0) {
        $stmt = $conn->prepare("UPDATE constancias SET nombre_completo = ?, correo = ?, curso_id = ? WHERE id = ?");
        $stmt->bind_param("ssii", $nombre, $correo, $curso_id, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Location: ../../dashboard.php?seccion=constancias_generadas&msg=const_editado");
        } else {
            header("Location: ../../dashboard.php?seccion=constancias_generadas&msg=const_noupdate");
        }
        exit;
    } else {
        header("Location: ../../dashboard.php?seccion=constancias_generadas&msg=const_vacio");
        exit;
    }
}

header("Location: ../../dashboard.php?seccion=constancias_generadas&msg=const_metodo");
exit;
