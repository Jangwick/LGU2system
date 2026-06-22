<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/middleware/CsrfMiddleware.php';
CsrfMiddleware::requireValidToken();

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../models/DocumentTag.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

$db = getDatabase();
$tagModel = new DocumentTag($db);
$logger = new Logger($db);

$data = json_decode(file_get_contents('php://input'), true);

$documentId = Sanitizer::int($data['document_id'] ?? 0, 0);
$tagId = Sanitizer::int($data['tag_id'] ?? 0, 0);

if (!$documentId || !$tagId) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

try {
    $result = $tagModel->assignToDocument($tagId, $documentId);
    
    if ($result) {
        // Log tag assignment
        $logger->logActivity(Logger::ACTION_TAG_ASSIGN, 'document_tags', $documentId,
            "Tag assigned to document", [
                'tag_id' => $tagId,
                'document_id' => $documentId
            ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Tag assigned successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to assign tag'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
