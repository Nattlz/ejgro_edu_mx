<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$busqueda = isset($_GET['buscar']) ? $conn->real_escape_string($_GET['buscar']) : '';
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

$condiciones = [];

if ($busqueda !== '') {
    $condiciones[] = "nombre LIKE '%$busqueda%'";
}
if ($fecha_inicio !== '') {
    $condiciones[] = "DATE(creado_en) >= '$fecha_inicio'";
}
if ($fecha_fin !== '') {
    $condiciones[] = "DATE(creado_en) <= '$fecha_fin'";
}

$whereClause = count($condiciones) > 0 ? 'WHERE ' . implode(' AND ', $condiciones) : '';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=cursos_filtrados.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>";
echo "<tr>
        <th>Asistencia</th>
        <th>Nombre</th>
        <th>Impartido el</th>
        <th>Horas</th>
        <th>Lugar</th>
        <th>Fecha de Registro</th>
      </tr>";

$sql = "SELECT * FROM cursos $whereClause ORDER BY creado_en DESC";
$result = $conn->query($sql);

while ($curso = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($curso['asistencia']) . "</td>";
    echo "<td>" . htmlspecialchars($curso['nombre']) . "</td>";
    echo "<td>" . htmlspecialchars($curso['fecha_imparticion']) . "</td>";
    echo "<td>" . htmlspecialchars($curso['horas']) . "</td>";
    echo "<td>" . htmlspecialchars($curso['lugar']) . "</td>";
    echo "<td>" . date('d/m/Y H:i', strtotime($curso['creado_en'])) . "</td>";
    echo "</tr>";
}

echo "</table>";
exit;