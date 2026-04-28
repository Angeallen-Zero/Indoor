<?php
session_start();

// Solo admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: acceso.php');
    exit;
}

require_once __DIR__ . '/api/db.php';

$db     = getDB();
$nombre = $_SESSION['nombre'] ?? 'Admin';

// ── Estadísticas generales ────────────────────
$stats = [];

$r = $db->query("SELECT COUNT(*) AS total FROM dispositivos");
$stats['total'] = (int)$r->fetch_assoc()['total'];

$r = $db->query("SELECT COUNT(*) AS total FROM dispositivos WHERE estado='disponible'");
$stats['disponibles'] = (int)$r->fetch_assoc()['total'];

$r = $db->query("SELECT COUNT(*) AS total FROM dispositivos WHERE estado='asignado'");
$stats['asignados'] = (int)$r->fetch_assoc()['total'];

$r = $db->query("SELECT COUNT(*) AS total FROM usuarios WHERE rol='cliente'");
$stats['usuarios'] = (int)$r->fetch_assoc()['total'];

// ── Lista de dispositivos ─────────────────────
$dispositivos = $db->query("
    SELECT 
        d.id, d.numero_serie, d.estado,
        d.fecha_creacion, d.fecha_asignacion,
        u.nombre AS usuario_nombre, u.email AS usuario_email,
        i.id AS instancia_id, i.alias,
        t.token, t.activo AS token_activo, t.ultimo_uso
    FROM dispositivos d
    LEFT JOIN usuarios u ON d.usuario_id = u.id
    LEFT JOIN instancias i ON d.id = i.dispositivo_id AND i.activa = 1
    LEFT JOIN api_tokens t ON t.id = (
        SELECT id FROM api_tokens
        WHERE dispositivo_id = d.id AND activo = 1
        ORDER BY creado_en DESC
        LIMIT 1
    )
    ORDER BY d.id ASC
")->fetch_all(MYSQLI_ASSOC);

// ── Lista de usuarios ─────────────────────────
$usuarios = $db->query("
    SELECT u.id, u.nombre, u.email, u.rol, u.fecha_registro,
           COUNT(i.id) AS total_instancias
    FROM usuarios u
    LEFT JOIN instancias i ON u.id = i.usuario_id AND i.activa = 1
    GROUP BY u.id
    ORDER BY u.fecha_registro DESC
")->fetch_all(MYSQLI_ASSOC);

$db->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GrowSystem — Panel Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
<style>
:root {
  --verde:#31C048; --verde-dark:#219E35; --verde-suave:#EAF7ED;
  --verde-mid:#C5EAC9; --gris-bg:#f0f4f2; --gris-borde:#e2ebe4;
  --gris-texto:#6b7c6e; --texto:#1e2d22; --rojo:#e05252;
  --ambar:#f0a500; --azul:#3d5a6c; --azul-dark:#2c4655;
  --font:'DM Sans',sans-serif; --mono:'DM Mono',monospace;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:var(--font);background:var(--gris-bg);min-height:100vh;}

/* Header */
.header{background:#fff;border-bottom:1px solid var(--gris-borde);padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
.header-logo{display:flex;align-items:center;gap:10px;text-decoration:none;}
.header-logo img{width:34px;height:34px;object-fit:contain;}
.header-logo span{font-size:20px;font-weight:600;color:var(--verde);}
.header-right{display:flex;align-items:center;gap:12px;}
.admin-badge{font-size:11px;font-weight:600;background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:6px;border:1px solid #fde68a;text-transform:uppercase;letter-spacing:.5px;}
.btn-logout{font-size:13px;color:var(--gris-texto);text-decoration:none;padding:6px 12px;border-radius:8px;border:1px solid var(--gris-borde);transition:background .2s;}
.btn-logout:hover{background:var(--gris-bg);}

/* Page */
.page{max-width:1200px;margin:0 auto;padding:32px 24px 64px;}
.page-titulo{font-size:26px;font-weight:600;color:var(--texto);margin-bottom:4px;}
.page-sub{font-size:14px;color:var(--gris-texto);margin-bottom:28px;}

/* Stats */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:32px;}
.stat-card{background:#fff;border:1px solid var(--gris-borde);border-radius:12px;padding:20px;box-shadow:0 2px 12px rgba(30,45,34,.06);}
.stat-label{font-size:12px;font-weight:500;color:var(--gris-texto);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;}
.stat-valor{font-size:32px;font-weight:700;color:var(--texto);font-family:var(--mono);}
.stat-card.verde .stat-valor{color:var(--verde-dark);}
.stat-card.ambar .stat-valor{color:var(--ambar);}
.stat-card.azul  .stat-valor{color:var(--azul);}

/* Tabs */
.tabs{display:flex;gap:4px;margin-bottom:20px;background:#fff;border:1px solid var(--gris-borde);border-radius:10px;padding:4px;width:fit-content;}
.tab{padding:8px 20px;border-radius:7px;border:none;font-size:14px;font-family:var(--font);font-weight:500;cursor:pointer;color:var(--gris-texto);background:none;transition:all .2s;}
.tab.active{background:var(--verde-suave);color:var(--verde-dark);}
.tab-panel{display:none;}
.tab-panel.active{display:block;}

/* Tabla */
.tabla-wrap{background:#fff;border:1px solid var(--gris-borde);border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(30,45,34,.06);}
.tabla-header{padding:16px 20px;border-bottom:1px solid var(--gris-borde);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
.tabla-titulo{font-size:15px;font-weight:600;color:var(--texto);}
.tabla-buscar{padding:8px 12px;border:1px solid var(--gris-borde);border-radius:8px;font-size:13px;font-family:var(--font);color:var(--texto);background:var(--gris-bg);outline:none;width:220px;transition:border-color .2s;}
.tabla-buscar:focus{border-color:var(--verde);background:#fff;}
.btn-nuevo{padding:8px 16px;background:var(--verde);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;font-family:var(--font);cursor:pointer;transition:background .2s;}
.btn-nuevo:hover{background:var(--verde-dark);}
table{width:100%;border-collapse:collapse;}
th{padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:var(--gris-texto);text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--gris-borde);background:var(--gris-bg);}
td{padding:13px 16px;font-size:13px;color:var(--texto);border-bottom:1px solid var(--gris-borde);}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fafcfa;}

/* Badges estado */
.badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;}
.badge-disponible{background:var(--verde-suave);color:var(--verde-dark);border:1px solid var(--verde-mid);}
.badge-asignado{background:#dbeafe;color:#1e40af;border:1px solid #bfdbfe;}
.badge-baja{background:#fef2f2;color:var(--rojo);border:1px solid #fecaca;}
.badge-admin{background:#fef3c7;color:#92400e;border:1px solid #fde68a;}
.badge-cliente{background:var(--gris-bg);color:var(--gris-texto);border:1px solid var(--gris-borde);}

/* Token */
.token-txt{font-family:var(--mono);font-size:11px;color:var(--gris-texto);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:pointer;}
.token-txt:hover{color:var(--texto);}

/* Acciones */
.acciones{display:flex;gap:6px;flex-wrap:wrap;}
.btn-accion{padding:5px 12px;border-radius:6px;border:none;font-size:12px;font-weight:500;font-family:var(--font);cursor:pointer;transition:all .2s;}
.btn-liberar{background:#fef2f2;color:var(--rojo);border:1px solid #fecaca;}
.btn-liberar:hover{background:#fee2e2;}
.btn-copiar{background:var(--gris-bg);color:var(--gris-texto);border:1px solid var(--gris-borde);}
.btn-copiar:hover{background:var(--gris-borde);}
.btn-generar{background:var(--verde-suave);color:var(--verde-dark);border:1px solid var(--verde-mid);}
.btn-generar:hover{background:var(--verde-mid);}
.btn-baja{background:#fef3c7;color:#92400e;border:1px solid #fde68a;}
.btn-baja:hover{background:#fde68a;}

/* Modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;}
.modal-overlay.visible{display:flex;}
.modal-box{background:#fff;border-radius:16px;padding:32px;max-width:460px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.modal-icono{font-size:36px;text-align:center;margin-bottom:12px;}
.modal-titulo{font-size:18px;font-weight:600;color:var(--texto);text-align:center;margin-bottom:8px;}
.modal-sub{font-size:14px;color:var(--gris-texto);text-align:center;line-height:1.5;margin-bottom:24px;}
.modal-acciones{display:flex;gap:10px;}
.modal-btn-cancel{flex:1;padding:11px;background:none;border:1.5px solid var(--gris-borde);border-radius:10px;font-size:14px;font-family:var(--font);cursor:pointer;color:var(--gris-texto);}
.modal-btn-cancel:hover{border-color:var(--verde-mid);}
.modal-btn-confirm{flex:1;padding:11px;background:var(--rojo);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:600;font-family:var(--font);cursor:pointer;}
.modal-btn-confirm:hover{background:#c0392b;}
.modal-btn-confirm.verde{background:var(--verde);}
.modal-btn-confirm.verde:hover{background:var(--verde-dark);}

/* Modal nuevo dispositivo */
.campo-modal{margin-bottom:16px;}
.campo-modal label{display:block;font-size:13px;font-weight:500;color:var(--gris-texto);margin-bottom:5px;}
.campo-modal input{width:100%;padding:10px 14px;border:1.5px solid var(--gris-borde);border-radius:8px;font-size:14px;font-family:var(--mono);color:var(--texto);background:var(--gris-bg);outline:none;text-transform:uppercase;letter-spacing:1px;}
.campo-modal input:focus{border-color:var(--verde);background:#fff;}

/* Token generado */
.token-generado{background:var(--gris-bg);border:1px solid var(--gris-borde);border-radius:8px;padding:10px 14px;font-family:var(--mono);font-size:11px;word-break:break-all;color:var(--texto);margin-top:12px;display:none;}
.token-generado.visible{display:block;}

/* Toast */
.toast{position:fixed;bottom:24px;right:24px;background:var(--texto);color:#fff;padding:10px 18px;border-radius:8px;font-size:13px;opacity:0;transform:translateY(8px);transition:all .3s;pointer-events:none;z-index:999;}
.toast.visible{opacity:1;transform:translateY(0);}
.toast.ok{background:var(--verde-dark);}
.toast.error{background:var(--rojo);}

/* Sin datos */
.sin-datos{text-align:center;padding:40px;color:var(--gris-texto);font-size:14px;}
.btn-admin{
  font-size:13px;
  color:var(--gris-texto);
  text-decoration:none;
  padding:6px 12px;
  border-radius:8px;
  border:1px solid var(--gris-borde);
  transition:background .2s;
}

.btn-admin:hover{
  background:var(--gris-bg);
}
@media (max-width: 768px) {

  .header {
    flex-wrap: wrap;
    height: auto;
    padding: 12px 16px;
    gap: 10px;
  }

  .header-right {
    flex-wrap: wrap;
    width: 100%;
    justify-content: flex-end;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .tabla-header {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }

  .tabla-buscar {
    width: 100% !important;
  }

  .acciones {
    flex-direction: column;
    align-items: stretch;
  }

  .btn-accion {
    width: 100%;
  }
}
</style>
</head>
<body>

<header class="header">
    <a href="index.php" class="header-logo">
        <img src="icono.png" alt="GrowSystem">
        <span>GrowSystem</span>
    </a>
  <div class="header-right">
    <a href="admin_pedidos.php" class="btn-admin">📦 Pedidos</a>
    <a href="admin_productos.php" class="btn-logout">🛒 Productos</a>
    <span class="admin-badge">Admin</span>
    <span style="font-size:13px;color:var(--gris-texto)"><?= htmlspecialchars($nombre) ?></span>
    <a href="logout.php" class="btn-logout">Cerrar sesión</a>
</div>
</header>

<div class="page">
    <h1 class="page-titulo">Panel de administración</h1>
    <p class="page-sub">Gestión de dispositivos, usuarios y tokens</p>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total dispositivos</div>
            <div class="stat-valor"><?= $stats['total'] ?></div>
        </div>
        <div class="stat-card verde">
            <div class="stat-label">Disponibles</div>
            <div class="stat-valor"><?= $stats['disponibles'] ?></div>
        </div>
        <div class="stat-card azul">
            <div class="stat-label">Asignados</div>
            <div class="stat-valor"><?= $stats['asignados'] ?></div>
        </div>
        <div class="stat-card ambar">
            <div class="stat-label">Clientes</div>
            <div class="stat-valor"><?= $stats['usuarios'] ?></div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab active" onclick="cambiarTab('dispositivos', this)">📟 Dispositivos</button>
        <button class="tab"        onclick="cambiarTab('usuarios', this)">👤 Usuarios</button>
    </div>

    <!-- Tab: Dispositivos -->
    <div class="tab-panel active" id="tab-dispositivos">
        <div class="tabla-wrap">
            <div class="tabla-header">
                <span class="tabla-titulo">Inventario de ESP32</span>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input type="text" class="tabla-buscar" id="buscarDisp"
                           placeholder="Buscar serie o usuario..."
                           oninput="filtrarTabla('tablaDisp', this.value)">
                    <button class="btn-nuevo" onclick="abrirModalNuevo()">+ Nuevo dispositivo</button>
                </div>
            </div>
            <table id="tablaDisp">
                <thead>
                    <tr>
                        <th>Serie</th>
                        <th>Estado</th>
                        <th>Usuario</th>
                        <th>Invernadero</th>
                        <th>API Token</th>
                        <th>Último reporte</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($dispositivos)): ?>
                    <tr><td colspan="7" class="sin-datos">No hay dispositivos registrados</td></tr>
                <?php else: ?>
                <?php foreach ($dispositivos as $d): ?>
                <tr data-serie="<?= strtolower($d['numero_serie']) ?>"
                    data-usuario="<?= strtolower($d['usuario_nombre'] ?? '') ?>">
                    <td>
                        <span style="font-family:var(--mono);font-weight:500">
                            <?= htmlspecialchars($d['numero_serie']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?= $d['estado'] ?>">
                            <?= ucfirst($d['estado']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($d['usuario_nombre']): ?>
                            <div style="font-weight:500"><?= htmlspecialchars($d['usuario_nombre']) ?></div>
                            <div style="font-size:11px;color:var(--gris-texto)"><?= htmlspecialchars($d['usuario_email']) ?></div>
                        <?php else: ?>
                            <span style="color:var(--gris-texto);font-size:12px">Sin asignar</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $d['alias']
                            ? htmlspecialchars($d['alias'])
                            : '<span style="color:var(--gris-texto);font-size:12px">—</span>' ?>
                    </td>
                    <td>
                        <?php if ($d['token']): ?>
                            <span class="token-txt" title="<?= htmlspecialchars($d['token']) ?>"
                                  onclick="copiarToken('<?= htmlspecialchars($d['token']) ?>', this)">
                                <?= substr($d['token'], 0, 16) ?>...
                            </span>
                        <?php else: ?>
                            <span style="color:var(--gris-texto);font-size:12px">Sin token</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:var(--gris-texto)">
                        <?= $d['ultimo_uso']
                            ? date('d/m/Y H:i', strtotime($d['ultimo_uso']))
                            : '—' ?>
                    </td>
                    <td>
                        <div class="acciones">
                            <?php if ($d['token']): ?>
                            <button class="btn-accion btn-copiar"
                                    onclick="copiarToken('<?= htmlspecialchars($d['token']) ?>', this)">
                                Copiar token
                            </button>
                            <?php endif; ?>

                            <?php if ($d['estado'] === 'asignado'): ?>
                            <button class="btn-accion btn-liberar"
                                    onclick="confirmarLiberar(<?= $d['id'] ?>, '<?= htmlspecialchars($d['numero_serie']) ?>')">
                                Liberar
                            </button>
                            <?php else: ?>
                            <button class="btn-accion btn-generar"
                                    onclick="generarToken(<?= $d['id'] ?>, '<?= htmlspecialchars($d['numero_serie']) ?>')">
                                Generar token
                            </button>
                            <?php endif; ?>

                            <?php if ($d['estado'] !== 'baja'): ?>
                            <button class="btn-accion btn-baja"
                                    onclick="confirmarBaja(<?= $d['id'] ?>, '<?= htmlspecialchars($d['numero_serie']) ?>')">
                                Dar de baja
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Usuarios -->
    <div class="tab-panel" id="tab-usuarios">
        <div class="tabla-wrap">
            <div class="tabla-header">
                <span class="tabla-titulo">Cuentas registradas</span>
                <input type="text" class="tabla-buscar" id="buscarUser"
                       placeholder="Buscar nombre o email..."
                       oninput="filtrarTabla('tablaUsers', this.value)">
            </div>
            <table id="tablaUsers">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Invernaderos</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr><td colspan="6" class="sin-datos">No hay usuarios registrados</td></tr>
                <?php else: ?>
                <?php foreach ($usuarios as $u): ?>
                <tr data-nombre="<?= strtolower($u['nombre']) ?>"
                    data-email="<?= strtolower($u['email']) ?>">
                    <td style="font-weight:500"><?= htmlspecialchars($u['nombre']) ?></td>
                    <td style="color:var(--gris-texto)"><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge badge-<?= $u['rol'] ?>" id="badge-rol-<?= $u['id'] ?>"><?= ucfirst($u['rol']) ?></span></td>
                    <td>
                        <span style="font-family:var(--mono);font-weight:600;color:var(--azul)">
                            <?= $u['total_instancias'] ?>
                        </span>
                    </td>
                    <td style="font-size:12px;color:var(--gris-texto)">
                        <?= date('d/m/Y', strtotime($u['fecha_registro'])) ?>
                    </td>
                    <td>
                        <?php if ($u['id'] !== (int)$_SESSION['usuario_id']): ?>
                        <?php if ($u['rol'] === 'cliente'): ?>
                        <button class="btn-accion btn-generar"
                                onclick="confirmarRol(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nombre']) ?>', 'admin')">
                            ⬆ Hacer admin
                        </button>
                        <?php else: ?>
                        <button class="btn-accion btn-baja"
                                onclick="confirmarRol(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nombre']) ?>', 'cliente')">
                            ⬇ Quitar admin
                        </button>
                        <?php endif; ?>
                        <?php else: ?>
                        <span style="font-size:12px;color:var(--gris-texto)">Tú</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Cambiar rol -->
<div class="modal-overlay" id="modalRol">
    <div class="modal-box">
        <div class="modal-icono" id="modalRolIcono">⬆</div>
        <div class="modal-titulo" id="modalRolTitulo">Cambiar rol</div>
        <div class="modal-sub"   id="modalRolSub"></div>
        <div class="modal-acciones">
            <button class="modal-btn-cancel" onclick="cerrarModales()">Cancelar</button>
            <button class="modal-btn-confirm verde" id="btnConfirmRol">Confirmar</button>
        </div>
    </div>
</div>

<!-- Modal: Liberar dispositivo -->
<div class="modal-overlay" id="modalLiberar">
    <div class="modal-box">
        <div class="modal-icono">🔓</div>
        <div class="modal-titulo">Liberar dispositivo</div>
        <div class="modal-sub" id="modalLiberarSub"></div>
        <div class="modal-acciones">
            <button class="modal-btn-cancel" onclick="cerrarModales()">Cancelar</button>
            <button class="modal-btn-confirm" id="btnConfirmLiberar">Sí, liberar</button>
        </div>
    </div>
</div>

<!-- Modal: Dar de baja -->
<div class="modal-overlay" id="modalBaja">
    <div class="modal-box">
        <div class="modal-icono">🚫</div>
        <div class="modal-titulo">Dar de baja</div>
        <div class="modal-sub" id="modalBajaSub"></div>
        <div class="modal-acciones">
            <button class="modal-btn-cancel" onclick="cerrarModales()">Cancelar</button>
            <button class="modal-btn-confirm" id="btnConfirmBaja">Sí, dar de baja</button>
        </div>
    </div>
</div>

<!-- Modal: Nuevo dispositivo -->
<div class="modal-overlay" id="modalNuevo">
    <div class="modal-box">
        <div class="modal-icono">📟</div>
        <div class="modal-titulo">Agregar nuevo dispositivo</div>
        <div class="modal-sub">El ID único se usa para que el cliente active su ESP32.</div>
        <div class="campo-modal">
            <label>Número de serie</label>
            <input type="text" id="nuevoSerie" placeholder="IND-0006"
                   maxlength="20" oninput="this.value=this.value.toUpperCase()">
        </div>
        <div class="token-generado" id="tokenGeneradoNuevo"></div>
        <div class="modal-acciones" style="margin-top:16px">
            <button class="modal-btn-cancel" onclick="cerrarModales()">Cancelar</button>
            <button class="modal-btn-confirm verde" onclick="crearDispositivo()">Crear dispositivo</button>
        </div>
    </div>
</div>

<!-- Modal: Token generado -->
<div class="modal-overlay" id="modalToken">
    <div class="modal-box">
        <div class="modal-icono">🔑</div>
        <div class="modal-titulo">Token generado</div>
        <div class="modal-sub">Copia este token y flashéalo en el ESP32 correspondiente.</div>
        <div class="token-generado visible" id="tokenGeneradoMostrar" style="display:block"></div>
        <div class="modal-acciones" style="margin-top:16px">
            <button class="modal-btn-cancel" onclick="cerrarModales()">Cerrar</button>
            <button class="modal-btn-confirm verde" onclick="copiarTokenModal()">Copiar token</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
// ── Tabs ──────────────────────────────────────
function cambiarTab(tab, btn) {
    document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}

// ── Filtrar tabla ─────────────────────────────
function filtrarTabla(tablaId, texto) {
    const t = texto.toLowerCase();
    document.querySelectorAll(`#${tablaId} tbody tr`).forEach(tr => {
        const datos = Object.values(tr.dataset).join(' ');
        tr.style.display = datos.includes(t) ? '' : 'none';
    });
}

// ── Copiar token ──────────────────────────────
function copiarToken(token, btn) {
    navigator.clipboard.writeText(token).then(() => {
        mostrarToast('Token copiado al portapapeles', 'ok');
    });
}

// ── Modales ───────────────────────────────────
function cerrarModales() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('visible'));
}
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) cerrarModales(); });
});

// ── Liberar dispositivo ───────────────────────
let dispIdPendiente = 0;

function confirmarLiberar(id, serie) {
    dispIdPendiente = id;
    document.getElementById('modalLiberarSub').innerHTML =
        `¿Liberar el dispositivo <strong>${serie}</strong>?<br><br>
         Se eliminará su instancia, datos históricos y token. 
         Quedará disponible para activarse en otra cuenta.`;
    document.getElementById('modalLiberar').classList.add('visible');
}

document.getElementById('btnConfirmLiberar').addEventListener('click', async () => {
    const btn = document.getElementById('btnConfirmLiberar');
    btn.textContent = 'Liberando...';
    btn.disabled    = true;

    try {
        const res  = await fetch('api/admin-accion.php', {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ accion: 'liberar', dispositivo_id: dispIdPendiente })
        });
        const data = await res.json();

        if (data.ok) {
            mostrarToast('Dispositivo liberado correctamente', 'ok');
            setTimeout(() => location.reload(), 1200);
        } else {
            mostrarToast(data.error || 'Error al liberar', 'error');
        }
    } catch(e) { mostrarToast('Error de conexión', 'error'); }

    btn.textContent = 'Sí, liberar';
    btn.disabled    = false;
    cerrarModales();
});

// ── Dar de baja ───────────────────────────────
function confirmarBaja(id, serie) {
    dispIdPendiente = id;
    document.getElementById('modalBajaSub').innerHTML =
        `¿Dar de baja el dispositivo <strong>${serie}</strong>?<br><br>
         Ya no podrá ser activado por ningún usuario.`;
    document.getElementById('modalBaja').classList.add('visible');
}

document.getElementById('btnConfirmBaja').addEventListener('click', async () => {
    const btn = document.getElementById('btnConfirmBaja');
    btn.textContent = 'Procesando...';
    btn.disabled    = true;

    try {
        const res  = await fetch('api/admin-accion.php', {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ accion: 'baja', dispositivo_id: dispIdPendiente })
        });
        const data = await res.json();

        if (data.ok) {
            mostrarToast('Dispositivo dado de baja', 'ok');
            setTimeout(() => location.reload(), 1200);
        } else {
            mostrarToast(data.error || 'Error', 'error');
        }
    } catch(e) { mostrarToast('Error de conexión', 'error'); }

    btn.textContent = 'Sí, dar de baja';
    btn.disabled    = false;
    cerrarModales();
});

// ── Nuevo dispositivo ─────────────────────────
function abrirModalNuevo() {
    document.getElementById('nuevoSerie').value = '';
    document.getElementById('tokenGeneradoNuevo').classList.remove('visible');
    document.getElementById('modalNuevo').classList.add('visible');
    setTimeout(() => document.getElementById('nuevoSerie').focus(), 100);
}

async function crearDispositivo() {
    const serie = document.getElementById('nuevoSerie').value.trim().toUpperCase();
    if (!serie) { alert('Ingresa el número de serie.'); return; }

    try {
        const res  = await fetch('api/admin-accion.php', {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ accion: 'crear', numero_serie: serie })
        });
        const data = await res.json();

        if (data.ok) {
            mostrarToast('Dispositivo creado', 'ok');
            setTimeout(() => location.reload(), 1200);
            cerrarModales();
        } else {
            mostrarToast(data.error || 'Error al crear', 'error');
        }
    } catch(e) { mostrarToast('Error de conexión', 'error'); }
}

// ── Generar token para dispositivo disponible ─
async function generarToken(id, serie) {
    try {
        const res  = await fetch('api/admin-accion.php', {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ accion: 'generar_token', dispositivo_id: id })
        });
        const data = await res.json();

        if (data.ok) {
            document.getElementById('tokenGeneradoMostrar').textContent = data.token;
            document.getElementById('modalToken').classList.add('visible');
            setTimeout(() => location.reload(), 5000);
        } else {
            mostrarToast(data.error || 'Error', 'error');
        }
    } catch(e) { mostrarToast('Error de conexión', 'error'); }
}

function copiarTokenModal() {
    const token = document.getElementById('tokenGeneradoMostrar').textContent;
    navigator.clipboard.writeText(token).then(() => {
        mostrarToast('Token copiado', 'ok');
    });
}

// ── Cambiar rol de usuario ────────────────────
let userIdPendiente  = 0;
let nuevoRolPendiente = '';

function confirmarRol(id, nombre, nuevoRol) {
    userIdPendiente   = id;
    nuevoRolPendiente = nuevoRol;

    document.getElementById('modalRolIcono').textContent = nuevoRol === 'admin' ? '⬆' : '⬇';
    document.getElementById('modalRolTitulo').textContent = nuevoRol === 'admin'
        ? 'Dar permisos de admin' : 'Quitar permisos de admin';
    document.getElementById('modalRolSub').innerHTML = nuevoRol === 'admin'
        ? `¿Darle permisos de <strong>administrador</strong> a <strong>${nombre}</strong>?<br><br>Podrá acceder al panel admin y gestionar todos los dispositivos.`
        : `¿Quitarle los permisos de admin a <strong>${nombre}</strong>?<br><br>Pasará a ser cliente normal.`;

    document.getElementById('modalRol').classList.add('visible');
}

document.getElementById('btnConfirmRol').addEventListener('click', async () => {
    const btn = document.getElementById('btnConfirmRol');
    btn.textContent = 'Guardando...';
    btn.disabled    = true;

    try {
        const res  = await fetch('api/admin-accion.php', {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ accion: 'cambiar_rol', usuario_id: userIdPendiente, rol: nuevoRolPendiente })
        });
        const data = await res.json();

        if (data.ok) {
            mostrarToast(`Rol actualizado a ${nuevoRolPendiente}`, 'ok');
            setTimeout(() => location.reload(), 1200);
        } else {
            mostrarToast(data.error || 'Error al cambiar rol', 'error');
        }
    } catch(e) { mostrarToast('Error de conexión', 'error'); }

    btn.textContent = 'Confirmar';
    btn.disabled    = false;
    cerrarModales();
});
function mostrarToast(msg, tipo = '') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className   = `toast visible ${tipo}`;
    setTimeout(() => { t.className = 'toast'; }, 3000);
}
</script>

</body>
</html>
