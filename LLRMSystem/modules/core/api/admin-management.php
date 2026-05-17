<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/PermissionMiddleware.php';

header('Content-Type: application/json');

try {
    $db = getDatabase();
    $permissions = new PermissionMiddleware($db);
    
    // Require Super Admin access
    $permissions->requireLogin();
    $permissions->requirePermission('admin.manage');
    
    require_once __DIR__ . '/../controllers/SuperAdminController.php';
    $controller = new SuperAdminController();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_GET['action'] ?? null;
    
    switch ($action) {
        case 'promote':
            $userId = $input['user_id'] ?? null;
            $result = $controller->promoteToAdmin($userId);
            echo json_encode($result);
            break;
            
        case 'demote':
            $userId = $input['user_id'] ?? null;
            $result = $controller->demoteFromAdmin($userId);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("Admin Management API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
