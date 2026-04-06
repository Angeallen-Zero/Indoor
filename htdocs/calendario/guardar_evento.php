<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo "Debes iniciar sesión";
    exit;
}

require_once 'api/db.php';
$db = getDB();

$usuario_id = $_SESSION['usuario_id'];
$fecha      = $_POST['fecha'] ?? '';
$comentario = $_POST['comentario'] ?? '';
$imagenNombre = "";

/* VALIDACIÓN CORRECTA */
if (!$fecha || (!$comentario && empty($_FILES['imagen']['name']))) {
    echo "Faltan datos";
    exit;
}

/* SUBIR IMAGEN */
if (isset($_FILES['imagen']) && $_FILES['imagen']['name'] != "") {

    $imagenNombre = time() . "_" . basename($_FILES['imagen']['name']);
    $ruta = "imagenes/" . $imagenNombre;

    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {
        echo "Error al subir la imagen";
        exit;
    }
}

/* INSERTAR EN BD */
$stmt = $db->prepare("INSERT INTO eventos (usuario_id, fecha, comentario, imagen) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $usuario_id, $fecha, $comentario, $imagenNombre);

if ($stmt->execute()) {
    echo "OK";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$db->close();
?>