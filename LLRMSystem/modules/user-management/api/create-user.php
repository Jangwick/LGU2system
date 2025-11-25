<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../controllers/UserController.php';

try {
    $controller = new UserController();
    $result = $controller->create($_POST);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
