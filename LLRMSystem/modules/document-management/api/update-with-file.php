<?php
ob_start();

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/middleware/CsrfMiddleware.php';
CsrfMiddleware::requireValidToken();

require_once __DIR__ . '/../../core/config/config.php';
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

$documentId = Sanitizer::int($_POST['document_id'] ?? 0, 0);

if (!$documentId) {
    echo json_encode(['success' => false, 'error' => 'Document ID is required']);
    exit;
}

try {
    // Update metadata
    $data = [
        'title' => Sanitizer::plainText($_POST['title'] ?? ''),
        'document_type' => Sanitizer::plainText($_POST['document_type'] ?? ''),
        'document_date' => Sanitizer::date($_POST['document_date'] ?? ''),
        'status' => Sanitizer::enum($_POST['status'] ?? '', ['draft', 'pending', 'approved', 'rejected'], ''),
        'description' => Sanitizer::richText($_POST['description'] ?? '')
    ];
    
    $documentModel->update($documentId, $data);
    
    // Handle file replacement
    if (isset($_FILES['replacement_file']) && $_FILES['replacement_file']['error'] === UPLOAD_ERR_OK) {
        $changeDescription = Sanitizer::plainText($_POST['change_description'] ?? 'File replaced');
        
        $result = $versionService->createVersion(
            $documentId,
            $_FILES['replacement_file'],
            $changeDescription,
            $_SESSION['user_id']
        );
        
        if (!$result['success']) {
            ob_clean();
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
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Document updated successfully'
    ]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
