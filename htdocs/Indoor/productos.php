<?php
session_start();
include "conect.php";

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if (!isset($_SESSION['cantidad_temp'])) {
    $_SESSION['cantidad_temp'] = [];
}

$resultado = $conexion->query("SELECT * FROM productos");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Tienda</title>

<link rel="stylesheet" href="tienda.css">
</head>
<body>

<div class="header">
  <img src="icono.png" class="logo">
  <h1>Tienda</h1>
  <a href="carrito.php">🛒 Carrito</a>
</div>

<div class="contenedor">
<div class="grid">

<?php while($row = $resultado->fetch_assoc()) {

$id = $row['id'];

if (!isset($_SESSION['cantidad_temp'][$id])) {
    $_SESSION['cantidad_temp'][$id] = 0;
}
?>

<div class="card">

  <img src="<?php echo $row['imagen']; ?>">

  <h3><?php echo $row['nombre']; ?></h3>

  <p>$<?php echo $row['precio']; ?></p>

  <!-- ➖ MENOS -->
  <form action="agregar_carrito.php" method="POST" style="display:inline;">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <button type="submit" name="accion" value="menos">−</button>
  </form>

  <!-- CANTIDAD -->
  <span><?php echo $_SESSION['cantidad_temp'][$id]; ?></span>

  <!-- ➕ MAS -->
  <form action="agregar_carrito.php" method="POST" style="display:inline;">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <button type="submit" name="accion" value="mas">+</button>
  </form>

  <!-- AGREGAR -->
  <form action="agregar_carrito.php" method="POST">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <button type="submit" name="accion" value="agregar" class="btn-agregar">
      Agregar al carrito
    </button>
  </form>

</div>

<?php } ?>

</div>
</div>

</body>
</html>