<?php

require_once __DIR__ . '/../../core/config/database.php';

class DashboardService {
    private $db;
    
    public function __construct() {
        $this->db = getDatabase();
    }
    
    /**
     * Get dashboard widgets configuration
     */
    public function getWidgetsConfig() {
        return [
            'total_documents' => [
                'title' => 'Total Documents',
                'icon' => 'bi-file-earmark-text',
                'color' => 'blue',
                'link' => '/LLRMSystem/modules/document-management/views/index.php'
            ],
            'pending_review' => [
                'title' => 'Pending Review',
                'icon' => 'bi-hourglass-split',
                'color' => 'yellow',
                'link' => '/LLRMSystem/modules/document-management/views/index.php?status=pending'
            ],
            'approved_today' => [
                'title' => 'Approved Today',
                'icon' => 'bi-check-circle',
                'color' => 'green',
                'link' => '/LLRMSystem/modules/document-management/views/index.php?status=approved'
            ],
            'storage_used' => [
                'title' => 'Storage Used',
                'icon' => 'bi-hdd-stack',
                'color' => 'purple',
                'link' => null
            ]
        ];
    }
    
    /**
     * Get user activity summary
     */
    public function getUserActivitySummary($userId) {
        $summary = [];
        
        // Documents uploaded by user
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM legislative_documents
            WHERE uploaded_by = :user_id
            AND deleted_at IS NULL
        ");
        $stmt->execute([':user_id' => $userId]);
        $summary['documents_uploaded'] = $stmt->fetchColumn();
        
        // Recent activities
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM activity_logs
            WHERE user_id = :user_id
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute([':user_id' => $userId]);
        $summary['activities_week'] = $stmt->fetchColumn();
        
        // Last login
        $stmt = $this->db->prepare("
            SELECT created_at
            FROM activity_logs
            WHERE user_id = :user_id
            AND action = 'LOGIN'
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        $lastLogin = $stmt->fetchColumn();
        $summary['last_login'] = $lastLogin ? date('M d, Y h:i A', strtotime($lastLogin)) : 'Never';
        
        return $summary;
    }
    
    /**
     * Get quick stats for comparison
     */
    public function getComparisonStats() {
        $stats = [];
        
        // Documents this month vs last month
        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as this_month,
                SUM(CASE WHEN MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN 1 ELSE 0 END) as last_month
            FROM legislative_documents
            WHERE deleted_at IS NULL
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['documents_comparison'] = $result;
        
        // Active users this week vs last week
        $stmt = $this->db->query("
            SELECT 
                COUNT(DISTINCT CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) THEN user_id END) as this_week,
                COUNT(DISTINCT CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 WEEK), 1) THEN user_id END) as last_week
            FROM activity_logs
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 2 WEEK)
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['users_comparison'] = $result;
        
        return $stats;
    }
    
    /**
     * Get pending tasks for current user
     */
    public function getPendingTasks($userId) {
        $tasks = [];
        
        // Pending approvals (for officers and admins)
        $stmt = $this->db->prepare("
            SELECT 
                ld.id,
                ld.title,
                ld.reference_number,
                ld.created_at,
                u.full_name as uploader
            FROM legislative_documents ld
            LEFT JOIN users u ON ld.uploaded_by = u.id
            WHERE ld.status = 'pending'
            AND ld.deleted_at IS NULL
            ORDER BY ld.created_at ASC
            LIMIT 5
        ");
        $stmt->execute();
        $tasks['pending_approvals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // User's draft documents
        $stmt = $this->db->prepare("
            SELECT 
                id,
                title,
                reference_number,
                created_at
            FROM legislative_documents
            WHERE uploaded_by = :user_id
            AND status = 'draft'
            AND deleted_at IS NULL
            ORDER BY updated_at DESC
            LIMIT 5
        ");
        $stmt->execute([':user_id' => $userId]);
        $tasks['my_drafts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $tasks;
    }
    
    /**
     * Get notifications count
     */
    public function getNotificationsCount($userId) {
        // This could be expanded with a proper notifications table
        // For now, return count of pending approvals
        $stmt = $this->db->query("
            SELECT COUNT(*) as count
            FROM legislative_documents
            WHERE status = 'pending'
            AND deleted_at IS NULL
        ");
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Calculate percentage change
     */
    public function calculatePercentageChange($current, $previous) {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }
    
    /**
     * Get storage usage by type
     */
    public function getStorageByType() {
        $stmt = $this->db->query("
            SELECT 
                document_type,
                COUNT(*) as count,
                SUM(file_size) as total_size
            FROM legislative_documents
            WHERE deleted_at IS NULL
            GROUP BY document_type
            ORDER BY total_size DESC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get top contributors
     */
    public function getTopContributors($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT 
                u.id,
                u.full_name,
                u.username,
                u.role,
                COUNT(ld.id) as document_count,
                SUM(ld.file_size) as total_size
            FROM users u
            LEFT JOIN legislative_documents ld ON u.id = ld.uploaded_by AND ld.deleted_at IS NULL
            WHERE u.status = 'active'
            GROUP BY u.id
            HAVING document_count > 0
            ORDER BY document_count DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
