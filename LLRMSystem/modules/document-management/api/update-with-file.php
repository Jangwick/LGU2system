<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../models/DocumentVersion.php';
require_once __DIR__ . '/../services/VersionService.php';
require_once __DIR__ . '/../services/FileStorageService.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

$db = getDatabase();
$documentModel = new Document($db);
$versionModel = new DocumentVersion($db);
$fileStorageService = new FileStorageService();
$logger = new Logger($db);
$versionService = new VersionService($versionModel, $documentModel, $fileStorageService, $logger);

$documentId = $_POST['document_id'] ?? null;

if (!$documentId) {
    echo json_encode(['success' => false, 'error' => 'Document ID is required']);
    exit;
}

try {
    // Update metadata
    $data = [
        'title' => $_POST['title'] ?? '',
        'document_type' => $_POST['document_type'] ?? '',
        'document_date' => $_POST['document_date'] ?? '',
        'status' => $_POST['status'] ?? '',
        'description' => $_POST['description'] ?? ''
    ];
    
    $documentModel->update($documentId, $data);
    
    // Handle file replacement
    if (isset($_FILES['replacement_file']) && $_FILES['replacement_file']['error'] === UPLOAD_ERR_OK) {
        $changeDescription = $_POST['change_description'] ?? 'File replaced';
        
        $result = $versionService->createVersion(
            $documentId,
            $_FILES['replacement_file'],
            $changeDescription,
            $_SESSION['user_id']
        );
        
        if (!$result['success']) {
            echo json_encode($result);
            exit;
        }
    } else {
        // Log metadata update only
        $logger->log(
            $_SESSION['user_id'],
            'document_updated',
            $documentId,
            'Updated document metadata'
        );
    }
    
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
