<?php
// Buffer all output so that any stray PHP warnings/notices produced during
// file validation do not corrupt the JSON response body.
ob_start();

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    ob_clean();
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/middleware/CsrfMiddleware.php';
CsrfMiddleware::requireValidToken();

// Check if user has permission to upload documents (not viewer)
$userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
if ($userRole === 'viewer') {
    ob_clean();
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Viewers cannot upload documents.']);
    exit;
}

require_once __DIR__ . '/../controllers/DocumentController.php';

$controller = new DocumentController();
$result = $controller->store();

ob_clean(); // discard any warnings emitted during processing

if ($result['success']) {
    echo json_encode($result);
} else {
    http_response_code(400);
    echo json_encode($result);
}
