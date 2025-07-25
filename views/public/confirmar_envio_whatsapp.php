<?php
// Debug - eliminar después de solucionar
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión solo si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Constancia por WhatsApp - EJGRO</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="20" cy="80" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .whatsapp-icon {
            font-size: 4rem;
            margin-bottom: 10px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .content {
            padding: 30px;
        }

        .user-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid #25D366;
        }

        .info-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-row i {
            width: 25px;
            color: #666;
            margin-right: 10px;
        }

        .info-label {
            font-weight: 600;
            color: #333;
            margin-right: 8px;
        }

        .info-value {
            color: #666;
            flex: 1;
        }

        .message-preview {
            background: #e8f5e8;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border: 2px dashed #25D366;
            position: relative;
        }

        .message-preview h3 {
            color: #128C7E;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .message-preview h3 i {
            margin-right: 8px;
        }

        .message-text {
            background: white;
            padding: 15px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            white-space: pre-line;
            max-height: 200px;
            overflow-y: auto;
        }

        .actions {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .btn {
            flex: 1;
            padding: 15px 20px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-whatsapp {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
        }

        .btn-whatsapp:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 211, 102, 0.3);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #666;
            border: 2px solid #dee2e6;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .timer {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 15px;
        }

        .timer strong {
            color: #dc3545;
        }

        .instructions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }

        .instructions h4 {
            color: #856404;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .instructions h4 i {
            margin-right: 8px;
        }

        .instructions ol {
            color: #856404;
            padding-left: 20px;
        }

        .instructions li {
            margin-bottom: 5px;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            text-align: center;
        }

        @media (max-width: 600px) {
            .actions {
                flex-direction: column;
            }
            
            .content {
                padding: 20px;
            }
            
            .header {
                padding: 20px;
            }
            
            .whatsapp-icon {
                font-size: 3rem;
            }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .loading-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #25D366;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <?php
    // Iniciar sesión solo si no está activa
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar que existan los datos de WhatsApp
    if (!isset($_SESSION['whatsapp_data'])) {
        ?>
        <div class="container">
            <div class="header">
                <i class="fas fa-exclamation-triangle whatsapp-icon"></i>
                <h1>Sesión Expirada</h1>
                <p>Los datos de la constancia no están disponibles</p>
            </div>
            <div class="content">
                <div class="error-message">
                    <i class="fas fa-info-circle"></i>
                    La sesión ha expirado o no se encontraron datos válidos.
                </div>
                <div class="actions">
                    <a href="/index.php" class="btn btn-secondary">
                        <i class="fas fa-home"></i>
                        Volver al inicio
                    </a>
                </div>
            </div>
        </div>
        <?php
        exit;
    }
    
    $data = $_SESSION['whatsapp_data'];
    ?>

    <div class="container">
        <div class="header">
            <i class="fab fa-whatsapp whatsapp-icon"></i>
            <h1>Constancia Lista</h1>
            <p>Su constancia será enviada por WhatsApp</p>
        </div>

        <div class="content">
            <div class="user-info">
                <div class="info-row">
                    <i class="fas fa-user"></i>
                    <span class="info-label">Nombre:</span>
                    <span class="info-value"><?= htmlspecialchars($data['nombre']) ?></span>
                </div>
                <div class="info-row">
                    <i class="fas fa-phone"></i>
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value">+52 <?= htmlspecialchars($data['telefono']) ?></span>
                </div>
                <div class="info-row">
                    <i class="fas fa-graduation-cap"></i>
                    <span class="info-label">Curso:</span>
                    <span class="info-value"><?= htmlspecialchars($data['curso']) ?></span>
                </div>
                <div class="info-row">
                    <i class="fas fa-clock"></i>
                    <span class="info-label">Válido por:</span>
                    <span class="info-value"><?= $data['tiempo_expiracion'] ?> minutos</span>
                </div>
            </div>

            <div class="message-preview">
                <h3>
                    <i class="fas fa-eye"></i>
                    Vista previa del mensaje
                </h3>
                <div class="message-text"><?= htmlspecialchars($data['mensaje']) ?></div>
            </div>

            <div class="actions">
                <a href="<?= htmlspecialchars($data['whatsapp_url']) ?>" 
                   class="btn btn-whatsapp" 
                   target="_blank"
                   onclick="showLoading()">
                    <i class="fab fa-whatsapp"></i>
                    Enviar por WhatsApp
                </a>
                <a href="/index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Volver
                </a>
            </div>

            <div class="timer">
                <i class="fas fa-hourglass-half"></i>
                Este enlace expira en <strong><?= $data['tiempo_expiracion'] ?> minutos</strong>
            </div>

            <div class="instructions">
                <h4>
                    <i class="fas fa-info-circle"></i>
                    Instrucciones:
                </h4>
                <ol>
                    <li>Haga clic en "Enviar por WhatsApp"</li>
                    <li>Se abrirá WhatsApp con el mensaje listo</li>
                    <li>Presione "Enviar" en WhatsApp</li>
                    <li>Recibirá el enlace para descargar su constancia</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <h3>Abriendo WhatsApp...</h3>
            <p>Preparando su mensaje</p>
        </div>
    </div>

    <script>
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            // Ocultar después de 3 segundos
            setTimeout(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
            }, 3000);
        }

        // Limpiar datos de sesión después de 10 minutos
        setTimeout(() => {
            fetch('/views/public/limpiar_datos_whatsapp.php', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            }).catch(err => console.log('Error limpiando sesión:', err));
        }, 600000); // 10 minutos

        // Agregar efecto visual al copiar enlace
        document.addEventListener('DOMContentLoaded', function() {
            const messageText = document.querySelector('.message-text');
            if (messageText) {
                messageText.addEventListener('click', function() {
                    // Seleccionar texto
                    const range = document.createRange();
                    range.selectNode(this);
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(range);
                    
                    // Mostrar feedback
                    this.style.background = '#d4edda';
                    setTimeout(() => {
                        this.style.background = 'white';
                        window.getSelection().removeAllRanges();
                    }, 1000);
                });
            }
        });
    </script>
</body>
</html>