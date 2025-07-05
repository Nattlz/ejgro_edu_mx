<?php
require_once(__DIR__ . '/../includes/auth.php');
require_once(__DIR__ . '/../includes/db.php');

$where = [];

if (!empty($_GET['curso_id'])) {
    $curso_id = (int)$_GET['curso_id'];
    $where[] = "c.curso_id = $curso_id";
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

$cursos = $conn->query("SELECT id, nombre FROM cursos");
?>

<link rel="stylesheet" href="/assets/css/st_views.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<form method="POST" class="generar-form" id="formGenerar" action="/views/process/procesar_constancia.php">
    <div class="form-row">
        <div class="form-column">
            <div class="form-group2">
                <label for="nombre">Nombre Completo:</label>
                <input type="text" name="nombre" id="nombre" required>
            </div>
            <div class="form-group2">
                <label for="correo">Correo:</label>
                <input type="email" name="correo" id="correo" required>
            </div>
            <div class="form-group2">
                <label for="cursoSelect">Seleccionar Curso:</label>
                <div class="curso-select-btn">
                    <select name="curso_id" id="cursoSelect" required onchange="location.href='dashboard.php?seccion=generar_constancia&curso_id=' + this.value">
                        <option value="">Seleccionar un Curso</option>
                        <?php $cursos->data_seek(0);
                        while ($curso = $cursos->fetch_assoc()): ?>
                            <option value="<?= $curso['id'] ?>" <?= ($_GET['curso_id'] ?? '') == $curso['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($curso['nombre']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="btn-generar">
                        <i class="fas fa-file-alt"></i> Generar
                    </button>
                </div>
            </div>
        </div>
        <div class="form-column columna-detalle">
            <div class="form-group2">
                <label>Datos del Curso:</label>
                <div class="curso-detalle" id="datosCurso">
                    <?php if (!empty($curso_id)): ?>
                        <?php
                        $cursoInfo = $conn->prepare("SELECT * FROM cursos WHERE id = ?");
                        $cursoInfo->bind_param("i", $curso_id);
                        $cursoInfo->execute();
                        $info = $cursoInfo->get_result()->fetch_assoc();
                        ?>
                        Por su asistencia <?= htmlspecialchars($info['asistencia']) ?><br>
                        <?= htmlspecialchars($info['nombre']) ?><br>
                        <?= htmlspecialchars($info['fecha_imparticion']) ?><br>
                        <?= htmlspecialchars($info['horas']) ?><br>
                        <?= htmlspecialchars($info['lugar']) ?>
                    <?php else: ?>
                        Seleccione un curso para ver los detalles.
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<?php if (!empty($curso_id)): ?>
    <div class="table-panel">
        <h2>Constancias Generadas</h2>

        <p class="resumen">Mostrando <?= $rango_inicio ?> a <?= $rango_fin ?> de <?= $total_resultado ?> constancias</p>

        <div class="tabla-container">
            <table class="table-cursos">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nombre Completo</th>
                        <th>Correo</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $inicio + 1; ?>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($fila['nombre_completo']) ?></td>
                            <td><?= htmlspecialchars($fila['correo']) ?></td>
                            <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($fila['generado_en']))) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="paginacion">
            <?php
            function mostrarPagina($i, $pagina, $params)
            {
                $params['pagina'] = $i;
                $url = 'dashboard.php?' . http_build_query($params);
                $clase = $i === $pagina ? 'activo' : '';
                echo "<a href='" . htmlspecialchars($url) . "' class='$clase'>$i</a>";
            }

            $params = ['seccion' => 'generar_constancia', 'curso_id' => $curso_id];
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
<?php endif; ?>

<script src="/assets/js/sc_cgeneradas.js"></script>