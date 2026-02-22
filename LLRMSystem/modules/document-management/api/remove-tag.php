<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/middleware/CsrfMiddleware.php';
CsrfMiddleware::requireValidToken();

require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../models/DocumentTag.php';

$db = getDatabase();
$tagModel = new DocumentTag($db);

$data = json_decode(file_get_contents('php://input'), true);

$documentId = $data['document_id'] ?? null;
$tagId = $data['tag_id'] ?? null;

if (!$documentId || !$tagId) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

try {
    $result = $tagModel->removeFromDocument($tagId, $documentId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Tag removed successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
