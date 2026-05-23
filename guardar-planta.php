<?php
//  GrowSystem — Guardar planta identificada por PlantNet
//  Devuelve planta_id para que add-plantas.php redirija
//  a activar.php con el parámetro ?planta_id=X

session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["success" => false, "error" => "Sesión no iniciada"]);
    exit;
}

$input = file_get_contents("php://input");
$data  = json_decode($input, true);

if (!$data) {
    echo json_encode(["success" => false, "error" => "No llegaron datos"]);
    exit;
}

require_once __DIR__ . '/api/db.php';
$db = getDB();

$stmt = $db->prepare("
    INSERT INTO plantas
        (nombre_comun, nombre_cientifico, familia, genero,
         confianza, imagen_subida, imagen_referencia)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    echo json_encode(["success" => false, "error" => $db->error]);
    exit;
}

$stmt->bind_param(
    "ssssiss",
    $data["nombreComun"],
    $data["nombreCientifico"],
    $data["familia"],
    $data["genero"],
    $data["confianza"],
    $data["imagenSubida"],
    $data["imagenReferencia"]
);

if ($stmt->execute()) {
    $plantaId = $stmt->insert_id;
    $stmt->close();
    $db->close();
    echo json_encode(["success" => true, "planta_id" => $plantaId]);
} else {
    $error = $stmt->error;
    $stmt->close();
    $db->close();
    echo json_encode(["success" => false, "error" => $error]);
}
