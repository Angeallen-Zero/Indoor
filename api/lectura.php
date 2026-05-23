<?php

//  GrowSystem — API Endpoint: Guardar lectura histórica
//  Archivo : api/lectura.php
//  Método  : POST
//  Llamado por el ESP32 cada 10 segundos.
//  Inserta una fila en la tabla lecturas (historial para gráficas).
//  A diferencia de estado.php, aquí siempre se hace INSERT
//  para conservar el historial completo.
//


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['ok' => false, 'error' => 'Método no permitido']));
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$dispositivo = autenticar();

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

if ($dispositivo['instancia_id'] === null) {
    http_response_code(422);
    die(json_encode(['ok' => false, 'error' => 'Dispositivo sin instancia asignada']));
}

// Validar que vengan los datos de sensores
if (!isset($data['humedad']) && !isset($data['nutrientes'])) {
    http_response_code(400);
    die(json_encode(['ok' => false, 'error' => 'Se requiere al menos humedad o nutrientes']));
}

$instanciaId = $dispositivo['instancia_id'];
$humedad     = isset($data['humedad'])    ? round((float)$data['humedad'],    2) : null;
$nutrientes  = isset($data['nutrientes']) ? round((float)$data['nutrientes'], 2) : null;

// Validar rangos razonables (0–100%)
if ($humedad    !== null && ($humedad    < 0 || $humedad    > 100)) {
    http_response_code(400);
    die(json_encode(['ok' => false, 'error' => 'Humedad fuera de rango (0-100)']));
}
if ($nutrientes !== null && ($nutrientes < 0 || $nutrientes > 100)) {
    http_response_code(400);
    die(json_encode(['ok' => false, 'error' => 'Nutrientes fuera de rango (0-100)']));
}

$db   = getDB();
$stmt = $db->prepare("
    INSERT INTO lecturas (instancia_id, humedad, nutrientes)
    VALUES (?, ?, ?)
");
$stmt->bind_param('idd', $instanciaId, $humedad, $nutrientes);

if (!$stmt->execute()) {
    $error = $stmt->error;
    $stmt->close();
    $db->close();
    http_response_code(500);
    die(json_encode(['ok' => false, 'error' => 'Error BD: ' . $error]));
}

$insertId = $stmt->insert_id;
$stmt->close();
$db->close();

echo json_encode(['ok' => true, 'id' => $insertId]);
