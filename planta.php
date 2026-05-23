<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: acceso.php');
    exit;
}

require_once __DIR__ . '/api/db.php';

$usuarioId   = (int)$_SESSION['usuario_id'];
$instanciaId = isset($_GET['instancia']) ? (int)$_GET['instancia'] : 0;

if ($instanciaId <= 0) {
    header('Location: index.php');
    exit;
}

$db = getDB();

// Verificar acceso y cargar datos completos
$stmt = $db->prepare("
    SELECT i.id AS instancia_id, i.alias, i.ubicacion,
           d.numero_serie,
           p.id AS planta_id, p.nombre_comun, p.nombre_cientifico,
           p.familia, p.genero, p.confianza, p.imagen_subida,
           p.imagen_referencia, p.fecha,
           c.umbral_humedad, c.umbral_nutrientes, c.ventilacion,
           DATE_FORMAT(c.hora_encendido,'%H:%i') AS hora_encendido,
           DATE_FORMAT(c.hora_apagado,  '%H:%i') AS hora_apagado
    FROM instancias i
    JOIN dispositivos d  ON i.dispositivo_id = d.id
    LEFT JOIN plantas p  ON i.planta_id      = p.id
    LEFT JOIN control c  ON i.id             = c.instancia_id
    WHERE i.id = ? AND i.usuario_id = ? AND i.activa = 1
");
$stmt->bind_param('ii', $instanciaId, $usuarioId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();
$db->close();

if (!$data) {
    header('Location: index.php');
    exit;
}

$nombreComun    = htmlspecialchars($data['nombre_comun']      ?? 'Sin identificar');
$nombreCient    = htmlspecialchars($data['nombre_cientifico'] ?? '');
$familia        = htmlspecialchars($data['familia']           ?? '');
$genero         = htmlspecialchars($data['genero']            ?? '');
$alias          = htmlspecialchars($data['alias']             ?? '');
$serie          = htmlspecialchars($data['numero_serie']      ?? '');
$imagen         = htmlspecialchars($data['imagen_subida']     ?? '');
$fecha          = htmlspecialchars($data['fecha']             ?? '');
$umbralHum      = (int)($data['umbral_humedad']    ?? 30);
$umbralNut      = (int)($data['umbral_nutrientes'] ?? 40);
$ventilacion    = (bool)($data['ventilacion']      ?? false);
$horaON         = htmlspecialchars($data['hora_encendido']    ?? '08:00');
$horaOFF        = htmlspecialchars($data['hora_apagado']      ?? '18:00');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $nombreComun ?> — GrowSystem</title>
<link rel="stylesheet" href="styles.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<style>
:root {
  --verde:#31C048; --verde-dark:#219E35; --verde-suave:#EAF7ED;
  --verde-mid:#C5EAC9; --gris-bg:#f0f4f2; --gris-borde:#e2ebe4;
  --gris-texto:#6b7c6e; --texto:#1e2d22; --rojo:#e05252;
  --ambar:#f0a500; --azul:#3d5a6c; --azul-dark:#2c4655;
  --font:'DM Sans',sans-serif; --mono:'DM Mono',monospace;
}
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:var(--font); background:var(--gris-bg); min-height:100vh; }

/* Header */
.header { background:#fff; padding:20px 40px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
.logo { display:flex; align-items:center; gap:10px; }
.Titulo { color:var(--verde); font-size:28px; font-weight:600; }
.plantIcon img { width:40px; height:40px; object-fit:contain; }
.header-right { display:flex; align-items:center; gap:12px; }
.serie-badge { font-family:var(--mono); font-size:12px; background:var(--verde-suave); color:var(--verde-dark); padding:4px 10px; border-radius:6px; border:1px solid var(--verde-mid); }
.pulse-dot { width:8px; height:8px; border-radius:50%; background:var(--verde); animation:pulse 2s infinite; }
.pulse-dot.offline { background:var(--rojo); animation:none; }
@keyframes pulse { 0%,100%{box-shadow:0 0 0 0 rgba(49,192,72,.5)} 50%{box-shadow:0 0 0 5px rgba(49,192,72,0)} }

/* Contenedor */
.container { width:90%; max-width:1200px; margin:30px auto; }
.btn-volver { display:inline-block; margin-bottom:20px; padding:8px 15px; background:var(--azul); color:white; border-radius:8px; text-decoration:none; font-size:14px; }
.btn-volver:hover { background:var(--azul-dark); }

/* Layout principal: izquierda + derecha */
.layout-planta { display:grid; grid-template-columns:1fr 1fr; gap:25px; }
@media(max-width:900px) { .layout-planta { grid-template-columns:1fr; } }

/* Card planta (izquierda) */
.planta-card { background:white; border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,0.1); overflow:hidden; }
.planta-img { width:100%; height:320px; object-fit:cover; }
.planta-img-placeholder { width:100%; height:320px; background:var(--verde-suave); display:flex; align-items:center; justify-content:center; font-size:80px; }
.planta-info { padding:25px; }
.planta-info h2 { font-size:22px; color:var(--texto); margin-bottom:4px; }
.planta-info p { margin-top:8px; font-size:14px; color:var(--gris-texto); }
.planta-info strong { color:var(--texto); }
.planta-alias { margin-top:16px; padding:10px 14px; background:var(--verde-suave); border-radius:8px; font-size:13px; color:var(--verde-dark); font-weight:500; }

/* Edición */
.planta-info input { width:100%; padding:8px; margin-top:5px; border-radius:6px; border:1px solid #ddd; font-family:var(--font); font-size:14px; }
.file-input { margin-top:10px; }
.guardar-btn { margin-top:10px; padding:10px; width:100%; background:var(--azul); color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold; font-family:var(--font); }
.guardar-btn:hover { background:var(--azul-dark); }

/* Botón eliminar */
.btn-eliminar {
  margin-top:10px; padding:10px; width:100%;
  background:none; color:var(--rojo);
  border:1.5px solid #fecaca; border-radius:8px;
  cursor:pointer; font-weight:600; font-family:var(--font);
  font-size:14px; transition:all .2s;
}
.btn-eliminar:hover { background:#fef2f2; border-color:var(--rojo); }

/* Modal confirmación */
.modal-overlay {
  display:none; position:fixed; inset:0;
  background:rgba(0,0,0,.45); z-index:200;
  align-items:center; justify-content:center;
}
.modal-overlay.visible { display:flex; }
.modal-box {
  background:#fff; border-radius:16px;
  padding:32px; max-width:420px; width:90%;
  box-shadow:0 20px 60px rgba(0,0,0,.2);
}
.modal-icono { font-size:36px; text-align:center; margin-bottom:12px; }
.modal-titulo { font-size:18px; font-weight:600; color:var(--texto); text-align:center; margin-bottom:8px; }
.modal-sub { font-size:14px; color:var(--gris-texto); text-align:center; line-height:1.5; margin-bottom:24px; }
.modal-sub strong { color:var(--texto); }
.modal-acciones { display:flex; gap:10px; }
.modal-btn-cancelar {
  flex:1; padding:11px; background:none;
  border:1.5px solid var(--gris-borde); border-radius:10px;
  font-size:14px; font-family:var(--font); cursor:pointer; color:var(--gris-texto);
  transition:all .2s;
}
.modal-btn-cancelar:hover { border-color:var(--verde-mid); color:var(--verde-dark); }
.modal-btn-eliminar {
  flex:1; padding:11px; background:var(--rojo); color:#fff;
  border:none; border-radius:10px; font-size:14px;
  font-weight:600; font-family:var(--font); cursor:pointer;
  transition:background .2s;
}
.modal-btn-eliminar:hover { background:#c0392b; }

/* Panel derecho */
.panel-derecho { display:flex; flex-direction:column; gap:16px; }

/* Grid sensores + relés */
.panel-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
.panel-card { background:white; border-radius:12px; padding:16px; box-shadow:0 4px 16px rgba(0,0,0,0.08); text-align:center; position:relative; overflow:hidden; border-top:3px solid var(--verde); transition:box-shadow .2s; }
.panel-card:hover { box-shadow:0 8px 24px rgba(0,0,0,0.12); }
.panel-card.alerta  { border-top-color:var(--ambar); }
.panel-card.critico { border-top-color:var(--rojo); }
.panel-card h3 { margin-bottom:8px; font-size:13px; color:var(--gris-texto); font-weight:500; }
.panel-valor { font-size:28px; font-weight:700; color:var(--texto); font-family:var(--mono); line-height:1; }
.panel-valor .unidad { font-size:14px; color:var(--gris-texto); font-weight:400; }
.panel-estado { font-size:11px; color:var(--gris-texto); margin-top:4px; }

/* Badges ON/OFF */
.rele-badge { display:inline-block; padding:3px 12px; border-radius:12px; font-size:12px; font-weight:600; font-family:var(--mono); margin-top:6px; transition:all .3s; }
.rele-badge.on  { background:var(--verde-suave); color:var(--verde-dark); border:1px solid var(--verde-mid); }
.rele-badge.off { background:#f3f4f3; color:#999; border:1px solid #e5e7e5; }

/* Barra progreso sensores */
.barra-bg { height:4px; background:var(--gris-bg); border-radius:2px; overflow:hidden; margin-top:8px; }
.barra { height:100%; border-radius:2px; background:var(--verde); transition:width .6s ease; }
.panel-card.alerta  .barra { background:var(--ambar); }
.panel-card.critico .barra { background:var(--rojo); }

/* Gráfica */
.grafica-card { background:white; border-radius:12px; padding:20px; box-shadow:0 4px 16px rgba(0,0,0,0.08); }
.grafica-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
.grafica-titulo { font-size:14px; font-weight:600; color:var(--texto); }
.chart-tabs { display:flex; gap:6px; }
.chart-tab { font-size:11px; padding:3px 10px; border-radius:6px; border:1px solid var(--gris-borde); background:none; cursor:pointer; color:var(--gris-texto); font-family:var(--font); transition:all .15s; }
.chart-tab.active { background:var(--verde-suave); border-color:var(--verde-mid); color:var(--verde-dark); font-weight:500; }
.chart-wrap { position:relative; height:180px; }
.ultima-act { font-size:11px; font-family:var(--mono); color:var(--gris-texto); text-align:right; margin-top:6px; }

/* Controles */
.controles-card { background:white; border-radius:12px; padding:20px; box-shadow:0 4px 16px rgba(0,0,0,0.08); }
.ctrl-titulo { font-size:14px; font-weight:600; color:var(--texto); margin-bottom:14px; }
.ctrl-grupo { margin-bottom:14px; }
.ctrl-label { font-size:12px; font-weight:600; color:var(--gris-texto); text-transform:uppercase; letter-spacing:.4px; display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; }
.ctrl-val { font-family:var(--mono); font-size:12px; color:var(--verde-dark); background:var(--verde-suave); padding:2px 8px; border-radius:5px; }
.slider-custom { width:100%; -webkit-appearance:none; height:4px; border-radius:2px; background:var(--gris-bg); outline:none; cursor:pointer; }
.slider-custom::-webkit-slider-thumb { -webkit-appearance:none; width:16px; height:16px; border-radius:50%; background:var(--verde); border:2px solid #fff; box-shadow:0 1px 4px rgba(49,192,72,.4); cursor:pointer; }
.toggle-row { display:flex; align-items:center; justify-content:space-between; }
.toggle-txt { font-size:13px; color:var(--texto); }
.toggle { position:relative; width:44px; height:24px; }
.toggle input { opacity:0; width:0; height:0; }
.toggle-pista { position:absolute; inset:0; background:#d0d8d2; border-radius:12px; cursor:pointer; transition:background .25s; }
.toggle input:checked + .toggle-pista { background:var(--verde); }
.toggle-pista::before { content:''; position:absolute; width:18px; height:18px; border-radius:50%; background:#fff; top:3px; left:3px; transition:transform .25s; box-shadow:0 1px 4px rgba(0,0,0,.2); }
.toggle input:checked + .toggle-pista::before { transform:translateX(20px); }
.horario-row { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.horario-grupo label { font-size:11px; color:var(--gris-texto); display:block; margin-bottom:3px; }
.horario-grupo input[type="time"] { width:100%; padding:7px 9px; border:1px solid var(--gris-borde); border-radius:7px; font-family:var(--mono); font-size:13px; background:var(--gris-bg); outline:none; }
.horario-grupo input[type="time"]:focus { border-color:var(--verde); background:#fff; }
.divisor { border:none; border-top:1px solid var(--gris-borde); margin:14px 0; }
.btn-guardar-ctrl { width:100%; padding:10px; background:var(--verde); color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; font-family:var(--font); cursor:pointer; transition:background .2s; margin-top:10px; }
.btn-guardar-ctrl:hover { background:var(--verde-dark); }

/* Toast */
.toast { position:fixed; bottom:24px; right:24px; background:var(--texto); color:#fff; padding:10px 18px; border-radius:8px; font-size:13px; opacity:0; transform:translateY(8px); transition:all .3s; pointer-events:none; z-index:999; }
.toast.visible { opacity:1; transform:translateY(0); }
.toast.error { background:var(--rojo); }

/* Banner dispositivo incompatible */
.banner-error-disp {
  display:none;
  background:#fef2f2;
  border:1.5px solid #fecaca;
  border-radius:12px;
  padding:14px 20px;
  margin-bottom:20px;
  align-items:center;
  gap:14px;
}
.banner-error-disp.visible { display:flex; }
.banner-error-icono { font-size:28px; flex-shrink:0; }
.banner-error-texto strong { display:block; font-size:15px; color:var(--rojo); margin-bottom:2px; }
.banner-error-texto span   { font-size:13px; color:#9b3131; line-height:1.4; }
.banner-error-serie {
  margin-left:auto; font-family:var(--mono); font-size:12px;
  background:#fecaca; color:#9b3131; padding:3px 10px;
  border-radius:6px; flex-shrink:0;
}
</style>
</head>
<body>

<header class="header">
    <div class="logo">
        <div class="plantIcon"><img src="icono.png" width="40"></div>
        <h1 class="Titulo">GrowSystem</h1>
    </div>
    <div class="header-right">
        <span class="serie-badge"><?= $serie ?></span>
        <div class="pulse-dot" id="pulseDot" title="Estado ESP32"></div>
    </div>
</header>

<div class="container">

    <a href="index.php" class="btn-volver">⬅ Volver</a>

    <!-- Banner: dispositivo incompatible -->
    <div class="banner-error-disp" id="bannerErrorDisp">
        <span class="banner-error-icono">⚠️</span>
        <div class="banner-error-texto">
            <strong>Dispositivo incompatible</strong>
            <span id="bannerErrorMsg">
                El ESP32 asignado no está enviando datos a esta instancia.
                Verifica que el token del firmware corresponde al dispositivo
                <strong><?= $serie ?></strong>.
            </span>
        </div>
        <span class="banner-error-serie" id="bannerSerie"><?= $serie ?></span>
    </div>

    <div class="layout-planta">

        <!-- ── IZQUIERDA: info de la planta ── -->
        <div class="planta-card">

            <?php if ($imagen): ?>
                <img src="<?= $imagen ?>" class="planta-img" id="previewImagen">
            <?php else: ?>
                <div class="planta-img-placeholder" id="previewImagen">🌿</div>
            <?php endif; ?>

            <form action="editar-planta.php" method="POST"
                  enctype="multipart/form-data" class="planta-info">

                <input type="hidden" name="id"            value="<?= (int)$data['planta_id'] ?>">
                <input type="hidden" name="imagen_actual"  value="<?= $imagen ?>">

                <h2 id="nombreTexto"><?= $nombreComun ?></h2>
                <input type="text" name="nombre_comun" id="nombreInput"
                       value="<?= $nombreComun ?>" style="display:none">

                <p><strong>Nombre científico:</strong>
                    <span id="cientificoTexto"><?= $nombreCient ?></span>
                    <input type="text" name="nombre_cientifico" id="cientificoInput"
                           value="<?= $nombreCient ?>" style="display:none">
                </p>
                <p><strong>Familia:</strong>
                    <span id="familiaTexto"><?= $familia ?></span>
                    <input type="text" name="familia" id="familiaInput"
                           value="<?= $familia ?>" style="display:none">
                </p>
                <p><strong>Género:</strong>
                    <span id="generoTexto"><?= $genero ?></span>
                    <input type="text" name="genero" id="generoInput"
                           value="<?= $genero ?>" style="display:none">
                </p>
                <?php if ($fecha): ?>
                <p><strong>Fecha:</strong> <?= $fecha ?></p>
                <?php endif; ?>

                <input type="file" name="imagen" id="imagenInput"
                       class="file-input" style="display:none" accept="image/*">

                <div class="planta-alias">📍 <?= $alias ?></div>

                <div style="margin-top:15px; display:flex; gap:8px;">
                    <button type="button" onclick="activarEdicion()" id="btnEditar" class="guardar-btn">✏️ Editar</button>
                    <button type="submit"  id="btnGuardar"  class="guardar-btn" style="display:none">💾 Guardar</button>
                    <button type="button" onclick="cancelarEdicion()" id="btnCancelar" class="guardar-btn" style="display:none;background:#777">Cancelar</button>
                </div>
            </form>

            <!-- Botón eliminar fuera del form de edición -->
            <div style="padding:0 25px 20px">
                <button type="button" class="btn-eliminar" onclick="confirmarEliminar()">
                    🗑 Eliminar planta y desvincular dispositivo
                </button>
            </div>
        </div>

        <!-- ── DERECHA: datos del ESP32 ── -->
        <div class="panel-derecho">

            <!-- Sensores -->
            <div class="panel-grid">
                <div class="panel-card" id="cardHumedad">
                    <h3>💧 Humedad del suelo</h3>
                    <div class="panel-valor">
                        <span id="valHumedad">--</span>
                        <span class="unidad">%</span>
                    </div>
                    <div class="barra-bg"><div class="barra" id="barraHumedad" style="width:0%"></div></div>
                    <div class="panel-estado" id="estadoHumedad">Esperando datos...</div>
                </div>

                <div class="panel-card" id="cardNutrientes">
                    <h3>🌱 Nutrientes</h3>
                    <div class="panel-valor">
                        <span id="valNutrientes">--</span>
                        <span class="unidad">%</span>
                    </div>
                    <div class="barra-bg"><div class="barra" id="barraNutrientes" style="width:0%"></div></div>
                    <div class="panel-estado" id="estadoNutrientes">Esperando datos...</div>
                </div>
            </div>

            <!-- Relés -->
            <div class="panel-grid">
                <div class="panel-card">
                    <h3>💦 Bomba</h3>
                    <span class="rele-badge off" id="badgeBomba">OFF</span>
                </div>
                <div class="panel-card">
                    <h3>🌬 Ventilador</h3>
                    <span class="rele-badge off" id="badgeVent">OFF</span>
                </div>
                <div class="panel-card">
                    <h3>💡 Luces</h3>
                    <span class="rele-badge off" id="badgeLuces">OFF</span>
                </div>
                <div class="panel-card">
                    <h3>🧪 Surtidor</h3>
                    <span class="rele-badge off" id="badgeSurtidor">OFF</span>
                </div>
            </div>

            <!-- Gráfica -->
            <div class="grafica-card">
                <div class="grafica-header">
                    <span class="grafica-titulo">Historial</span>
                    <div class="chart-tabs">
                        <button class="chart-tab active" onclick="cambiarGrafica('humedad',this)">Humedad</button>
                        <button class="chart-tab" onclick="cambiarGrafica('nutrientes',this)">Nutrientes</button>
                        <button class="chart-tab" onclick="cambiarGrafica('ambos',this)">Ambos</button>
                    </div>
                </div>
                <div class="chart-wrap"><canvas id="grafica"></canvas></div>
                <p class="ultima-act" id="ultimaAct">Actualizando...</p>
            </div>

            <!-- Controles -->
            <div class="controles-card">
                <div class="ctrl-titulo">Parámetros de control</div>

                <div class="ctrl-grupo">
                    <div class="ctrl-label">Umbral riego
                        <span class="ctrl-val" id="lblHum"><?= $umbralHum ?>%</span>
                    </div>
                    <input type="range" class="slider-custom" id="sliderHum"
                           min="0" max="100" value="<?= $umbralHum ?>"
                           oninput="document.getElementById('lblHum').textContent=this.value+'%'">
                </div>

                <div class="ctrl-grupo">
                    <div class="ctrl-label">Umbral nutrientes
                        <span class="ctrl-val" id="lblNut"><?= $umbralNut ?>%</span>
                    </div>
                    <input type="range" class="slider-custom" id="sliderNut"
                           min="0" max="100" value="<?= $umbralNut ?>"
                           oninput="document.getElementById('lblNut').textContent=this.value+'%'">
                </div>

                <hr class="divisor">

                <div class="ctrl-grupo">
                    <div class="toggle-row">
                        <span class="toggle-txt">Ventilación forzada</span>
                        <label class="toggle">
                            <input type="checkbox" id="toggleVent" <?= $ventilacion ? 'checked' : '' ?>>
                            <span class="toggle-pista"></span>
                        </label>
                    </div>
                </div>

                <hr class="divisor">

                <div class="ctrl-grupo">
                    <div class="ctrl-label" style="margin-bottom:8px">Horario de luces</div>
                    <div class="horario-row">
                        <div class="horario-grupo">
                            <label>Encendido</label>
                            <input type="time" id="horaON" value="<?= $horaON ?>">
                        </div>
                        <div class="horario-grupo">
                            <label>Apagado</label>
                            <input type="time" id="horaOFF" value="<?= $horaOFF ?>">
                        </div>
                    </div>
                </div>

                <button class="btn-guardar-ctrl" onclick="guardarControl()">
                    Guardar parámetros
                </button>
            </div>

        </div><!-- /panel-derecho -->
    </div><!-- /layout-planta -->
</div>

<!-- Modal eliminar -->
<div class="modal-overlay" id="modalEliminar">
    <div class="modal-box">
        <div class="modal-icono">🗑</div>
        <div class="modal-titulo">Eliminar planta</div>
        <div class="modal-sub">
            ¿Estás seguro de que quieres eliminar
            <strong><?= $nombreComun ?></strong>?<br><br>
            Esto desvinculará el dispositivo <strong><?= $serie ?></strong>
            de tu cuenta y quedará disponible para ser activado de nuevo.
            Los datos históricos se eliminarán.
        </div>
        <div class="modal-acciones">
            <button class="modal-btn-cancelar" onclick="cerrarModal()">Cancelar</button>
            <button class="modal-btn-eliminar" onclick="eliminarPlanta()">Sí, eliminar</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const INSTANCIA_ID = <?= $instanciaId ?>;
const API_BASE     = 'api/datos.php';
let grafica        = null;
let modoGrafica    = 'humedad';

// ── Chart.js ─────────────────────────────────
function initGrafica() {
    const ctx = document.getElementById('grafica').getContext('2d');
    grafica = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                { label:'Humedad (%)',    data:[], borderColor:'#31C048', backgroundColor:'rgba(49,192,72,.08)', borderWidth:2, pointRadius:2, tension:0.4, fill:true },
                { label:'Nutrientes (%)', data:[], borderColor:'#3d5a6c', backgroundColor:'rgba(61,90,108,.08)', borderWidth:2, pointRadius:2, tension:0.4, fill:true, hidden:true }
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            interaction:{ mode:'index', intersect:false },
            plugins:{ legend:{display:false}, tooltip:{ backgroundColor:'#1e2d22', titleColor:'#9ab09c', bodyColor:'#fff', padding:8, cornerRadius:6 } },
            scales:{
                x:{ grid:{color:'#f0f4f2'}, ticks:{color:'#6b7c6e', font:{family:"'DM Mono'", size:10}, maxTicksLimit:7} },
                y:{ min:0, max:100, grid:{color:'#f0f4f2'}, ticks:{color:'#6b7c6e', font:{size:11}, callback:v=>v+'%'} }
            }
        }
    });
}

function cambiarGrafica(modo, btn) {
    modoGrafica = modo;
    document.querySelectorAll('.chart-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    if (!grafica) return;
    grafica.data.datasets[0].hidden = (modo === 'nutrientes');
    grafica.data.datasets[1].hidden = (modo === 'humedad');
    grafica.update('none');
}

// ── Polling estado (5s) ───────────────────────
async function fetchEstado() {
    try {
        const res  = await fetch(`${API_BASE}?instancia_id=${INSTANCIA_ID}&accion=estado`);
        const data = await res.json();
        if (!data.ok) return;

        const e = data.estado;
        const c = data.control;

        // Sensores
        actualizarSensor('valHumedad',    'barraHumedad',    'cardHumedad',    'estadoHumedad',    e.humedad,    c.umbral_humedad);
        actualizarSensor('valNutrientes', 'barraNutrientes', 'cardNutrientes', 'estadoNutrientes', e.nutrientes, c.umbral_nutrientes);

        // Relés
        setBadge('badgeBomba',    e.bomba);
        setBadge('badgeVent',     e.ventilador);
        setBadge('badgeLuces',    e.luces);
        setBadge('badgeSurtidor', e.surtidor);

        // Controles (si no está editando)
        if (document.activeElement.tagName !== 'INPUT') {
            document.getElementById('sliderHum').value   = c.umbral_humedad;
            document.getElementById('sliderNut').value   = c.umbral_nutrientes;
            document.getElementById('lblHum').textContent = c.umbral_humedad    + '%';
            document.getElementById('lblNut').textContent = c.umbral_nutrientes + '%';
            document.getElementById('toggleVent').checked = c.ventilacion;
            document.getElementById('horaON').value  = c.hora_encendido;
            document.getElementById('horaOFF').value = c.hora_apagado;
        }

        // Dot conexión
        const hace = e.ultima_actualizacion
            ? Math.floor((Date.now() - new Date(e.ultima_actualizacion)) / 1000) : 999;
        const dot = document.getElementById('pulseDot');
        dot.classList.toggle('offline', hace > 20);
        dot.title = hace < 20 ? `ESP32 en línea — hace ${hace}s` : `Sin reporte (hace ${hace}s)`;

        document.getElementById('ultimaAct').textContent = e.ultima_actualizacion
            ? 'Último reporte: ' + new Date(e.ultima_actualizacion).toLocaleTimeString('es-MX')
            : 'Sin datos del ESP32 aún';

    } catch(err) { console.warn('[Planta] Error fetch estado:', err); }
}

function actualizarSensor(idVal, idBarra, idCard, idEstado, valor, umbral) {
    document.getElementById(idVal).textContent = valor !== null ? valor.toFixed(1) : '--';
    if (valor !== null) {
        document.getElementById(idBarra).style.width = Math.min(valor, 100) + '%';
        const card = document.getElementById(idCard);
        card.classList.remove('alerta','critico');
        const est = document.getElementById(idEstado);
        if (valor < umbral * 0.5) { card.classList.add('critico'); est.textContent = '⚠ Nivel crítico'; }
        else if (valor < umbral)  { card.classList.add('alerta');  est.textContent = '⚠ Por debajo del umbral'; }
        else                       { est.textContent = '✓ Normal'; }
    }
}

function setBadge(id, on) {
    const el = document.getElementById(id);
    el.textContent = on ? 'ON' : 'OFF';
    el.className   = 'rele-badge ' + (on ? 'on' : 'off');
}

// ── Polling gráfica (15s) ─────────────────────
async function fetchGrafica() {
    try {
        const res  = await fetch(`${API_BASE}?instancia_id=${INSTANCIA_ID}&accion=grafica&limite=50`);
        const data = await res.json();
        if (!data.ok || !grafica) return;
        grafica.data.labels            = data.lecturas.map(l => l.hora);
        grafica.data.datasets[0].data  = data.lecturas.map(l => l.humedad);
        grafica.data.datasets[1].data  = data.lecturas.map(l => l.nutrientes);
        grafica.data.datasets[0].hidden = (modoGrafica === 'nutrientes');
        grafica.data.datasets[1].hidden = (modoGrafica === 'humedad');
        grafica.update();
    } catch(err) { console.warn('[Planta] Error fetch gráfica:', err); }
}

// ── Guardar control ───────────────────────────
async function guardarControl() {
    const btn = document.querySelector('.btn-guardar-ctrl');
    btn.textContent = 'Guardando...';
    try {
        const res = await fetch(`${API_BASE}?instancia_id=${INSTANCIA_ID}&accion=control`, {
            method:      'POST',
            credentials: 'same-origin',
            headers:     {'Content-Type':'application/json'},
            body: JSON.stringify({
                umbral_humedad:    parseInt(document.getElementById('sliderHum').value),
                umbral_nutrientes: parseInt(document.getElementById('sliderNut').value),
                ventilacion:       document.getElementById('toggleVent').checked,
                hora_encendido:    document.getElementById('horaON').value,
                hora_apagado:      document.getElementById('horaOFF').value,
            })
        });
        const data = await res.json();
        mostrarToast(data.ok ? '✓ Parámetros guardados' : 'Error al guardar', !data.ok);
    } catch(e) { mostrarToast('Error de conexión', true); }
    btn.textContent = 'Guardar parámetros';
}

function mostrarToast(msg, esError=false) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className   = 'toast visible' + (esError ? ' error' : '');
    setTimeout(() => { t.className = 'toast'; }, 3000);
}

// ── Modal eliminar ────────────────────────────
function confirmarEliminar() {
    document.getElementById('modalEliminar').classList.add('visible');
}
function cerrarModal() {
    document.getElementById('modalEliminar').classList.remove('visible');
}

async function eliminarPlanta() {
    const btn = document.querySelector('.modal-btn-eliminar');
    btn.textContent = 'Eliminando...';
    btn.disabled    = true;

    try {
        const res  = await fetch('api/eliminar-instancia.php', {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ instancia_id: INSTANCIA_ID })
        });
        const data = await res.json();

        if (data.ok) {
            window.location.href = 'index.php';
        } else {
            cerrarModal();
            mostrarToast(data.error || 'Error al eliminar', true);
            btn.textContent = 'Sí, eliminar';
            btn.disabled    = false;
        }
    } catch(e) {
        cerrarModal();
        mostrarToast('Error de conexión', true);
        btn.textContent = 'Sí, eliminar';
        btn.disabled    = false;
    }
}

// Cerrar modal al hacer clic fuera
document.getElementById('modalEliminar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
function activarEdicion() {
    ['nombreTexto','cientificoTexto','familiaTexto','generoTexto'].forEach(id => document.getElementById(id).style.display='none');
    ['nombreInput','cientificoInput','familiaInput','generoInput','imagenInput'].forEach(id => document.getElementById(id).style.display='block');
    document.getElementById('btnEditar').style.display   = 'none';
    document.getElementById('btnGuardar').style.display  = 'block';
    document.getElementById('btnCancelar').style.display = 'block';
}
function cancelarEdicion() { location.reload(); }

document.getElementById('imagenInput').addEventListener('change', function(e) {
    const reader = new FileReader();
    reader.onload = () => { document.getElementById('previewImagen').src = reader.result; };
    reader.readAsDataURL(e.target.files[0]);
});

// ── Init ──────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initGrafica();
    fetchEstado();
    fetchGrafica();
    setInterval(fetchEstado,  5000);
    setInterval(fetchGrafica, 15000);
});
</script>

</body>
</html>
