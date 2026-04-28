<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: acceso.php');
    exit;
}

include "conect.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* Guardar cambios */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)$_POST['id'];
    $nombre = trim($_POST['nombre']);
    $precio = (float)$_POST['precio'];

    // Mantener imagen actual por defecto
    $imagenRuta = $_POST['imagen_actual'];

    /* Si suben nueva imagen */
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $carpeta = "uploads/productos/";

        // Crear carpeta si no existe
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
        UPDATE productos 
        SET nombre = ?, precio = ?, imagen = ?
        WHERE id = ?
    ");

    $stmt->bind_param("sdsi", $nombre, $precio, $imagenRuta, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_productos.php");
    exit;
}

/* Obtener producto */
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$producto = $resultado->fetch_assoc();
$stmt->close();

if (!$producto) {
    die("Producto no encontrado");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar producto</title>
<style>
body{
    font-family:Arial,sans-serif;
    background:#f0f4f2;
    padding:30px;
}
.form-card{
    max-width:500px;
    margin:auto;
    background:white;
    padding:25px;
    border-radius:15px;
    box-shadow:0 4px 14px rgba(0,0,0,.08);
}
input{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #ddd;
    border-radius:10px;
}
button{
    width:100%;
    background:#31C048;
    color:white;
    border:none;
    padding:14px;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
}
img{
    width:150px;
    border-radius:10px;
    margin:10px 0;
}
</style>
</head>
<body>

<div class="form-card">
    <h2>✏️ Editar producto</h2>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
        <input type="hidden" name="imagen_actual" value="<?php echo $producto['imagen']; ?>">

        <label>Nombre</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>

        <label>Precio</label>
        <input type="number" step="0.01" name="precio" value="<?php echo $producto['precio']; ?>" required>

        <label>Imagen actual</label><br>
        <img src="<?php echo $producto['imagen']; ?>" alt="Producto">

        <label>Nueva imagen</label>
        <input type="file" name="imagen" accept="image/*">

        <button type="submit">Guardar cambios</button>
    </form>
</div>

</body>
</html>