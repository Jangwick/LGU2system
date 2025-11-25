<?php
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/middleware/PermissionMiddleware.php';

class AuditController {
    private $db;
    private $permissions;
    
    public function __construct() {
        $this->db = getDatabase();
        $this->permissions = new PermissionMiddleware($this->db);
        
        // Require administrator access
        $this->permissions->requireLogin();
        $this->permissions->requirePermission('audit.view');
    }
    
    /**
     * Get activity logs with filters
     */
    public function index() {
        // Get filter parameters
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'table_name' => $_GET['table_name'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        // Build query
        $query = "SELECT al.*, u.username, u.full_name, u.email 
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE 1=1";
        $params = [];
        
        // Apply filters
        if ($filters['user_id']) {
            $query .= " AND al.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if ($filters['action']) {
            $query .= " AND al.action = :action";
            $params[':action'] = $filters['action'];
        }
        
        if ($filters['table_name']) {
            $query .= " AND al.table_name = :table_name";
            $params[':table_name'] = $filters['table_name'];
        }
        
        if ($filters['date_from']) {
            $query .= " AND DATE(al.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if ($filters['date_to']) {
            $query .= " AND DATE(al.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if ($filters['search']) {
            $query .= " AND (al.description LIKE :search OR u.username LIKE :search OR u.full_name LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        // Count total
        $countStmt = $this->db->prepare(str_replace("SELECT al.*, u.username, u.full_name, u.email", "SELECT COUNT(*)", $query));
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Get paginated results
        $query .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get filter options
        $users = $this->getUsers();
        $actions = $this->getActions();
        $tables = $this->getTables();
        
        return [
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage),
            'filters' => $filters,
            'users' => $users,
            'actions' => $actions,
            'tables' => $tables
        ];
    }
    
    /**
     * Get unique users from activity logs
     */
    private function getUsers() {
        $stmt = $this->db->query("
            SELECT DISTINCT u.id, u.username, u.full_name 
            FROM users u
            INNER JOIN activity_logs al ON u.id = al.user_id
            ORDER BY u.full_name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get unique actions from activity logs
     */
    private function getActions() {
        $stmt = $this->db->query("
            SELECT DISTINCT action 
            FROM activity_logs 
            ORDER BY action
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get unique table names from activity logs
     */
    private function getTables() {
        $stmt = $this->db->query("
            SELECT DISTINCT table_name 
            FROM activity_logs 
            ORDER BY table_name
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Export logs to CSV
     */
    public function exportCSV() {
        // Same filters as index
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'table_name' => $_GET['table_name'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        // Build query (same as index but without pagination)
        $query = "SELECT al.*, u.username, u.full_name 
                  FROM activity_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE 1=1";
        $params = [];
        
        if ($filters['user_id']) {
            $query .= " AND al.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if ($filters['action']) {
            $query .= " AND al.action = :action";
            $params[':action'] = $filters['action'];
        }
        
        if ($filters['table_name']) {
            $query .= " AND al.table_name = :table_name";
            $params[':table_name'] = $filters['table_name'];
        }
        
        if ($filters['date_from']) {
            $query .= " AND DATE(al.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if ($filters['date_to']) {
            $query .= " AND DATE(al.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if ($filters['search']) {
            $query .= " AND (al.description LIKE :search OR u.username LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $query .= " ORDER BY al.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, ['ID', 'Date/Time', 'User', 'Action', 'Table', 'Record ID', 'Description', 'IP Address']);
        
        // CSV data
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['created_at'],
                $log['full_name'] ?? $log['username'] ?? 'Unknown',
                $log['action'],
                $log['table_name'],
                $log['record_id'],
                $log['description'],
                $log['ip_address']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Get statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total logs
        $stmt = $this->db->query("SELECT COUNT(*) FROM activity_logs");
        $stats['total_logs'] = $stmt->fetchColumn();
        
        // Logs today
        $stmt = $this->db->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()");
        $stats['logs_today'] = $stmt->fetchColumn();
        
        // Logs this week
        $stmt = $this->db->query("SELECT COUNT(*) FROM activity_logs WHERE YEARWEEK(created_at) = YEARWEEK(NOW())");
        $stats['logs_this_week'] = $stmt->fetchColumn();
        
        // Most active user
        $stmt = $this->db->query("
            SELECT u.full_name, COUNT(*) as count 
            FROM activity_logs al
            JOIN users u ON al.user_id = u.id
            GROUP BY al.user_id
            ORDER BY count DESC
            LIMIT 1
        ");
        $mostActive = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['most_active_user'] = $mostActive['full_name'] ?? 'N/A';
        $stats['most_active_count'] = $mostActive['count'] ?? 0;
        
        // Actions by type
        $stmt = $this->db->query("
            SELECT action, COUNT(*) as count 
            FROM activity_logs 
            GROUP BY action 
            ORDER BY count DESC
        ");
        $stats['actions_by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
}
