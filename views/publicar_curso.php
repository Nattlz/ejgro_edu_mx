<?php
require_once(__DIR__ . '/../includes/auth.php');
require_once(__DIR__ . '/../includes/db.php');

$cursos = $conn->query("SELECT id, nombre FROM cursos ORDER BY creado_en DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Publicar Curso</title>
    <link rel="stylesheet" href="/assets/css/st_views.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="generar-form">

        <form action="views/process/guardar_publicacion.php" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-column">
                    <div class="form-group2">
                        <label for="curso_id">Curso:</label>
                        <select name="curso_id" id="curso_id" required>
                            <option value="">Selecciona un curso</option>
                            <?php foreach ($cursos as $curso): ?>
                                <option value="<?= $curso['id'] ?>"><?= htmlspecialchars($curso['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group2">
                        <label for="estado">Estado:</label>
                        <select name="estado" id="estado" required>
                            <option value="activo">Activo</option>
                            <option value="proximo">Próximo</option>
                            <option value="finalizado">Finalizado</option>
                        </select>
                    </div>
                </div>

                <div class="form-group2">
                    <label for="flyer">Flyer (JPG/PNG):</label>
                    <input type="file" name="flyer" id="flyer" accept="image/*" required onchange="vistaPreviaFlyer(event)">
                    <div id="previewFlyer" class="flyer-container" style="margin-top: 10px; display: none; cursor: pointer;" onclick="mostrarModalFlyer(document.getElementById('previewImg').src)">
                        <img id="previewImg" src="#" alt="Vista previa del flyer">
                    </div>
                </div>
            </div>

            <div class="boton-generar">
                <button type="submit" class="btn-generar">
                    <i class="fas fa-upload"></i> Publicar Curso
                </button>
            </div>
        </form>
    </div>

    <div id="flyerModal" class="modal-flyer" style="display: none;">
        <div class="modal-flyer-contenido">
            <span class="cerrar-modal" onclick="cerrarModalFlyer()">&times;</span>
            <img id="flyerModalImg" src="#" alt="Vista previa completa" class="modal-flyer-img">
        </div>
    </div>

    <script src="/assets/js/sc_publicacion.js"></script>
</body>

</html>