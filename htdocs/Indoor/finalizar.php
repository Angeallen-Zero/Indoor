<?php
session_start();

// Vaciar carrito
unset($_SESSION['carrito']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Compra realizada</title>

<link rel="stylesheet" href="finalizar.css">
</head>
<body>

<!-- HEADER -->
<div class="header">
  <h1>Compra completada</h1>
  <a href="productos.php">Volver a tienda</a>
</div>

<!-- CONTENEDOR -->
<div class="contenedor">

  <div class="card">

    <div class="icono">✅</div>

    <h2>¡Compra realizada con éxito!</h2>
    <p>Gracias por tu compra, tu pedido ha sido procesado correctamente.</p>

    <a href="productos.php" class="btn">Seguir comprando</a>

  </div>

</div>

</body>
</html>