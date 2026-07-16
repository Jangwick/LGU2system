<?php

class ApiAuthMiddleware {
    private $db;
    private $rateLimits = [];
    
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
     * Check rate limiting (max 100 requests per minute per API key)
     */
    private function checkRateLimit($apiKeyId) {
        $cacheKey = "rate_limit_{$apiKeyId}";
        $currentMinute = floor(time() / 60);
        
        // Initialize or get current count
        if (!isset($this->rateLimits[$cacheKey])) {
            $this->rateLimits[$cacheKey] = [
                'minute' => $currentMinute,
                'count' => 0
            ];
        }
        
        $rateData = &$this->rateLimits[$cacheKey];
        
        // Reset if new minute
        if ($rateData['minute'] !== $currentMinute) {
            $rateData['minute'] = $currentMinute;
            $rateData['count'] = 0;
        }
        
        // Check limit (100 requests per minute)
        if ($rateData['count'] >= 100) {
            return false;
        }
        
        // Increment count
        $rateData['count']++;
        
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
