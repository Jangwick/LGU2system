<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../controllers/CommitteeController.php';

try {
    if (!isset($_SESSION['user_id']) || !isAdmin()) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    $controller = new CommitteeController();
    echo json_encode($controller->create($_POST));
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
