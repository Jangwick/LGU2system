<?php
/**
 * Add an external document tracking event
 */
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../services/DocumentTrackingService.php';

// CORS headers for API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

CsrfMiddleware::requireValidToken();

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

$userRole = strtolower(trim($_SESSION['user_role'] ?? ''));
if ($userRole !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied. Admin only.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$documentId = Sanitizer::int($data['document_id'] ?? 0, 0);

if (!$documentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Document ID required']);
    exit;
}

try {
    $service = new DocumentTrackingService();
    $eventId = $service->addTrackingEvent($documentId, $data, $_SESSION['user_id']);

    echo json_encode([
        'success' => true,
        'id' => $eventId,
        'message' => 'Tracking event added successfully',
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
