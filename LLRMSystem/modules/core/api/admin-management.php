<?php
session_start();
require_once __DIR__ . '/../config/config.php';
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
    $action = Sanitizer::enum($input['action'] ?? $_GET['action'] ?? '', ['list', 'promote', 'demote', 'promote_to_super', 'activate', 'deactivate', 'delete', 'update_admin'], '');

    // Handle CSV export
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        $params = [
            'search' => Sanitizer::plainText($_GET['search'] ?? null),
            'role' => Sanitizer::enum($_GET['role'] ?? null, ['viewer', 'staff', 'officer', 'administrator', 'super_admin', 'superadmin'], null),
            'status' => Sanitizer::enum($_GET['status'] ?? null, ['active', 'inactive', 'suspended', 'pending'], null),
            'department' => Sanitizer::plainText($_GET['department'] ?? null),
            'sort_by' => Sanitizer::enum($_GET['sort_by'] ?? 'created_at', ['created_at', 'name', 'email', 'role', 'status'], 'created_at'),
            'sort_order' => Sanitizer::enum($_GET['sort_order'] ?? 'DESC', ['ASC', 'DESC'], 'DESC'),
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
                'search' => Sanitizer::plainText($_GET['search'] ?? null),
                'role' => Sanitizer::enum($_GET['role'] ?? null, ['viewer', 'staff', 'officer', 'administrator', 'super_admin', 'superadmin'], null),
                'status' => Sanitizer::enum($_GET['status'] ?? null, ['active', 'inactive', 'suspended', 'pending'], null),
                'department' => Sanitizer::plainText($_GET['department'] ?? null),
                'sort_by' => Sanitizer::enum($_GET['sort_by'] ?? 'created_at', ['created_at', 'name', 'email', 'role', 'status'], 'created_at'),
                'sort_order' => Sanitizer::enum($_GET['sort_order'] ?? 'DESC', ['ASC', 'DESC'], 'DESC'),
                'page' => Sanitizer::int($_GET['page'] ?? 1, 1),
                'per_page' => Sanitizer::int($_GET['per_page'] ?? 10, 10)
            ];
            $result = $controller->getAdministrators($params);
            echo json_encode($result);
            break;

        case 'promote':
            $userId = Sanitizer::int($input['user_id'] ?? 0, 0);
            $result = $controller->promoteToAdmin($userId);
            echo json_encode($result);
            break;

        case 'demote':
            $userId = Sanitizer::int($input['user_id'] ?? 0, 0);
            $result = $controller->demoteFromAdmin($userId);
            echo json_encode($result);
            break;

        case 'promote_to_super':
            $userId = Sanitizer::int($input['user_id'] ?? 0, 0);
            $result = $controller->promoteToSuperAdmin($userId);
            echo json_encode($result);
            break;

        case 'activate':
            $userId = Sanitizer::int($input['user_id'] ?? 0, 0);
            $result = $controller->activateUser($userId);
            echo json_encode($result);
            break;

        case 'deactivate':
            $userId = Sanitizer::int($input['user_id'] ?? 0, 0);
            $result = $controller->deactivateUser($userId);
            echo json_encode($result);
            break;

        case 'delete':
            $userId = Sanitizer::int($input['user_id'] ?? 0, 0);
            $result = $controller->deleteUser($userId);
            echo json_encode($result);
            break;

        case 'update_admin':
            $userId = Sanitizer::int($input['user_id'] ?? 0, 0);
            $data = [
                'full_name' => Sanitizer::plainText($input['full_name'] ?? null),
                'email' => Sanitizer::email($input['email'] ?? null),
                'employee_id' => Sanitizer::plainText($input['employee_id'] ?? null),
                'department' => Sanitizer::plainText($input['department'] ?? null),
                'role' => Sanitizer::enum($input['role'] ?? null, ['viewer', 'staff', 'officer', 'administrator', 'super_admin', 'superadmin'], null),
                'status' => Sanitizer::enum($input['status'] ?? null, ['active', 'inactive', 'suspended', 'pending'], null)
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
