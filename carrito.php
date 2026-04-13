<?php
session_start();
include "conect.php";

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$total = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Carrito</title>

<link rel="stylesheet" href="carrito.css">
</head>
<body>

<div class="header">
  <h1>Carrito</h1>
  <a href="productos.php">← Seguir comprando</a>
</div>

<div class="contenedor">
<div class="card">

<?php if (empty($_SESSION['carrito'])) { ?>

  <p class="vacio">Carrito vacío</p>

<?php } else { ?>

<?php foreach ($_SESSION['carrito'] as $id => $cantidad) {

    $resultado = $conexion->query("SELECT * FROM productos WHERE id=" . (int)$id);

    if (!$resultado || $resultado->num_rows == 0) continue;

    $producto = $resultado->fetch_assoc();

    $subtotal = $producto['precio'] * $cantidad;
    $total += $subtotal;
?>

<div class="item">

    <img src="<?php echo $producto['imagen']; ?>">

    <div class="info">
        <h3><?php echo $producto['nombre']; ?></h3>
        <p>$<?php echo $producto['precio']; ?> x <?php echo $cantidad; ?></p>
        <p><b>Subtotal:</b> $<?php echo $subtotal; ?></p>
    </div>

    <a href="eliminar_carrito.php?id=<?php echo $id; ?>" class="eliminar">✖</a>

</div>

<?php } ?>

<div class="resumen">
  <div class="fila total">
    <span>Total</span>
    <span>$<?php echo $total; ?></span>
  </div>
</div>

<a href="checkout.php" class="btn-comprar">Ir al checkout</a>

<?php } ?>

</div>
</div>

</body>
</html>