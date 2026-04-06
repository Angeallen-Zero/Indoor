<?php
require "conexion.php";

$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$password = $_POST['password'];

// Encriptar contraseña
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Verificar si ya existe
$sql = "SELECT id FROM usuarios WHERE correo = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    header("Location: registro.php?error=1");
    exit;
}

// Insertar usuario
$sql = "INSERT INTO usuarios (nombre, correo, password) VALUES (?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sss", $nombre, $correo, $passwordHash);

if ($stmt->execute()) {
    header("Location: login.php");
    exit;
} else {
    echo "Error al registrar";
}
?>