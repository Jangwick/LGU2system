<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../controllers/DocumentController.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$id = Sanitizer::int($_GET['id'] ?? 0, 0);

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
        echo json_encode([
            'success' => true, 
            'document' => $data['document'],
            'versions' => $data['versions'],
            'related' => $data['related'],
            'activity' => $data['activity']
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
