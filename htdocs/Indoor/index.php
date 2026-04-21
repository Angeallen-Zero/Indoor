<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: inicio_indoor.php');
    exit;
}

require_once __DIR__ . '/api/db.php';

$usuarioId = (int)$_SESSION['usuario_id'];
$nombre    = $_SESSION['nombre'] ?? 'Usuario';

$db = getDB();

// Plantas del usuario con instancia_id para link al dashboard
$stmt = $db->prepare("
    SELECT p.*, i.id AS instancia_id, i.alias
    FROM plantas p
    JOIN instancias i ON i.planta_id = p.id
    WHERE i.usuario_id = ? AND i.activa = 1
    ORDER BY p.fecha DESC
");
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$plantas = $stmt->get_result();
$stmt->close();
$db->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GrowSystem</title>

<!-- 🔥 VANILLA CALENDAR PRIMERO -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/build/vanilla-calendar.min.css">
<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="calendario.css">

<!-- SCRIPT CALENDARIO -->
<script src="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/build/vanilla-calendar.min.js"></script>

<style>
.header-acciones { display:flex; align-items:center; gap:12px; }
.btn-salir {
    font-size:13px; color:#6b7c6e; text-decoration:none;
    padding:6px 14px; border-radius:8px; border:1px solid #e2ebe4;
    transition:background .2s;
}
.btn-salir:hover { background:#f0f4f2; }
.sin-plantas {
    grid-column:1/-1; text-align:center;
    padding:60px 20px; color:#6b7c6e;
}
.sin-plantas .icono { font-size:52px; margin-bottom:12px; }
.sin-plantas p { font-size:15px; margin-top:6px; }
.planta-imagen-placeholder {
    width:100%; height:220px;
    background:#EAF7ED;
    display:flex; align-items:center; justify-content:center;
    font-size:52px;
}
</style>
</head>
<body>

<header class="header">
    <div class="logo">
        <div class="plantIcon"><img src="icono.png" alt="GrowSystem"></div>
        <h1 class="Titulo">GrowSystem</h1>
    </div>
    <div class="header-acciones">
   <a href="productos.php" class="btn-tienda">Tienda</a>
    <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
     

    <a href="admin.php" class="admin-btn">
        Panel Admin
    </a>
    <?php endif; ?>

    <div class="profile-menu">
        <button class="profile-btn" onclick="toggleMenu()">
            🍀
        </button>

        <div class="dropdown-profile" id="profileDropdown">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?= strtoupper(substr($nombre,0,1)) ?>
                </div>
                <h4><?= htmlspecialchars($nombre) ?></h4>
                <p><?= htmlspecialchars($_SESSION['rol'] ?? 'Usuario') ?></p>
            </div>

            <a href="perfil.php">Mi perfil</a>
            <a href="mis_plantas.php">Mis plantas</a>
            <a href="configuracion.php">Configuración</a>
            <a href="logout.php" class="logout-link">Cerrar sesión</a>
        </div>
    </div>
</div>
</header>

<div class="container">

    <h2 class="welcome">
        Bienvenido, <span class="username"><?= htmlspecialchars($nombre) ?></span>.
    </h2>

    <div class="categorias">
        <div class="categorias-header">
            <h3 class="categorias-titulo">Mis plantas</h3>
        </div>
        <div class="main-categories">
            <button class="sub-categoria active">Todas</button>
        </div>
    </div>

    <div class="plantas-agregar">

        <!-- Agregar planta → activar.php -->
        <div class="add-planta-tarjeta" onclick="location.href='activar.php'">
            <div class="add-icono">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M20 12h-8m0 0V4m0 8v8m0-8H4"
                        stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="12" r="10"
                        stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                </svg>
            </div>
            <div class="add-texto">+ AGREGAR<br>NUEVA PLANTA</div>
        </div>

        <?php if ($plantas->num_rows === 0): ?>
        <div class="sin-plantas">
            <div class="icono">🌱</div>
            <p>Aún no tienes plantas registradas.</p>
            <p>Presiona el botón para agregar tu primera planta.</p>
        </div>
        <?php else: ?>

        <?php while ($row = $plantas->fetch_assoc()): ?>
        <!-- Al presionar → dashboard de esa planta -->
        <div class="planta-tarjeta"
             onclick="location.href='planta.php?instancia=<?= $row['instancia_id'] ?>'">

            <?php if (!empty($row['imagen_subida'])): ?>
                <img src="<?= htmlspecialchars($row['imagen_subida']) ?>" class="planta-imagen">
            <?php else: ?>
                <div class="planta-imagen-placeholder">🌿</div>
            <?php endif; ?>

            <div class="planta-info">
                <h3 class="planta-nombre"><?= htmlspecialchars($row['nombre_comun']) ?></h3>
                <p class="planta-subtitulo">(<?= htmlspecialchars($row['nombre_cientifico']) ?>)</p>
                <p class="planta-categoria"><?= htmlspecialchars($row['familia'] ?? 'Sin clasificar') ?></p>
            </div>
        </div>
        <?php endwhile; ?>

        <?php endif; ?>
    </div>
</div>
</div> <!-- cierre .container de miguel -->

<!-- 🔥 CALENDARIO -->
<?php include 'calendario.php'; ?>
<script>
function toggleMenu() {
    document.getElementById("profileDropdown").classList.toggle("active");
}

window.addEventListener("click", function(e) {
    if (!e.target.closest(".profile-menu")) {
        document.getElementById("profileDropdown").classList.remove("active");
    }
});
</script>
</body>
</html>
