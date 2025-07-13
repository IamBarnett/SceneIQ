<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$query = trim($input['query'] ?? '');

if (empty($query)) {
    echo json_encode(['success' => false, 'message' => 'Query requerido']);
    exit;
}

try {
    $results = $sceneiq->searchContent($query, 20);
    
    // Registrar búsqueda si el usuario está logueado
    if ($sceneiq->isLoggedIn()) {
        $sceneiq->logActivity($_SESSION['user_id'], 'search', null, ['query' => $query]);
    }
    
    echo json_encode([
        'success' => true,
        'results' => $results,
        'query' => $query,
        'count' => count($results)
    ]);
    
} catch (Exception $e) {
    error_log("Search API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en la búsqueda']);
}
?>