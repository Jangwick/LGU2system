<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../controllers/UserController.php';

try {
    if (!isset($_SESSION['user_id']) || !isAdmin()) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    
    $controller = new UserController();
    
    $id = $_GET['id'] ?? null;
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
