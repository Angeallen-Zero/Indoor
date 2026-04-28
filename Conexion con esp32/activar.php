<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: acceso.php');
    exit;
}

require_once __DIR__ . '/api/db.php';

$usuarioId = (int)$_SESSION['usuario_id'];
$error     = '';
$paso      = 1;
$dispId    = 0;
$serie     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // PASO 1: verificar serie
    if ($accion === 'verificar') {
        $serie = strtoupper(trim($_POST['serie'] ?? ''));
        if (empty($serie)) {
            $error = 'Ingresa el número de serie del dispositivo.';
        } else {
            $db   = getDB();
            $stmt = $db->prepare("SELECT id, estado FROM dispositivos WHERE numero_serie = ? LIMIT 1");
            $stmt->bind_param('s', $serie);
            $stmt->execute();
            $disp = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $db->close();

            if (!$disp)                          { $error = "El ID <strong>$serie</strong> no existe."; }
            elseif ($disp['estado'] === 'asignado') { $error = "El dispositivo <strong>$serie</strong> ya está registrado en otra cuenta."; }
            elseif ($disp['estado'] === 'baja')     { $error = "El dispositivo <strong>$serie</strong> está dado de baja."; }
            else { $paso = 2; $dispId = $disp['id']; }
        }
    }

    // PASO 2: activar
    if ($accion === 'activar') {
        $dispId   = (int)($_POST['disp_id']  ?? 0);
        $plantaId = (int)($_POST['planta_id'] ?? 0);
        $serie    = strtoupper(trim($_POST['serie'] ?? ''));

        if ($dispId <= 0)   { $error = 'Error al procesar el dispositivo.'; $paso = 1; }
        elseif ($plantaId <= 0) { $error = 'Debes identificar la planta antes de continuar.'; $paso = 2; }
        else {
            $db = getDB();
            $db->begin_transaction();
            try {
                // Nombre de la planta como alias
                $stmtP = $db->prepare("SELECT nombre_comun FROM plantas WHERE id = ?");
                $stmtP->bind_param('i', $plantaId);
                $stmtP->execute();
                $pRow  = $stmtP->get_result()->fetch_assoc();
                $stmtP->close();
                $alias = $pRow['nombre_comun'] ?? 'Mi invernadero';

                $stmt = $db->prepare("INSERT INTO instancias (usuario_id, dispositivo_id, planta_id, alias) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('iiis', $usuarioId, $dispId, $plantaId, $alias);
                $stmt->execute();
                $instanciaId = $stmt->insert_id;
                $stmt->close();

                $stmt = $db->prepare("INSERT INTO control (instancia_id) VALUES (?)");
                $stmt->bind_param('i', $instanciaId);
                $stmt->execute();
                $stmt->close();

                $stmt = $db->prepare("INSERT INTO estado_actual (instancia_id) VALUES (?)");
                $stmt->bind_param('i', $instanciaId);
                $stmt->execute();
                $stmt->close();

                $stmt = $db->prepare("UPDATE dispositivos SET estado='asignado', usuario_id=?, fecha_asignacion=NOW() WHERE id=?");
                $stmt->bind_param('ii', $usuarioId, $dispId);
                $stmt->execute();
                $stmt->close();

                // Generar API token — UPDATE si ya existe, INSERT si no
                $token = bin2hex(random_bytes(32));
                $stmt  = $db->prepare("
                    INSERT INTO api_tokens (dispositivo_id, token, activo)
                    VALUES (?, ?, 1)
                    ON DUPLICATE KEY UPDATE token = VALUES(token), activo = 1
                ");
                $stmt->bind_param('is', $dispId, $token);
                $stmt->execute();
                $stmt->close();

                $db->commit();
                $db->close();

                header("Location: planta.php?instancia=$instanciaId");
                exit;
            } catch (Exception $e) {
                $db->rollback();
                $db->close();
                $error = 'Error al activar. Intenta de nuevo.';
                $paso  = 2;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GrowSystem — Activar dispositivo</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
<style>
:root {
  --verde:#31C048; --verde-dark:#219E35; --verde-suave:#EAF7ED;
  --verde-mid:#C5EAC9; --gris-bg:#f0f4f2; --gris-borde:#e2ebe4;
  --gris-texto:#6b7c6e; --texto:#1e2d22; --rojo:#e05252;
  --azul:#3d5a6c; --azul-dark:#2c4655;
  --font:'DM Sans',sans-serif; --mono:'DM Mono',monospace;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:var(--font);background:var(--gris-bg);min-height:100vh;display:flex;flex-direction:column;}

.header{background:#fff;border-bottom:1px solid var(--gris-borde);padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between;}
.header-logo{display:flex;align-items:center;gap:10px;text-decoration:none;}
.header-logo img{width:34px;height:34px;object-fit:contain;}
.header-logo span{font-size:20px;font-weight:600;color:var(--verde);}
.btn-volver-top{font-size:13px;color:var(--gris-texto);text-decoration:none;padding:6px 12px;border-radius:8px;border:1px solid var(--gris-borde);transition:background .2s;}
.btn-volver-top:hover{background:var(--gris-bg);}

.page{flex:1;display:flex;align-items:flex-start;justify-content:center;padding:40px 20px;}

/* Paso 1 */
.card-paso1{background:#fff;border-radius:20px;box-shadow:0 8px 40px rgba(30,45,34,.1);width:100%;max-width:480px;overflow:hidden;}
.pasos{display:flex;border-bottom:1px solid var(--gris-borde);}
.paso-item{flex:1;padding:16px;text-align:center;font-size:13px;font-weight:500;color:var(--gris-texto);display:flex;align-items:center;justify-content:center;gap:8px;position:relative;}
.paso-item.activo{color:var(--verde-dark);}
.paso-item.activo::after,.paso-item.completo::after{content:'';position:absolute;bottom:-1px;left:0;right:0;height:2px;background:var(--verde);}
.paso-num{width:22px;height:22px;border-radius:50%;font-size:12px;font-weight:600;display:flex;align-items:center;justify-content:center;background:var(--gris-bg);color:var(--gris-texto);flex-shrink:0;}
.paso-item.activo .paso-num,.paso-item.completo .paso-num{background:var(--verde-suave);color:var(--verde-dark);}
.paso-item.completo .paso-num{background:var(--verde);color:#fff;}
.card-body{padding:32px;}
.card-titulo{font-size:22px;font-weight:600;color:var(--texto);margin-bottom:6px;}
.card-sub{font-size:14px;color:var(--gris-texto);margin-bottom:24px;line-height:1.5;}
.serie-input{width:100%;padding:14px 16px;font-family:var(--mono);font-size:18px;font-weight:500;letter-spacing:2px;text-transform:uppercase;border:2px solid var(--gris-borde);border-radius:12px;background:var(--gris-bg);color:var(--texto);outline:none;transition:border-color .2s;text-align:center;}
.serie-input:focus{border-color:var(--verde);background:#fff;}
.serie-hint{text-align:center;font-size:12px;color:var(--gris-texto);margin-top:8px;}
.serie-hint code{font-family:var(--mono);background:var(--gris-bg);padding:1px 6px;border-radius:4px;}
.error-msg{background:#fef2f2;border:1px solid #fecaca;color:var(--rojo);font-size:13px;padding:10px 14px;border-radius:8px;margin-bottom:16px;line-height:1.5;}
.btn-primary{width:100%;padding:13px;background:var(--verde);color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:600;font-family:var(--font);cursor:pointer;transition:background .2s;margin-top:16px;}
.btn-primary:hover{background:var(--verde-dark);}

/* Paso 2 */
.paso2-wrap{width:100%;max-width:1000px;}
.paso2-header{margin-bottom:40px;}
.paso2-header h2{font-size:36px;font-weight:700;color:#2d3e50;margin-bottom:8px;}
.paso2-header p{font-size:16px;color:var(--gris-texto);}
.disp-badge{display:inline-flex;align-items:center;gap:8px;background:var(--verde-suave);border:1px solid var(--verde-mid);border-radius:8px;padding:6px 14px;margin-bottom:24px;font-family:var(--mono);font-size:13px;color:var(--verde-dark);font-weight:500;}

/* Tarjetas opción */
.contenedor-opciones{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:25px;margin-bottom:30px;}
.tarjeta-opcion{background:white;padding:30px;border-radius:16px;box-shadow:0 8px 20px rgba(0,0,0,0.08);text-align:center;transition:transform .2s;}
.tarjeta-opcion:hover{transform:translateY(-4px);}
.titulo-opcion{font-size:20px;font-weight:700;color:#2d3e50;margin-bottom:10px;}
.descripcion-opcion{color:var(--gris-texto);margin-bottom:20px;font-size:14px;}
.boton-opcion{background:var(--azul);color:white;border:none;padding:12px 24px;border-radius:10px;cursor:pointer;font-size:15px;font-family:var(--font);transition:background .2s;}
.boton-opcion:hover{background:var(--azul-dark);}

/* Secciones manual / foto */
.seccion-contenido{background:white;padding:25px;border-radius:20px;box-shadow:0 10px 25px rgba(0,0,0,0.08);margin-bottom:20px;}
.seccion-contenido h3{margin-bottom:20px;color:#2e7d32;font-size:18px;}

/* Campos manual — igual que add-manual.php */
.campo-manual{margin-bottom:15px;}
.campo-manual label{display:block;font-weight:bold;color:#2e7d32;margin-bottom:5px;font-size:14px;}
.campo-manual input[type="text"],
.campo-manual input[type="file"]{width:100%;padding:12px;border-radius:12px;border:1px solid #ddd;font-size:14px;font-family:var(--font);transition:.3s;background:#fff;}
.campo-manual input[type="text"]:focus{border-color:#2e7d32;outline:none;box-shadow:0 0 0 3px rgba(46,125,50,0.15);}
.preview-img{width:100%;border-radius:15px;margin-top:10px;display:none;max-height:240px;object-fit:cover;}
.btn-guardar-manual{background:linear-gradient(135deg,#333533,#122142);color:white;border:none;padding:14px;border-radius:14px;font-size:16px;cursor:pointer;transition:.3s;width:100%;font-family:var(--font);margin-top:8px;}
.btn-guardar-manual:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,0.15);}
.confirm-manual{display:none;margin-top:12px;padding:10px 14px;background:var(--verde-suave);border-radius:8px;font-size:13px;color:var(--verde-dark);}

/* Resultado escaneo */
.card-resultado{background:white;padding:25px;border-radius:16px;box-shadow:0 8px 20px rgba(0,0,0,0.08);text-align:center;}
.card-resultado img{max-width:220px;border-radius:10px;margin-top:10px;}
.loader{margin-top:15px;font-weight:bold;color:#2e7d32;}
.error-scan{color:var(--rojo);font-weight:bold;margin-top:10px;}
.badge-conf{display:inline-block;padding:4px 12px;border-radius:20px;font-size:13px;font-weight:600;margin:8px 0;}
.badge-alto{background:var(--verde-suave);color:var(--verde-dark);}
.badge-medio{background:#FEF9E7;color:#9a6700;}
.badge-bajo{background:#fef2f2;color:var(--rojo);}

/* Banner planta guardada */
.planta-guardada{display:none;background:var(--verde-suave);border:1px solid var(--verde-mid);border-radius:12px;padding:14px 18px;margin-bottom:16px;align-items:center;gap:12px;}
.planta-guardada.visible{display:flex;}
.planta-guardada img{width:56px;height:56px;object-fit:cover;border-radius:8px;flex-shrink:0;}
.pg-nombre{font-size:15px;font-weight:600;color:var(--texto);}
.pg-sub{font-size:12px;color:var(--gris-texto);font-style:italic;}
.pg-cambiar{margin-left:auto;font-size:12px;color:var(--gris-texto);cursor:pointer;background:none;border:none;text-decoration:underline;font-family:var(--font);flex-shrink:0;}

/* Botones finales */
.btn-activar{width:100%;padding:14px;background:var(--verde);color:#fff;border:none;border-radius:12px;font-size:16px;font-weight:600;font-family:var(--font);cursor:pointer;transition:background .2s,transform .1s;}
.btn-activar:hover{background:var(--verde-dark);}
.btn-activar:active{transform:scale(.98);}
.btn-cambiar-disp{width:100%;padding:11px;background:none;color:var(--gris-texto);border:1.5px solid var(--gris-borde);border-radius:10px;font-size:14px;font-family:var(--font);cursor:pointer;margin-top:10px;transition:all .2s;}
.btn-cambiar-disp:hover{border-color:var(--verde-mid);color:var(--verde-dark);}
</style>
</head>
<body>

<header class="header">
    <a href="index.php" class="header-logo">
        <img src="icono.png" alt="GrowSystem">
        <span>GrowSystem</span>
    </a>
    <a href="index.php" class="btn-volver-top">← Volver</a>
</header>

<div class="page">

<?php if ($paso === 1): ?>
<!-- ══ PASO 1 ══ -->
<div class="card-paso1">
    <div class="pasos">
        <div class="paso-item activo"><span class="paso-num">1</span>Verificar ID</div>
        <div class="paso-item"><span class="paso-num">2</span>Configurar</div>
    </div>
    <div class="card-body">
        <?php if ($error): ?><div class="error-msg"><?= $error ?></div><?php endif; ?>
        <h2 class="card-titulo">Activa tu dispositivo</h2>
        <p class="card-sub">Ingresa el ID único que viene en la etiqueta de tu GrowSystem ESP32.</p>
        <form method="POST">
            <input type="hidden" name="accion" value="verificar">
            <input type="text" name="serie" class="serie-input"
                   placeholder="IND-0000" maxlength="20"
                   value="<?= htmlspecialchars($_POST['serie'] ?? '') ?>"
                   autofocus oninput="this.value=this.value.toUpperCase()">
            <p class="serie-hint">Formato: <code>IND-0001</code> — en la etiqueta del dispositivo</p>
            <button type="submit" class="btn-primary">Verificar dispositivo</button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- ══ PASO 2 ══ -->
<div class="paso2-wrap">

    <div style="background:#fff;border-radius:16px;box-shadow:0 4px 16px rgba(30,45,34,.08);overflow:hidden;margin-bottom:32px;">
        <div class="pasos">
            <div class="paso-item completo"><span class="paso-num">✓</span>Verificar ID</div>
            <div class="paso-item activo"><span class="paso-num">2</span>Configurar</div>
        </div>
    </div>

    <div class="disp-badge">✅ <?= htmlspecialchars($_POST['serie'] ?? '') ?> — verificado</div>

    <div class="paso2-header">
        <h2>Agregar Nueva Planta</h2>
        <p>Elige cómo deseas agregar tu planta</p>
    </div>

    <!-- Tarjetas -->
    <div class="contenedor-opciones">
        <div class="tarjeta-opcion">
            <h3 class="titulo-opcion">Agregar Manualmente</h3>
            <p class="descripcion-opcion">Ingresa los detalles manualmente.</p>
            <button class="boton-opcion" onclick="mostrarSeccion('manual')">Ingresar Datos</button>
        </div>
        <div class="tarjeta-opcion">
            <h3 class="titulo-opcion">Escanear con Foto</h3>
            <p class="descripcion-opcion">Toma una foto y obtén información automática.</p>
            <button class="boton-opcion" onclick="mostrarSeccion('foto')">Tomar Foto</button>
            <input type="file" id="inputFoto" accept="image/*" capture="environment" style="display:none">
        </div>
    </div>

    <!-- Sección MANUAL (oculta por defecto) -->
    <div id="seccionManual" style="display:none">
        <div class="seccion-contenido">
            <h3>Datos de la planta</h3>
            <div class="campo-manual">
                <input type="text" id="m_nombre" placeholder="Nombre común">
            </div>
            <div class="campo-manual">
                <input type="text" id="m_cientifico" placeholder="Nombre científico">
            </div>
            <div class="campo-manual">
                <input type="text" id="m_familia" placeholder="Familia">
            </div>
            <div class="campo-manual">
                <input type="text" id="m_genero" placeholder="Género">
            </div>
            <div class="campo-manual">
                <input type="text" id="m_organo" placeholder="Órgano">
            </div>
            <div class="campo-manual">
                <label>Subir Imagen:</label>
                <input type="file" id="m_imagen" accept="image/*">
                <img id="m_preview" class="preview-img">
            </div>
            <button type="button" class="btn-guardar-manual" onclick="guardarManual()">
                💾 Guardar Planta
            </button>
            <div class="confirm-manual" id="confirmManual">✓ Planta guardada correctamente</div>
        </div>
    </div>

    <!-- Sección FOTO (oculta por defecto) -->
    <div id="seccionFoto" style="display:none">
        <div id="resultadoEscaneo"></div>
    </div>

    <!-- Banner planta guardada -->
    <div class="planta-guardada" id="plantaGuardada">
        <img src="" alt="" id="pgImg" style="display:none">
        <div>
            <div class="pg-nombre" id="pgNombre"></div>
            <div class="pg-sub"    id="pgSub"></div>
        </div>
        <button type="button" class="pg-cambiar" onclick="resetPlanta()">Cambiar</button>
    </div>

    <!-- Formulario activar: OCULTO hasta guardar planta -->
    <form method="POST" id="formActivar" style="display:none">
        <input type="hidden" name="accion"    value="activar">
        <input type="hidden" name="disp_id"   value="<?= (int)($_POST['disp_id'] ?? $dispId) ?>">
        <input type="hidden" name="serie"     value="<?= htmlspecialchars($_POST['serie'] ?? $serie) ?>">
        <input type="hidden" name="planta_id" id="plantaIdInput" value="">
        <?php if ($error): ?><div class="error-msg" style="margin-top:16px"><?= $error ?></div><?php endif; ?>
        <button type="submit" class="btn-activar">Activar y entrar al dashboard</button>
        <button type="button" class="btn-cambiar-disp" onclick="location.href='activar.php'">← Cambiar dispositivo</button>
    </form>

    <!-- Solo visible antes de guardar planta -->
    <div id="btnSoloCambiar">
        <button type="button" class="btn-cambiar-disp" onclick="location.href='activar.php'">← Cambiar dispositivo</button>
    </div>

</div>
<?php endif; ?>
</div>

<script>
// ── Mostrar sección y limpiar la otra ─────────
function mostrarSeccion(cual) {
    resetPlanta();

    const secManual = document.getElementById('seccionManual');
    const secFoto   = document.getElementById('seccionFoto');

    if (!secManual || !secFoto) return; // Paso 1: elementos no existen

    secManual.style.display = 'none';
    secFoto.style.display   = 'none';

    const resEsc = document.getElementById('resultadoEscaneo');
    const inFoto = document.getElementById('inputFoto');
    const mPrev  = document.getElementById('m_preview');
    const confM  = document.getElementById('confirmManual');

    if (resEsc) resEsc.innerHTML = '';
    if (inFoto) inFoto.value    = '';
    ['m_nombre','m_cientifico','m_familia','m_genero','m_organo'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    if (mPrev) mPrev.style.display    = 'none';
    if (confM) confM.style.display    = 'none';

    if (cual === 'manual') {
        secManual.style.display = 'block';
        secManual.scrollIntoView({ behavior:'smooth' });
    } else {
        secFoto.style.display = 'block';
        secFoto.scrollIntoView({ behavior:'smooth' });
        if (inFoto) inFoto.click();
    }
}

// ── Preview imagen manual ─────────────────────
const mImagen = document.getElementById('m_imagen');
if (mImagen) {
    mImagen.addEventListener('change', function(e) {
        const reader = new FileReader();
        reader.onload = () => {
            const prev = document.getElementById('m_preview');
            prev.src = reader.result;
            prev.style.display = 'block';
        };
        reader.readAsDataURL(e.target.files[0]);
    });
}

// ── Guardar manual ────────────────────────────
async function guardarManual() {
    const nombre = document.getElementById('m_nombre').value.trim();
    if (!nombre) { alert('Escribe al menos el nombre común.'); return; }

    let imagenSubida = null;
    const imgFile = document.getElementById('m_imagen').files[0];

    // Si hay imagen, subirla via procesar-imagen.php
    if (imgFile) {
        const fd = new FormData();
        fd.append('imagen', imgFile);
        try {
            const upRes  = await fetch('procesar-imagen.php', {
                method:      'POST',
                credentials: 'same-origin',
                body:        fd
            });
            const upJson = await upRes.json();
            if (upJson.imagenSubida) imagenSubida = upJson.imagenSubida;
        } catch(e) {
            console.warn('No se pudo subir la imagen:', e);
        }
    }

    const data = {
        nombreComun:      nombre,
        nombreCientifico: document.getElementById('m_cientifico').value.trim() || 'No especificado',
        familia:          document.getElementById('m_familia').value.trim()    || 'No especificada',
        genero:           document.getElementById('m_genero').value.trim()     || 'No especificado',
        confianza:        100,
        imagenSubida:     imagenSubida,
        imagenReferencia: null
    };

    try {
        const res = await (await fetch('guardar-planta.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(data)
        })).json();

        if (res.success) {
            document.getElementById('plantaIdInput').value          = res.planta_id;
            document.getElementById('confirmManual').style.display  = 'block';
            mostrarBannerYActivar(nombre, data.nombreCientifico, imagenSubida);
        } else {
            alert('Error al guardar: ' + (res.error || 'Inténtalo de nuevo'));
        }
    } catch(e) { alert('Error de conexión.'); }
}

// ── Escaneo PlantNet ──────────────────────────
const inputFotoEl = document.getElementById('inputFoto');
if (inputFotoEl) {
    inputFotoEl.addEventListener('change', async function() {
    const archivo = this.files[0];
    if (!archivo) return;

    const resultado = document.getElementById('resultadoEscaneo');
    resultado.innerHTML = "<div class='loader'>🔍 Analizando imagen con PlantNet...</div>";

    const formData = new FormData();
    formData.append('imagen', archivo);

    try {
        const res  = await fetch('procesar-imagen.php', { method:'POST', body:formData });
        const data = await res.json();

        if (data.error) {
            resultado.innerHTML = `<div class='error-scan'>❌ ${data.error}</div>`;
            return;
        }

        const gRes = await (await fetch('guardar-planta.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(data)
        })).json();

        if (gRes.success) {
            document.getElementById('plantaIdInput').value = gRes.planta_id;
        }

        const c  = data.confianza || 0;
        const bc = c >= 60 ? 'badge-alto' : c >= 30 ? 'badge-medio' : 'badge-bajo';
        const bt = c >= 60 ? '✓ Alta confianza' : c >= 30 ? '⚠ Confianza media' : '⚠ Baja confianza';

        resultado.innerHTML = `
        <div class="card-resultado">
            <h2>${data.nombreComun}</h2>
            <p><i>${data.nombreCientifico}</i></p>
            <span class="badge-conf ${bc}">${bt} (${c}%)</span>
            <p><b>Familia:</b> ${data.familia} &nbsp;|&nbsp; <b>Género:</b> ${data.genero}</p>
            ${data.imagenSubida    ? `<img src="${data.imagenSubida}"    alt="Planta">` : ''}
            ${data.imagenReferencia ? `<img src="${data.imagenReferencia}" alt="Referencia">` : ''}
        </div>`;

        mostrarBannerYActivar(data.nombreComun, data.nombreCientifico, data.imagenSubida);

    } catch(e) {
        document.getElementById('resultadoEscaneo').innerHTML =
            "<div class='error-scan'>❌ Error de conexión</div>";
    }
    });
}

// ── Mostrar banner + botón activar ────────────
function mostrarBannerYActivar(nombre, cientifico, imgSrc) {
    document.getElementById('pgNombre').textContent = nombre;
    document.getElementById('pgSub').textContent    = cientifico;
    if (imgSrc) {
        const img = document.getElementById('pgImg');
        img.src = imgSrc;
        img.style.display = 'block';
    }
    document.getElementById('plantaGuardada').classList.add('visible');
    document.getElementById('formActivar').style.display    = 'block';
    document.getElementById('btnSoloCambiar').style.display = 'none';
}

// ── Reset ─────────────────────────────────────
function resetPlanta() {
    const ids = ['plantaIdInput','plantaGuardada','pgImg','formActivar','btnSoloCambiar'];
    if (!document.getElementById('plantaIdInput')) return; // Paso 1: no existen

    document.getElementById('plantaIdInput').value              = '';
    document.getElementById('plantaGuardada').classList.remove('visible');
    document.getElementById('pgImg').style.display              = 'none';
    document.getElementById('formActivar').style.display        = 'none';
    document.getElementById('btnSoloCambiar').style.display     = 'block';
}
</script>
</body>
</html>
