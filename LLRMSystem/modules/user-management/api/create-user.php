<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/middleware/CsrfMiddleware.php';
CsrfMiddleware::requireValidToken();

require_once __DIR__ . '/../controllers/UserController.php';

try {
    $controller = new UserController();
    $data = Request::postAll([
        'name' => 'plaintext',
        'email' => 'email',
        'password' => 'string',
        'role' => 'string',
        'department' => 'plaintext',
        'status' => 'string',
        'full_name' => 'plaintext',
        'username' => 'plaintext',
    ]);
    $result = $controller->create($data);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
