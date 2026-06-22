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

$documentId = Sanitizer::int($data['document_id'] ?? 0, 0);
$linkedDocumentId = Sanitizer::int($data['linked_document_id'] ?? 0, 0);
$linkType = Sanitizer::enum($data['link_type'] ?? 'related', ['related', 'reference', 'superseded', 'attachment'], 'related');

if (!$documentId || !$linkedDocumentId) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

if ($documentId == $linkedDocumentId) {
    echo json_encode(['success' => false, 'error' => 'Cannot link document to itself']);
    exit;
}

try {
    $result = $linkModel->create($documentId, $linkedDocumentId, $linkType);
    
    if ($result) {
        // Log activity
        $logger->log(
            $_SESSION['user_id'],
            'document_link_created',
            $documentId,
            "Linked to document ID {$linkedDocumentId} as '{$linkType}'"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Documents linked successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Link already exists or failed to create'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
