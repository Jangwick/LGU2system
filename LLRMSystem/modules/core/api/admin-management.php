<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/PermissionMiddleware.php';

// Disable error display to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

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

    // Handle CSV export
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        $params = [
            'search' => $_GET['search'] ?? null,
            'role' => $_GET['role'] ?? null,
            'status' => $_GET['status'] ?? null,
            'department' => $_GET['department'] ?? null,
            'sort_by' => $_GET['sort_by'] ?? 'created_at',
            'sort_order' => $_GET['sort_order'] ?? 'DESC',
            'page' => 1,
            'per_page' => 10000 // Export all results
        ];
        $result = $controller->getAdministrators($params);
        $administrators = $result['data'];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="administrators_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Name', 'Email', 'Role', 'Status', 'Department', 'Employee ID', 'Created At', 'Last Login']);

        foreach ($administrators as $admin) {
            fputcsv($output, [
                $admin['full_name'],
                $admin['email'],
                $admin['role'],
                $admin['status'],
                $admin['department'],
                $admin['employee_id'],
                $admin['created_at'],
                $admin['last_login'] ?? 'Never'
            ]);
        }

        fclose($output);
        exit;
    }

    switch ($action) {
        case 'list':
            $params = [
                'search' => $_GET['search'] ?? null,
                'role' => $_GET['role'] ?? null,
                'status' => $_GET['status'] ?? null,
                'department' => $_GET['department'] ?? null,
                'sort_by' => $_GET['sort_by'] ?? 'created_at',
                'sort_order' => $_GET['sort_order'] ?? 'DESC',
                'page' => $_GET['page'] ?? 1,
                'per_page' => $_GET['per_page'] ?? 10
            ];
            $result = $controller->getAdministrators($params);
            echo json_encode($result);
            break;

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

        case 'promote_to_super':
            $userId = $input['user_id'] ?? null;
            $result = $controller->promoteToSuperAdmin($userId);
            echo json_encode($result);
            break;

        case 'activate':
            $userId = $input['user_id'] ?? null;
            $result = $controller->activateUser($userId);
            echo json_encode($result);
            break;

        case 'deactivate':
            $userId = $input['user_id'] ?? null;
            $result = $controller->deactivateUser($userId);
            echo json_encode($result);
            break;

        case 'delete':
            $userId = $input['user_id'] ?? null;
            $result = $controller->deleteUser($userId);
            echo json_encode($result);
            break;

        case 'update_admin':
            $userId = $input['user_id'] ?? null;
            $data = [
                'full_name' => $input['full_name'] ?? null,
                'email' => $input['email'] ?? null,
                'employee_id' => $input['employee_id'] ?? null,
                'department' => $input['department'] ?? null,
                'role' => $input['role'] ?? null,
                'status' => $input['status'] ?? null
            ];
            $result = $controller->updateAdministrator($userId, $data);
            echo json_encode($result);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }

} catch (Exception $e) {
    error_log("Admin Management API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log("Admin Management API fatal error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Fatal error: ' . $e->getMessage()]);
}
