<?php

class Logger {
    private $db;
    
    // Activity types constants
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_LOGIN_FAILED = 'login_failed';
    const ACTION_DOCUMENT_VIEW = 'document_view';
    const ACTION_DOCUMENT_CREATE = 'document_create';
    const ACTION_DOCUMENT_UPLOAD = 'document_upload';
    const ACTION_DOCUMENT_UPDATE = 'document_update';
    const ACTION_DOCUMENT_DELETE = 'document_delete';
    const ACTION_DOCUMENT_DOWNLOAD = 'document_download';
    const ACTION_DOCUMENT_EXPORT = 'document_export';
    const ACTION_USER_CREATE = 'user_create';
    const ACTION_USER_UPDATE = 'user_update';
    const ACTION_USER_DELETE = 'user_delete';
    const ACTION_USER_ACTIVATE = 'user_activate';
    const ACTION_USER_DEACTIVATE = 'user_deactivate';
    const ACTION_ROLE_CHANGE = 'role_change';
    const ACTION_PASSWORD_CHANGE = 'password_change';
    const ACTION_PASSWORD_RESET = 'password_reset';
    const ACTION_PROFILE_UPDATE = 'profile_update';
    const ACTION_PROFILE_PICTURE_UPDATE = 'profile_picture_update';
    const ACTION_SETTINGS_UPDATE = 'settings_update';
    const ACTION_SEARCH = 'search';
    const ACTION_REPORT_GENERATE = 'report_generate';
    const ACTION_REPORT_EXPORT = 'report_export';
    const ACTION_TAG_CREATE = 'tag_create';
    const ACTION_TAG_ASSIGN = 'tag_assign';
    const ACTION_TAG_REMOVE = 'tag_remove';
    const ACTION_VERSION_REVERT = 'version_revert';
    const ACTION_BULK_DELETE = 'bulk_delete';
    const ACTION_PERMISSION_CHANGE = 'permission_change';
    
    // Severity levels
    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_ERROR = 'error';
    const SEVERITY_CRITICAL = 'critical';
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Log activity with comprehensive details
     */
    public function log($action, $documentId = null, $description = '', $additionalData = []) {
        try {
            // Get user info
            $userId = $_SESSION['user_id'] ?? null;
            $username = $_SESSION['username'] ?? 'Guest';
            
            // Build detailed description
            $fullDescription = $this->buildDescription($action, $description, $additionalData);
            
            // Get table name from action
            $tableName = $this->getTableNameFromAction($action);
            
            $payload = [
                'user_id' => $userId,
                'action' => $action,
                'table_name' => $tableName,
                'record_id' => $documentId,
                'description' => $fullDescription,
                'ip_address' => $this->getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ];
            
            if (LogQueue::enabled()) {
                return LogQueue::push('activity_logs', $payload);
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (
                    user_id, action, table_name, record_id, description,
                    ip_address, user_agent, created_at
                ) VALUES (
                    :user_id, :action, :table_name, :record_id, :description,
                    :ip_address, :user_agent, NOW()
                )
            ");
            
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':table_name' => $tableName,
                ':record_id' => $documentId,
                ':description' => $fullDescription,
                ':ip_address' => $this->getClientIP(),
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Logger error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log user activity (login, logout, etc.)
     */
    public function logUserActivity($userId, $action, $description = '', $additionalData = []) {
        try {
            $payload = [
                'user_id' => $userId,
                'action' => $action,
                'table_name' => 'users',
                'record_id' => null,
                'description' => $description,
                'ip_address' => $this->getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ];
            
            if (LogQueue::enabled()) {
                return LogQueue::push('activity_logs', $payload);
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (
                    user_id, action, table_name, description,
                    ip_address, user_agent, created_at
                ) VALUES (
                    :user_id, :action, 'users', :description,
                    :ip_address, :user_agent, NOW()
                )
            ");
            
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':description' => $description,
                ':ip_address' => $this->getClientIP(),
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Logger error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log comprehensive activity with old/new values tracking
     */
    public function logActivity($action, $tableName, $recordId, $description, $newValues = null, $oldValues = null, $severity = self::SEVERITY_INFO) {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            
            // Build description with change details if provided
            $fullDescription = $description;
            if ($newValues || $oldValues) {
                $changes = [];
                if ($oldValues) $changes[] = "Previous: " . json_encode($oldValues);
                if ($newValues) $changes[] = "New: " . json_encode($newValues);
                if (!empty($changes)) {
                    $fullDescription .= " | Changes: " . implode(' | ', $changes);
                }
            }
            
            $payload = [
                'user_id' => $userId,
                'action' => $action,
                'table_name' => $tableName,
                'record_id' => $recordId,
                'description' => $fullDescription,
                'ip_address' => $this->getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ];
            
            if (LogQueue::enabled()) {
                return LogQueue::push('activity_logs', $payload);
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (
                    user_id, action, table_name, record_id, description,
                    ip_address, user_agent, created_at
                ) VALUES (
                    :user_id, :action, :table_name, :record_id, :description,
                    :ip_address, :user_agent, NOW()
                )
            ");
            
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':table_name' => $tableName,
                ':record_id' => $recordId,
                ':description' => $fullDescription,
                ':ip_address' => $this->getClientIP(),
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Logger activity error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log session activity (login/logout)
     */
    public function logSession($userId, $action, $additionalData = [], $severity = self::SEVERITY_INFO) {
        try {
            $description = $this->buildDescription($action, '', $additionalData);
            
            // Add additional data to description if present
            if (!empty($additionalData)) {
                $description .= " | Details: " . json_encode($additionalData);
            }
            
            $payload = [
                'user_id' => $userId,
                'action' => $action,
                'table_name' => 'sessions',
                'record_id' => null,
                'description' => $description,
                'ip_address' => $this->getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ];
            
            if (LogQueue::enabled()) {
                return LogQueue::push('activity_logs', $payload);
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (
                    user_id, action, table_name, description,
                    ip_address, user_agent, created_at
                ) VALUES (
                    :user_id, :action, 'sessions', :description,
                    :ip_address, :user_agent, NOW()
                )
            ");
            
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':description' => $description,
                ':ip_address' => $this->getClientIP(),
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Logger session error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log document activity with detailed tracking
     */
    public function logDocumentActivity($documentId, $action, $documentTitle = '', $additionalData = [], $oldValues = null) {
        try {
            $description = $this->buildDocumentDescription($action, $documentTitle, $additionalData);
            
            return $this->logActivity(
                $action, 
                'documents', 
                $documentId, 
                $description, 
                $additionalData,
                $oldValues
            );
        } catch (Exception $e) {
            error_log("Document activity logging error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log document access
     */
    public function logAccess($documentId, $userId, $accessType = 'view') {
        try {
            $payload = [
                'document_id' => $documentId,
                'user_id' => $userId,
                'access_type' => $accessType
            ];
            
            if (LogQueue::enabled()) {
                LogQueue::push('document_access_logs', $payload);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO document_access_logs (
                        document_id, user_id, access_type, accessed_at
                    ) VALUES (
                        :document_id, :user_id, :access_type, NOW()
                    )
                ");
                
                $stmt->execute([
                    ':document_id' => $documentId,
                    ':user_id' => $userId,
                    ':access_type' => $accessType
                ]);
            }
            
            // Also log to activity_logs for comprehensive tracking
            $this->log(strtoupper('DOCUMENT_' . $accessType), $documentId, "Document accessed: $accessType");
            
            return true;
        } catch (Exception $e) {
            error_log("Access logger error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log search activity
     */
    public function logSearch($searchQuery, $resultsCount = 0, $filters = []) {
        $filterStr = !empty($filters) ? ' with filters: ' . json_encode($filters) : '';
        $description = "Searched for: '$searchQuery'$filterStr - Found $resultsCount results";
        return $this->log(self::ACTION_SEARCH, null, $description);
    }
    
    /**
     * Log report activity
     */
    public function logReport($reportType, $action, $parameters = []) {
        $description = "Generated $reportType report";
        if (!empty($parameters)) {
            $description .= " with parameters: " . json_encode($parameters);
        }
        return $this->log($action, null, $description);
    }
    
    /**
     * Build detailed description based on action
     */
    private function buildDescription($action, $description, $additionalData) {
        if (!empty($description)) {
            return $description;
        }
        
        $username = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
        
        switch ($action) {
            case self::ACTION_LOGIN:
                return "$username logged into the system";
            case self::ACTION_LOGOUT:
                return "$username logged out of the system";
            case self::ACTION_LOGIN_FAILED:
                return "Failed login attempt" . (isset($additionalData['email']) ? " for: " . $additionalData['email'] : '');
            case self::ACTION_PASSWORD_CHANGE:
                return "$username changed their password";
            case self::ACTION_PROFILE_UPDATE:
                return "$username updated their profile";
            case self::ACTION_SETTINGS_UPDATE:
                return "$username updated their settings";
            default:
                return $description ?: "$username performed action: $action";
        }
    }
    
    /**
     * Build document-specific description
     */
    private function buildDocumentDescription($action, $documentTitle, $additionalData) {
        $username = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
        $title = $documentTitle ?: 'Unknown Document';
        
        switch ($action) {
            case self::ACTION_DOCUMENT_UPLOAD:
                return "$username uploaded document: $title";
            case self::ACTION_DOCUMENT_UPDATE:
                return "$username updated document: $title";
            case self::ACTION_DOCUMENT_DELETE:
                return "$username deleted document: $title";
            case self::ACTION_DOCUMENT_VIEW:
                return "$username viewed document: $title";
            case self::ACTION_DOCUMENT_DOWNLOAD:
                return "$username downloaded document: $title";
            case self::ACTION_DOCUMENT_EXPORT:
                return "$username exported document: $title";
            case self::ACTION_VERSION_REVERT:
                $version = $additionalData['version'] ?? 'previous';
                return "$username reverted document '$title' to version $version";
            default:
                return "$username performed $action on document: $title";
        }
    }
    
    /**
     * Get table name from action
     */
    private function getTableNameFromAction($action) {
        $actionLower = strtolower($action);
        if (strpos($actionLower, 'document') !== false) {
            return 'documents';
        } elseif (strpos($actionLower, 'user') !== false || strpos($actionLower, 'password') !== false || 
                  strpos($actionLower, 'profile') !== false || strpos($actionLower, 'login') !== false ||
                  strpos($actionLower, 'logout') !== false || strpos($actionLower, 'settings') !== false ||
                  strpos($actionLower, 'role') !== false) {
            return 'users';
        } elseif (strpos($actionLower, 'tag') !== false) {
            return 'tags';
        } elseif (strpos($actionLower, 'report') !== false) {
            return 'reports';
        }
        return 'system';
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                   'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Get recent activities for a user
     */
    public function getUserActivities($userId, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM activity_logs 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT :limit
            ");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get user activities error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get system-wide recent activities
     */
    public function getRecentActivities($limit = 50) {
        try {
            $stmt = $this->db->prepare("
                SELECT al.*, u.username, u.full_name, u.email
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC 
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get recent activities error: " . $e->getMessage());
            return [];
        }
    }
}
