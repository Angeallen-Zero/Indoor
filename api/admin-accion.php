<?php

//  GrowSystem — API Admin: acciones sobre dispositivos
//  Archivo: api/admin-accion.php
//  Solo accesible por usuarios con rol = 'admin'

session_start();
header('Content-Type: application/json');

// Solo admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    die(json_encode(['ok' => false, 'error' => 'Acceso no autorizado']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['ok' => false, 'error' => 'Método no permitido']));
}

require_once __DIR__ . '/db.php';

$body   = json_decode(file_get_contents('php://input'), true);
$accion = trim($body['accion'] ?? '');

if (empty($accion)) {
    http_response_code(400);
    die(json_encode(['ok' => false, 'error' => 'Acción no especificada']));
}

$db = getDB();

// ── LIBERAR dispositivo ───────────────────────
if ($accion === 'liberar') {
    $dispId = (int)($body['dispositivo_id'] ?? 0);
    if ($dispId <= 0) {
        $db->close();
        die(json_encode(['ok' => false, 'error' => 'dispositivo_id inválido']));
    }

    $db->begin_transaction();
    try {
        // Eliminar instancia (cascade limpia control, estado_actual, lecturas, notas)
        $stmt = $db->prepare("DELETE FROM instancias WHERE dispositivo_id = ?");
        $stmt->bind_param('i', $dispId);
        $stmt->execute();
        $stmt->close();

        // Desactivar token
        $stmt = $db->prepare("UPDATE api_tokens SET activo = 0 WHERE dispositivo_id = ?");
        $stmt->bind_param('i', $dispId);
        $stmt->execute();
        $stmt->close();

        // Liberar dispositivo
        $stmt = $db->prepare("
            UPDATE dispositivos
            SET estado = 'disponible', usuario_id = NULL, fecha_asignacion = NULL
            WHERE id = ?
        ");
        $stmt->bind_param('i', $dispId);
        $stmt->execute();
        $stmt->close();

        $db->commit();
        $db->close();
        echo json_encode(['ok' => true]);

    } catch (Exception $e) {
        $db->rollback();
        $db->close();
        echo json_encode(['ok' => false, 'error' => 'Error al liberar: ' . $e->getMessage()]);
    }
    exit;
}

// ── DAR DE BAJA dispositivo ───────────────────
if ($accion === 'baja') {
    $dispId = (int)($body['dispositivo_id'] ?? 0);
    if ($dispId <= 0) {
        $db->close();
        die(json_encode(['ok' => false, 'error' => 'dispositivo_id inválido']));
    }

    $db->begin_transaction();
    try {
        // Eliminar instancia si tiene
        $stmt = $db->prepare("DELETE FROM instancias WHERE dispositivo_id = ?");
        $stmt->bind_param('i', $dispId);
        $stmt->execute();
        $stmt->close();

        // Desactivar token
        $stmt = $db->prepare("UPDATE api_tokens SET activo = 0 WHERE dispositivo_id = ?");
        $stmt->bind_param('i', $dispId);
        $stmt->execute();
        $stmt->close();

        // Marcar como baja
        $stmt = $db->prepare("
            UPDATE dispositivos
            SET estado = 'baja', usuario_id = NULL, fecha_asignacion = NULL
            WHERE id = ?
        ");
        $stmt->bind_param('i', $dispId);
        $stmt->execute();
        $stmt->close();

        $db->commit();
        $db->close();
        echo json_encode(['ok' => true]);

    } catch (Exception $e) {
        $db->rollback();
        $db->close();
        echo json_encode(['ok' => false, 'error' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// ── CREAR nuevo dispositivo ───────────────────
if ($accion === 'crear') {
    $serie = strtoupper(trim($body['numero_serie'] ?? ''));

    if (empty($serie)) {
        $db->close();
        die(json_encode(['ok' => false, 'error' => 'Número de serie vacío']));
    }

    // Verificar que no exista
    $chk = $db->prepare("SELECT id FROM dispositivos WHERE numero_serie = ? LIMIT 1");
    $chk->bind_param('s', $serie);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $chk->close();
        $db->close();
        die(json_encode(['ok' => false, 'error' => "El ID $serie ya existe"]));
    }
    $chk->close();

    // Crear dispositivo
    $stmt = $db->prepare("INSERT INTO dispositivos (numero_serie, estado) VALUES (?, 'disponible')");
    $stmt->bind_param('s', $serie);
    if ($stmt->execute()) {
        $nuevoId = $stmt->insert_id;
        $stmt->close();

        // Generar token inicial
        $token = bin2hex(random_bytes(32));
        $stmt2 = $db->prepare("INSERT INTO api_tokens (dispositivo_id, token) VALUES (?, ?)");
        $stmt2->bind_param('is', $nuevoId, $token);
        $stmt2->execute();
        $stmt2->close();

        $db->close();
        echo json_encode(['ok' => true, 'id' => $nuevoId, 'token' => $token]);
    } else {
        $err = $db->error;
        $stmt->close();
        $db->close();
        echo json_encode(['ok' => false, 'error' => $err]);
    }
    exit;
}

// ── CAMBIAR ROL de usuario ────────────────────
if ($accion === 'cambiar_rol') {
    $usuarioId = (int)($body['usuario_id'] ?? 0);
    $rol       = trim($body['rol'] ?? '');

    if ($usuarioId <= 0) {
        $db->close();
        die(json_encode(['ok' => false, 'error' => 'usuario_id inválido']));
    }

    if (!in_array($rol, ['admin', 'cliente'])) {
        $db->close();
        die(json_encode(['ok' => false, 'error' => 'Rol no válido']));
    }

    // No puede cambiar su propio rol
    if ($usuarioId === (int)$_SESSION['usuario_id']) {
        $db->close();
        die(json_encode(['ok' => false, 'error' => 'No puedes cambiar tu propio rol']));
    }

    $stmt = $db->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
    $stmt->bind_param('si', $rol, $usuarioId);

    if ($stmt->execute()) {
        $stmt->close();
        $db->close();
        echo json_encode(['ok' => true]);
    } else {
        $err = $stmt->error;
        $stmt->close();
        $db->close();
        echo json_encode(['ok' => false, 'error' => $err]);
    }
    exit;
}

// ── GENERAR TOKEN para dispositivo existente ──
if ($accion === 'generar_token') {
    $dispId = (int)($body['dispositivo_id'] ?? 0);
    if ($dispId <= 0) {
        $db->close();
        die(json_encode(['ok' => false, 'error' => 'dispositivo_id inválido']));
    }

    $token = bin2hex(random_bytes(32));

    // Usar INSERT ... ON DUPLICATE KEY UPDATE aprovechando el UNIQUE KEY uq_dispositivo
    $stmt = $db->prepare("
        INSERT INTO api_tokens (dispositivo_id, token, activo)
        VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE token = VALUES(token), activo = 1
    ");
    $stmt->bind_param('is', $dispId, $token);

    if ($stmt->execute()) {
        $stmt->close();
        $db->close();
        echo json_encode(['ok' => true, 'token' => $token]);
    } else {
        $err = $stmt->error;
        $stmt->close();
        $db->close();
        echo json_encode(['ok' => false, 'error' => $err]);
    }
    exit;
}

$db->close();
http_response_code(400);
echo json_encode(['ok' => false, 'error' => "Acción '$accion' no reconocida"]);
