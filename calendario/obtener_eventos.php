<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

require_once 'api/db.php';
$db = getDB();

$usuario_id = $_SESSION['usuario_id'];

$stmt = $db->prepare("SELECT fecha, comentario, imagen FROM eventos WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$eventos = [];
while ($row = $result->fetch_assoc()) {
    $eventos[] = $row;
}

echo json_encode($eventos);
$stmt->close();
$db->close();