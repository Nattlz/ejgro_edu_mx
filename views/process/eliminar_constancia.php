<?php
require_once(__DIR__ . '/../../includes/db.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM constancias WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: ../../dashboard.php?seccion=constancias_generadas&msg=const_eliminado");
    } else {
        header("Location: ../../dashboard.php?seccion=constancias_generadas&msg=const_noexiste");
    }
    exit;
} else {
    header("Location: ../../dashboard.php?seccion=constancias_generadas&msg=const_idinvalido");
    exit;
}