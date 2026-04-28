<?php

define('DB_HOST', 'db');          // 👈 nombre del servicio en docker-compose
define('DB_USER', 'user');
define('DB_PASS', 'password');
define('DB_NAME', 'indoor_db');

function getDB(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode([
            'ok' => false,
            'error' => 'Error de conexión a BD: ' . $conn->connect_error
        ]));
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}