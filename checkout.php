<?php
session_start();
include "conect.php";

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$nombre = $_SESSION['nombre'] ?? '';
$correo = $_SESSION['email'] ?? '';

$total = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout | GrowSystem</title>

<link rel="stylesheet" href="checkout.css">
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
        <a href="carrito.php" class="btn-tienda">← Volver al carrito</a>
    </div>
</header>

<div class="container">

<h2 class="welcome">Checkout 🧾</h2>

<div class="checkout-card">

    <h3>Resumen del pedido</h3>

    <?php if (empty($_SESSION['carrito'])): ?>

        <p>No hay productos en el carrito.</p>

    <?php else: ?>

        <?php foreach ($_SESSION['carrito'] as $id => $cantidad):

            $resultado = $conexion->query("SELECT * FROM productos WHERE id=" . (int)$id);

            if (!$resultado || $resultado->num_rows == 0) continue;

            $producto = $resultado->fetch_assoc();

            $subtotal = $producto['precio'] * $cantidad;
            $total += $subtotal;
        ?>

        <div class="producto">
            <img src="<?php echo htmlspecialchars($producto['imagen']); ?>">

            <div>
                <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                <p>$<?php echo $producto['precio']; ?> x <?php echo $cantidad; ?></p>
                <p><b>Subtotal:</b> $<?php echo $subtotal; ?></p>
            </div>
        </div>

        <?php endforeach; ?>

        <h2 class="total">Total: $<?php echo $total; ?></h2>

        <form action="procesar_pedido.php" method="POST" class="formulario">

            <label>Nombre</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>

            <label>Correo</label>
            <input type="email" name="correo" value="<?php echo htmlspecialchars($correo); ?>" required>

            <label>Dirección</label>
            <textarea name="direccion" required></textarea>

            <label>Celular</label>
            <input type="text" name="celular" required>

            <button type="submit" class="btn-enviar">
                Enviar pedido
            </button>

        </form>

    <?php endif; ?>

</div>
</div>

</body>
</html>