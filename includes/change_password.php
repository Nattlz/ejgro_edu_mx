<?php
require_once 'auth.php';
require_once 'db.php';

$msg = "";
$tipo = ""; // success o error

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nueva = $_POST['nueva'];
  $confirmar = $_POST['confirmar'];

  if ($nueva === $confirmar && strlen($nueva) >= 6) {
    $hash = password_hash($nueva, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios SET password_hash = ?, debe_cambiar_password = 0 WHERE id = ?");
    $stmt->bind_param("si", $hash, $_SESSION['usuario_id']);
    $stmt->execute();
    header("Location: ../dashboard.php?msg=cpassword");
    exit;
  } else {
    $msg = "❌ Las contraseñas no coinciden o son muy cortas (mínimo 6 caracteres).";
    $tipo = "error";
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Cambiar Contraseña</title>
  <link href="../assets/css/st_login.css" rel="stylesheet">
</head>

<body class="flex items-center justify-center h-screen bg-gray-100">
  <form method="POST" class="bg-white p-8 rounded shadow-md w-full max-w-sm animate-fade-in-up">
    <h2 class="text-xl font-bold mb-6 text-center">Cambiar Contraseña</h2>

    <?php if ($msg): ?>
      <div class="mb-4 px-4 py-2 rounded text-sm font-semibold 
                  <?= $tipo === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700' ?>">
        <?= $msg ?>
      </div>
    <?php endif; ?>

    <label for="nueva" class="block mb-2 text-sm">Nueva contraseña:</label>
    <input type="password" name="nueva" id="nueva" class="w-full mb-4 p-2 border rounded" required>

    <label for="confirmar" class="block mb-2 text-sm">Confirmar contraseña:</label>
    <input type="password" name="confirmar" id="confirmar" class="w-full mb-6 p-2 border rounded" required>

    <button type="submit" class="w-full bg-naranja text-white py-2 rounded transition duration-200 transform hover:scale-105 active:scale-95">
      Cambiar
    </button>
  </form>
</body>

</html>