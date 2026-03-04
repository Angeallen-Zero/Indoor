<?php
$conn = new mysqli("localhost", "root", "", "indoor");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indoor Plantas</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <div class="logo">
            <div class="plantIcon">🌱</div>
            <h1 class="Titulo">Indoor</h1>
        </div>
        <div class="avatar">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="#3d5a6c">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
            </svg>
        </div>
    </header>

    <div class="container">
        <h2 class="welcome">Bienvenido, <span class="username">Angel</span>.</h2>

        <div class="categorias">
            <div class="categorias-header">
                <h3 class="categorias-titulo">Categorías</h3>
                <button class="add-categorias-btn">
                    + Agregar Categoría
                </button>
            </div>
            
            <div class="main-categories">
                <button class="sub-categoria active">Todas</button>
                <button class="sub-categoria">Flor</button>
                <button class="sub-categoria">Fruta</button>
            </div>
        </div>

        <div class="plantas-agregar">

            <!-- TARJETA AGREGAR -->
            <div class="add-planta-tarjeta" onclick="location.href='add-plantas.php'" style="cursor:pointer;">
                <div class="add-icono">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M20 12h-8m0 0V4m0 8v8m0-8H4" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    </svg>
                </div>
                <div class="add-texto">
                    + AGREGAR<br>NUEVA PLANTA
                </div>
            </div>

            <!-- 🔥 PLANTAS DINÁMICAS DESDE BD -->
            <?php
            if (!$conn->connect_error) {
                $result = $conn->query("SELECT * FROM plantas ORDER BY fecha DESC");

                while($row = $result->fetch_assoc()){
            ?>
                <div class="planta-tarjeta">
                   <img src="<?php echo $row['imagen_subida']; ?>" class="planta-imagen">
                    <div class="planta-info">
                        <h3 class="planta-nombre"><?php echo $row['nombre_comun']; ?></h3>
                        <p class="planta-subtitulo">(<?php echo $row['nombre_cientifico']; ?>)</p>
                        <p class="planta-categoria">Categoría: Flor</p>
                    </div>
                </div>
            <?php
                }
                $conn->close();
            }
            ?>

            <!-- 🌿 TUS PLANTAS DE PRUEBA (SIGUEN IGUAL) -->

            <div class="planta-tarjeta">
                <img src="mota.jpeg" alt="Mota" class="planta-imagen">
                <div class="planta-info">
                    <h3 class="planta-nombre">Planta 1</h3>
                    <p class="planta-subtitulo">(Mota)</p>
                    <p class="planta-categoria">Categoría: Flor</p>
                </div>
            </div>

            <div class="planta-tarjeta">
                <img src="rosa.jpg" alt="Rosa" class="planta-imagen">
                <div class="planta-info">
                    <h3 class="planta-nombre">Planta 2</h3>
                    <p class="planta-subtitulo">(Rosa)</p>
                    <p class="planta-categoria">Categoría: Flor</p>
                </div>
            </div>
            
            <div class="planta-tarjeta">
                <img src="sandia.webp" alt="Sandia" class="planta-imagen">
                <div class="planta-info">
                    <h3 class="planta-nombre">Planta 3</h3>
                    <p class="planta-subtitulo">(Sandia)</p>
                    <p class="planta-categoria">Categoría: Futouto</p>
                </div>
            </div>

        </div>
    </div>

</body>
</html>