<?php
require_once(__DIR__ . '/../../includes/db.php');

session_start();

function validarTelefonoMexicano($telefono) {
    $telefono = preg_replace('/[^0-9]/', '', $telefono);

    if (strlen($telefono) === 10 && preg_match('/^[1-9][0-9]{9}$/', $telefono)) {
        return $telefono;
    }
    
    return false;
}

function generarMensaje($nombre, $curso_nombre, $url, $tiempo_expiracion) {
    $mensaje = "★ *Estimado(a) {$nombre}*\n\n";
    $mensaje .= "Su constancia del curso *\"{$curso_nombre}\"* está lista para descargar.\n\n";
    $mensaje .= "*Enlace de descarga:*\n{$url}\n\n";
    $mensaje .= "*Importante:* Este enlace expira en *{$tiempo_expiracion} minutos*\n\n";
    $mensaje .= "*¿Necesita ayuda?*\n";
    $mensaje .= "   • Responda a este mensaje\n";
    $mensaje .= "   • Llame al (747) 471-9209\n\n";
    $mensaje .= "*Email:* contacto@ejgro.edu.mx\n\n";
    $mensaje .= "_Instituto para el Mejoramiento Judicial_\n";
    $mensaje .= "*Comprometidos con la excelencia académica*";
    
    return $mensaje;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = trim($_POST['nombre_completo'] ?? '');
        $telefono_raw = trim($_POST['telefono'] ?? '');
        $curso_id = (int)($_POST['curso_id'] ?? 0);

        if (empty($nombre) || empty($telefono_raw) || $curso_id <= 0) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        if (strlen($nombre) < 5 || strlen($nombre) > 100) {
            throw new Exception("El nombre debe tener entre 5 y 100 caracteres.");
        }

        $telefono = validarTelefonoMexicano($telefono_raw);
        if (!$telefono) {
            throw new Exception("Ingrese un número de teléfono mexicano válido de 10 dígitos.");
        }

        $stmt = $conn->prepare("
            SELECT c.*, cur.nombre as curso_nombre 
            FROM constancias c 
            INNER JOIN cursos cur ON c.curso_id = cur.id 
            WHERE c.nombre_completo = ? AND c.curso_id = ?
        ");
        $stmt->bind_param("si", $nombre, $curso_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $constancia = $res->fetch_assoc();

        if (!$constancia) {
            throw new Exception("No se encontró una constancia con ese nombre para este curso.");
        }

        if (empty($constancia['telefono'])) {
            $update_stmt = $conn->prepare("UPDATE constancias SET telefono = ? WHERE id = ?");
            $update_stmt->bind_param("si", $telefono, $constancia['id']);
            $update_stmt->execute();
        } elseif ($constancia['telefono'] !== $telefono) {
            throw new Exception("Este nombre ya está vinculado al número " . substr($constancia['telefono'], 0, 3) . "****" . substr($constancia['telefono'], -2) . ". Si es usted, use ese número.");
        }

        $mark_used = $conn->prepare("UPDATE tokens_temporales SET usado = 1 WHERE uuid = ? AND telefono = ? AND usado = 0");
        $mark_used->bind_param("ss", $constancia['uuid'], $telefono);
        $mark_used->execute();

        $token = bin2hex(random_bytes(16));
        $tiempo_expiracion = 15;
        $expira = date("Y-m-d H:i:s", strtotime("+{$tiempo_expiracion} minutes"));
        $uuid = $constancia['uuid'];

        $ins = $conn->prepare("INSERT INTO tokens_temporales (token, uuid, telefono, expira_en, usado) VALUES (?, ?, ?, ?, 0)");
        $ins->bind_param("ssss", $token, $uuid, $telefono, $expira);
        $ins->execute();
        
        $tiempo_restante = $tiempo_expiracion;

        $url = "https://ejgro.edu.mx/descargar?token=$token";
        $mensaje = generarMensaje($nombre, $constancia['curso_nombre'], $url, $tiempo_restante);

        $mensaje = mb_convert_encoding($mensaje, 'UTF-8', 'auto');
        $msg_encoded = urlencode($mensaje);
        $whatsapp_url = "https://wa.me/52{$telefono}?text={$msg_encoded}";

        $_SESSION['whatsapp_data'] = [
            'nombre' => $nombre,
            'telefono' => $telefono,
            'curso' => $constancia['curso_nombre'],
            'url_descarga' => $url,
            'tiempo_expiracion' => $tiempo_restante,
            'whatsapp_url' => $whatsapp_url,
            'mensaje' => $mensaje
        ];

        header("Location: /views/public/confirmar_envio_whatsapp.php");
        exit;

    } catch (Exception $e) {
        $_SESSION['toast_error'] = $e->getMessage();
        header("Location: /index.php");
        exit;
    }
}

$_SESSION['toast_error'] = "Acceso inválido.";
header("Location: /index.php");
exit;
?>