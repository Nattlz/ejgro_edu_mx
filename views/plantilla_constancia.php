<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id) {
    die('ID de constancia no proporcionado.');
}

$stmt = $conn->prepare("
    SELECT c.*, cu.nombre AS nombre_curso, cu.fecha_imparticion 
    FROM constancias c 
    JOIN cursos cu ON c.curso_id = cu.id 
    WHERE c.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    die('Constancia no encontrada.');
}

$plantillaPath = __DIR__ . '/../templates/plantilla_v0.html';
if (!file_exists($plantillaPath)) {
    die('Plantilla no encontrada.');
}

$html = file_get_contents($plantillaPath);

$reemplazos = [
    '{{nombre_completo}}'     => strtoupper($data['nombre_completo']),
    '{{correo}}'              => $data['correo'],
    '{{nombre_curso}}'        => $data['nombre_curso'],
    '{{fecha}}'               => date('d/m/Y', strtotime($data['fecha_imparticion'])),
    '{{numero_certificado}}'  => $data['numero_certificado'],
    '{{uuid}}'                => $data['uuid']
];

$html = strtr($html, $reemplazos);

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'landscape');
$dompdf->render();

$dompdf->stream('Constancia_' . $data['nombre_completo'] . '.pdf', ['Attachment' => false]);