<?php
/**
 * VDMsystem - Audit Logs Controller
 * Handles viewing and filtering of system audit trail
 */
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

class AuditController {
    private $db;
    
    public function __construct() {
        $this->db = getDatabase();
    }
    
    /**
     * Get paginated audit logs with filters
     */
    public function index() {
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'module' => $_GET['module'] ?? null,
            'event_type' => $_GET['event_type'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Build query
        $query = "SELECT al.*, u.full_name, u.email, u.role as user_role
                  FROM audit_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE 1=1";
        $params = [];
        
        if ($filters['user_id']) {
            $query .= " AND al.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if ($filters['module']) {
            $query .= " AND al.module = :module";
            $params[':module'] = $filters['module'];
        }
        
        if ($filters['event_type']) {
            $query .= " AND al.event_type = :event_type";
            $params[':event_type'] = $filters['event_type'];
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
            $query .= " AND (al.action LIKE :search1 OR al.event_type LIKE :search2 OR u.full_name LIKE :search3 OR u.email LIKE :search4)";
            $searchVal = '%' . $filters['search'] . '%';
            $params[':search1'] = $searchVal;
            $params[':search2'] = $searchVal;
            $params[':search3'] = $searchVal;
            $params[':search4'] = $searchVal;
        }
        
        // Count total
        $countQuery = str_replace("SELECT al.*, u.full_name, u.email, u.role as user_role", "SELECT COUNT(*)", $query);
        $countStmt = $this->db->prepare($countQuery);
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
        $modules = $this->getModules();
        $eventTypes = $this->getEventTypes();
        
        return [
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage),
            'filters' => $filters,
            'users' => $users,
            'modules' => $modules,
            'eventTypes' => $eventTypes
        ];
    }
    
    /**
     * Get unique users from audit logs
     */
    private function getUsers() {
        $stmt = $this->db->query("
            SELECT DISTINCT u.id, u.full_name, u.email 
            FROM users u
            INNER JOIN audit_logs al ON u.id = al.user_id
            ORDER BY u.full_name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get unique modules from audit logs
     */
    private function getModules() {
        $stmt = $this->db->query("
            SELECT DISTINCT module 
            FROM audit_logs 
            WHERE module IS NOT NULL AND module != ''
            ORDER BY module
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get unique event types from audit logs
     */
    private function getEventTypes() {
        $stmt = $this->db->query("
            SELECT DISTINCT event_type 
            FROM audit_logs 
            WHERE event_type IS NOT NULL AND event_type != ''
            ORDER BY event_type
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Export logs to CSV
     */
    public function exportCSV() {
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'module' => $_GET['module'] ?? null,
            'event_type' => $_GET['event_type'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        $query = "SELECT al.*, u.full_name, u.email
                  FROM audit_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE 1=1";
        $params = [];
        
        if ($filters['user_id']) {
            $query .= " AND al.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        if ($filters['module']) {
            $query .= " AND al.module = :module";
            $params[':module'] = $filters['module'];
        }
        if ($filters['event_type']) {
            $query .= " AND al.event_type = :event_type";
            $params[':event_type'] = $filters['event_type'];
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
            $query .= " AND (al.action LIKE :search1 OR u.full_name LIKE :search2)";
            $searchVal = '%' . $filters['search'] . '%';
            $params[':search1'] = $searchVal;
            $params[':search2'] = $searchVal;
        }
        
        $query .= " ORDER BY al.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Date/Time', 'User', 'Event Type', 'Module', 'Entity', 'Action', 'IP Address']);
        
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['created_at'],
                $log['full_name'] ?? 'System',
                $log['event_type'],
                $log['module'],
                ($log['entity_type'] ?? '') . ($log['entity_id'] ? '#' . $log['entity_id'] : ''),
                $log['action'],
                $log['ip_address']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Get audit statistics
     */
    public function getStatistics() {
        $stats = [];
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM audit_logs");
        $stats['total_logs'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM audit_logs WHERE DATE(created_at) = CURDATE()");
        $stats['logs_today'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM audit_logs WHERE YEARWEEK(created_at) = YEARWEEK(NOW())");
        $stats['logs_this_week'] = $stmt->fetchColumn();
        
        // Most active user
        $stmt = $this->db->query("
            SELECT u.full_name, COUNT(*) as count 
            FROM audit_logs al
            JOIN users u ON al.user_id = u.id
            GROUP BY al.user_id
            ORDER BY count DESC
            LIMIT 1
        ");
        $mostActive = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['most_active_user'] = $mostActive['full_name'] ?? 'N/A';
        $stats['most_active_count'] = $mostActive['count'] ?? 0;
        
        return $stats;
    }
}
