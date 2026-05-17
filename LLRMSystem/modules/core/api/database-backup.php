<?php
// Minimal error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Disable error display to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Increase execution time for backup operations
set_time_limit(300);
ini_set('max_execution_time', 300);

// Set header before any output
header('Content-Type: application/json; charset=utf-8');

// Default response
$response = ['success' => false, 'error' => 'Unknown error', 'debug' => 'API reached'];

try {
    session_start();
    
    // Log session status
    $response['debug'] = 'Session started, user_id: ' . ($_SESSION['user_id'] ?? 'not set');
    
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../middleware/PermissionMiddleware.php';

    $db = getDatabase();
    $response['debug'] .= ', DB connected';
    
    $permissions = new PermissionMiddleware($db);

    // Require Super Admin access
    $permissions->requireLogin();
    $permissions->requirePermission('database.backup');

    require_once __DIR__ . '/../controllers/SuperAdminController.php';
    $controller = new SuperAdminController();

    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_GET['action'] ?? null;
    
    $response['debug'] .= ', action: ' . ($action ?? 'none');

    switch ($action) {
        case 'create':
            $response = $controller->createBackup();
            $response['debug'] = 'Backup method called';
            break;

        case 'delete':
            $filename = $input['filename'] ?? null;
            $response = $controller->deleteBackup($filename);
            break;

        case 'restore':
            $filename = $input['filename'] ?? null;
            $response = $controller->restoreBackup($filename);
            break;

        case 'cleanup':
            $retentionDays = $input['retention_days'] ?? 30;
            $response = $controller->cleanupOldBackups($retentionDays);
            break;

        case 'stats':
            $stats = $controller->getBackupStats();
            $response = ['success' => true, 'data' => $stats];
            break;

        default:
            $response = ['success' => false, 'error' => 'Invalid action', 'debug' => 'Invalid action: ' . $action];
    }

} catch (Exception $e) {
    error_log("Database Backup API error: " . $e->getMessage());
    $response = ['success' => false, 'error' => 'An error occurred: ' . $e->getMessage(), 'debug' => 'Exception: ' . $e->getMessage()];
} catch (Error $e) {
    error_log("Database Backup API fatal error: " . $e->getMessage());
    $response = ['success' => false, 'error' => 'Fatal error: ' . $e->getMessage(), 'debug' => 'Fatal error: ' . $e->getMessage()];
}

echo json_encode($response);
