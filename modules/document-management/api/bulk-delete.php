<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../../modules/core/config/database.php';
require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../services/DocumentService.php';
require_once __DIR__ . '/../services/FileStorageService.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['document_ids']) || !is_array($input['document_ids'])) {
        throw new Exception('Invalid request: document_ids required');
    }
    
    $documentIds = $input['document_ids'];
    
    if (empty($documentIds)) {
        throw new Exception('No documents selected');
    }
    
    // Initialize services
    $db = getDatabase();
    $documentModel = new Document($db);
    $fileStorageService = new FileStorageService();
    $logger = new Logger($db);
    $documentService = new DocumentService($documentModel, $fileStorageService, $logger);
    
    $deleted = 0;
    $errors = [];
    
    // Delete each document
    foreach ($documentIds as $id) {
        try {
            $result = $documentService->deleteDocument($id);
            if ($result['success']) {
                $deleted++;
            } else {
                $errors[] = "Document ID {$id}: " . ($result['error'] ?? 'Unknown error');
            }
        } catch (Exception $e) {
            $errors[] = "Document ID {$id}: " . $e->getMessage();
        }
    }
    
    // Log bulk delete action with enhanced logging
    $logger->logActivity(Logger::ACTION_DOCUMENT_DELETE, 'documents', null,
        "Bulk deleted {$deleted} document(s)", [
            'deleted_count' => $deleted,
            'document_ids' => $documentIds,
            'errors_count' => count($errors)
        ]);
    
    echo json_encode([
        'success' => true,
        'deleted' => $deleted,
        'errors' => $errors,
        'message' => "{$deleted} document(s) deleted successfully" . (!empty($errors) ? " with " . count($errors) . " error(s)" : "")
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
