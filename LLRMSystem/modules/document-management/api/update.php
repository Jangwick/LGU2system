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
require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../services/DocumentService.php';
require_once __DIR__ . '/../services/FileStorageService.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

$db = getDatabase();
$documentModel = new Document($db);
$fileStorageService = new FileStorageService();
$logger = new Logger($db);
$documentService = new DocumentService($documentModel, $fileStorageService, $logger);

$documentId = Sanitizer::int($_POST['document_id'] ?? 0, 0);

if (!$documentId) {
    echo json_encode(['success' => false, 'error' => 'Document ID is required']);
    exit;
}

// Check permissions
$userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));

// Viewers cannot edit any documents
if ($userRole === 'viewer') {
    echo json_encode(['success' => false, 'error' => 'Access denied. Viewers cannot edit documents.']);
    exit;
}

// Staff can only edit their own documents
if ($userRole === 'staff') {
    $document = $documentModel->getById($documentId);
    
    if (!$document) {
        echo json_encode(['success' => false, 'error' => 'Document not found']);
        exit;
    }
    
    if ($document['uploaded_by'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'error' => 'Access denied. You can only edit your own documents.']);
        exit;
    }
}

$data = [
    'title' => Sanitizer::plainText($_POST['title'] ?? ''),
    'document_type' => Sanitizer::plainText($_POST['document_type'] ?? ''),
    'document_date' => Sanitizer::date($_POST['document_date'] ?? ''),
    'status' => Sanitizer::enum($_POST['status'] ?? '', ['draft', 'pending', 'approved', 'rejected', 'archived'], ''),
    'description' => Sanitizer::richText($_POST['description'] ?? ''),
    'tags' => Sanitizer::plainText($_POST['tags'] ?? '')
];

try {
    $result = $documentService->updateDocument($documentId, $data);
    
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
