<?php

session_start();
include "conect.php";
// 🔒 verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: inicio_indoor.php");
    exit;
}
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['redirect_after_login'] = "productos.php";
    header("Location: inicio_indoor.php");
    exit;
}

include "conect.php";

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$nombre = $_SESSION['nombre'] ?? 'Usuario';
$total_carrito = array_sum($_SESSION['carrito']);

$resultado = $conexion->query("SELECT * FROM productos");


if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$nombre = $_SESSION['nombre'] ?? 'Usuario';

// 🔴 total de productos en carrito
$total_carrito = array_sum($_SESSION['carrito']);

$resultado = $conexion->query("SELECT * FROM productos");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tienda | GrowSystem</title>

<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="tienda.css">
</head>
<body>

<header class="header">
    <div class="logo">
        <div class="plantIcon">
            <img src="icono.png" alt="GrowSystem">
        </div>
        <h1 class="Titulo">GrowSystem</h1>
    </div>

    <div class="header-acciones">
        <a href="index.php" class="btn-tienda">Inicio</a>

        <!-- 🛒 carrito con contador -->
        <a href="carrito.php" class="btn-tienda carrito-icono">
            🛒 Carrito

            <?php if ($total_carrito > 0): ?>
                <span class="badge-carrito" id="badgeCarrito">
                    <?php echo $total_carrito; ?>
                </span>
            <?php endif; ?>
        </a>
    </div>
</header>

<div class="container">
    <h2 class="welcome">
        Tienda GrowSystem 🌱
    </h2>

    <div class="productos-grid">
        <?php while($row = $resultado->fetch_assoc()) { ?>
        
        <div class="producto-card">

            <img 
                src="<?php echo htmlspecialchars($row['imagen']); ?>" 
                class="producto-imagen"
                alt="<?php echo htmlspecialchars($row['nombre']); ?>"
            >

            <div class="producto-info">
                <h3><?php echo htmlspecialchars($row['nombre']); ?></h3>
                <p class="precio">
                    $<?php echo number_format($row['precio'], 2); ?>
                </p>
            </div>

            <!-- 🔥 BOTÓN SIN FORM -->
            <button 
                class="btn-agregar"
                onclick="agregarCarrito(<?php echo $row['id']; ?>)"
            >
                Agregar al carrito
            </button>

        </div>

        <?php } ?>
    </div>
</div>

<script>
function agregarCarrito(id) {
    fetch("agregar_carrito.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "id=" + id + "&accion=agregar"
    })
    .then(response => response.text())
    .then(() => {
        let badge = document.getElementById("badgeCarrito");

        // si ya existe el contador
        if (badge) {
            badge.textContent = parseInt(badge.textContent) + 1;
        } else {
            // si aún no existe
            let carrito = document.querySelector(".carrito-icono");

            let nuevoBadge = document.createElement("span");
            nuevoBadge.className = "badge-carrito";
            nuevoBadge.id = "badgeCarrito";
            nuevoBadge.textContent = "1";

            carrito.appendChild(nuevoBadge);
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}
</script>

</body>
</html>