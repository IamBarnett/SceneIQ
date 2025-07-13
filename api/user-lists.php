<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!$sceneiq->isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$sceneiq->validateCSRFToken($input['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

$contentId = intval($input['content_id'] ?? 0);
$listType = $input['list_type'] ?? 'watchlist';
$action = $input['action'] ?? 'add';

if (!$contentId || !in_array($listType, ['watchlist', 'favorites', 'watched'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    
    if ($action === 'add') {
        $result = $sceneiq->addToUserList($userId, $contentId, $listType);
        $message = 'Agregado a tu lista exitosamente';
    } else {
        $result = $sceneiq->removeFromUserList($userId, $contentId, $listType);
        $message = 'Removido de tu lista exitosamente';
    }
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la lista']);
    }
    
} catch (Exception $e) {
    error_log("User lists error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}