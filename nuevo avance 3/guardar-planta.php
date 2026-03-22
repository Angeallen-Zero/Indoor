<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // 🔥 IMPORTANTE

header("Content-Type: application/json");

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        "success" => false,
        "error" => "Usuario no autenticado"
    ]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Leer JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode([
        "success" => false,
        "error" => "No llegaron datos"
    ]);
    exit;
}

// Conexión
$conexion = new mysqli("localhost", "root", "", "indoor");

if ($conexion->connect_error) {
    echo json_encode([
        "success" => false,
        "error" => "Error conexión BD: " . $conexion->connect_error
    ]);
    exit;
}

// Preparar INSERT (🔥 ahora con usuario_id)
$stmt = $conexion->prepare("INSERT INTO plantas 
(nombre_comun, nombre_cientifico, familia, genero, confianza, imagen_subida, imagen_referencia, usuario_id) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "error" => $conexion->error
    ]);
    exit;
}

// Validar datos
$nombreComun = $data["nombreComun"] ?? "";
$nombreCientifico = $data["nombreCientifico"] ?? "";
$familia = $data["familia"] ?? "";
$genero = $data["genero"] ?? "";
$confianza = isset($data["confianza"]) ? (int)$data["confianza"] : 0;
$imagenSubida = $data["imagenSubida"] ?? "";
$imagenReferencia = $data["imagenReferencia"] ?? null;

// Bind (🔥 agregamos usuario_id)
$stmt->bind_param(
    "ssssissi",
    $nombreComun,
    $nombreCientifico,
    $familia,
    $genero,
    $confianza,
    $imagenSubida,
    $imagenReferencia,
    $usuario_id
);

// Ejecutar
if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "mensaje" => "Planta guardada correctamente"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => $stmt->error
    ]);
}

$stmt->close();
$conexion->close();
?>