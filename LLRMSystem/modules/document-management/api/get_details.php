<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../controllers/DocumentController.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'Missing document ID']);
    exit;
}

try {
    $controller = new DocumentController();
    $data = $controller->show($id);
    
    if (isset($data['error']) && $data['error']) {
        echo json_encode(['error' => $data['message']]);
    } else {
        echo json_encode(['success' => true, 'document' => $data['document']]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
