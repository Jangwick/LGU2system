<?php
/**
 * Reject a Non-Compliant Document with a Reviewer Comment
 */

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../services/ComplianceService.php';

header('Content-Type: application/json');

CsrfMiddleware::requireValidToken();

// CORS headers for API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$documentId = Sanitizer::int($data['document_id'] ?? 0, 0);
$comment = Sanitizer::plainText($data['comment'] ?? '');

if (!$documentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Document ID required']);
    exit;
}

if (empty($comment)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Rejection comment is required']);
    exit;
}

$userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
if ($userRole === 'viewer') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied. Viewers cannot reject documents.']);
    exit;
}

try {
    $db = getDatabase();
    $service = new ComplianceService($db);
    $result = $service->rejectWithComment($documentId, $comment, $_SESSION['user_id'] ?? null);

    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
