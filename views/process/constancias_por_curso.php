<?php
require_once '../../includes/db.php';

$curso_id = isset($_GET['curso_id']) ? (int) $_GET['curso_id'] : 0;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$resultado = [
    'datos' => [],
    'total' => 0,
    'pagina' => $page,
    'por_pagina' => $limit
];

if ($curso_id > 0) {
    $total = $conn->query("SELECT COUNT(*) as total FROM constancias WHERE curso_id = $curso_id")
        ->fetch_assoc()['total'];
    $resultado['total'] = $total;

    $stmt = $conn->prepare("SELECT nombre_completo, correo, generado_en FROM constancias WHERE curso_id = ? ORDER BY generado_en DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("iii", $curso_id, $limit, $offset);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($fila = $res->fetch_assoc()) {
        $resultado['datos'][] = [
            'nombre_completo' => $fila['nombre_completo'],
            'correo' => $fila['correo'],
            'fecha' => date('Y-m-d H:i', strtotime($fila['generado_en']))
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($resultado);
