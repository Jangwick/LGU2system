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
    $permissions->requirePermission('database.backup');
    
    require_once __DIR__ . '/../controllers/SuperAdminController.php';
    $controller = new SuperAdminController();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_GET['action'] ?? null;
    
    switch ($action) {
        case 'create':
            $result = $controller->createBackup();
            echo json_encode($result);
            break;
            
        case 'delete':
            $filename = $input['filename'] ?? null;
            $result = $controller->deleteBackup($filename);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("Database Backup API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
