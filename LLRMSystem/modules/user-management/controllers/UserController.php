<?php
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/middleware/PermissionMiddleware.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

class UserController {
    private $db;
    private $permissions;
    private $logger;
    
    public function __construct() {
        $this->db = getDatabase();
        $this->permissions = new PermissionMiddleware($this->db);
        $this->logger = new Logger($this->db);
        
        // Require administrator access
        $this->permissions->requireLogin();
        $this->permissions->requirePermission('user.manage');
    }
    
    /**
     * Get all users with filters and pagination
     */
    public function index() {
        $filters = [
            'role' => $_GET['role'] ?? null,
            'status' => $_GET['status'] ?? null,
            'department' => $_GET['department'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Build query
        $query = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        if ($filters['role']) {
            $query .= " AND role = :role";
            $params[':role'] = $filters['role'];
        }
        
        if ($filters['status']) {
            $query .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if ($filters['department']) {
            $query .= " AND department LIKE :department";
            $params[':department'] = '%' . $filters['department'] . '%';
        }
        
        if ($filters['search']) {
            $query .= " AND (name LIKE :search1 OR email LIKE :search2 OR username LIKE :search3)";
            $searchValue = '%' . $filters['search'] . '%';
            $params[':search1'] = $searchValue;
            $params[':search2'] = $searchValue;
            $params[':search3'] = $searchValue;
        }
        
        // Count total
        $countStmt = $this->db->prepare(str_replace("SELECT *", "SELECT COUNT(*)", $query));
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Get paginated results
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get filter options
        $departments = $this->getDepartments();
        
        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage),
            'filters' => $filters,
            'departments' => $departments
        ];
    }
    
    /**
     * Get user by ID
     */
    public function show($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new user
     */
    public function create($data) {
        // Validate required fields
        $required = ['name', 'email', 'password', 'role'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'error' => ucfirst($field) . ' is required'];
            }
        }
        
        // Check if email already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $data['email']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Email already exists'];
        }
        
        // Generate username from email if not provided
        $username = $data['username'] ?? explode('@', $data['email'])[0];
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, username, full_name, password, role, department, status, created_at)
            VALUES (:name, :email, :username, :full_name, :password, :role, :department, :status, NOW())
        ");
        
        $result = $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':username' => $username,
            ':full_name' => $data['full_name'] ?? $data['name'],
            ':password' => $hashedPassword,
            ':role' => $data['role'],
            ':department' => $data['department'] ?? null,
            ':status' => $data['status'] ?? 'active'
        ]);
        
        if ($result) {
            $newUserId = $this->db->lastInsertId();
            
            // Log user creation activity
            $this->logger->logActivity(Logger::ACTION_USER_CREATE, 'users', $newUserId, 
                "Created new user: {$data['name']} ({$data['email']}) with role: {$data['role']}", [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'department' => $data['department'] ?? null,
                'status' => $data['status'] ?? 'active'
            ]);
            
            return ['success' => true, 'id' => $newUserId];
        }
        
        return ['success' => false, 'error' => 'Failed to create user'];
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        // Check if user exists
        $user = $this->show($id);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        // Store old values for audit trail
        $oldValues = [
            'name' => $user['name'],
            'email' => $user['email'],
            'full_name' => $user['full_name'] ?? '',
            'role' => $user['role'],
            'department' => $user['department'] ?? '',
            'status' => $user['status']
        ];
        
        // Build update query
        $fields = [];
        $params = [':id' => $id];
        $newValues = [];
        
        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        
        if (isset($data['email'])) {
            // Check if new email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
            $stmt->execute([':email' => $data['email'], ':id' => $id]);
            if ($stmt->fetch()) {
                return ['success' => false, 'error' => 'Email already exists'];
            }
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        
        if (isset($data['full_name'])) {
            $fields[] = "full_name = :full_name";
            $params[':full_name'] = $data['full_name'];
        }
        
        if (isset($data['role'])) {
            $fields[] = "role = :role";
            $params[':role'] = $data['role'];
        }
        
        if (isset($data['department'])) {
            $fields[] = "department = :department";
            $params[':department'] = $data['department'];
        }
        
        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $params[':status'] = $data['status'];
            $newValues['status'] = $data['status'];
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $newValues['password'] = '[CHANGED]';
            $oldValues['password'] = '[HIDDEN]';
        }
        
        // Track changes for logging
        foreach (['name', 'email', 'full_name', 'role', 'department'] as $field) {
            if (isset($data[$field])) {
                $newValues[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return ['success' => false, 'error' => 'No fields to update'];
        }
        
        $fields[] = "updated_at = NOW()";
        
        $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        if ($stmt->execute($params)) {
            // Determine if this is a role change (special logging)
            $action = Logger::ACTION_USER_UPDATE;
            $description = "Updated user: {$user['name']} ({$user['email']})";
            
            if (isset($data['role']) && $data['role'] !== $user['role']) {
                $action = Logger::ACTION_ROLE_CHANGE;
                $description = "Changed role for {$user['name']} from {$user['role']} to {$data['role']}";
            }
            
            if (isset($data['status']) && $data['status'] !== $user['status']) {
                if ($data['status'] === 'active') {
                    $action = Logger::ACTION_USER_ACTIVATE;
                    $description = "Activated user account: {$user['name']}";
                } else if ($data['status'] === 'inactive') {
                    $action = Logger::ACTION_USER_DEACTIVATE;
                    $description = "Deactivated user account: {$user['name']}";
                }
            }
            
            // Log the update with old and new values
            $this->logger->logActivity($action, 'users', $id, $description, $newValues, $oldValues);
            
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'Failed to update user'];
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        // Prevent deleting yourself
        if ($id == $_SESSION['user_id']) {
            return ['success' => false, 'error' => 'You cannot delete your own account'];
        }
        
        // Get user info before deletion for logging
        $user = $this->show($id);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        
        if ($stmt->execute([':id' => $id])) {
            // Log the deletion with user details
            $this->logger->logActivity(Logger::ACTION_USER_DELETE, 'users', $id, 
                "Deleted user: {$user['name']} ({$user['email']})", null, [
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'department' => $user['department'] ?? ''
            ]);
            
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'Failed to delete user'];
    }
    
    /**
     * Get unique departments
     */
    private function getDepartments() {
        $stmt = $this->db->query("
            SELECT DISTINCT department 
            FROM users 
            WHERE department IS NOT NULL 
            ORDER BY department
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total users
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        $stats['total_users'] = $stmt->fetchColumn();
        
        // Active users
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
        $stats['active_users'] = $stmt->fetchColumn();
        
        // Users by role
        $stmt = $this->db->query("
            SELECT role, COUNT(*) as count 
            FROM users 
            GROUP BY role 
            ORDER BY count DESC
        ");
        $stats['users_by_role'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Recent users (last 7 days)
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM users 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stats['recent_users'] = $stmt->fetchColumn();
        
        return $stats;
    }
}
