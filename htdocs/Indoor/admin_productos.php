<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: acceso.php');
    exit;
}

include "conect.php";

$productos = $conexion->query("SELECT * FROM productos ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Productos | Admin</title>
<link rel="stylesheet" href="admin_productos.css">
</head>
<body>

<div class="header">
    <h1>🛒 Gestión de productos</h1>
    <a href="admin.php">← Volver</a>
</div>

<div class="contenedor">
    <a href="nuevo_producto.php" class="btn-nuevo">+ Nuevo producto</a>

    <div class="grid">
        <?php while($producto = $productos->fetch_assoc()) { ?>

            <div class="card">
                <img src="<?php echo $producto['imagen']; ?>">

                <h3><?php echo $producto['nombre']; ?></h3>

                <p>$<?php echo $producto['precio']; ?></p>

                <div class="acciones">
                    <a href="editar_producto.php?id=<?php echo $producto['id']; ?>">Editar</a>
                    <a href="eliminar_producto.php?id=<?php echo $producto['id']; ?>">Eliminar</a>
                </div>
            </div>

        <?php } ?>
    </div>
</div>

</body>
</html>