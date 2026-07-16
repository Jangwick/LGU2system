<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../models/DocumentTag.php';

$db = getDatabase();
$tagModel = new DocumentTag($db);

$data = json_decode(file_get_contents('php://input'), true);

$tagName = $data['name'] ?? null;
$documentId = $data['document_id'] ?? null;

if (!$tagName) {
    echo json_encode(['success' => false, 'error' => 'Tag name is required']);
    exit;
}

try {
    $tagId = $tagModel->create($tagName);
    
    // If document ID provided, assign tag to document
    if ($documentId) {
        $tagModel->assignToDocument($tagId, $documentId);
    }
    
    echo json_encode([
        'success' => true,
        'tag_id' => $tagId,
        'message' => 'Tag created successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
