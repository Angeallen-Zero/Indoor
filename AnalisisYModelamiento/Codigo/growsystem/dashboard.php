<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/api/db.php';

$usuarioId   = (int)$_SESSION['usuario_id'];
$rolUsuario  = $_SESSION['rol'] ?? 'cliente';

$instanciaId = isset($_GET['instancia']) ? (int)$_GET['instancia'] : 0;

$db = getDB();

if ($instanciaId <= 0) {
    $r = $db->prepare("SELECT id FROM instancias WHERE usuario_id = ? AND activa = 1 ORDER BY id ASC LIMIT 1");
    $r->bind_param('i', $usuarioId);
    $r->execute();
    $row = $r->get_result()->fetch_assoc();
    $r->close();
    if ($row) {
        $instanciaId = (int)$row['id'];
    } else {
        header('Location: activar.php');
        exit;
    }
}

$stmt = $db->prepare("
    SELECT i.alias, i.ubicacion, d.numero_serie, p.nombre_comun
    FROM instancias i
    JOIN dispositivos d ON i.dispositivo_id = d.id
    LEFT JOIN plantas p ON i.planta_id = p.id
    WHERE i.id = ? AND (i.usuario_id = ? OR ? = 'admin')
");
$stmt->bind_param('iis', $instanciaId, $usuarioId, $rolUsuario);
$stmt->execute();
$instancia = $stmt->get_result()->fetch_assoc();
$stmt->close();
$db->close();

if (!$instancia) {
    header('Location: index.php');
    exit;
}

$alias     = htmlspecialchars($instancia['alias']        ?? 'Mi invernadero');
$ubicacion = htmlspecialchars($instancia['ubicacion']    ?? '');
$serie     = htmlspecialchars($instancia['numero_serie'] ?? '');
$planta    = htmlspecialchars($instancia['nombre_comun'] ?? 'Sin identificar');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GrowSystem — Dashboard</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<style>
/* ── Variables ───────────────────────────────── */
:root {
  --verde:        #31C048;
  --verde-dark:   #219E35;
  --verde-suave:  #EAF7ED;
  --verde-mid:    #C5EAC9;
  --azul-pizarra: #3d5a6c;
  --azul-dark:    #2c4655;
  --gris-bg:      #f0f4f2;
  --gris-card:    #ffffff;
  --gris-borde:   #e2ebe4;
  --gris-texto:   #6b7c6e;
  --texto:        #1e2d22;
  --rojo:         #e05252;
  --ambar:        #f0a500;
  --font:         'DM Sans', sans-serif;
  --mono:         'DM Mono', monospace;
  --radio:        14px;
  --sombra:       0 2px 16px rgba(30,45,34,.07);
  --sombra-hover: 0 6px 28px rgba(30,45,34,.13);
}

* { margin:0; padding:0; box-sizing:border-box; }

body {
  font-family: var(--font);
  background: var(--gris-bg);
  color: var(--texto);
  min-height: 100vh;
}

/* ── Header ─────────────────────────────────── */
.header {
  background: #fff;
  border-bottom: 1px solid var(--gris-borde);
  padding: 0 32px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 100;
}
.header-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
}
.header-logo img { width: 36px; height: 36px; object-fit: contain; }
.header-logo span {
  font-size: 20px;
  font-weight: 600;
  color: var(--verde);
  letter-spacing: -.3px;
}
.header-right { display: flex; align-items: center; gap: 16px; }
.header-serie {
  font-family: var(--mono);
  font-size: 12px;
  background: var(--verde-suave);
  color: var(--verde-dark);
  padding: 4px 10px;
  border-radius: 6px;
  border: 1px solid var(--verde-mid);
}
.pulse-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: var(--verde);
  animation: pulse 2s infinite;
}
.pulse-dot.offline { background: var(--rojo); animation: none; }
@keyframes pulse {
  0%,100% { box-shadow: 0 0 0 0 rgba(49,192,72,.5); }
  50%      { box-shadow: 0 0 0 5px rgba(49,192,72,.0); }
}
.btn-logout {
  font-size: 13px;
  color: var(--gris-texto);
  text-decoration: none;
  padding: 6px 12px;
  border-radius: 8px;
  transition: background .2s;
}
.btn-logout:hover { background: var(--gris-bg); }

/* ── Layout principal ───────────────────────── */
.page { max-width: 1100px; margin: 0 auto; padding: 32px 24px 64px; }

.page-title {
  font-size: 26px;
  font-weight: 600;
  color: var(--texto);
  margin-bottom: 4px;
}
.page-sub {
  font-size: 14px;
  color: var(--gris-texto);
  margin-bottom: 28px;
}
.page-sub span { color: var(--verde-dark); font-weight: 500; }

/* ── Grid de métricas ────────────────────────── */
.metricas {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.metrica-card {
  background: var(--gris-card);
  border: 1px solid var(--gris-borde);
  border-radius: var(--radio);
  padding: 20px 22px;
  box-shadow: var(--sombra);
  position: relative;
  overflow: hidden;
  transition: box-shadow .2s;
}
.metrica-card:hover { box-shadow: var(--sombra-hover); }

.metrica-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: var(--verde);
  border-radius: var(--radio) var(--radio) 0 0;
}
.metrica-card.alerta::before { background: var(--ambar); }
.metrica-card.critico::before { background: var(--rojo); }

.metrica-label {
  font-size: 12px;
  font-weight: 500;
  color: var(--gris-texto);
  text-transform: uppercase;
  letter-spacing: .5px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.metrica-icono { font-size: 14px; }

.metrica-valor {
  font-size: 36px;
  font-weight: 600;
  color: var(--texto);
  font-family: var(--mono);
  line-height: 1;
  margin-bottom: 6px;
}
.metrica-valor .unidad {
  font-size: 16px;
  color: var(--gris-texto);
  font-weight: 400;
}

.metrica-barra-bg {
  height: 4px;
  background: var(--gris-bg);
  border-radius: 2px;
  overflow: hidden;
  margin-top: 10px;
}
.metrica-barra {
  height: 100%;
  border-radius: 2px;
  background: var(--verde);
  transition: width .6s ease;
}
.metrica-card.alerta  .metrica-barra { background: var(--ambar); }
.metrica-card.critico .metrica-barra { background: var(--rojo); }

/* ── Grid estado relés ──────────────────────── */
.reles-titulo {
  font-size: 15px;
  font-weight: 600;
  color: var(--texto);
  margin-bottom: 12px;
}
.reles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 12px;
  margin-bottom: 24px;
}
.rele-card {
  background: var(--gris-card);
  border: 1px solid var(--gris-borde);
  border-radius: var(--radio);
  padding: 16px;
  box-shadow: var(--sombra);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  transition: box-shadow .2s;
}
.rele-card:hover { box-shadow: var(--sombra-hover); }
.rele-nombre {
  font-size: 12px;
  font-weight: 500;
  color: var(--gris-texto);
  text-align: center;
  text-transform: uppercase;
  letter-spacing: .4px;
}
.rele-badge {
  font-size: 12px;
  font-weight: 600;
  padding: 3px 12px;
  border-radius: 20px;
  font-family: var(--mono);
  transition: all .3s;
}
.rele-badge.on  { background: var(--verde-suave); color: var(--verde-dark); border: 1px solid var(--verde-mid); }
.rele-badge.off { background: #f3f4f3; color: #999; border: 1px solid #e5e7e5; }
.rele-icono { font-size: 22px; }

/* ── Dos columnas: gráfica + controles ─────── */
.dos-col {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 20px;
  align-items: start;
}
@media (max-width: 820px) {
  .dos-col { grid-template-columns: 1fr; }
}

/* ── Card genérica ──────────────────────────── */
.card {
  background: var(--gris-card);
  border: 1px solid var(--gris-borde);
  border-radius: var(--radio);
  box-shadow: var(--sombra);
  overflow: hidden;
}
.card-header {
  padding: 18px 22px 14px;
  border-bottom: 1px solid var(--gris-borde);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.card-titulo {
  font-size: 15px;
  font-weight: 600;
  color: var(--texto);
}
.card-body { padding: 22px; }

/* ── Gráfica ────────────────────────────────── */
.chart-wrap { position: relative; height: 240px; }

.chart-tabs {
  display: flex;
  gap: 6px;
}
.chart-tab {
  font-size: 12px;
  padding: 4px 10px;
  border-radius: 6px;
  border: 1px solid var(--gris-borde);
  background: none;
  cursor: pointer;
  color: var(--gris-texto);
  font-family: var(--font);
  transition: all .15s;
}
.chart-tab.active {
  background: var(--verde-suave);
  border-color: var(--verde-mid);
  color: var(--verde-dark);
  font-weight: 500;
}

/* ── Controles ──────────────────────────────── */
.control-grupo { margin-bottom: 20px; }
.control-grupo:last-child { margin-bottom: 0; }

.control-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--gris-texto);
  text-transform: uppercase;
  letter-spacing: .4px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}
.control-label-val {
  font-family: var(--mono);
  font-size: 13px;
  color: var(--verde-dark);
  background: var(--verde-suave);
  padding: 2px 8px;
  border-radius: 5px;
}

/* Slider */
.slider-custom {
  width: 100%;
  -webkit-appearance: none;
  height: 5px;
  border-radius: 3px;
  background: var(--gris-bg);
  outline: none;
  cursor: pointer;
}
.slider-custom::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 18px; height: 18px;
  border-radius: 50%;
  background: var(--verde);
  border: 2px solid #fff;
  box-shadow: 0 1px 6px rgba(49,192,72,.4);
  cursor: pointer;
  transition: transform .15s;
}
.slider-custom::-webkit-slider-thumb:hover { transform: scale(1.2); }

/* Toggle */
.toggle-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.toggle-texto {
  font-size: 13px;
  color: var(--texto);
}
.toggle {
  position: relative;
  width: 46px; height: 26px;
}
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-pista {
  position: absolute;
  inset: 0;
  background: #d0d8d2;
  border-radius: 13px;
  cursor: pointer;
  transition: background .25s;
}
.toggle input:checked + .toggle-pista { background: var(--verde); }
.toggle-pista::before {
  content: '';
  position: absolute;
  width: 20px; height: 20px;
  border-radius: 50%;
  background: #fff;
  top: 3px; left: 3px;
  transition: transform .25s;
  box-shadow: 0 1px 4px rgba(0,0,0,.2);
}
.toggle input:checked + .toggle-pista::before { transform: translateX(20px); }

/* Time inputs */
.horario-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.horario-grupo label {
  font-size: 11px;
  color: var(--gris-texto);
  display: block;
  margin-bottom: 4px;
}
.horario-grupo input[type="time"] {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid var(--gris-borde);
  border-radius: 8px;
  font-family: var(--mono);
  font-size: 14px;
  color: var(--texto);
  background: var(--gris-bg);
  outline: none;
  transition: border-color .2s;
}
.horario-grupo input[type="time"]:focus {
  border-color: var(--verde);
  background: #fff;
}

/* Divisor */
.divisor {
  border: none;
  border-top: 1px solid var(--gris-borde);
  margin: 18px 0;
}

/* Botón guardar */
.btn-guardar {
  width: 100%;
  padding: 11px;
  background: var(--verde);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  font-family: var(--font);
  cursor: pointer;
  transition: background .2s, transform .1s;
  margin-top: 18px;
}
.btn-guardar:hover { background: var(--verde-dark); }
.btn-guardar:active { transform: scale(.98); }
.btn-guardar.guardando { opacity: .7; cursor: not-allowed; }

/* Toast */
.toast {
  position: fixed;
  bottom: 28px; right: 28px;
  background: var(--texto);
  color: #fff;
  padding: 12px 20px;
  border-radius: 10px;
  font-size: 14px;
  opacity: 0;
  transform: translateY(10px);
  transition: all .3s;
  pointer-events: none;
  z-index: 999;
}
.toast.visible { opacity: 1; transform: translateY(0); }
.toast.error { background: var(--rojo); }

/* Última actualización */
.ultima-act {
  font-size: 11px;
  font-family: var(--mono);
  color: var(--gris-texto);
  text-align: right;
  margin-top: 8px;
}
</style>
</head>
<body>

<!-- ── Header ────────────────────────── -->
<header class="header">
  <a href="../index.php" class="header-logo">
    <img src="../icono.png" alt="GrowSystem">
    <span>GrowSystem</span>
  </a>
  <div class="header-right">
    <span class="header-serie"><?= $serie ?></span>
    <div class="pulse-dot" id="pulseDot" title="Conexión ESP32"></div>
    <a href="../logout.php" class="btn-logout">Cerrar sesión</a>
  </div>
</header>

<!-- ── Página ────────────────────────── -->
<main class="page">

  <h1 class="page-title"><?= $alias ?></h1>
  <p class="page-sub">
    <?php if ($ubicacion): ?><span><?= $ubicacion ?></span> · <?php endif; ?>
    <?= $planta ?>
  </p>

  <!-- Métricas de sensores -->
  <div class="metricas">
    <div class="metrica-card" id="cardHumedad">
      <div class="metrica-label">
        <span class="metrica-icono">💧</span> Humedad del suelo
      </div>
      <div class="metrica-valor">
        <span id="valHumedad">--</span>
        <span class="unidad">%</span>
      </div>
      <div class="metrica-barra-bg">
        <div class="metrica-barra" id="barraHumedad" style="width:0%"></div>
      </div>
    </div>

    <div class="metrica-card" id="cardNutrientes">
      <div class="metrica-label">
        <span class="metrica-icono">🌱</span> Nivel de nutrientes
      </div>
      <div class="metrica-valor">
        <span id="valNutrientes">--</span>
        <span class="unidad">%</span>
      </div>
      <div class="metrica-barra-bg">
        <div class="metrica-barra" id="barraNutrientes" style="width:0%"></div>
      </div>
    </div>
  </div>

  <!-- Estado de relés -->
  <p class="reles-titulo">Estado de actuadores</p>
  <div class="reles-grid">
    <div class="rele-card">
      <span class="rele-icono">💦</span>
      <span class="rele-nombre">Bomba</span>
      <span class="rele-badge off" id="badgeBomba">OFF</span>
    </div>
    <div class="rele-card">
      <span class="rele-icono">🌬</span>
      <span class="rele-nombre">Ventilador</span>
      <span class="rele-badge off" id="badgeVent">OFF</span>
    </div>
    <div class="rele-card">
      <span class="rele-icono">💡</span>
      <span class="rele-nombre">Luces</span>
      <span class="rele-badge off" id="badgeLuces">OFF</span>
    </div>
    <div class="rele-card">
      <span class="rele-icono">🧪</span>
      <span class="rele-nombre">Surtidor</span>
      <span class="rele-badge off" id="badgeSurtidor">OFF</span>
    </div>
  </div>

  <!-- Gráfica + Controles -->
  <div class="dos-col">

    <!-- Gráfica -->
    <div class="card">
      <div class="card-header">
        <span class="card-titulo">Historial de sensores</span>
        <div class="chart-tabs">
          <button class="chart-tab active" onclick="cambiarGrafica('humedad', this)">Humedad</button>
          <button class="chart-tab" onclick="cambiarGrafica('nutrientes', this)">Nutrientes</button>
          <button class="chart-tab" onclick="cambiarGrafica('ambos', this)">Ambos</button>
        </div>
      </div>
      <div class="card-body">
        <div class="chart-wrap">
          <canvas id="grafica"></canvas>
        </div>
        <p class="ultima-act" id="ultimaAct">Actualizando...</p>
      </div>
    </div>

    <!-- Controles -->
    <div class="card">
      <div class="card-header">
        <span class="card-titulo">Parámetros de control</span>
      </div>
      <div class="card-body">

        <!-- Umbral humedad -->
        <div class="control-grupo">
          <div class="control-label">
            Umbral riego
            <span class="control-label-val" id="lblUmbralHum">30%</span>
          </div>
          <input type="range" class="slider-custom" id="sliderHum"
                 min="0" max="100" value="30"
                 oninput="document.getElementById('lblUmbralHum').textContent=this.value+'%'">
        </div>

        <!-- Umbral nutrientes -->
        <div class="control-grupo">
          <div class="control-label">
            Umbral nutrientes
            <span class="control-label-val" id="lblUmbralNut">40%</span>
          </div>
          <input type="range" class="slider-custom" id="sliderNut"
                 min="0" max="100" value="40"
                 oninput="document.getElementById('lblUmbralNut').textContent=this.value+'%'">
        </div>

        <hr class="divisor">

        <!-- Ventilación forzada -->
        <div class="control-grupo">
          <div class="toggle-row">
            <span class="toggle-texto">Ventilación forzada</span>
            <label class="toggle">
              <input type="checkbox" id="toggleVent">
              <span class="toggle-pista"></span>
            </label>
          </div>
        </div>

        <hr class="divisor">

        <!-- Horario luces -->
        <div class="control-grupo">
          <div class="control-label" style="margin-bottom:10px">Horario de luces</div>
          <div class="horario-row">
            <div class="horario-grupo">
              <label>Encendido</label>
              <input type="time" id="horaON" value="08:00">
            </div>
            <div class="horario-grupo">
              <label>Apagado</label>
              <input type="time" id="horaOFF" value="18:00">
            </div>
          </div>
        </div>

        <button class="btn-guardar" id="btnGuardar" onclick="guardarControl()">
          Guardar parámetros
        </button>

      </div>
    </div>

  </div><!-- /dos-col -->

</main>

<!-- Toast de notificación -->
<div class="toast" id="toast"></div>

<script>
const INSTANCIA_ID = <?= $instanciaId ?>;
const API_BASE     = 'api/datos.php';

// ── Estado actual ─────────────────────────────
let modoGrafica   = 'humedad';
let grafica       = null;
let datosGrafica  = { labels:[], humedad:[], nutrientes:[] };

// ── Chart.js ──────────────────────────────────
function initGrafica() {
  const ctx = document.getElementById('grafica').getContext('2d');
  grafica = new Chart(ctx, {
    type: 'line',
    data: {
      labels: [],
      datasets: [
        {
          label: 'Humedad (%)',
          data: [],
          borderColor: '#31C048',
          backgroundColor: 'rgba(49,192,72,.08)',
          borderWidth: 2,
          pointRadius: 2,
          tension: 0.4,
          fill: true,
        },
        {
          label: 'Nutrientes (%)',
          data: [],
          borderColor: '#3d5a6c',
          backgroundColor: 'rgba(61,90,108,.08)',
          borderWidth: 2,
          pointRadius: 2,
          tension: 0.4,
          fill: true,
          hidden: true,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1e2d22',
          titleColor: '#9ab09c',
          bodyColor: '#fff',
          padding: 10,
          cornerRadius: 8,
        }
      },
      scales: {
        x: {
          grid: { color: '#f0f4f2' },
          ticks: { color: '#6b7c6e', font: { family: "'DM Mono'", size: 10 }, maxTicksLimit: 8 }
        },
        y: {
          min: 0, max: 100,
          grid: { color: '#f0f4f2' },
          ticks: { color: '#6b7c6e', font: { size: 11 }, callback: v => v + '%' }
        }
      }
    }
  });
}

function cambiarGrafica(modo, btn) {
  modoGrafica = modo;
  document.querySelectorAll('.chart-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  actualizarDatasetVisibilidad();
}

function actualizarDatasetVisibilidad() {
  if (!grafica) return;
  const ds = grafica.data.datasets;
  ds[0].hidden = (modoGrafica === 'nutrientes');
  ds[1].hidden = (modoGrafica === 'humedad');
  grafica.update('none');
}

function actualizarGrafica(lecturas) {
  if (!grafica || !lecturas.length) return;
  datosGrafica.labels     = lecturas.map(l => l.hora);
  datosGrafica.humedad    = lecturas.map(l => l.humedad);
  datosGrafica.nutrientes = lecturas.map(l => l.nutrientes);

  grafica.data.labels         = datosGrafica.labels;
  grafica.data.datasets[0].data = datosGrafica.humedad;
  grafica.data.datasets[1].data = datosGrafica.nutrientes;
  actualizarDatasetVisibilidad();
  grafica.update();
}

// ── Polling estado (cada 5s) ──────────────────
async function fetchEstado() {
  try {
    const res  = await fetch(`${API_BASE}?instancia_id=${INSTANCIA_ID}&accion=estado`);
    const data = await res.json();
    if (!data.ok) return;

    const e = data.estado;
    const c = data.control;

    // Sensores
    actualizarMetrica('valHumedad',    'barraHumedad',    'cardHumedad',    e.humedad,    c.umbral_humedad);
    actualizarMetrica('valNutrientes', 'barraNutrientes', 'cardNutrientes', e.nutrientes, c.umbral_nutrientes);

    // Relés
    setBadge('badgeBomba',    e.bomba);
    setBadge('badgeVent',     e.ventilador);
    setBadge('badgeLuces',    e.luces);
    setBadge('badgeSurtidor', e.surtidor);

    // Controles (solo si el usuario no está editando)
    if (document.activeElement.tagName !== 'INPUT') {
      document.getElementById('sliderHum').value  = c.umbral_humedad;
      document.getElementById('sliderNut').value  = c.umbral_nutrientes;
      document.getElementById('lblUmbralHum').textContent = c.umbral_humedad + '%';
      document.getElementById('lblUmbralNut').textContent = c.umbral_nutrientes + '%';
      document.getElementById('toggleVent').checked = c.ventilacion;
      document.getElementById('horaON').value  = c.hora_encendido;
      document.getElementById('horaOFF').value = c.hora_apagado;
    }

    // Dot de conexión
    const hace = e.ultima_actualizacion
      ? Math.floor((Date.now() - new Date(e.ultima_actualizacion)) / 1000)
      : 999;
    const dot = document.getElementById('pulseDot');
    dot.classList.toggle('offline', hace > 20);
    dot.title = hace < 20
      ? `ESP32 en línea — hace ${hace}s`
      : `Sin reporte del ESP32 (hace ${hace}s)`;

    document.getElementById('ultimaAct').textContent =
      e.ultima_actualizacion
        ? 'Último reporte: ' + new Date(e.ultima_actualizacion).toLocaleTimeString('es-MX')
        : 'Sin datos del ESP32 aún';

  } catch(err) {
    console.warn('[Dashboard] Error fetch estado:', err);
  }
}

function actualizarMetrica(idVal, idBarra, idCard, valor, umbral) {
  const v = valor !== null ? valor.toFixed(1) : '--';
  document.getElementById(idVal).textContent = v;
  if (valor !== null) {
    document.getElementById(idBarra).style.width = Math.min(valor, 100) + '%';
    const card = document.getElementById(idCard);
    card.classList.remove('alerta', 'critico');
    if (valor < umbral * 0.5) card.classList.add('critico');
    else if (valor < umbral)   card.classList.add('alerta');
  }
}

function setBadge(id, on) {
  const el = document.getElementById(id);
  el.textContent = on ? 'ON' : 'OFF';
  el.className   = 'rele-badge ' + (on ? 'on' : 'off');
}

// ── Polling gráfica (cada 15s) ────────────────
async function fetchGrafica() {
  try {
    const res  = await fetch(`${API_BASE}?instancia_id=${INSTANCIA_ID}&accion=grafica&limite=60`);
    const data = await res.json();
    if (data.ok) actualizarGrafica(data.lecturas);
  } catch(err) {
    console.warn('[Dashboard] Error fetch gráfica:', err);
  }
}

// ── Guardar parámetros ────────────────────────
async function guardarControl() {
  const btn = document.getElementById('btnGuardar');
  btn.classList.add('guardando');
  btn.textContent = 'Guardando...';

  const payload = {
    umbral_humedad:    parseInt(document.getElementById('sliderHum').value),
    umbral_nutrientes: parseInt(document.getElementById('sliderNut').value),
    ventilacion:       document.getElementById('toggleVent').checked,
    hora_encendido:    document.getElementById('horaON').value,
    hora_apagado:      document.getElementById('horaOFF').value,
  };

  try {
    const res  = await fetch(`${API_BASE}?instancia_id=${INSTANCIA_ID}&accion=control`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(payload),
    });
    const data = await res.json();
    mostrarToast(data.ok ? '✓ Parámetros guardados' : 'Error al guardar', !data.ok);
  } catch(err) {
    mostrarToast('Error de conexión', true);
  } finally {
    btn.classList.remove('guardando');
    btn.textContent = 'Guardar parámetros';
  }
}

// ── Toast ─────────────────────────────────────
function mostrarToast(msg, esError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className   = 'toast visible' + (esError ? ' error' : '');
  setTimeout(() => { t.className = 'toast'; }, 3000);
}

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
