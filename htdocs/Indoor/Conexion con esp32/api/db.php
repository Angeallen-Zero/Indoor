<?php

//  GrowSystem — Conexión a MySQL
//  Archivo: api/db.php
//  Incluir en todos los endpoints de la API.


define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'growsystem');

function getDB(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode(['ok' => false, 'error' => 'Error de conexión a BD']));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
