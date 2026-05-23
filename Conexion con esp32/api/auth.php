<?php

//  GrowSystem — Validación de token de ESP32
//  Archivo: api/auth.php
//
//  Valida el header X-API-Token Y que el numero_serie
//  del body/params coincida con el dueño del token en BD.
//  Si no coinciden → 403. Así un ESP32 con token ajeno
//  no puede escribir datos en ninguna instancia.


require_once __DIR__ . '/db.php';

function autenticar(): array {
    // ── 1. Leer token del header ──────────────────────────
    $token = trim($_SERVER['HTTP_X_API_TOKEN'] ?? '');

    if (empty($token)) {
        http_response_code(401);
        die(json_encode([
            'ok'    => false,
            'error' => 'Token no proporcionado'
        ]));
    }

    // ── 2. Buscar token en BD ─────────────────────────────
    $db   = getDB();
    $stmt = $db->prepare("
        SELECT t.dispositivo_id, d.numero_serie, i.id AS instancia_id
        FROM api_tokens t
        JOIN dispositivos d ON t.dispositivo_id = d.id
        LEFT JOIN instancias i ON d.id = i.dispositivo_id
        WHERE t.token = ? AND t.activo = 1
        LIMIT 1
    ");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $db->close();
        http_response_code(401);
        die(json_encode([
            'ok'    => false,
            'error' => 'Token inválido o inactivo'
        ]));
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    // ── 3. Leer serie enviada por el ESP32 ────────────────
    // Puede venir del body JSON (POST) o de $_GET (GET)
    $serieEnviada = '';

    $body = file_get_contents('php://input');
    if (!empty($body)) {
        $json = json_decode($body, true);
        $serieEnviada = strtoupper(trim($json['serie'] ?? ''));
    }

    // Si no vino en el body, buscar en GET
    if (empty($serieEnviada) && isset($_GET['serie'])) {
        $serieEnviada = strtoupper(trim($_GET['serie']));
    }

    // ── 4. Validar que la serie coincide con el token ─────
    // Si el ESP32 mandó su serie pero no coincide → rechazar
    if (!empty($serieEnviada) && $serieEnviada !== $row['numero_serie']) {
        $db->close();
        http_response_code(403);
        die(json_encode([
            'ok'    => false,
            'error' => "Token no corresponde al dispositivo $serieEnviada. " .
                       "Este token pertenece a " . $row['numero_serie'] . "."
        ]));
    }

    // ── 5. Registrar último uso ───────────────────────────
    $upd = $db->prepare("UPDATE api_tokens SET ultimo_uso = NOW() WHERE token = ?");
    $upd->bind_param('s', $token);
    $upd->execute();
    $upd->close();
    $db->close();

    return [
        'dispositivo_id' => (int)$row['dispositivo_id'],
        'numero_serie'   => $row['numero_serie'],
        'instancia_id'   => $row['instancia_id'] ? (int)$row['instancia_id'] : null,
    ];
}
