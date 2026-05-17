<?php
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/middleware/PermissionMiddleware.php';

class ReportController {
    private $db;
    private $permissions;
    
    public function __construct() {
        $this->db = getDatabase();
        $this->permissions = new PermissionMiddleware($this->db);
        
        // Require administrator access
        $this->permissions->requireLogin();
        $this->permissions->requireMinimumRole('officer');
    }
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $stats = [];
        
        // Document statistics
        $stmt = $this->db->query("SELECT COUNT(*) FROM legislative_documents WHERE deleted_at IS NULL");
        $stats['total_documents'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM legislative_documents WHERE status = 'approved' AND deleted_at IS NULL");
        $stats['approved_documents'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM legislative_documents WHERE status = 'pending' AND deleted_at IS NULL");
        $stats['pending_documents'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM legislative_documents WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND deleted_at IS NULL");
        $stats['new_documents_30days'] = $stmt->fetchColumn();
        
        // User statistics
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
        $stats['active_users'] = $stmt->fetchColumn();
        
        // Activity statistics
        $stmt = $this->db->query("SELECT COUNT(*) FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stats['activities_24h'] = $stmt->fetchColumn();
        
        // Storage statistics
        $stmt = $this->db->query("SELECT SUM(file_size) FROM legislative_documents WHERE deleted_at IS NULL");
        $stats['total_storage'] = $stmt->fetchColumn() ?? 0;
        
        return $stats;
    }
    
    /**
     * Get documents by type
     */
    public function getDocumentsByType() {
        $stmt = $this->db->query("
            SELECT document_type, COUNT(*) as count 
            FROM legislative_documents 
            WHERE deleted_at IS NULL
            GROUP BY document_type 
            ORDER BY count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get documents by status
     */
    public function getDocumentsByStatus() {
        $stmt = $this->db->query("
            SELECT status, COUNT(*) as count 
            FROM legislative_documents 
            WHERE deleted_at IS NULL
            GROUP BY status 
            ORDER BY count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get documents timeline (last 12 months)
     */
    public function getDocumentsTimeline() {
        $stmt = $this->db->query("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
            FROM legislative_documents
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 MONTH)
            AND deleted_at IS NULL
            GROUP BY month
            ORDER BY month ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get top uploaders
     */
    public function getTopUploaders($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT 
                u.id,
                u.full_name,
                u.email,
                u.department,
                COUNT(ld.id) as document_count
            FROM users u
            INNER JOIN legislative_documents ld ON u.id = ld.uploaded_by
            WHERE ld.deleted_at IS NULL
            GROUP BY u.id
            ORDER BY document_count DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get activity by action type
     */
    public function getActivityByAction() {
        $stmt = $this->db->query("
            SELECT action, COUNT(*) as count 
            FROM activity_logs 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY action 
            ORDER BY count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get recent activities
     */
    public function getRecentActivities($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT 
                al.*,
                u.full_name,
                u.username
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get documents by department
     */
    public function getDocumentsByDepartment() {
        $stmt = $this->db->query("
            SELECT 
                u.department,
                COUNT(ld.id) as count
            FROM legislative_documents ld
            INNER JOIN users u ON ld.uploaded_by = u.id
            WHERE ld.deleted_at IS NULL
            AND u.department IS NOT NULL
            GROUP BY u.department
            ORDER BY count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user activity report
     */
    public function getUserActivityReport($startDate = null, $endDate = null) {
        $params = [];
        $query = "
            SELECT 
                u.id,
                u.full_name,
                u.email,
                u.role,
                u.department,
                COUNT(DISTINCT ld.id) as documents_uploaded,
                COUNT(DISTINCT al.id) as total_activities,
                MAX(al.created_at) as last_activity
            FROM users u
            LEFT JOIN legislative_documents ld ON u.id = ld.uploaded_by AND ld.deleted_at IS NULL
            LEFT JOIN activity_logs al ON u.id = al.user_id
            WHERE u.status = 'active'
        ";
        
        if ($startDate) {
            $query .= " AND (al.created_at >= :start_date OR al.created_at IS NULL)";
            $params[':start_date'] = $startDate;
        }
        
        if ($endDate) {
            $query .= " AND (al.created_at <= :end_date OR al.created_at IS NULL)";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }
        
        $query .= " GROUP BY u.id ORDER BY total_activities DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get document access report
     */
    public function getDocumentAccessReport($startDate = null, $endDate = null) {
        $params = [];
        $query = "
            SELECT 
                ld.id,
                ld.reference_number,
                ld.title,
                ld.document_type,
                COUNT(dal.id) as access_count,
                COUNT(DISTINCT dal.user_id) as unique_users,
                MAX(dal.accessed_at) as last_accessed
            FROM legislative_documents ld
            LEFT JOIN document_access_logs dal ON ld.id = dal.document_id
            WHERE ld.deleted_at IS NULL
        ";
        
        if ($startDate) {
            $query .= " AND (dal.accessed_at >= :start_date OR dal.accessed_at IS NULL)";
            $params[':start_date'] = $startDate;
        }
        
        if ($endDate) {
            $query .= " AND (dal.accessed_at <= :end_date OR dal.accessed_at IS NULL)";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }
        
        $query .= " GROUP BY ld.id ORDER BY access_count DESC LIMIT 50";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Export report to CSV
     */
    public function exportToCSV($reportType, $data) {
        $filename = $reportType . '_report_' . date('Y-m-d_His') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            // Write headers
            fputcsv($output, array_keys($data[0]));
            
            // Write data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Get storage usage by document type
     */
    public function getStorageByType() {
        $stmt = $this->db->query("
            SELECT 
                document_type,
                COUNT(*) as count,
                SUM(file_size) as total_size,
                AVG(file_size) as avg_size
            FROM legislative_documents
            WHERE deleted_at IS NULL
            GROUP BY document_type
            ORDER BY total_size DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get monthly growth statistics
     */
    public function getMonthlyGrowth() {
        $stmt = $this->db->query("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as new_documents,
                SUM(file_size) as storage_added
            FROM legislative_documents
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 MONTH)
            AND deleted_at IS NULL
            GROUP BY month
            ORDER BY month ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
