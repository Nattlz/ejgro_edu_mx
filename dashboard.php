<?php
ob_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

$stmt1 = $conn->query("SELECT COUNT(*) AS total FROM cursos");
$totalCursos = $stmt1 ? $stmt1->fetch_assoc()['total'] : 0;

$stmt2 = $conn->query("SELECT COUNT(*) AS total FROM constancias");
$totalConstancias = $stmt2 ? $stmt2->fetch_assoc()['total'] : 0;

$stmt2 = $conn->query("SELECT COUNT(*) AS total FROM usuarios");
$totalUsuarios = $stmt2 ? $stmt2->fetch_assoc()['total'] : 0;

$stmt3 = $conn->query("SELECT generado_en FROM constancias ORDER BY generado_en DESC LIMIT 1");
$ultimaFecha = ($stmt3 && $stmt3->num_rows > 0) ? date("d/m/Y", strtotime($stmt3->fetch_assoc()['generado_en'])) : '—';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Dashboard - IMJ</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
  <div class="dashboard-container">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header d-flex align-items-center justify-content-between px-3 mb-3">
        <div class="d-flex align-items-center gap-2 sidebar-branding">
          <img src="assets/img/logo.svg" alt="Logo" class="sidebar-logo" style="width: 30px; height: 30px;">
          <span class="logo">IMJ CONSTANCIAS</span>
        </div>
        <button id="toggleSidebar" class="toggle-btn">
          <i class="fas fa-bars"></i>
        </button>
      </div>

      <nav class="menu">
        <div class="menu-section">Navegación</div>
        <a href="dashboard.php?seccion=agregar_curso" class="<?= ($_GET['seccion'] ?? '') === 'agregar_curso' ? 'active' : '' ?>">
          <i class="fas fa-plus-square"></i><span class="link-text">Agregar Curso</span>
        </a>
        <a href="dashboard.php?seccion=generar_constancia" class="<?= ($_GET['seccion'] ?? '') === 'generar_constancia' ? 'active' : '' ?>">
          <i class="fas fa-certificate"></i><span class="link-text">Generar Constancia</span>
        </a>

        <div class="menu-section">UI Element</div>
        <a href="dashboard.php?seccion=constancias_generadas" class="<?= ($_GET['seccion'] ?? '') === 'constancias_generadas' ? 'active' : '' ?>">
          <i class="fas fa-folder"></i><span class="link-text">Constancias Generadas</span>
        </a>

        <div class="menu-section">Publicaciones</div>
        <a href="dashboard.php?seccion=publicar_curso" class="<?= ($_GET['seccion'] ?? '') === 'publicar_curso' ? 'active' : '' ?>">
          <i class="fas fa-upload"></i><span class="link-text">Publicar Curso</span>
        </a>
        <a href="dashboard.php?seccion=ver_publicaciones" class="<?= ($_GET['seccion'] ?? '') === 'ver_publicaciones' ? 'active' : '' ?>">
          <i class="fas fa-list-alt"></i><span class="link-text">Ver Publicaciones</span>
        </a>
      </nav>

      <div class="sidebar-footer text-center mt-auto pb-2">
        <span class="logo">Plataforma de Administración</span>
        <span class="version">Versión 1.0</span>
      </div>
    </aside>

    <div class="main-content" id="mainContent">
      <?php
      $mensajes = [
        'cpassword'        => 'Contraseña actualizada correctamente.',
        'agregado'         => 'Curso agregado correctamente.',
        'eliminado'        => 'Curso eliminado correctamente.',
        'editado'          => 'Curso actualizado correctamente.',
        'creado'           => 'Creado correctamente.',
        'const_creada'     => 'Constancia generada correctamente.',
        'const_editado'    => 'Constancia actualizada correctamente.',
        'const_noupdate'   => 'No se realizaron cambios en la constancia.',
        'const_vacio'      => 'Todos los campos son obligatorios para editar la constancia.',
        'const_metodo'     => 'Acceso no permitido al editar constancia.',
        'const_eliminado'  => 'Constancia eliminada correctamente.',
        'const_noexiste'   => 'No se encontró la constancia que intentas eliminar.',
        'const_idinvalido' => 'ID inválido para eliminar constancia.',
        'pub_creada'       => 'Publicacion creada correctamente.',
        'pub_actualizada'  => 'Publicacion actualizada correctamente.',
        'pub_eliminada'    => 'Publicacion eliminada correctamente.'
      ];

      if (isset($_GET['msg']) && isset($mensajes[$_GET['msg']])): ?>
        <div class="alerta-exito" id="alertaExito">
          <i class="fas fa-check-circle"></i> <?= $mensajes[$_GET['msg']] ?>
        </div>
      <?php endif; ?>

      <header class="topbar d-flex justify-content-between align-items-center px-4">
        <h2 class="m-0">ADMINISTRACIÓN DE CURSOS & CONSTANCIAS</h2>
        <ul class="nav">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle profile-pic" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="assets/uploads/admin.jpg" alt="Avatar" class="avatar-img">
              <span class="profile-username"><?= strtoupper($_SESSION['nombre_completo']) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-user animated fadeIn" aria-labelledby="navbarDropdown">
              <li class="dropdown-user">
                <div class="user-box text-center">
                  <div class="avatar-lg">
                    <img src="assets/uploads/admin.jpg" alt="Imagen de perfil">
                  </div>
                  <div class="u-text">
                    <h4><?= strtoupper($_SESSION['nombre_completo']) ?></h4>
                  </div>
                </div>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="includes/logout.php">Salir <img src="assets/img/logout.svg" alt="salir" width="18"></a></li>
            </ul>
          </li>
        </ul>
      </header>

      <?php
      $seccion = $_GET['seccion'] ?? 'inicio';

      if ($seccion === 'agregar_curso') {
        include 'views/agregar_curso.php';
      } elseif ($seccion === 'generar_constancia') {
        include 'views/generar_constancia.php';
      } elseif ($seccion === 'constancias_generadas') {
        include 'views/constancias_generadas.php';
      } elseif ($seccion === 'publicar_curso') {
        include 'views/publicar_curso.php';
      } elseif ($seccion === 'ver_publicaciones') {
        include 'views/publicaciones.php';
      } elseif ($seccion === 'editar_publicacion' && isset($_GET['id'])) {
        include 'views/editar_publicacion.php';
      } else {
      ?>
        <div class="dashboard-welcome">
          <h1>¡Bienvenido <?= strtoupper($_SESSION['nombre_completo']) ?>!</h1>
          <p class="reloj" id="fechaHora"></p>
        </div>

        <div class="dashboard-cards">
          <div class="card">
            <i class="fas fa-file-alt icono"></i>
            <div class="texto">
              <h3><?= $totalConstancias ?? '0' ?></h3>
              <p>Constancias Generadas</p>
            </div>
          </div>
          <div class="card">
            <i class="fas fa-book icono"></i>
            <div class="texto">
              <h3><?= $totalCursos ?? '0' ?></h3>
              <p>Cursos Totales</p>
            </div>
          </div>
          <div class="card">
            <i class="fas fa-clock icono"></i>
            <div class="texto">
              <h3><?= $ultimaFecha ?? '—' ?></h3>
              <p>Última Publicación</p>
            </div>
          </div>
          <div class="card">
            <i class="fas fa-user icono"></i>
            <div class="texto">
              <h3><?= $totalUsuarios ?></h3>
              <p>Alta de Usuarios</p>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/sc_dashboard.js"></script>
</body>

</html>
<?php
ob_end_flush();
?>