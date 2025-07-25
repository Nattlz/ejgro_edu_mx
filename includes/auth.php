<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
  header("Location: /login");
  exit;
}

if (!defined('APP_SECURE')) {
  define('APP_SECURE', true);
}
?>
