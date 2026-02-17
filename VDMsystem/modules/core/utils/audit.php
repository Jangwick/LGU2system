<?php
/**
 * VDMsystem - Audit Logger Utility
 * Shared logAudit function available across all modules
 */

if (!function_exists('logAudit')) {
    /**
     * Log an audit event to the audit_logs table
     * 
     * @param string $eventType  Type of event (e.g., 'login_success', 'vote_cast', 'session_start')
     * @param int|null $userId   ID of the user performing the action
     * @param string $module     Module name (e.g., 'voting', 'authentication')
     * @param string|null $entityType  Entity type (e.g., 'voting_sessions', 'votes')
     * @param int|null $entityId Entity ID being acted upon
     * @param string $action     Human-readable action description
     * @param array $details     Additional details as key-value pairs
     */
    function logAudit($eventType, $userId, $module, $entityType = null, $entityId = null, $action = '', $details = []) {
        try {
            $db = getDatabase();
            
            // Get previous hash for chain integrity
            $lastLog = dbFetchOne("SELECT current_hash FROM audit_logs ORDER BY id DESC LIMIT 1");
            $previousHash = $lastLog['current_hash'] ?? '';
            
            // Create current hash for chain
            $hashData = json_encode([
                'event_type' => $eventType,
                'user_id' => $userId,
                'action' => $action,
                'timestamp' => date('Y-m-d H:i:s'),
                'previous_hash' => $previousHash
            ]);
            $currentHash = hash('sha256', $hashData);
            
            // Insert audit log
            $stmt = $db->prepare(
                "INSERT INTO audit_logs (event_type, user_id, module, entity_type, entity_id, action, details, ip_address, user_agent, previous_hash, current_hash) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $eventType,
                $userId,
                $module,
                $entityType,
                $entityId,
                $action,
                json_encode($details),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $previousHash,
                $currentHash
            ]);
        } catch (Exception $e) {
            error_log('Audit log error: ' . $e->getMessage());
        }
    }
}
