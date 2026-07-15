<?php
/**
 * Document Workflow Actions API
 * Performs status transitions: review, approve, reject, request_revision, forward, archive
 */
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        ob_clean();
        http_response_code(401);
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

    $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
    $userId = $_SESSION['user_id'];

    $data = json_decode(file_get_contents('php://input'), true);
    $documentId = Sanitizer::int($data['document_id'] ?? 0, 0);
    $action = Sanitizer::enum($data['action'] ?? '', ['review', 'approve', 'reject', 'request_revision', 'forward', 'archive'], '');

    if (!$documentId || !$action) {
        ob_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Document ID and action are required']);
        exit;
    }

    $document = $documentModel->getById($documentId);
    if (!$document) {
        ob_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Document not found']);
        exit;
    }

    // Viewers cannot perform workflow actions
    if ($userRole === 'viewer') {
        ob_clean();
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied. Viewers cannot change document status.']);
        exit;
    }

    // Staff can only act on their own documents
    if ($userRole === 'staff' && $document['uploaded_by'] != $userId) {
        ob_clean();
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }

    $actionMap = [
        'review' => 'pending',
        'approve' => 'approved',
        'reject' => 'rejected',
        'request_revision' => 'draft',
        'forward' => 'pending',
        'archive' => 'archived'
    ];

    $newStatus = $actionMap[$action];

    // Cannot change status of archived documents except unarchive? Allow for now.
    $result = $documentService->updateDocument($documentId, ['status' => $newStatus]);

    ob_clean();
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Document ' . str_replace('_', ' ', $action) . 'd',
            'status' => $newStatus
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => $result['message'] ?? 'Workflow action failed']);
    }
} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
