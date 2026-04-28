<?php
//  GrowSystem — Eliminar instancia y desvincular dispositivo
//  Archivo: api/eliminar-instancia.php


session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    die(json_encode(['ok' => false, 'error' => 'Sesión no iniciada']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['ok' => false, 'error' => 'Método no permitido']));
}

require_once __DIR__ . '/db.php';

$body        = json_decode(file_get_contents('php://input'), true);
$instanciaId = (int)($body['instancia_id'] ?? 0);
$usuarioId   = (int)$_SESSION['usuario_id'];

if ($instanciaId <= 0) {
    http_response_code(400);
    die(json_encode(['ok' => false, 'error' => 'instancia_id inválido']));
}

$db = getDB();

// Verificar que la instancia pertenece al usuario
$chk = $db->prepare("
    SELECT i.id, i.dispositivo_id
    FROM instancias i
    WHERE i.id = ? AND i.usuario_id = ?
    LIMIT 1
");
$chk->bind_param('ii', $instanciaId, $usuarioId);
$chk->execute();
$instancia = $chk->get_result()->fetch_assoc();
$chk->close();

if (!$instancia) {
    $db->close();
    http_response_code(403);
    die(json_encode(['ok' => false, 'error' => 'Sin acceso a esta instancia']));
}

$dispositivoId = (int)$instancia['dispositivo_id'];

$db->begin_transaction();
try {
    // 1. Eliminar instancia (cascade elimina control, estado_actual, lecturas, notas)
    $stmt = $db->prepare("DELETE FROM instancias WHERE id = ?");
    $stmt->bind_param('i', $instanciaId);
    $stmt->execute();
    $stmt->close();

    // 2. Marcar dispositivo como disponible y quitar usuario
    $stmt = $db->prepare("
        UPDATE dispositivos
        SET estado = 'disponible',
            usuario_id = NULL,
            fecha_asignacion = NULL
        WHERE id = ?
    ");
    $stmt->bind_param('i', $dispositivoId);
    $stmt->execute();
    $stmt->close();

    // 3. Desactivar token del dispositivo
    $stmt = $db->prepare("
        UPDATE api_tokens SET activo = 0
        WHERE dispositivo_id = ?
    ");
    $stmt->bind_param('i', $dispositivoId);
    $stmt->execute();
    $stmt->close();

    $db->commit();
    $db->close();

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    $db->rollback();
    $db->close();
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al eliminar: ' . $e->getMessage()]);
}
