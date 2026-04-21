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
<title>Checkout</title>

<link rel="stylesheet" href="checkout.css">
</head>
<body>

<div class="header">
  <h1>Checkout</h1>
  <a href="carrito.php">← Volver</a>
</div>

<div class="contenedor">
<div class="card">

<h2>Resumen</h2>

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
  <h3><?php echo $producto['nombre']; ?></h3>
  <p>$<?php echo $producto['precio']; ?> x <?php echo $cantidad; ?></p>
  <p><b>Subtotal:</b> $<?php echo $subtotal; ?></p>
</div>

<?php } ?>

<div class="resumen">
  <div class="fila total">
    <span>Total</span>
    <span>$<?php echo $total; ?></span>
  </div>
</div>

<a href="finalizar.php" class="btn-comprar">Confirmar compra</a>

<?php } ?>

</div>
</div>

</body>
</html>