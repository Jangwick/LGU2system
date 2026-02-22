<?php

class ApiAuthMiddleware {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Validate API key from request headers
     */
    public function validateApiKey() {
        $apiKey = $this->getApiKeyFromRequest();
        
        if (!$apiKey) {
            $this->sendUnauthorizedResponse('API key is required');
            return false;
        }
        
        // Validate API key in database
        $stmt = $this->db->prepare("
            SELECT * FROM api_keys 
            WHERE api_key = :api_key 
            AND status = 'active'
            AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([':api_key' => $apiKey]);
        $keyData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$keyData) {
            $this->sendUnauthorizedResponse('Invalid or expired API key');
            return false;
        }
        
        // Check rate limiting
        if (!$this->checkRateLimit($keyData['id'])) {
            $this->sendTooManyRequestsResponse();
            return false;
        }
        
        // Update last used timestamp
        $this->updateLastUsed($keyData['id']);
        
        // Store API key data for use in controllers
        $_SERVER['API_KEY_DATA'] = $keyData;
        
        return true;
    }
    
    /**
     * Get API key from request headers
     */
    private function getApiKeyFromRequest() {
        // Check Authorization header
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            // Format: "Bearer <api_key>" or just "<api_key>"
            $auth = $headers['Authorization'];
            if (strpos($auth, 'Bearer ') === 0) {
                return substr($auth, 7);
            }
            return $auth;
        }
        
        // Check X-API-Key header
        if (isset($headers['X-API-Key'])) {
            return $headers['X-API-Key'];
        }
        
        // Check query parameter (less secure, for GET requests only)
        if (isset($_GET['api_key'])) {
            return $_GET['api_key'];
        }
        
        return null;
    }
    
    /**
     * Check rate limiting (max 100 requests per minute per API key).
     *
     * Uses the api_request_log table so the counter persists across PHP
     * processes.  Each successful check inserts one row and returns true;
     * if the rolling-window count is already >= 100 it returns false without
     * inserting.  Rows older than 5 minutes are pruned on every call to
     * prevent unbounded table growth (the rate window is only 60 seconds,
     * so anything older is irrelevant).
     */
    private function checkRateLimit(int $apiKeyId): bool {
        // Purge stale rows (older than 5 minutes) to keep the table lean.
        $this->db->prepare("
            DELETE FROM api_request_log
            WHERE requested_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ")->execute();

        // Count requests in the last 60 seconds for this key.
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS cnt
            FROM api_request_log
            WHERE api_key_id  = :id
              AND requested_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
        ");
        $stmt->execute([':id' => $apiKeyId]);
        $count = (int) $stmt->fetchColumn();

        if ($count >= 100) {
            return false;
        }

        // Record this request.
        $this->db->prepare("
            INSERT INTO api_request_log (api_key_id, requested_at)
            VALUES (:id, NOW())
        ")->execute([':id' => $apiKeyId]);

        return true;
    }
    
    /**
     * Update last used timestamp
     */
    private function updateLastUsed($apiKeyId) {
        $stmt = $this->db->prepare("
            UPDATE api_keys 
            SET last_used_at = NOW() 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $apiKeyId]);
    }
    
    /**
     * Send 401 Unauthorized response
     */
    private function sendUnauthorizedResponse($message) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message,
            'code' => 'UNAUTHORIZED'
        ]);
        exit;
    }
    
    /**
     * Send 429 Too Many Requests response
     */
    private function sendTooManyRequestsResponse() {
        http_response_code(429);
        header('Content-Type: application/json');
        header('Retry-After: 60');
        echo json_encode([
            'success' => false,
            'error' => 'Rate limit exceeded. Maximum 100 requests per minute.',
            'code' => 'RATE_LIMIT_EXCEEDED',
            'retry_after' => 60
        ]);
        exit;
    }
    
    /**
     * Get current API key data
     */
    public function getApiKeyData() {
        return $_SERVER['API_KEY_DATA'] ?? null;
    }
    
    /**
     * Check if request has valid API key (without terminating)
     */
    public function hasValidApiKey() {
        $apiKey = $this->getApiKeyFromRequest();
        
        if (!$apiKey) {
            return false;
        }
        
        $stmt = $this->db->prepare("
            SELECT * FROM api_keys 
            WHERE api_key = :api_key 
            AND status = 'active'
            AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([':api_key' => $apiKey]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
}
