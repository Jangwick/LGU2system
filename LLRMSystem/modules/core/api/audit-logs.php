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
    $action = Sanitizer::enum($input['action'] ?? $_GET['action'] ?? '', ['list', 'export_csv'], '');

    switch ($action) {
        case 'list':
            $filters = [
                'user_id' => Sanitizer::int($input['user_id'] ?? $_GET['user_id'] ?? 0, 0) ?: null,
                'action' => Sanitizer::plainText($input['action_filter'] ?? $_GET['action'] ?? null),
                'table_name' => Sanitizer::plainText($input['table_name'] ?? $_GET['table_name'] ?? null),
                'date_from' => Sanitizer::date($input['date_from'] ?? $_GET['date_from'] ?? ''),
                'date_to' => Sanitizer::date($input['date_to'] ?? $_GET['date_to'] ?? ''),
                'search' => Sanitizer::plainText($input['search'] ?? $_GET['search'] ?? null)
            ];
            $page = Sanitizer::int($input['page'] ?? $_GET['page'] ?? 1, 1);
            $perPage = Sanitizer::int($input['per_page'] ?? $_GET['per_page'] ?? 20, 20);
            $result = $controller->getAuditLogs($filters, $page, $perPage);
            echo json_encode(['success' => true, 'data' => $result]);
            break;

        case 'export_csv':
            $filters = [
                'user_id' => Sanitizer::int($_GET['user_id'] ?? 0, 0) ?: null,
                'action' => Sanitizer::plainText($_GET['action'] ?? null),
                'table_name' => Sanitizer::plainText($_GET['table_name'] ?? null),
                'date_from' => Sanitizer::date($_GET['date_from'] ?? ''),
                'date_to' => Sanitizer::date($_GET['date_to'] ?? ''),
                'search' => Sanitizer::plainText($_GET['search'] ?? null)
            ];
            $result = $controller->getAuditLogs($filters, 1, 10000);
            $logs = $result['logs'];

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d_His') . '.csv"');

            $output = fopen('php://output', 'w');

            fputcsv($output, ['ID', 'Date/Time', 'User', 'Email', 'Action', 'Table', 'Record ID', 'Description', 'IP Address', 'User Agent']);

            foreach ($logs as $log) {
                fputcsv($output, [
                    $log['id'],
                    $log['created_at'],
                    $log['full_name'] ?? $log['username'] ?? 'Unknown',
                    $log['email'] ?? '',
                    $log['action'],
                    $log['table_name'],
                    $log['record_id'],
                    $log['description'],
                    $log['ip_address'],
                    $log['user_agent'] ?? ''
                ]);
            }

            fclose($output);
            exit;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }

} catch (Exception $e) {
    error_log("Audit Logs API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]);
}
