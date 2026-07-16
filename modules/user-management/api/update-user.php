<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../controllers/UserController.php';

try {
    $controller = new UserController();
    
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'User ID is required']);
        exit;
    }
    
    $result = $controller->update($id, $_POST);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
