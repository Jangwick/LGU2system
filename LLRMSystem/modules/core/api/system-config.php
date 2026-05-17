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
    $permissions->requirePermission('system.config');
    
    require_once __DIR__ . '/../controllers/SuperAdminController.php';
    $controller = new SuperAdminController();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_GET['action'] ?? null;
    
    switch ($action) {
        case 'get_config':
            $config = $controller->getEditableConfig();
            echo json_encode(['success' => true, 'data' => $config]);
            break;
            
        case 'update':
            $configData = $input['config'] ?? [];
            $result = $controller->updateSystemConfig($configData);
            echo json_encode($result);
            break;
            
        case 'reset':
            $result = $controller->resetSystemConfig();
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("System Config API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
