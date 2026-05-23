<?php

//  GrowSystem — API Endpoint: Reportar estado de relés y sensores
//  Archivo : api/estado.php
//  Método  : POST
//  Llamado por el ESP32 cada 5 segundos.
//  Actualiza la tabla estado_actual (una sola fila por instancia).
//  El dashboard hace polling a este endpoint para refrescar la UI.


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['ok' => false, 'error' => 'Método no permitido']));
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$dispositivo = autenticar();

// Leer body JSON
$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!$data) {
    http_response_code(400);
    die(json_encode(['ok' => false, 'error' => 'Body JSON inválido']));
}

// Validar serie
$serie = isset($data['serie']) ? trim($data['serie']) : '';
if ($serie !== $dispositivo['numero_serie']) {
    http_response_code(403);
    die(json_encode(['ok' => false, 'error' => 'Serie no coincide con el token']));
}

// Verificar instancia
if ($dispositivo['instancia_id'] === null) {
    http_response_code(422);
    die(json_encode(['ok' => false, 'error' => 'Dispositivo sin instancia asignada']));
}

// Extraer y sanitizar campos
$instanciaId = $dispositivo['instancia_id'];
$bomba       = isset($data['bomba'])      ? (int)(bool)$data['bomba']      : 0;
$ventilador  = isset($data['ventilador']) ? (int)(bool)$data['ventilador'] : 0;
$luces       = isset($data['luces'])      ? (int)(bool)$data['luces']      : 0;
$surtidor    = isset($data['surtidor'])   ? (int)(bool)$data['surtidor']   : 0;
$humedad     = isset($data['humedad'])    ? round((float)$data['humedad'],    2) : null;
$nutrientes  = isset($data['nutrientes']) ? round((float)$data['nutrientes'], 2) : null;

$db = getDB();

// INSERT si no existe, UPDATE si ya existe (UPSERT)
// bind_param no acepta null para DECIMAL — convertir a 0 si vienen null
if ($humedad    === null) $humedad    = 0.0;
if ($nutrientes === null) $nutrientes = 0.0;

$stmt = $db->prepare("
    INSERT INTO estado_actual
        (instancia_id, bomba, ventilador, luces, surtidor, humedad, nutrientes)
    VALUES
        (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        bomba      = VALUES(bomba),
        ventilador = VALUES(ventilador),
        luces      = VALUES(luces),
        surtidor   = VALUES(surtidor),
        humedad    = VALUES(humedad),
        nutrientes = VALUES(nutrientes),
        ultima_actualizacion = NOW()
");
$stmt->bind_param('iiiiidd',
    $instanciaId,
    $bomba,
    $ventilador,
    $luces,
    $surtidor,
    $humedad,
    $nutrientes
);

if (!$stmt->execute()) {
    $error = $stmt->error;
    $stmt->close();
    $db->close();
    http_response_code(500);
    die(json_encode(['ok' => false, 'error' => 'Error BD: ' . $error]));
}

$stmt->close();
$db->close();

echo json_encode(['ok' => true]);
