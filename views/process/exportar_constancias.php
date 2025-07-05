<?php
require_once(__DIR__ . '/../../includes/db.php');
require_once(__DIR__ . '/../../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$where = [];

if (!empty($_GET['buscar_nombre'])) {
    $nombre = $conn->real_escape_string($_GET['buscar_nombre']);
    $where[] = "c.nombre_completo LIKE '%$nombre%'";
}
if (!empty($_GET['curso_id'])) {
    $curso_id = (int)$_GET['curso_id'];
    $where[] = "c.curso_id = $curso_id";
}
if (!empty($_GET['fecha_inicio'])) {
    $fecha_inicio = $conn->real_escape_string($_GET['fecha_inicio']);
    $where[] = "DATE(c.generado_en) >= '$fecha_inicio'";
}
if (!empty($_GET['fecha_fin'])) {
    $fecha_fin = $conn->real_escape_string($_GET['fecha_fin']);
    $where[] = "DATE(c.generado_en) <= '$fecha_fin'";
}

$condicion = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "
SELECT c.nombre_completo, cu.nombre AS curso, c.uuid, c.generado_en
FROM constancias c
JOIN cursos cu ON c.curso_id = cu.id
$condicion
ORDER BY c.generado_en DESC
";

$result = $conn->query($query);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Constancias');

$headers = ['Nombre completo', 'Curso', 'Enlace de Verificación', 'Generado en'];
$sheet->fromArray($headers, NULL, 'A1');

$fila = 2;
while ($row = $result->fetch_assoc()) {
    $link = "https://ejgro.edu.mx/cotejo/" . $row['uuid'];
    $sheet->setCellValue("A$fila", $row['nombre_completo']);
    $sheet->setCellValue("B$fila", $row['curso']);
    $sheet->setCellValue("C$fila", $link);
    $sheet->setCellValue("D$fila", $row['generado_en']);
    $fila++;
}

foreach (range('A', 'D') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = 'constancias_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;