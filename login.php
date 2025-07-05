<?php
session_start();
require_once 'includes/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $matricula = isset($_POST['matricula']) ? $_POST['matricula'] : '';
  $password = isset($_POST['password']) ? $_POST['password'] : '';

  $stmt = $conn->prepare("SELECT id, nombre_completo, password_hash, debe_cambiar_password FROM usuarios WHERE matricula = ?");
  $stmt->bind_param("s", $matricula);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows === 1) {
    $usuario = $result->fetch_assoc();
    if (password_verify($password, $usuario['password_hash'])) {
      $_SESSION['usuario_id'] = $usuario['id'];
      $_SESSION['nombre_completo'] = $usuario['nombre_completo'];

      if ($usuario['debe_cambiar_password']) {
        header("Location: includes/change_password.php");
      } else {
        header("Location: dashboard.php");
      }
      exit;
    }
  }

  $error = "Matrícula o contraseña incorrecta.";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Login | Instituto para el Mejoramiento Judicial</title>
  <link href="assets/css/st_login.css" rel="stylesheet">
</head>

<body class="flex items-center justify-center h-screen bg-gray-100">
  <form method="POST" class="bg-white p-8 rounded shadow-md w-full max-w-sm animate-fade-in-up">
    <h2 class="text-xl font-bold mb-6 text-center">Iniciar Sesión</h2>
    <?php if ($error): ?>
      <p class="text-red-600 text-sm mb-4 text-center"><?= $error ?></p>
    <?php endif; ?>
    <label class="block mb-2 text-sm">Matrícula:</label>
    <input type="text" name="matricula" class="w-full mb-4 p-2 border rounded" required>
    <label class="block mb-2 text-sm">Contraseña:</label>
    <input type="password" name="password" class="w-full mb-6 p-2 border rounded" required>
    <button type="submit" class="w-full bg-naranja text-white py-2 rounded transition duration-200 transform hover:scale-105 active:scale-95">
      Entrar
    </button>
  </form>
</body>

</html>