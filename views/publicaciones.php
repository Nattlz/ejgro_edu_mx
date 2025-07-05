<?php
require_once(__DIR__ . '/../includes/auth.php');
require_once(__DIR__ . '/../includes/db.php');

$where = [];
$condicion = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$por_pagina = 10;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$inicio = ($pagina - 1) * $por_pagina;

$total_resultado = $conn->query("SELECT COUNT(*) AS total FROM pub_cursos $condicion")->fetch_assoc()['total'];
$total_paginas = ceil($total_resultado / $por_pagina);

$stmt = $conn->query("
  SELECT p.id, c.nombre AS nombre_curso, p.flyer, p.estado, p.creado_en
  FROM pub_cursos p
  JOIN cursos c ON p.curso_id = c.id
  $condicion
  ORDER BY p.creado_en DESC
  LIMIT $inicio, $por_pagina
");

$publicaciones = $stmt ? $stmt->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Publicaciones</title>
    <link rel="stylesheet" href="/assets/css/st_views.css">
</head>

<body>
    <div class="table-panel">
        <p class="resumen">Total de publicaciones: <?= count($publicaciones) ?></p>

        <div class="tabla-container">
            <table class="table-cursos">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Curso</th>
                        <th>Flyer</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;
                    foreach ($publicaciones as $pub): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($pub['nombre_curso']) ?></td>
                            <td>
                                <div class="flyer-container" onclick="mostrarModalFlyer('<?= $pub['flyer'] ?>')" title="Ver flyer">
                                    <img src="<?= $pub['flyer'] ?>" alt="Flyer del curso">
                                </div>
                            </td>
                            <td><span class="estado <?= $pub['estado'] ?>"><?= ucfirst($pub['estado']) ?></span></td>
                            <td><?= date("d/m/Y H:i", strtotime($pub['creado_en'])) ?></td>
                            <td>
                                <button class="btn-action" onclick='abrirModalEditar(<?= json_encode($pub) ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-action" onclick="abrirModalEliminar(<?= $pub['id'] ?>)">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="paginacion">
            <?php
            function mostrarPagina($i, $pagina)
            {
                $params = $_GET;
                $params['pagina'] = $i;
                $url = 'dashboard.php?' . http_build_query(array_merge($params, ['seccion' => 'ver_publicaciones']));
                $clase = $i === $pagina ? 'activo' : '';
                echo "<a href='" . htmlspecialchars($url) . "' class='$clase'>$i</a>";
            }

            $rango = 2;
            $mostrar_puntos = false;

            // Anterior
            if ($pagina > 1) {
                $params['pagina'] = $pagina - 1;
                echo "<a href='" . htmlspecialchars('dashboard.php?' . http_build_query(array_merge($_GET, ['seccion' => 'ver_publicaciones', 'pagina' => $pagina - 1]))) . "' class='prev-next'>&laquo; Anterior</a>";
            } else {
                echo "<span class='prev-next disabled'>&laquo; Anterior</span>";
            }

            for ($i = 1; $i <= $total_paginas; $i++) {
                if ($i <= 2 || $i > $total_paginas - 2 || abs($i - $pagina) <= $rango) {
                    mostrarPagina($i, $pagina);
                    $mostrar_puntos = true;
                } elseif ($mostrar_puntos) {
                    echo "<span class='puntos'>...</span>";
                    $mostrar_puntos = false;
                }
            }

            // Siguiente
            if ($pagina < $total_paginas) {
                echo "<a href='" . htmlspecialchars('dashboard.php?' . http_build_query(array_merge($_GET, ['seccion' => 'ver_publicaciones', 'pagina' => $pagina + 1]))) . "' class='prev-next'>Siguiente &raquo;</a>";
            } else {
                echo "<span class='prev-next disabled'>Siguiente &raquo;</span>";
            }
            ?>
        </div>
    </div>

    <div id="modalEditar" class="modal-eliminar">
        <div class="modal-contenido" style="text-align: left; max-width: 500px;">
            <h3 style="text-align: center;">Editar Publicación</h3>
            <form method="POST" action="views/process/actualizar_publicacion.php" enctype="multipart/form-data" class="formulario-modal">
                <input type="hidden" name="id" id="edit_id">

                <div class="form-group">
                    <label for="edit_estado">Estado:</label>
                    <select name="estado" id="edit_estado" class="input-normal" required>
                        <option value="activo">Activo</option>
                        <option value="proximo">Próximo</option>
                        <option value="finalizado">Finalizado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_flyer">Nuevo flyer (opcional):</label>
                    <input type="file" name="flyer" id="edit_flyer" class="input-normal" accept="image/*">
                </div>

                <div class="modal-botones">
                    <button type="submit" class="btn-confirmar">Guardar</button>
                    <button type="button" class="btn-cancelar" onclick="cerrarModalEditar()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="flyerModal" class="modal-flyer" style="display: none;">
        <div class="modal-flyer-contenido">
            <span class="cerrar-modal" onclick="cerrarModalFlyer()">&times;</span>
            <img id="flyerModalImg" src="" alt="Vista previa" class="modal-flyer-img">
        </div>
    </div>

    <div id="modalEliminar" class="modal-eliminar">
        <div class="modal-contenido">
            <h3>¿Deseas eliminar esta publicación?</h3>
            <form method="GET" action="views/process/eliminar_publicacion.php">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-botones">
                    <button type="submit" class="btn-confirmar">Eliminar</button>
                    <button type="button" class="btn-cancelar" onclick="cerrarModalEliminar()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="flyerModal" class="modal-eliminar" style="display: none; align-items: center; justify-content: center;">
        <div class="modal-contenido" style="max-width: 90%; max-height: 90%; text-align: center; position: relative;">
            <span onclick="cerrarModalFlyer()" style="position: absolute; top: 10px; right: 15px; font-size: 28px; cursor: pointer;">&times;</span>
            <img id="flyerModalImg" src="" alt="Flyer Ampliado" style="max-width: 100%; max-height: 100%;">
        </div>
    </div>

    <script src="/assets/js/sc_publicacion.js"></script>
</body>

</html>