<?php
/**
 * Get document tracking / provenance timeline
 */
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../services/DocumentTrackingService.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$documentId = Sanitizer::int($_GET['id'] ?? 0, 0);

if (!$documentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Document ID required']);
    exit;
}

try {
    $service = new DocumentTrackingService();
    $events = $service->getTrackingEvents($documentId);
    $isAdmin = $service->isAdmin($_SESSION['user_role'] ?? '');

    echo json_encode([
        'success' => true,
        'events' => $events,
        'can_add' => $isAdmin,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
