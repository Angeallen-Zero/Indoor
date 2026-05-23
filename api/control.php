<?php

//  GrowSystem — API Endpoint: Obtener parámetros de control
//  Archivo : api/control.php
//  Método  : GET
//  Llamado por el ESP32 al arrancar y cada 30 segundos.
//


header('Content-Type: application/json');

// Solo aceptar GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die(json_encode(['ok' => false, 'error' => 'Método no permitido']));
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Validar token — la identidad del dispositivo viene 100% del token,
// no del parámetro ?serie (que podría ser falsificado).
// El ESP32 ya no necesita mandar ?serie en el GET.
$dispositivo = autenticar();

// Si no hay instancia asignada aún, devolver valores por defecto
if ($dispositivo['instancia_id'] === null) {
    echo json_encode([
        'ok'                => true,
        'umbral_humedad'    => 30,
        'umbral_nutrientes' => 40,
        'ventilacion'       => false,
        'hora_encendido'    => '08:00',
        'hora_apagado'      => '18:00',
        'aviso'             => 'Dispositivo sin instancia asignada, usando valores por defecto'
    ]);
    exit;
}

// Obtener parámetros de la instancia
$db   = getDB();
$stmt = $db->prepare("
    SELECT umbral_humedad,
           umbral_nutrientes,
           ventilacion,
           DATE_FORMAT(hora_encendido, '%H:%i') AS hora_encendido,
           DATE_FORMAT(hora_apagado,   '%H:%i') AS hora_apagado
    FROM control
    WHERE instancia_id = ?
    LIMIT 1
");
$stmt->bind_param('i', $dispositivo['instancia_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $db->close();
    // No hay registro de control todavía, devolver defaults
    echo json_encode([
        'ok'                => true,
        'umbral_humedad'    => 30,
        'umbral_nutrientes' => 40,
        'ventilacion'       => false,
        'hora_encendido'    => '08:00',
        'hora_apagado'      => '18:00'
    ]);
    exit;
}

$row = $result->fetch_assoc();
$stmt->close();
$db->close();

echo json_encode([
    'ok'                => true,
    'umbral_humedad'    => (int)$row['umbral_humedad'],
    'umbral_nutrientes' => (int)$row['umbral_nutrientes'],
    'ventilacion'       => (bool)$row['ventilacion'],
    'hora_encendido'    => $row['hora_encendido'],
    'hora_apagado'      => $row['hora_apagado']
]);
