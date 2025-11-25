<?php

class Logger {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Log activity
     */
    public function log($action, $documentId = null, $description = '') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (
                    user_id, action, document_id, description,
                    ip_address, user_agent, created_at
                ) VALUES (
                    :user_id, :action, :document_id, :description,
                    :ip_address, :user_agent, NOW()
                )
            ");
            
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'] ?? null,
                ':action' => $action,
                ':document_id' => $documentId,
                ':description' => $description,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Logger error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log document access
     */
    public function logAccess($documentId, $userId, $accessType = 'view') {
        try {
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
            
            return true;
        } catch (Exception $e) {
            error_log("Access logger error: " . $e->getMessage());
            return false;
        }
    }
}
