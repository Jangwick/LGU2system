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
    
    $data = Request::postAll([
        'name' => 'plaintext',
        'email' => 'email',
        'full_name' => 'plaintext',
        'role' => 'string',
        'department' => 'plaintext',
        'status' => 'string',
        'password' => 'string',
    ]);
    $data['id'] = $id;
    
    $result = $controller->update($id, $data);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
