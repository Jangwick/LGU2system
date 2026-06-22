<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../controllers/UserController.php';

try {
    $controller = new UserController();
    
    $id = Sanitizer::int($_GET['id'] ?? 0, 0);
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'User ID is required']);
        exit;
    }
    
    $user = $controller->show($id);
    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'error' => 'User not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
