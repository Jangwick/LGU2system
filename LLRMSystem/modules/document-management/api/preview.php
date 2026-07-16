<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
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

$controller->preview($id);
