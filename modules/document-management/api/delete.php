<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../controllers/DocumentController.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../models/Document.php';

$controller = new DocumentController();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Document ID is required']);
    exit;
}

// Check permissions
$userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));

// Viewers cannot delete any documents
if ($userRole === 'viewer') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Viewers cannot delete documents.']);
    exit;
}

// Staff can only delete their own documents
if ($userRole === 'staff') {
    $db = getDatabase();
    $documentModel = new Document($db);
    $document = $documentModel->getById($id);
    
    if (!$document) {
        http_response_code(404);
        echo json_encode(['error' => 'Document not found']);
        exit;
    }
    
    if ($document['uploaded_by'] != $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied. You can only delete your own documents.']);
        exit;
    }
}

$result = $controller->delete($id);

if ($result['success']) {
    echo json_encode($result);
} else {
    http_response_code(500);
    echo json_encode($result);
}
