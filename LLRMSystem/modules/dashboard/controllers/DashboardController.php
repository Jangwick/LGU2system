<?php

require_once __DIR__ . '/../../core/config/database.php';

class DashboardController {
    private $db;
    
    public function __construct() {
        $this->db = getDatabase();
    }
    
    /**
     * Get dashboard statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total Documents
        $stmt = $this->db->query("
            SELECT COUNT(*) as count
            FROM legislative_documents 
            WHERE deleted_at IS NULL
        ");
        $stats['total_documents'] = $stmt->fetchColumn();
        
        // New Documents (unread document-management notifications for current user)
        $userId = $_SESSION['user_id'] ?? 0;
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM notifications
            WHERE user_id = :user_id
            AND is_read = 0
            AND source_module = 'document-management'
            AND type = 'file'
        ");
        $stmt->execute([':user_id' => $userId]);
        $stats['new_documents'] = $stmt->fetchColumn();
        
        // Pending Review
        $stmt = $this->db->query("
            SELECT COUNT(*) as count
            FROM legislative_documents 
            WHERE status = 'pending' AND deleted_at IS NULL
        ");
        $stats['pending_documents'] = $stmt->fetchColumn();
        
        // Pending Urgent (documents pending for more than 7 days)
        $stmt = $this->db->query("
            SELECT COUNT(*) as count
            FROM legislative_documents 
            WHERE status = 'pending' 
            AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND deleted_at IS NULL
        ");
        $stats['urgent_pending'] = $stmt->fetchColumn();
        
        // Approved Today
        $stmt = $this->db->query("
            SELECT COUNT(*) as count
            FROM legislative_documents 
            WHERE status = 'approved' 
            AND DATE(updated_at) = CURDATE()
            AND deleted_at IS NULL
        ");
        $stats['approved_today'] = $stmt->fetchColumn();
        
        // Storage Used
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(file_size), 0) as total_size
            FROM legislative_documents 
            WHERE deleted_at IS NULL
        ");
        $stats['storage_used'] = $stmt->fetchColumn();
        $stats['storage_used_gb'] = round($stats['storage_used'] / (1024 * 1024 * 1024), 2);
        $stats['storage_total_gb'] = 500; // Total storage capacity in GB
        $stats['storage_percentage'] = round(($stats['storage_used_gb'] / $stats['storage_total_gb']) * 100);
        
        // Calculate percentage growth from last month
        $stmt = $this->db->query("
            SELECT COUNT(*) as count
            FROM legislative_documents 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
            AND deleted_at IS NULL
        ");
        $thisMonth = $stmt->fetchColumn();
        
        $stmt = $this->db->query("
            SELECT COUNT(*) as count
            FROM legislative_documents 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
            AND created_at < DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
            AND deleted_at IS NULL
        ");
        $lastMonth = $stmt->fetchColumn();
        
        if ($lastMonth > 0) {
            $stats['growth_percentage'] = round((($thisMonth - $lastMonth) / $lastMonth) * 100);
        } else {
            $stats['growth_percentage'] = $thisMonth > 0 ? 100 : 0;
        }
        
        return $stats;
    }
    
    /**
     * Get documents upload trend (last 7 days)
     */
    public function getUploadTrend() {
        $stmt = $this->db->query("
            SELECT 
                DAYNAME(created_at) as day_name,
                DATE(created_at) as date,
                COUNT(*) as count
            FROM legislative_documents
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            AND deleted_at IS NULL
            GROUP BY DATE(created_at), DAYNAME(created_at)
            ORDER BY date ASC
        ");
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure we have all 7 days (fill missing days with 0)
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $result = [];
        
        foreach ($days as $day) {
            $found = false;
            foreach ($data as $row) {
                if ($row['day_name'] === $day) {
                    $result[] = $row['count'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $result[] = 0;
            }
        }
        
        return $result;
    }
    
    /**
     * Get document types distribution
     */
    public function getDocumentTypesDistribution() {
        $stmt = $this->db->query("
            SELECT 
                document_type,
                COUNT(*) as count
            FROM legislative_documents
            WHERE deleted_at IS NULL
            GROUP BY document_type
            ORDER BY count DESC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get recent documents
     */
    public function getRecentDocuments($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT 
                ld.*,
                u.full_name as uploader_name
            FROM legislative_documents ld
            LEFT JOIN users u ON ld.uploaded_by = u.id
            WHERE ld.deleted_at IS NULL
            ORDER BY ld.created_at DESC
            LIMIT :limit
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get system status
     */
    public function getSystemStatus() {
        $status = [];
        
        // Check API Integration (check if we can query the database)
        try {
            $this->db->query("SELECT 1");
            $status['api'] = ['status' => 'online', 'class' => 'success'];
        } catch (Exception $e) {
            $status['api'] = ['status' => 'offline', 'class' => 'danger'];
        }
        
        // Check Database Health
        try {
            $stmt = $this->db->query("SHOW STATUS LIKE 'Threads_connected'");
            $connections = $stmt->fetch(PDO::FETCH_ASSOC);
            $status['database'] = ['status' => 'healthy', 'class' => 'success'];
        } catch (Exception $e) {
            $status['database'] = ['status' => 'error', 'class' => 'danger'];
        }
        
        // Storage Status
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(file_size), 0) as total_size
            FROM legislative_documents 
            WHERE deleted_at IS NULL
        ");
        $storageUsed = $stmt->fetchColumn();
        $storageUsedGB = round($storageUsed / (1024 * 1024 * 1024), 2);
        $storageTotalGB = 500;
        $storagePercentage = round(($storageUsedGB / $storageTotalGB) * 100);
        
        if ($storagePercentage < 70) {
            $status['storage'] = ['status' => $storagePercentage . '%', 'class' => 'success'];
        } elseif ($storagePercentage < 85) {
            $status['storage'] = ['status' => $storagePercentage . '%', 'class' => 'warning'];
        } else {
            $status['storage'] = ['status' => $storagePercentage . '%', 'class' => 'danger'];
        }
        
        return $status;
    }
    
    /**
     * Get activity summary
     */
    public function getActivitySummary() {
        $summary = [];
        
        // Total activities today
        $stmt = $this->db->query("
            SELECT COUNT(*) as count
            FROM activity_logs
            WHERE DATE(created_at) = CURDATE()
        ");
        $summary['today'] = $stmt->fetchColumn();
        
        // Total activities this week
        $stmt = $this->db->query("
            SELECT COUNT(*) as count
            FROM activity_logs
            WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
        ");
        $summary['this_week'] = $stmt->fetchColumn();
        
        // Most active user today
        $stmt = $this->db->query("
            SELECT 
                u.full_name,
                COUNT(*) as count
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE DATE(al.created_at) = CURDATE()
            GROUP BY al.user_id, u.full_name
            ORDER BY count DESC
            LIMIT 1
        ");
        $mostActive = $stmt->fetch(PDO::FETCH_ASSOC);
        $summary['most_active_user'] = $mostActive ? $mostActive['full_name'] : 'N/A';
        $summary['most_active_count'] = $mostActive ? $mostActive['count'] : 0;
        
        return $summary;
    }
    
    /**
     * Format file size to human readable
     */
    public function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Get status badge class
     */
    public function getStatusBadgeClass($status) {
        $classes = [
            'draft' => 'badge-secondary',
            'pending' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger'
        ];
        
        return $classes[$status] ?? 'badge-secondary';
    }
    
    /**
     * Get document type badge class
     */
    public function getTypeBadgeClass($type) {
        $classes = [
            'ordinance' => 'badge-primary',
            'resolution' => 'badge-info',
            'session' => 'badge-success',
            'agenda' => 'badge-warning',
            'committee' => 'badge-purple',
            'voting' => 'badge-pink',
            'hearing' => 'badge-orange',
            'archive' => 'badge-gray',
            'consultation' => 'badge-teal',
            'research' => 'badge-indigo'
        ];
        
        return $classes[$type] ?? 'badge-secondary';
    }
    
    /**
     * Format document type for display
     */
    public function formatDocumentType($type) {
        return ucfirst(str_replace('_', ' ', $type));
    }
}
