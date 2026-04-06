<?php
session_start();

// 🔒 Proteger la página (solo usuarios logueados)
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $conn = new mysqli("localhost", "root", "", "indoor");

    if ($conn->connect_error) {
        die("Error conexión BD");
    }

    // 👤 Obtener usuario logueado
    $usuario_id = $_SESSION['usuario_id'];

    // Crear carpeta uploads si no existe
    if (!is_dir("uploads")) {
        mkdir("uploads", 0777, true);
    }

    // Validar imagen
    if (!isset($_FILES["imagen"]) || $_FILES["imagen"]["error"] !== 0) {
        die("Error al subir imagen");
    }

    // Procesar imagen
    $nombreImagen = time() . "_" . basename($_FILES["imagen"]["name"]);
    $ruta = "uploads/" . $nombreImagen;

    move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta);

    // Insertar en BD (AGREGAMOS usuario_id)
    $stmt = $conn->prepare("INSERT INTO plantas 
    (nombre_comun, nombre_cientifico, familia, genero, confianza, organo, imagen_subida, usuario_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssssissi",
        $_POST["nombre_comun"],
        $_POST["nombre_cientifico"],
        $_POST["familia"],
        $_POST["genero"],
        $_POST["confianza"],
        $_POST["organo"],
        $ruta,
        $usuario_id
    );

    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agregar Manual - Indoor</title>
<link rel="stylesheet" href="styles.css">

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    margin: 0;
}

.encabezado {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    padding: 20px;
    text-align: center;
}

.boton-volver {
    position: absolute;
    left: 20px;
    top: 20px;
    color: white;
    text-decoration: none;
    font-size: 20px;
}

.titulo-app {
    margin: 0;
}

.contenedor {
    padding: 30px 20px;
}

.card-form {
    background: white;
    max-width: 500px;
    margin: auto;
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.card-form input {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid #ddd;
    margin-bottom: 15px;
    font-size: 14px;
    transition: 0.3s;
}

.card-form input:focus {
    border-color: #2e7d32;
    outline: none;
    box-shadow: 0 0 0 3px rgba(46,125,50,0.15);
}

.card-form label {
    font-weight: bold;
    color: #2e7d32;
}

.boton-guardar {
    background: linear-gradient(135deg, #333533, #122142);
    color: white;
    border: none;
    padding: 14px;
    border-radius: 14px;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
    width: 100%;
}

.boton-guardar:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.preview-img {
    width: 100%;
    border-radius: 15px;
    margin-top: 10px;
    display: none;
}
</style>

</head>
<body>

<header class="encabezado">
    <a href="add-plantas.php" class="boton-volver">←</a>
    <h1 class="titulo-app">Indoor Plant</h1>
</header>

<main class="contenedor">

<div class="card-form">
<form method="POST" enctype="multipart/form-data">

    <input type="text" name="nombre_comun" placeholder="Nombre común" required>
    <input type="text" name="nombre_cientifico" placeholder="Nombre científico" required>
    <input type="text" name="familia" placeholder="Familia">
    <input type="text" name="genero" placeholder="Género">
    <input type="text" name="organo" placeholder="Órgano">

    <!-- 🔴 IMPORTANTE: agregar este input oculto -->
    <input type="hidden" name="confianza" value="100">

    <label>Subir Imagen:</label>
    <input type="file" name="imagen" accept="image/*" required>

    <img id="preview" class="preview-img">

    <br><br>

    <button type="submit" class="boton-guardar">
        💾 Guardar Planta
    </button>

</form>
</div>

</main>

<script>
document.querySelector('input[name="imagen"]').addEventListener("change", function(e){
    const reader = new FileReader();
    reader.onload = function(){
        const preview = document.getElementById("preview");
        preview.src = reader.result;
        preview.style.display = "block";
    }
    reader.readAsDataURL(e.target.files[0]);
});
</script>

</body>
</html>