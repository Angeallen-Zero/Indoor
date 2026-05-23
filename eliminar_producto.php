<?php
session_start();
include "conect.php";

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: acceso.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID no válido");
}

$id = (int)$_GET['id'];

// eliminar producto
$stmt = $conexion->prepare("DELETE FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// volver al panel
header("Location: admin_productos.php");
exit;