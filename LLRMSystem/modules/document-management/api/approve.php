<?php
/**
 * Approve Document API
 */
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../services/DocumentService.php';
require_once __DIR__ . '/../controllers/DocumentController.php';

// CSRF protection
CsrfMiddleware::requireValidToken();

// CORS headers for API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Only POST method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get document ID from POST data
$data = json_decode(file_get_contents('php://input'), true);
$documentId = $data['id'] ?? null;

if (!$documentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Document ID required']);
    exit;
}

try {
    $db = getDatabase();
    $documentService = new DocumentService($db);
    
    // Allow viewers and public portal users to approve
    $result = $documentService->approveDocument($documentId);
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
