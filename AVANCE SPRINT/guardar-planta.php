<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode([
        "success" => false,
        "error" => "No llegaron datos"
    ]);
    exit;
}

$conexion = new mysqli("localhost", "root", "", "growsystem");

if ($conexion->connect_error) {
    echo json_encode([
        "success" => false,
        "error" => "Error conexión BD"
    ]);
    exit;
}

$stmt = $conexion->prepare("INSERT INTO plantas 
(nombre_comun, nombre_cientifico, familia, genero, confianza, imagen_subida, imagen_referencia) 
VALUES (?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "error" => $conexion->error
    ]);
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
    echo json_encode(["success" => true]);
} else {
    echo json_encode([
        "success" => false,
        "error" => $stmt->error
    ]);
}

$stmt->close();
$conexion->close();
?>