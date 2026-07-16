<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Viewers cannot download documents
$userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
if ($userRole === 'viewer') {
    http_response_code(403);
    echo json_encode(['error' => 'Viewers do not have permission to download documents']);
    exit;
}

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../controllers/DocumentController.php';

$controller = new DocumentController();
$id = Sanitizer::int($_GET['id'] ?? 0, 0);

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Document ID is required']);
    exit;
}

$controller->download($id);
