<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once 'includes/db.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;

function ajustarClaseNombre($texto, $largoNormal = 32, $largoMaximo = 38)
{
    $longitud = mb_strlen($texto, 'UTF-8');
    if ($longitud > $largoMaximo) return 'nombre-xs';
    if ($longitud > $largoNormal) return 'nombre-sm';
    return '';
}

$uuid = $_GET['uuid'] ?? '';
if (!$uuid) {
    $_SESSION['toast_error'] = "UUID no proporcionado.";
    header("Location: /index.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        c.nombre_completo, 
        c.correo, 
        c.numero_certificado, 
        c.uuid, 
        c.generado_en,
        cu.asistencia,
        cu.nombre AS nombre,
        cu.fecha_imparticion,
        cu.horas,
        cu.lugar,
        cu.plantilla_nombre
    FROM constancias c 
    JOIN cursos cu ON c.curso_id = cu.id 
    WHERE c.uuid = ?
");
$stmt->bind_param("s", $uuid);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    $_SESSION['toast_error'] = "Constancia no encontrada.";
    header("Location: /index.php");
    exit;
}

$plantillaArchivo = $data['plantilla_nombre'] ?? 'plantilla.html';
$rutaPlantilla = __DIR__ . "/templates/{$plantillaArchivo}";

if (!file_exists($rutaPlantilla)) {
    $_SESSION['toast_error'] = "Plantilla HTML no encontrada: {$plantillaArchivo}";
    header("Location: /index.php");
    exit;
}

$html = file_get_contents($rutaPlantilla);

$nombrePlantilla = pathinfo($plantillaArchivo, PATHINFO_FILENAME);
$imagePath = __DIR__ . "/templates/{$nombrePlantilla}_archivos/image001.png";

if (!file_exists($imagePath)) {
    $_SESSION['toast_error'] = "Imagen de fondo no encontrada para la plantilla.";
    header("Location: /index.php");
    exit;
}

$imgMime = mime_content_type($imagePath);
$imgData = base64_encode(file_get_contents($imagePath));
$imgBase64 = "data:$imgMime;base64,$imgData";

$firmaPath = __DIR__ . "/templates/firmas/firma_emf.png";
if (file_exists($firmaPath)) {
    $firmaMime = mime_content_type($firmaPath);
    $firmaData = base64_encode(file_get_contents($firmaPath));
    $firmaBase64 = "data:$firmaMime;base64,$firmaData";
} else {
    $firmaBase64 = '';
}

try {
    if (class_exists(\Endroid\QrCode\Builder\Builder::class)) {
        $builder = \Endroid\QrCode\Builder\Builder::create()
            ->writer(new \Endroid\QrCode\Writer\PngWriter())
            ->data("https://ejgro.edu.mx/cotejo/{$data['uuid']}")
            ->size(200)
            ->margin(2)
            ->build();

        $qrBase64 = 'data:image/png;base64,' . base64_encode($builder->getString());
    } elseif (method_exists(QrCode::class, 'writeDataUri')) {
        $qrCode = new QrCode("https://ejgro.edu.mx/cotejo/{$data['uuid']}");
        $qrBase64 = $qrCode->writeDataUri();
    } else {
        $qrBase64 = '';
    }
} catch (Exception $e) {
    $qrBase64 = '';
}

$claseNombreCompleto = ajustarClaseNombre($data['nombre_completo']);

$html = strtr($html, [
    '{{nombre_completo}}'    => mb_strtoupper($data['nombre_completo'], 'UTF-8'),
    '{{clase_nombre}}'       => $claseNombreCompleto,
    '{{correo}}'             => $data['correo'],
    '{{nombre}}'             => mb_strtoupper($data['nombre'], 'UTF-8'),
    '{{asistencia}}'         => $data['asistencia'],
    '{{fecha}}'              => $data['fecha_imparticion'],
    '{{generado_en}}'        => $data['generado_en'],
    '{{horas}}'              => $data['horas'],
    '{{lugar}}'              => $data['lugar'],
    '{{numero_certificado}}' => $data['numero_certificado'],
    '{{uuid}}'               => $data['uuid'],
    '{{fondo_base64}}'       => $imgBase64,
    '{{firma_base64}}'       => $firmaBase64,
    '{{qr_base64}}'          => $qrBase64
]);

$orientacion = (str_contains(strtolower($plantillaArchivo), 'vertical')) ? 'portrait' : 'landscape';

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', $orientacion);
$dompdf->render();

// ✅ Marcar token como usado solo si existe uno activo para este UUID y aún no usado
$marcar = $conn->prepare("UPDATE tokens_temporales SET usado = 1 WHERE uuid = ? AND usado = 0");
$marcar->bind_param("s", $uuid);
$marcar->execute();

// Evitar cache
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

// Limpiar nombre de archivo
$nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', $data['nombre_completo']);
$dompdf->stream("Constancia_{$nombreLimpio}.pdf", ['Attachment' => false]);
exit;