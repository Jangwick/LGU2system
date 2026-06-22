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
require_once __DIR__ . '/../models/DocumentLink.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

$db = getDatabase();
$linkModel = new DocumentLink($db);
$logger = new Logger($db);

$data = json_decode(file_get_contents('php://input'), true);

$linkId = Sanitizer::int($data['link_id'] ?? 0, 0);
$documentId = Sanitizer::int($data['document_id'] ?? 0, 0);

if (!$linkId) {
    echo json_encode(['success' => false, 'error' => 'Link ID is required']);
    exit;
}

try {
    $result = $linkModel->delete($linkId);
    
    if ($result) {
        // Log activity
        if ($documentId) {
            $logger->log(
                $_SESSION['user_id'],
                'document_link_deleted',
                $documentId,
                "Removed document link"
            );
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Link removed successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to remove link'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
