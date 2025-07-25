<?php
$host = "localhost";
$user = "poderju1_adminconst";
$pass = "oaDnX)+p(t[)";
$dbname = "poderju1_imjgroconstancias";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
