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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Carrito | GrowSystem</title>

<link rel="stylesheet" href="carrito.css">
<link rel="stylesheet" href="styles.css">
</head>

<body>

<!-- HEADER IGUAL A TIENDA -->
<header class="header">
    <div class="logo">
        <div class="plantIcon">
            <img src="icono.png" alt="GrowSystem">
        </div>
        <h1 class="Titulo">GrowSystem</h1>
    </div>

    <div class="header-acciones">
        <a href="productos.php" class="btn-tienda">← Seguir comprando</a>
    </div>
</header>

<div class="container">

    <h2 class="welcome">Tu carrito 🛒</h2>

    <div class="card">

    <?php if (empty($_SESSION['carrito'])) { ?>

        <p class="vacio">Tu carrito está vacío 🌱</p>

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

                <div class="cantidad-control">

                    <form action="agregar_carrito.php" method="POST">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <button type="submit" name="accion" value="menos">−</button>
                    </form>

                    <span><?php echo $cantidad; ?></span>

                    <form action="agregar_carrito.php" method="POST">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <button type="submit" name="accion" value="mas">+</button>
                    </form>

                </div>

                <p>$<?php echo $producto['precio']; ?> c/u</p>
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