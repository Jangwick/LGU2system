<?php
/**
 * Integration API Authentication Service
 * Validates API keys from external integration modules
 */

require_once __DIR__ . '/../../core/config/database.php';

class IntegrationAuth {
    private $db;
    
    public function __construct() {
        $this->db = getDatabase();
    }
    
    /**
     * Validate API key and return module info
     */
    /**
     * Retrieve a request header from multiple possible sources
     */
    private function getHeader($headerName) {
        $headerKey = strtolower($headerName);
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        }

        foreach ($headers as $name => $value) {
            if (strtolower($name) === $headerKey) {
                return $value;
            }
        }

        // Fallback to $_SERVER (underscore, uppercase with HTTP_ prefix)
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $headerName));
        if ($headerName === 'Authorization') {
            return $_SERVER[$serverKey] ?? ($_SERVER['REDIRECT_' . $serverKey] ?? '');
        }
        return $_SERVER[$serverKey] ?? '';
    }

    public function validateApiKey($apiKey = null) {
        try {
            // Extract from X-API-Key header
            if (empty($apiKey)) {
                $apiKey = $this->getHeader('X-API-Key') ?: ($_SERVER['HTTP_X_API_KEY'] ?? '');
            }
            // Extract from Authorization: Bearer header
            if (empty($apiKey)) {
                $authHeader = $this->getHeader('Authorization');
                if (stripos($authHeader, 'Bearer ') === 0) {
                    $apiKey = trim(substr($authHeader, 7));
                }
            }
            if (empty($apiKey)) {
                return null;
            }

            $sql = "SELECT * FROM integration_api_keys 
                    WHERE api_key = :api_key AND is_active = 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':api_key' => $apiKey]);
            $module = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($module) {
                // Update last used timestamp
                $this->updateLastUsed($module['id']);
                
                // Decode permissions
                if ($module['permissions']) {
                    $module['permissions'] = json_decode($module['permissions'], true);
                }
                
                return $module;
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("IntegrationAuth validateApiKey error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Validate API key with secret (for sensitive operations)
     */
    public function validateApiKeyWithSecret($apiKey, $apiSecret) {
        try {
            $sql = "SELECT * FROM integration_api_keys 
                    WHERE api_key = :api_key 
                    AND api_secret = SHA2(:api_secret, 256) 
                    AND is_active = 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':api_key' => $apiKey,
                ':api_secret' => $apiSecret
            ]);
            
            $module = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($module) {
                $this->updateLastUsed($module['id']);
                if ($module['permissions']) {
                    $module['permissions'] = json_decode($module['permissions'], true);
                }
                return $module;
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("IntegrationAuth validateApiKeyWithSecret error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if module has specific permission
     */
    public function hasPermission($module, $permission) {
        if (!$module || !isset($module['permissions'])) {
            return false;
        }
        
        return isset($module['permissions'][$permission]) && $module['permissions'][$permission] === true;
    }
    
    /**
     * Update last used timestamp
     */
    private function updateLastUsed($moduleId) {
        try {
            $sql = "UPDATE integration_api_keys SET last_used_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $moduleId]);
        } catch (PDOException $e) {
            error_log("IntegrationAuth updateLastUsed error: " . $e->getMessage());
        }
    }
    
    /**
     * Generate new API key for a module
     */
    public function generateApiKey($moduleName, $permissions = [], $createdBy = null) {
        try {
            $apiKey = $this->generateUniqueKey($moduleName);
            $apiSecret = bin2hex(random_bytes(32));
            
            $sql = "INSERT INTO integration_api_keys (module_name, api_key, api_secret, permissions, created_by) 
                    VALUES (:module_name, :api_key, SHA2(:api_secret, 256), :permissions, :created_by)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':module_name' => $moduleName,
                ':api_key' => $apiKey,
                ':api_secret' => $apiSecret,
                ':permissions' => json_encode($permissions),
                ':created_by' => $createdBy
            ]);
            
            return [
                'api_key' => $apiKey,
                'api_secret' => $apiSecret, // Only returned once during creation
                'module_name' => $moduleName
            ];
        } catch (PDOException $e) {
            error_log("IntegrationAuth generateApiKey error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate unique API key
     */
    private function generateUniqueKey($prefix) {
        $prefix = strtolower(substr(preg_replace('/[^a-zA-Z]/', '', $prefix), 0, 3));
        return $prefix . '_' . bin2hex(random_bytes(16));
    }
    
    /**
     * Revoke API key
     */
    public function revokeApiKey($apiKey) {
        try {
            $sql = "UPDATE integration_api_keys SET is_active = 0 WHERE api_key = :api_key";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':api_key' => $apiKey]);
        } catch (PDOException $e) {
            error_log("IntegrationAuth revokeApiKey error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all registered modules
     */
    public function getAllModules() {
        try {
            $sql = "SELECT id, module_name, api_key, permissions, is_active, last_used_at, created_at 
                    FROM integration_api_keys ORDER BY module_name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($modules as &$module) {
                if ($module['permissions']) {
                    $module['permissions'] = json_decode($module['permissions'], true);
                }
            }
            
            return $modules;
        } catch (PDOException $e) {
            error_log("IntegrationAuth getAllModules error: " . $e->getMessage());
            return [];
        }
    }
}
