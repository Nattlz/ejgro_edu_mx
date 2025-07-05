<?php
require_once(__DIR__ . '/../includes/auth.php');
require_once(__DIR__ . '/../includes/db.php');

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

$por_pagina = 10;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$inicio = ($pagina - 1) * $por_pagina;

$total_resultado = $conn->query("SELECT COUNT(*) AS total FROM constancias c $condicion")->fetch_assoc()['total'];
$total_paginas = ceil($total_resultado / $por_pagina);

$rango_inicio = $inicio + 1;
$rango_fin = min($inicio + $por_pagina, $total_resultado);

$query = "
SELECT c.id, c.nombre_completo, c.correo, c.uuid, c.numero_certificado, c.generado_en, c.curso_id, cu.nombre AS curso
FROM constancias c
JOIN cursos cu ON c.curso_id = cu.id
$condicion
ORDER BY c.generado_en DESC
LIMIT $inicio, $por_pagina
";
$resultado = $conn->query($query);

$cursos_all = $conn->query("SELECT id, nombre FROM cursos");
?>

<link rel="stylesheet" href="/assets/css/st_views.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="table-panel">
    <h2>Constancias Generadas</h2>

    <form method="GET" class="buscador">
        <input type="hidden" name="seccion" value="constancias_generadas">
        <input type="text" name="buscar_nombre" placeholder="Buscar por nombre..."
            value="<?= htmlspecialchars($_GET['buscar_nombre'] ?? '') ?>">
        <label for="curso_id">Curso:</label>
        <select name="curso_id" id="curso_id">
            <option value="">-- Todos --</option>
            <?php while ($c = $cursos_all->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>" <?= ($_GET['curso_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <label for="fecha_inicio">Desde:</label>
        <input type="date" name="fecha_inicio" id="fecha_inicio"
            value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>">
        <label for="fecha_fin">Hasta:</label>
        <input type="date" name="fecha_fin" id="fecha_fin"
            value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>">

        <button type="submit"><i class="fas fa-filter"></i> Filtrar</button>
        <a href="dashboard.php?seccion=constancias_generadas" class="limpiar-filtros">Limpiar</a>
        <a class="btn-excel"
            href="views/process/exportar_constancias.php?buscar_nombre=<?= urlencode($_GET['buscar_nombre'] ?? '') ?>&curso_id=<?= $_GET['curso_id'] ?? '' ?>&fecha_inicio=<?= $_GET['fecha_inicio'] ?? '' ?>&fecha_fin=<?= $_GET['fecha_fin'] ?? '' ?>">
            <i class="fas fa-file-excel"></i> Excel
        </a>
    </form>

    <p class="resumen">Mostrando <?= $rango_inicio ?> a <?= $rango_fin ?> de <?= $total_resultado ?> constancias</p>

    <div class="tabla-container">
        <table class="table-cursos">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Curso</th>
                    <th>PDF</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = $inicio + 1; ?>
                <?php while ($fila = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($fila['nombre_completo']) ?></td>
                        <td><?= htmlspecialchars($fila['curso']) ?></td>
                        <td><a href="https://ejgro.edu.mx/cotejo/<?= $fila['uuid'] ?>" target="_blank">Ver PDF</a></td>
                        <td>
                            <button class="btn-action edit-constancia"
                                data-id="<?= $fila['id'] ?>"
                                data-nombre="<?= htmlspecialchars($fila['nombre_completo']) ?>"
                                data-correo="<?= htmlspecialchars($fila['correo']) ?>"
                                data-curso="<?= $fila['curso_id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action delete-constancia"
                                data-id="<?= $fila['id'] ?>"
                                data-nombre="<?= htmlspecialchars($fila['nombre_completo']) ?>">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="paginacion">
        <?php
        function mostrarPagina($i, $pagina, $params) {
            $params['pagina'] = $i;
            $url = 'dashboard.php?' . http_build_query($params);
            $clase = $i === $pagina ? 'activo' : '';
            echo "<a href='" . htmlspecialchars($url) . "' class='$clase'>$i</a>";
        }

        $params = ['seccion' => 'constancias_generadas'];
        if (!empty($_GET['buscar_nombre'])) $params['buscar_nombre'] = $_GET['buscar_nombre'];
        if (!empty($_GET['curso_id'])) $params['curso_id'] = $_GET['curso_id'];
        if (!empty($_GET['fecha_inicio'])) $params['fecha_inicio'] = $_GET['fecha_inicio'];
        if (!empty($_GET['fecha_fin'])) $params['fecha_fin'] = $_GET['fecha_fin'];

        $rango = 2;
        $mostrar_puntos = false;

        if ($total_paginas > 1) {
            if ($pagina > 1) {
                $params['pagina'] = $pagina - 1;
                echo "<a href='" . htmlspecialchars('dashboard.php?' . http_build_query($params)) . "' class='prev-next'>&laquo; Anterior</a>";
            } else {
                echo "<span class='prev-next disabled'>&laquo; Anterior</span>";
            }

            for ($i = 1; $i <= $total_paginas; $i++) {
                if ($i <= 2 || $i > $total_paginas - 2 || abs($i - $pagina) <= $rango) {
                    mostrarPagina($i, $pagina, $params);
                    $mostrar_puntos = true;
                } elseif ($mostrar_puntos) {
                    echo "<span class='puntos'>...</span>";
                    $mostrar_puntos = false;
                }
            }

            if ($pagina < $total_paginas) {
                $params['pagina'] = $pagina + 1;
                echo "<a href='" . htmlspecialchars('dashboard.php?' . http_build_query($params)) . "' class='prev-next'>Siguiente &raquo;</a>";
            } else {
                echo "<span class='prev-next disabled'>Siguiente &raquo;</span>";
            }
        }
        ?>
    </div>
</div>

<div id="modalEditar" class="modal-eliminar">
    <div class="modal-contenido" style="text-align: left;">
        <h3 style="text-align: center;">Editar Constancia</h3>
        <form method="POST" action="views/process/editar_constancia.php" class="formulario-modal">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-group">
                <label for="edit-nombre">Nombre:</label>
                <input type="text" name="nombre" id="edit-nombre" required>
            </div>
            <div class="form-group">
                <label for="edit-correo">Correo:</label>
                <input type="email" name="correo" id="edit-correo" required>
            </div>
            <div class="form-group">
                <label for="edit-curso">Curso:</label>
                <select name="curso_id" id="edit-curso" required>
                    <?php
                    $cursos_reset = $conn->query("SELECT id, nombre FROM cursos");
                    while ($c = $cursos_reset->fetch_assoc()):
                    ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="modal-botones">
                <button type="submit" class="btn-confirmar">Guardar</button>
                <button type="button" id="btnCerrarEditar" class="btn-cancelar">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEliminar" class="modal-eliminar">
    <div class="modal-contenido">
        <h3>¿Estás seguro que deseas eliminar esta constancia?</h3>
        <p id="nombreConstanciaEliminar" class="titulo-publicacion"></p>
        <form method="GET" action="/views/process/eliminar_constancia.php">
            <input type="hidden" name="id" id="delete-id">
            <div style="margin-top: 20px; display: flex; justify-content: space-between;">
                <button type="submit" class="btn-confirmar">Eliminar</button>
                <button type="button" id="btnCerrarEliminar" class="btn-cancelar">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/sc_cgeneradas.js"></script>