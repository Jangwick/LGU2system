<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

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

$documentId = $_POST['document_id'] ?? null;

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
    'title' => $_POST['title'] ?? '',
    'document_type' => $_POST['document_type'] ?? '',
    'document_date' => $_POST['document_date'] ?? '',
    'status' => $_POST['status'] ?? '',
    'description' => $_POST['description'] ?? ''
];

try {
    $result = $documentModel->update($documentId, $data);
    
    // Log activity
    $logger->log(
        $_SESSION['user_id'],
        'document_updated',
        $documentId,
        'Updated document metadata'
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Document updated successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
