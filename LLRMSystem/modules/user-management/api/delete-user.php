<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/middleware/CsrfMiddleware.php';
CsrfMiddleware::requireValidToken();

require_once __DIR__ . '/../controllers/UserController.php';

try {
    $controller = new UserController();
    
    $id = Sanitizer::int($_POST['id'] ?? 0, 0);
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'User ID is required']);
        exit;
    }
    
    $result = $controller->delete($id);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
