<?php
/**
 * VDMsystem - User Management Controller
 * Handles CRUD operations for user accounts
 */
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';

class UserController {
    private $db;
    
    public function __construct() {
        $this->db = getDatabase();
    }
    
    /**
     * Get paginated users with filters
     */
    public function index() {
        $filters = [
            'role' => $_GET['role'] ?? null,
            'status' => $_GET['status'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        
        // Build query
        $query = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        if ($filters['role']) {
            $query .= " AND role = :role";
            $params[':role'] = $filters['role'];
        }
        
        if ($filters['status'] !== null && $filters['status'] !== '') {
            $query .= " AND is_active = :status";
            $params[':status'] = (int)$filters['status'];
        }
        
        if ($filters['search']) {
            $query .= " AND (full_name LIKE :search1 OR email LIKE :search2 OR username LIKE :search3 OR position LIKE :search4)";
            $searchVal = '%' . $filters['search'] . '%';
            $params[':search1'] = $searchVal;
            $params[':search2'] = $searchVal;
            $params[':search3'] = $searchVal;
            $params[':search4'] = $searchVal;
        }
        
        // Count total
        $countQuery = str_replace("SELECT *", "SELECT COUNT(*)", $query);
        $countStmt = $this->db->prepare($countQuery);
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
        
        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage),
            'filters' => $filters
        ];
    }
    
    /**
     * Get a single user by ID
     */
    public function show($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            unset($user['password']); // Never return password
        }
        
        return $user;
    }
    
    /**
     * Create a new user
     */
    public function create($data) {
        // Validate required fields
        $required = ['full_name', 'email', 'password', 'role'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'error' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
            }
        }
        
        // Check email uniqueness
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Email already exists'];
        }
        
        // Check username uniqueness if provided
        if (!empty($data['username'])) {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$data['username']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'error' => 'Username already exists'];
            }
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $insertData = [
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'bound_email' => $data['bound_email'] ?? null,
            'username' => $data['username'] ?? null,
            'password' => $hashedPassword,
            'role' => $data['role'],
            'position' => $data['position'] ?? null,
            'department' => $data['department'] ?? null,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'approval_status' => $data['approval_status'] ?? 'approved',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $userId = dbInsert('users', $insertData);
            
            // Log audit
            logAudit(
                'user_created',
                $_SESSION['user_id'] ?? null,
                'users',
                'users',
                $userId,
                'Created new user: ' . $data['full_name'],
                ['role' => $data['role'], 'email' => $data['email']]
            );
            
            return ['success' => true, 'message' => 'User created successfully', 'id' => $userId];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to create user: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update an existing user
     */
    public function update($id, $data) {
        // Get current user data for audit trail
        $current = $this->show($id);
        if (!$current) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        // Check email uniqueness
        if (!empty($data['email'])) {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$data['email'], $id]);
            if ($stmt->fetch()) {
                return ['success' => false, 'error' => 'Email already in use by another user'];
            }
        }
        
        // Check username uniqueness if provided
        if (!empty($data['username'])) {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$data['username'], $id]);
            if ($stmt->fetch()) {
                return ['success' => false, 'error' => 'Username already in use'];
            }
        }
        
        $updateData = [
            'full_name' => $data['full_name'] ?? $current['full_name'],
            'email' => $data['email'] ?? $current['email'],
            'username' => $data['username'] ?? $current['username'],
            'role' => $data['role'] ?? $current['role'],
            'position' => $data['position'] ?? $current['position'],
            'department' => $data['department'] ?? $current['department'],
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : $current['is_active'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Update password only if provided
        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        try {
            dbUpdate('users', $updateData, 'id = ?', [$id]);
            
            // Track changes for audit
            $changes = [];
            foreach (['full_name', 'email', 'role', 'is_active', 'position', 'department'] as $field) {
                if (isset($updateData[$field]) && $updateData[$field] != ($current[$field] ?? null)) {
                    $changes[$field] = ['from' => $current[$field] ?? null, 'to' => $updateData[$field]];
                }
            }
            if (!empty($data['password'])) {
                $changes['password'] = ['from' => '***', 'to' => '***'];
            }
            
            logAudit(
                'user_updated',
                $_SESSION['user_id'] ?? null,
                'users',
                'users',
                $id,
                'Updated user: ' . ($data['full_name'] ?? $current['full_name']),
                $changes
            );
            
            return ['success' => true, 'message' => 'User updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to update user: ' . $e->getMessage()];
        }
    }
    
    /**
     * Delete a user
     */
    public function delete($id) {
        // Prevent self-deletion
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            return ['success' => false, 'error' => 'You cannot delete your own account'];
        }
        
        $user = $this->show($id);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        try {
            dbDelete('users', 'id = ?', [$id]);
            
            logAudit(
                'user_deleted',
                $_SESSION['user_id'] ?? null,
                'users',
                'users',
                $id,
                'Deleted user: ' . $user['full_name'],
                ['email' => $user['email'], 'role' => $user['role']]
            );
            
            return ['success' => true, 'message' => 'User deleted successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to delete user: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get user statistics
     */
    public function getStatistics() {
        $stats = [];
        
        $stats['total_users'] = dbCount('users');
        $stats['active_users'] = dbCount('users', 'is_active = 1');
        $stats['inactive_users'] = $stats['total_users'] - $stats['active_users'];
        
        // Pending approval count
        $stats['pending_approval'] = dbCount('users', "approval_status = 'pending'");
        
        // Users by role
        $stmt = $this->db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role ORDER BY count DESC");
        $stats['by_role'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Recently added (last 7 days)
        $stats['recent_users'] = dbCount('users', 'created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
        
        // Admin count
        $stats['admin_count'] = dbCount('users', "role IN ('admin', 'administrator')");
        
        return $stats;
    }
    
    /**
     * Get users pending approval
     */
    public function getPendingApprovals() {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE approval_status = 'pending' ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
