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
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Committee ID required']);
        exit;
    }
    echo json_encode($controller->update($id, $_POST));
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
