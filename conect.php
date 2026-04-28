<?php
$conexion = new mysqli("db", "user", "password", "indoor_db");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>