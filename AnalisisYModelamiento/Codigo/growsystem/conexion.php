<?php
$conn = new mysqli("localhost", "root", "", "growsystem");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>