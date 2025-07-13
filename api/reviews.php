<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!$sceneiq->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Login requerido']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'POST':
        // Crear o actualizar reseña
        if (!$sceneiq->validateCSRFToken($input['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
            exit;
        }
        
        $contentId = intval($input['content_id'] ?? 0);
        $rating = floatval($input['rating'] ?? 0);
        $reviewText = trim($input['review_text'] ?? '');
        $spoilerAlert = boolval($input['spoiler_alert'] ?? false);
        
        if (!$contentId || $rating < 0.5 || $rating > 10) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }
        
        try {
            $result = $sceneiq->addReview($_SESSION['user_id'], $contentId, $rating, $reviewText, $spoilerAlert);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Reseña guardada exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar la reseña']);
            }
        } catch (Exception $e) {
            error_log("Review creation error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno']);
        }
        break;
        
    case 'GET':
        // Obtener reseñas
        $contentId = intval($_GET['content_id'] ?? 0);
        $limit = intval($_GET['limit'] ?? 10);
        $offset = intval($_GET['offset'] ?? 0);
        
        if (!$contentId) {
            echo json_encode(['success' => false, 'message' => 'ID de contenido requerido']);
            exit;
        }
        
        try {
            $reviews = $sceneiq->getReviews($contentId, $limit, $offset);
            echo json_encode(['success' => true, 'reviews' => $reviews]);
        } catch (Exception $e) {
            error_log("Get reviews error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al obtener reseñas']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>