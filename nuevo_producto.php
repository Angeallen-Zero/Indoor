<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: acceso.php');
    exit;
}

include "conect.php";

// GUARDAR PRODUCTO

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre']);
    $precio = (float)$_POST['precio'];

    if ($nombre == '' || $precio <= 0) {
        die("❌ Datos inválidos");
    }

    $imagenRuta = "";


    // SUBIR IMAGEN

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {

        $carpeta = "uploads/productos/";

        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $nombreArchivo = time() . "_" . basename($_FILES["imagen"]["name"]);
        $rutaFinal = $carpeta . $nombreArchivo;

        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaFinal)) {
            $imagenRuta = $rutaFinal;
        }
    }

    $stmt = $conexion->prepare("
        INSERT INTO productos (nombre, precio, imagen)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("sds", $nombre, $precio, $imagenRuta);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_productos.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nuevo producto</title>

<style>
body{
    font-family: Arial, sans-serif;
    background: #f0f4f2;
    margin: 0;
    padding: 30px;
}

.card{
    max-width: 500px;
    margin: auto;
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 6px 18px rgba(0,0,0,.1);
}

h2{
    text-align: center;
    margin-bottom: 20px;
}

input{
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 14px;
}

button{
    width: 100%;
    padding: 14px;
    background: #31C048;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: bold;
    cursor: pointer;
    font-size: 15px;
}

button:hover{
    background: #219E35;
}

a{
    display: block;
    text-align: center;
    margin-top: 15px;
    color: #6b7c6e;
    text-decoration: none;
}
</style>

</head>
<body>

<div class="card">

    <h2> Nuevo producto</h2>

    <form method="POST" enctype="multipart/form-data">

        <label>Nombre del producto</label>
        <input type="text" name="nombre" required>

        <label>Precio</label>
        <input type="number" step="0.01" name="precio" required>

        <label>Imagen</label>
        <input type="file" name="imagen" accept="image/*" required>

        <button type="submit">Guardar producto</button>

    </form>

    <a href="admin_productos.php">← Volver</a>

</div>

</body>
</html>