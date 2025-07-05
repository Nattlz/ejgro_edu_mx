<?php
require_once '../../includes/db.php';

$id = $_GET['id'] ?? null;
if (!$id) die("ID no proporcionado.");

$stmt = $conn->prepare("DELETE FROM pub_cursos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: /dashboard.php?seccion=ver_publicaciones&msg=pub_eliminada");
exit;
