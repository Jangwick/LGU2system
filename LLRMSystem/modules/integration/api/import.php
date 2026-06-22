<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../controllers/IntegrationController.php';

try {
    $id = Sanitizer::int($_POST['id'] ?? 0, 0);
    if (!$id) {
        throw new Exception("Missing record ID");
    }

    $controller = new IntegrationController();
    $result = $controller->importToLRMS($id);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
