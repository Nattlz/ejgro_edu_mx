<?php
require_once(__DIR__ . '/../includes/auth.php');
require_once(__DIR__ . '/../includes/db.php');

$items_por_pagina = 10;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * $items_por_pagina;

$busqueda = isset($_GET['buscar']) ? $conn->real_escape_string($_GET['buscar']) : '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

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

$sql_total = "SELECT COUNT(*) AS total FROM cursos $whereClause";
$total_resultado = $conn->query($sql_total)->fetch_assoc()['total'];
$total_paginas = ceil($total_resultado / $items_por_pagina);

$sql_cursos = "SELECT * FROM cursos $whereClause ORDER BY creado_en DESC LIMIT $offset, $items_por_pagina";
$cursos = $conn->query($sql_cursos);

$rango_inicio = $offset + 1;
$rango_fin = min($offset + $items_por_pagina, $total_resultado);
?>

<link rel="stylesheet" href="../assets/css/st_views.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="table-panel">
    <div style="margin-bottom: 20px; display: flex; gap: 10px;">
        <button id="btnAbrirModal" class="btn-agregar"><i class="fas fa-plus"></i> Agregar Curso</button>
        <a class="btn-excel"
            href="views/process/exportar_cursos.php?buscar=<?= urlencode($busqueda) ?>&fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>">
            <i class="fas fa-file-excel"></i> Descargar Excel
        </a>
    </div>

    <form method="GET" class="buscador">
        <input type="hidden" name="seccion" value="agregar_curso">
        <input type="text" name="buscar" placeholder="Buscar por título..." value="<?= htmlspecialchars($busqueda) ?>">
        <label for="fecha_inicio">Desde:</label>
        <input type="date" name="fecha_inicio" value="<?= $fecha_inicio ?>">
        <label for="fecha_fin">Hasta:</label>
        <input type="date" name="fecha_fin" value="<?= $fecha_fin ?>">
        <button type="submit"><i class="fas fa-filter"></i> Filtrar</button>
        <a href="dashboard.php?seccion=agregar_curso" class="limpiar-filtros">Limpiar</a>
    </form>

    <div id="modalCurso" class="modal-eliminar">
        <div class="modal-contenido">
            <h3 style="text-align: center;">Agregar Curso</h3>
            <form method="POST" action="views/process/procesar_curso.php" class="formulario-modal">
                <div class="form-group">
                    <label for="asistencia">Por su asistencia:</label>
                    <input type="text" name="asistencia" id="asistencia" required>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre del Curso:</label>
                    <input type="text" name="nombre" id="nombre" required>
                </div>
                <div class="form-group">
                    <label for="fecha">Impartido el:</label>
                    <input type="text" name="fecha" id="fecha" required>
                </div>
                <div class="form-group">
                    <label for="horas">Horas lectivas:</label>
                    <input type="text" name="horas" id="horas">
                </div>
                <div class="form-group">
                    <label for="lugar">Lugar:</label>
                    <input type="text" name="lugar" id="lugar" required>
                </div>
                <div class="modal-botones">
                    <button type="submit" class="btn-confirmar">Agregar</button>
                    <button type="button" id="btnCerrarModal" class="btn-cancelar">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEditar" class="modal-eliminar">
        <div class="modal-contenido">
            <h3 style="text-align: center;">Editar Curso</h3>
            <form method="POST" action="views/process/editar_curso.php" class="formulario-modal">
                <input type="hidden" name="id" id="editarId">
                <div class="form-group">
                    <label for="editarAsistencia">Por su asistencia:</label>
                    <input type="text" name="asistencia" id="editarAsistencia" required>
                </div>
                <div class="form-group">
                    <label for="editarNombre">Nombre del Curso:</label>
                    <input type="text" name="nombre" id="editarNombre" required>
                </div>
                <div class="form-group">
                    <label for="editarFecha">Impartido el:</label>
                    <input type="text" name="fecha" id="editarFecha" required>
                </div>
                <div class="form-group">
                    <label for="editarHoras">Horas lectivas:</label>
                    <input type="text" name="horas" id="editarHoras">
                </div>
                <div class="form-group">
                    <label for="editarLugar">Lugar:</label>
                    <input type="text" name="lugar" id="editarLugar" required>
                </div>
                <div class="modal-botones">
                    <button type="submit" class="btn-confirmar">Guardar Cambios</button>
                    <button type="button" id="btnCerrarEditar" class="btn-cancelar">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEliminar" class="modal-eliminar">
        <div class="modal-contenido">
            <h3>¿Estás seguro que deseas eliminar este curso?</h3>
            <p id="nombreCursoEliminar" class="titulo-publicacion"></p>
            <form method="POST" action="views/process/eliminar_curso.php">
                <input type="hidden" name="id" id="eliminarId">
                <div class="modal-botones">
                    <button type="submit" class="btn-confirmar">Eliminar</button>
                    <button type="button" id="btnCerrarEliminar" class="btn-cancelar">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <p class="resumen">Mostrando <?= $rango_inicio ?> a <?= $rango_fin ?> de <?= $total_resultado ?> cursos</p>

    <div class="tabla-container">
        <table class="table-cursos">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Impartido el</th>
                    <th>Lugar</th>
                    <th>F. Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = $offset + 1; ?>
                <?php while ($curso = $cursos->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($curso['nombre']) ?></td>
                        <td><?= htmlspecialchars($curso['fecha_imparticion']) ?></td>
                        <td><?= htmlspecialchars($curso['lugar']) ?></td>
                        <td><?= date('d/m/Y', strtotime($curso['creado_en'])) ?><br><?= date('H:i', strtotime($curso['creado_en'])) ?></td>
                        <td>
                            <button class="btn-action edit-curso"
                                data-id="<?= $curso['id'] ?>"
                                data-asistencia="<?= htmlspecialchars($curso['asistencia']) ?>"
                                data-nombre="<?= htmlspecialchars($curso['nombre']) ?>"
                                data-fecha="<?= htmlspecialchars($curso['fecha_imparticion']) ?>"
                                data-horas="<?= htmlspecialchars($curso['horas']) ?>"
                                data-lugar="<?= htmlspecialchars($curso['lugar']) ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action delete-curso"
                                data-id="<?= $curso['id'] ?>"
                                data-nombre="<?= htmlspecialchars($curso['nombre']) ?>">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_paginas > 1): ?>
        <div class="paginacion">
            <?php if ($pagina > 1): ?>
                <a class="prev-next" href="?seccion=agregar_curso&pagina=<?= $pagina - 1 ?>&buscar=<?= urlencode($busqueda) ?>&fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>">« Anterior</a>
            <?php else: ?>
                <span class="prev-next disabled">« Anterior</span>
            <?php endif; ?>

            <?php
            $rango = 2;
            $inicio = max(1, $pagina - $rango);
            $fin = min($total_paginas, $pagina + $rango);

            if ($inicio > 1) echo '<span class="puntos">...</span>';
            for ($i = $inicio; $i <= $fin; $i++):
            ?>
                <a class="<?= $i === $pagina ? 'activo' : '' ?>"
                    href="?seccion=agregar_curso&pagina=<?= $i ?>&buscar=<?= urlencode($busqueda) ?>&fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>">
                    <?= $i ?>
                </a>
            <?php endfor;
            if ($fin < $total_paginas) echo '<span class="puntos">...</span>';
            ?>

            <?php if ($pagina < $total_paginas): ?>
                <a class="prev-next" href="?seccion=agregar_curso&pagina=<?= $pagina + 1 ?>&buscar=<?= urlencode($busqueda) ?>&fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>">Siguiente »</a>
            <?php else: ?>
                <span class="prev-next disabled">Siguiente »</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script src="../assets/js/sc_agcurso.js"></script>