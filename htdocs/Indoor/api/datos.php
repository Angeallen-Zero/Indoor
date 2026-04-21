<?php

//  GrowSystem — API Endpoint: Dashboard
//  Archivo : api/datos.php
//  Uso interno del frontend PHP (no del ESP32)
//  Requiere sesión de usuario activa.


header('Content-Type: application/json');
session_start();

// Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    die(json_encode(['ok' => false, 'error' => 'Sesión no iniciada']));
}

require_once __DIR__ . '/db.php';

$db          = getDB();
$usuarioId   = (int)$_SESSION['usuario_id'];
$instanciaId = isset($_GET['instancia_id']) ? (int)$_GET['instancia_id'] : 0;
$accion      = isset($_GET['accion']) ? trim($_GET['accion']) : 'estado';

if ($instanciaId <= 0) {
    http_response_code(400);
    die(json_encode(['ok' => false, 'error' => 'instancia_id inválido']));
}

// Verificar que la instancia pertenece al usuario (o es admin)
$rolUsuario = $_SESSION['rol'] ?? 'cliente';
if ($rolUsuario !== 'admin') {
    $chk = $db->prepare("SELECT id FROM instancias WHERE id = ? AND usuario_id = ? AND activa = 1");
    $chk->bind_param('ii', $instanciaId, $usuarioId);
    $chk->execute();
    if ($chk->get_result()->num_rows === 0) {
        $chk->close();
        $db->close();
        http_response_code(403);
        die(json_encode(['ok' => false, 'error' => 'Sin acceso a esta instancia']));
    }
    $chk->close();
}

// ── ACCIÓN: estado ──────────────────────────────────────────
if ($accion === 'estado' && $_SERVER['REQUEST_METHOD'] === 'GET') {

    $stmt = $db->prepare("
        SELECT
            ea.bomba, ea.ventilador, ea.luces, ea.surtidor,
            ea.humedad, ea.nutrientes, ea.ultima_actualizacion,
            c.umbral_humedad, c.umbral_nutrientes,
            c.ventilacion,
            DATE_FORMAT(c.hora_encendido, '%H:%i') AS hora_encendido,
            DATE_FORMAT(c.hora_apagado,   '%H:%i') AS hora_apagado
        FROM instancias i
        LEFT JOIN estado_actual ea ON i.id = ea.instancia_id
        LEFT JOIN control       c  ON i.id = c.instancia_id
        WHERE i.id = ?
    ");
    $stmt->bind_param('i', $instanciaId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $db->close();

    echo json_encode([
        'ok'    => true,
        'estado' => [
            'bomba'      => (bool)($row['bomba']      ?? 0),
            'ventilador' => (bool)($row['ventilador'] ?? 0),
            'luces'      => (bool)($row['luces']      ?? 0),
            'surtidor'   => (bool)($row['surtidor']   ?? 0),
            'humedad'    => $row['humedad']    !== null ? (float)$row['humedad']    : null,
            'nutrientes' => $row['nutrientes'] !== null ? (float)$row['nutrientes'] : null,
            'ultima_actualizacion' => $row['ultima_actualizacion'] ?? null,
        ],
        'control' => [
            'umbral_humedad'    => (int)($row['umbral_humedad']    ?? 30),
            'umbral_nutrientes' => (int)($row['umbral_nutrientes'] ?? 40),
            'ventilacion'       => (bool)($row['ventilacion']      ?? false),
            'hora_encendido'    => $row['hora_encendido'] ?? '08:00',
            'hora_apagado'      => $row['hora_apagado']   ?? '18:00',
        ]
    ]);
    exit;
}

// ── ACCIÓN: grafica ─────────────────────────────────────────
if ($accion === 'grafica' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $limite = isset($_GET['limite']) ? min((int)$_GET['limite'], 200) : 50;

    $stmt = $db->prepare("
        SELECT humedad, nutrientes,
               DATE_FORMAT(fecha, '%H:%i') AS hora,
               fecha
        FROM lecturas
        WHERE instancia_id = ?
        ORDER BY fecha DESC
        LIMIT ?
    ");
    $stmt->bind_param('ii', $instanciaId, $limite);
    $stmt->execute();
    $result = $stmt->get_result();

    $lecturas = [];
    while ($r = $result->fetch_assoc()) {
        $lecturas[] = [
            'hora'       => $r['hora'],
            'humedad'    => (float)$r['humedad'],
            'nutrientes' => (float)$r['nutrientes'],
        ];
    }
    $stmt->close();
    $db->close();

    // Invertir para que la gráfica vaya de más antiguo a más reciente
    echo json_encode(['ok' => true, 'lecturas' => array_reverse($lecturas)]);
    exit;
}

// ── ACCIÓN: control (guardar parámetros desde el dashboard) ─
if ($accion === 'control' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) {
        http_response_code(400);
        die(json_encode(['ok' => false, 'error' => 'Body JSON inválido']));
    }

    $umbralHum  = isset($body['umbral_humedad'])    ? max(0, min(100, (int)$body['umbral_humedad']))    : null;
    $umbralNut  = isset($body['umbral_nutrientes'])  ? max(0, min(100, (int)$body['umbral_nutrientes'])) : null;
    $ventilacion = isset($body['ventilacion'])       ? (int)(bool)$body['ventilacion']                  : null;
    $horaON     = isset($body['hora_encendido'])     ? substr(trim($body['hora_encendido']), 0, 5)       : null;
    $horaOFF    = isset($body['hora_apagado'])       ? substr(trim($body['hora_apagado']),   0, 5)       : null;

    // UPSERT en tabla control
    $stmt = $db->prepare("
        INSERT INTO control
            (instancia_id, umbral_humedad, umbral_nutrientes, ventilacion, hora_encendido, hora_apagado)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            umbral_humedad    = COALESCE(VALUES(umbral_humedad),    umbral_humedad),
            umbral_nutrientes = COALESCE(VALUES(umbral_nutrientes), umbral_nutrientes),
            ventilacion       = COALESCE(VALUES(ventilacion),       ventilacion),
            hora_encendido    = COALESCE(VALUES(hora_encendido),    hora_encendido),
            hora_apagado      = COALESCE(VALUES(hora_apagado),      hora_apagado)
    ");
    $stmt->bind_param('iiiidd',
        $instanciaId, $umbralHum, $umbralNut, $ventilacion, $horaON, $horaOFF
    );

    // Usar query dinámica para solo actualizar los campos enviados
    $stmt->close();

    $campos = [];
    $tipos  = '';
    $vals   = [];

    if ($umbralHum  !== null) { $campos[] = 'umbral_humedad = ?';    $tipos .= 'i'; $vals[] = $umbralHum; }
    if ($umbralNut  !== null) { $campos[] = 'umbral_nutrientes = ?'; $tipos .= 'i'; $vals[] = $umbralNut; }
    if ($ventilacion !== null){ $campos[] = 'ventilacion = ?';       $tipos .= 'i'; $vals[] = $ventilacion; }
    if ($horaON     !== null) { $campos[] = 'hora_encendido = ?';    $tipos .= 's'; $vals[] = $horaON; }
    if ($horaOFF    !== null) { $campos[] = 'hora_apagado = ?';      $tipos .= 's'; $vals[] = $horaOFF; }

    if (empty($campos)) {
        $db->close();
        die(json_encode(['ok' => false, 'error' => 'Sin campos para actualizar']));
    }

    $sql  = "UPDATE control SET " . implode(', ', $campos) . " WHERE instancia_id = ?";
    $tipos .= 'i';
    $vals[] = $instanciaId;

    $stmt = $db->prepare($sql);
    $stmt->bind_param($tipos, ...$vals);
    $ok = $stmt->execute();
    $stmt->close();
    $db->close();

    echo json_encode(['ok' => $ok]);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Acción no reconocida']);
