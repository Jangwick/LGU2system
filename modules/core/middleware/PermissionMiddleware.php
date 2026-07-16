<?php

class PermissionMiddleware {
    private $db;
    
    // Role hierarchy (higher number = more permissions)
    private $roleHierarchy = [
        'viewer' => 1,
        'staff' => 2,
        'officer' => 3,
        'administrator' => 4
    ];
    
    // Permission definitions
    private $permissions = [
        'document.view' => ['viewer', 'staff', 'officer', 'administrator'],
        'document.create' => ['staff', 'officer', 'administrator'],
        'document.edit' => ['staff', 'officer', 'administrator'],
        'document.delete' => ['officer', 'administrator'],
        'document.restore' => ['administrator'],
        'document.download' => ['viewer', 'staff', 'officer', 'administrator'],
        'tag.create' => ['staff', 'officer', 'administrator'],
        'tag.edit' => ['officer', 'administrator'],
        'tag.delete' => ['administrator'],
        'version.create' => ['staff', 'officer', 'administrator'],
        'version.revert' => ['officer', 'administrator'],
        'link.create' => ['staff', 'officer', 'administrator'],
        'link.delete' => ['officer', 'administrator'],
        'audit.view' => ['administrator'],
        'user.manage' => ['administrator'],
        'api.manage' => ['administrator']
    ];
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($permission, $userId = null) {
        if ($userId === null) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        
        if (!$userId) {
            return false;
        }
        
        $userRole = $this->getUserRole($userId);
        
        if (!$userRole) {
            return false;
        }
        
        // Check if permission exists and role is allowed
        if (!isset($this->permissions[$permission])) {
            return false;
        }
        
        return in_array($userRole, $this->permissions[$permission]);
    }
    
    /**
     * Require permission or send 403 response
     */
    public function requirePermission($permission, $userId = null) {
        if (!$this->hasPermission($permission, $userId)) {
            $this->sendForbiddenResponse();
        }
    }
    
    /**
     * Check if user has minimum role level
     */
    public function hasMinimumRole($requiredRole, $userId = null) {
        if ($userId === null) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        
        if (!$userId) {
            return false;
        }
        
        $userRole = $this->getUserRole($userId);
        
        if (!isset($this->roleHierarchy[$userRole]) || !isset($this->roleHierarchy[$requiredRole])) {
            return false;
        }
        
        return $this->roleHierarchy[$userRole] >= $this->roleHierarchy[$requiredRole];
    }
    
    /**
     * Require minimum role or send 403 response
     */
    public function requireMinimumRole($requiredRole, $userId = null) {
        if (!$this->hasMinimumRole($requiredRole, $userId)) {
            $this->sendForbiddenResponse();
        }
    }
    
    /**
     * Check if user can access specific document
     */
    public function canAccessDocument($documentId, $action = 'view', $userId = null) {
        if ($userId === null) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        
        if (!$userId) {
            return false;
        }
        
        // Check basic permission first
        if (!$this->hasPermission("document.{$action}", $userId)) {
            return false;
        }
        
        // Get document info
        $stmt = $this->db->prepare("
            SELECT uploaded_by, status 
            FROM legislative_documents 
            WHERE id = :id AND deleted_at IS NULL
        ");
        $stmt->execute([':id' => $documentId]);
        $document = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$document) {
            return false;
        }
        
        $userRole = $this->getUserRole($userId);
        
        // Administrators can access everything
        if ($userRole === 'administrator') {
            return true;
        }
        
        // Officers can access everything except deleted
        if ($userRole === 'officer') {
            return true;
        }
        
        // Staff can edit their own documents or view any approved documents
        if ($userRole === 'staff') {
            if ($action === 'view') {
                return in_array($document['status'], ['approved', 'archived', 'pending']);
            }
            if (in_array($action, ['edit', 'delete'])) {
                return $document['uploaded_by'] == $userId;
            }
        }
        
        // Viewers can only view approved/archived documents
        if ($userRole === 'viewer') {
            return $action === 'view' && in_array($document['status'], ['approved', 'archived']);
        }
        
        return false;
    }
    
    /**
     * Require document access or send 403 response
     */
    public function requireDocumentAccess($documentId, $action = 'view', $userId = null) {
        if (!$this->canAccessDocument($documentId, $action, $userId)) {
            $this->sendForbiddenResponse('You do not have permission to ' . $action . ' this document');
        }
    }
    
    /**
     * Get user role
     */
    private function getUserRole($userId) {
        // Check cache first
        if (isset($_SESSION['user_role']) && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
            return $this->normalizeRole($_SESSION['user_role']);
        }
        
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id AND status = 'active'");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user ? $this->normalizeRole($user['role']) : null;
    }
    
    /**
     * Normalize role name to handle different formats
     */
    private function normalizeRole($role) {
        if (!$role) {
            return null;
        }
        
        // Convert to lowercase for comparison
        $normalized = strtolower(trim($role));
        
        // Map common role variations to standard format
        $roleMap = [
            'admin' => 'administrator',
            'administrator' => 'administrator',
            'officer' => 'officer',
            'staff' => 'staff',
            'viewer' => 'viewer',
            'user' => 'viewer'
        ];
        
        return $roleMap[$normalized] ?? $normalized;
    }
    
    /**
     * Send 403 Forbidden response
     */
    private function sendForbiddenResponse($message = 'Access denied. Insufficient permissions.') {
        // Check if this is an API request or web request
        if ($this->isApiRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $message,
                'code' => 'FORBIDDEN'
            ]);
        } else {
            http_response_code(403);
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>Access Denied</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                    h1 { color: #dc3545; }
                    p { color: #6c757d; }
                    a { color: #007bff; text-decoration: none; }
                </style>
            </head>
            <body>
                <h1>403 - Access Denied</h1>
                <p>' . htmlspecialchars($message) . '</p>
                <a href="' . DASHBOARD_INDEX_URL . '">Go to Dashboard</a>
            </body>
            </html>';
        }
        exit;
    }
    
    /**
     * Check if request is API request
     */
    private function isApiRequest() {
        return strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false 
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
    
    /**
     * Get all permissions for a role
     */
    public function getRolePermissions($role) {
        $rolePermissions = [];
        
        foreach ($this->permissions as $permission => $roles) {
            if (in_array($role, $roles)) {
                $rolePermissions[] = $permission;
            }
        }
        
        return $rolePermissions;
    }
    
    /**
     * Check if user is logged in
     */
    public function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isApiRequest()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Authentication required',
                    'code' => 'UNAUTHORIZED'
                ]);
            } else {
                require_once __DIR__ . '/../config/config.php';
                redirectToLogin();
            }
            exit;
        }
    }
}
