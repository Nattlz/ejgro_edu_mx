<?php
session_start();
require_once 'includes/db.php';

function obtenerCursosPublicados($conn, $estado)
{
  $stmt = $conn->prepare("SELECT p.id, c.nombre, c.fecha_imparticion, c.lugar, p.flyer, c.id AS curso_id FROM pub_cursos p JOIN cursos c ON p.curso_id = c.id WHERE p.estado = ? ORDER BY p.creado_en DESC");
  $stmt->bind_param("s", $estado);
  $stmt->execute();
  return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$cursos_finalizados = obtenerCursosPublicados($conn, 'finalizado');
$cursos_activos = obtenerCursosPublicados($conn, 'activo');
$cursos_proximos = obtenerCursosPublicados($conn, 'proximo');
?>

<!DOCTYPE html>
<html lang="es" class="<?php echo (isset($_COOKIE['modoOscuro']) && $_COOKIE['modoOscuro'] === 'true') ? 'dark' : ''; ?>">

<head>
  <meta charset="UTF-8">
  <title>Inicio | Instituto para el Mejoramiento Judicial</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="/assets/css/st_index.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans" data-toast-error="<?= isset($_SESSION['toast_error']) ? htmlspecialchars($_SESSION['toast_error']) : '' ?>">

  <div class="fixed top-4 right-4 z-50">
    <div class="relative">
      <button id="menuButton" class="bg-naranja hover:bg-orange-600 p-3 rounded-full shadow-lg focus:outline-none transition-all">
        <i class="fas fa-bars text-white text-xl"></i>
      </button>
      <div id="menuDropdown" class="hidden absolute right-0 mt-3 w-52 bg-white dark:bg-gray-800 rounded-xl shadow-xl border dark:border-gray-700 overflow-hidden">
        <a href="index.php" class="flex items-center gap-3 px-5 py-3 text-sm text-gray-800 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
          <i class="fas fa-house text-lg"></i> Inicio
        </a>
        <a href="#" onclick="mostrarModalContacto(); toggleMenu();" class="flex items-center gap-3 px-5 py-3 text-sm text-gray-800 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
          <i class="fas fa-envelope text-lg"></i> Contacto
        </a>
      </div>
    </div>
  </div>

  <header class="relative bg-gradient-to-r from-white via-gray-100 to-white dark:from-gray-800 dark:via-gray-900 dark:to-gray-800 border-b shadow-sm py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
      <div class="flex items-center gap-4">
        <img src="/assets/img/logo.svg" alt="Logo IMJ" class="w-20 h-20 object-contain">
        <div>
          <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-verde-oscuro dark:text-white uppercase tracking-tight leading-tight">
            Instituto para el<br class="sm:hidden"> Mejoramiento Judicial
          </h1>
          <div class="w-24 h-1 mt-2 bg-dorado rounded"></div>
        </div>
      </div>
      <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 italic text-center sm:text-right">
        Plataforma informativa y de consulta institucional
      </p>
    </div>
  </header>

  <section class="py-6 bg-white dark:bg-gray-800">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="relative overflow-hidden rounded-xl shadow-lg">
        <div class="scroll-track flex w-max animate-scroll space-x-2">
          <?php for ($i = 0; $i < 2; $i++): ?>
            <img src="https://i.ibb.co/hFNm83Lx/1.png" class="rounded-lg shadow-md h-40 sm:h-56 md:h-64 lg:h-72 xl:h-80 object-cover transition duration-300 hover:scale-105" />
            <img src="https://i.ibb.co/cXCfMns7/2.png" class="rounded-lg shadow-md h-40 sm:h-56 md:h-64 lg:h-72 xl:h-80 object-cover transition duration-300 hover:scale-105" />
            <img src="https://i.ibb.co/bgcdcwQn/3.png" class="rounded-lg shadow-md h-40 sm:h-56 md:h-64 lg:h-72 xl:h-80 object-cover transition duration-300 hover:scale-105" />
            <img src="https://i.ibb.co/KpM39FjZ/4.png" class="rounded-lg shadow-md h-40 sm:h-56 md:h-64 lg:h-72 xl:h-80 object-cover transition duration-300 hover:scale-105" />
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="py-10 bg-gray-100 dark:bg-gray-800">
    <div class="max-w-6xl mx-auto px-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 sm:space-x-4 mb-6">
        <div class="flex flex-wrap gap-2">
          <button onclick="mostrarTab('finalizados')" class="btn-tab active-tab" id="btn-finalizados">Finalizados</button>
          <button onclick="mostrarTab('activos')" class="btn-tab" id="btn-activos">Activos</button>
          <button onclick="mostrarTab('proximos')" class="btn-tab" id="btn-proximos">Próximos</button>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
          <input type="text" id="buscadorCursos" class="p-2 border rounded shadow-sm w-full sm:w-64 dark:bg-gray-700 dark:text-white dark:border-gray-600" placeholder="Buscar curso...">
          <label class="flex items-center cursor-pointer">
            <span class="mr-2 text-sm text-gray-700 dark:text-gray-200">🌙</span>
            <input type="checkbox" id="switchTema" class="sr-only">
            <div class="w-10 h-5 bg-gray-300 rounded-full shadow-inner dark:bg-gray-600 relative transition duration-300">
              <div class="dot absolute left-1 top-1 w-3 h-3 bg-white rounded-full shadow transition-transform duration-300"></div>
            </div>
          </label>
        </div>
      </div>

      <?php $tabs = ["finalizados" => $cursos_finalizados, "activos" => $cursos_activos, "proximos" => $cursos_proximos]; ?>
      <?php foreach ($tabs as $clave => $cursos): ?>
        <div id="tab-<?= $clave ?>" class="tab-content <?= $clave !== 'finalizados' ? 'hidden' : '' ?>">
          <h2 class="text-2xl font-semibold text-dorado dark:text-yellow-400 mb-4 capitalize">Cursos <?= $clave ?></h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            <?php foreach ($cursos as $curso): ?>
              <div class="fade-in curso-item bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg overflow-hidden shadow transition transform hover:-translate-y-1 hover:shadow-lg cursor-pointer"
                onclick="abrirModalCurso('<?= htmlspecialchars(addslashes($curso['nombre'])) ?>',
                                    '<?= htmlspecialchars($curso['flyer']) ?>',
                                    <?= $curso['curso_id'] ?>,
                                    '<?= $clave ?>',
                                    '<?= htmlspecialchars(addslashes($curso['fecha_imparticion'] ?? '')) ?>',
                                    '<?= htmlspecialchars(addslashes($curso['lugar'] ?? '')) ?>')">
                <img src="<?= htmlspecialchars($curso['flyer']) ?>" alt="<?= htmlspecialchars($curso['nombre']) ?>" class="w-full h-56 object-cover">
                <div class="p-4">
                  <h3 class="text-lg font-semibold truncate text-ellipsis overflow-hidden whitespace-nowrap" title="<?= htmlspecialchars($curso['nombre']) ?>">
                    <?= htmlspecialchars($curso['nombre']) ?>
                  </h3>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <div id="modalCurso" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-5xl mx-4 sm:mx-auto p-6 relative animate-fade-in-down">
      <button onclick="cerrarModalCurso()" class="close-btn" aria-label="Cerrar modal">&times;</button>

      <div class="flex flex-col lg:flex-row items-center lg:items-start gap-6">
        <!-- Flyer -->
        <div class="w-full lg:w-1/2">
          <img id="modalFlyer" src="" alt="Flyer del curso" class="w-full max-h-[60vh] object-contain rounded-md shadow-md">
        </div>

        <!-- Descripción -->
        <div class="w-full lg:w-1/2 text-center lg:text-left flex flex-col justify-between">
          <div>
            <h3 class="text-xl font-bold text-dorado uppercase mb-2 tracking-wide">Instituto para el Mejoramiento Judicial</h3>
            <p class="text-gray-700 dark:text-gray-300 text-sm mb-5 leading-relaxed text-justify">
              Este curso forma parte de nuestro compromiso institucional por promover la formación continua y el perfeccionamiento jurídico.
            </p>
            <h2 id="modalNombre" class="text-2xl font-bold text-verde-oscuro dark:text-white mb-6 leading-tight"></h2>
            <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
              <p><strong class="font-semibold text-gray-900 dark:text-gray-200">Fecha:</strong> <span id="modalFecha">—</span></p>
              <p><strong class="font-semibold text-gray-900 dark:text-gray-200">Lugar:</strong> <span id="modalLugar">—</span></p>
            </div>
          </div>
          <div id="modalBotonConstancia" class="mt-8 hidden">
            <button class="bg-verde-oscuro hover:bg-green-700 text-white font-semibold py-2 px-6 rounded shadow transition"
              onclick="abrirModalSolicitudDesdeVistaCurso()">
              Solicitar Constancia
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="modalSolicitud" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-lg mx-4 sm:mx-auto p-6 relative animate-fade-in-down">
      <button onclick="cerrarModalSolicitud()" class="absolute top-3 right-3 text-gray-600 dark:text-gray-300 hover:text-red-500 text-xl font-bold">&times;</button>
      <h2 class="text-xl font-bold mb-4 text-center text-verde-oscuro dark:text-white">Solicitar Constancia</h2>
      <form action="/views/public/solicitar_constancia.php" method="POST" class="space-y-4">
        <input type="hidden" name="curso_id" id="cursoIdInput">
        <div>
          <label class="block text-sm font-semibold mb-1 dark:text-gray-200">Nombre completo</label>
          <input type="text" name="nombre_completo" required class="w-full p-2 border rounded dark:bg-gray-700 dark:text-white dark:border-gray-600">
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1 dark:text-gray-200">Teléfono</label>
          <input type="tel" name="telefono" required pattern="[0-9]{10}" class="w-full p-2 border rounded dark:bg-gray-700 dark:text-white dark:border-gray-600">
        </div>
        <div class="text-center">
          <button type="submit" class="bg-naranja hover:bg-orange-600 text-white font-semibold py-2 px-6 rounded">Enviar por WhatsApp</button>
        </div>
      </form>
    </div>
  </div>

  <div id="modalContacto" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-xl mx-4 sm:mx-auto p-6 relative animate-fade-in-down">
      <button onclick="cerrarModalContacto()" class="absolute top-3 right-3 text-gray-600 dark:text-gray-300 hover:text-red-500 text-xl font-bold">&times;</button>
      <h2 class="text-2xl font-bold text-center text-verde-oscuro dark:text-white mb-4">Contáctanos</h2>
      <form id="formContacto" class="space-y-4">
        <div>
          <label class="block text-sm font-semibold mb-1 dark:text-gray-200">Nombre completo</label>
          <input type="text" name="nombre" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Tu nombre completo">
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1 dark:text-gray-200">Correo electrónico</label>
          <input type="email" name="correo" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="tucorreo@ejemplo.com">
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1 dark:text-gray-200">Mensaje</label>
          <textarea name="mensaje" rows="4" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Escribe tu mensaje..."></textarea>
        </div>
        <div class="text-center">
          <button type="submit" class="bg-naranja hover:bg-orange-600 text-white font-semibold py-2 px-6 rounded">Enviar</button>
        </div>
      </form>
    </div>
  </div>

  <footer class="bg-verde-oscuro text-white text-sm text-center py-4 border-t dark:bg-gray-900">
    &copy; 2025 Instituto para el Mejoramiento Judicial. Todos los derechos reservados.
  </footer>

  <div id="toastExito" class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 opacity-0 pointer-events-none transition-opacity duration-500 z-50">
    <i class="fas fa-check-circle"></i>
    <span>Mensaje enviado correctamente.</span>
  </div>

  <div id="toastError" class="fixed bottom-4 right-4 bg-red-600 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 opacity-0 pointer-events-none transition-opacity duration-500 z-50">
    <i class="fas fa-exclamation-circle"></i>
    <span>Error al enviar el mensaje.</span>
  </div>

  <script src="/assets/js/sc_index.js" defer></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <?php unset($_SESSION['toast_error']); ?>
</body>

</html>