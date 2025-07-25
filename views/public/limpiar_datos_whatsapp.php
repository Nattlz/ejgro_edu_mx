<?php
// Iniciar sesión solo si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Establecer headers de respuesta
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Limpiar datos de WhatsApp de la sesión
    if (isset($_SESSION['whatsapp_data'])) {
        unset($_SESSION['whatsapp_data']);
        $mensaje = 'Datos de WhatsApp limpiados correctamente';
        $status = 'success';
    } else {
        $mensaje = 'No había datos de WhatsApp para limpiar';
        $status = 'info';
    }

    // Respuesta exitosa
    echo json_encode([
        'status' => $status,
        'message' => $mensaje,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    // Respuesta de error
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al limpiar datos: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>