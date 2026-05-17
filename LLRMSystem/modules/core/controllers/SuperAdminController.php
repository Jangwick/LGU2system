<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/PermissionMiddleware.php';
require_once __DIR__ . '/../utils/Logger.php';

class SuperAdminController {
    private $db;
    private $permissions;
    private $logger;
    
    public function __construct() {
        $this->db = getDatabase();
        $this->permissions = new PermissionMiddleware($this->db);
        $this->logger = new Logger($this->db);
        
        // Require Super Admin access
        $this->permissions->requireLogin();
        $this->permissions->requirePermission('admin.manage');
    }
    
    /**
     * Get all administrators with search, filter, pagination, and sorting
     */
    public function getAdministrators($params = []) {
        $search = $params['search'] ?? null;
        $roleFilter = $params['role'] ?? null;
        $statusFilter = $params['status'] ?? null;
        $departmentFilter = $params['department'] ?? null;
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortOrder = $params['sort_order'] ?? 'DESC';
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 10;

        // Check if last_login column exists
        $columns = $this->db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_ASSOC);
        $hasLastLogin = false;
        foreach ($columns as $col) {
            if ($col['Field'] === 'last_login') {
                $hasLastLogin = true;
                break;
            }
        }

        $selectColumns = "id, email, full_name, role, status, department, employee_id, created_at";
        if ($hasLastLogin) {
            $selectColumns .= ", last_login";
        }

        // Build query
        $whereConditions = ["role IN ('administrator', 'super_admin')"];
        $queryParams = [];

        if ($search) {
            $whereConditions[] = "(full_name LIKE ? OR email LIKE ? OR employee_id LIKE ?)";
            $queryParams[] = "%$search%";
            $queryParams[] = "%$search%";
            $queryParams[] = "%$search%";
        }

        if ($roleFilter && in_array($roleFilter, ['administrator', 'super_admin'])) {
            $whereConditions[] = "role = ?";
            $queryParams[] = $roleFilter;
        }

        if ($statusFilter && in_array($statusFilter, ['active', 'inactive'])) {
            $whereConditions[] = "status = ?";
            $queryParams[] = $statusFilter;
        }

        if ($departmentFilter) {
            $whereConditions[] = "department LIKE ?";
            $queryParams[] = "%$departmentFilter%";
        }

        $whereClause = implode(' AND ', $whereConditions);

        // Get total count
        $countQuery = "SELECT COUNT(*) FROM users WHERE $whereClause";
        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute($queryParams);
        $totalCount = $countStmt->fetchColumn();

        // Get paginated results
        $allowedSortColumns = ['id', 'full_name', 'email', 'role', 'status', 'department', 'created_at'];
        if ($hasLastLogin) {
            $allowedSortColumns[] = 'last_login';
        }

        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $offset = ($page - 1) * $perPage;
        $query = "SELECT $selectColumns FROM users WHERE $whereClause ORDER BY $sortBy $sortOrder LIMIT ? OFFSET ?";
        $queryParams[] = $perPage;
        $queryParams[] = $offset;

        $stmt = $this->db->prepare($query);
        $stmt->execute($queryParams);
        $administrators = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $administrators,
            'total' => $totalCount,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalCount / $perPage)
        ];
    }
    
    /**
     * Promote user to administrator
     */
    public function promoteToAdmin($userId) {
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        if ($user['role'] === 'super_admin') {
            return ['success' => false, 'error' => 'User is already Super Admin'];
        }
        
        $stmt = $this->db->prepare("UPDATE users SET role = 'administrator' WHERE id = ?");
        $result = $stmt->execute([$userId]);
        
        if ($result) {
            $this->logger->log($_SESSION['user_id'], 'user_promoted', $userId, "Promoted user to administrator role");
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'Failed to promote user'];
    }
    
    /**
     * Demote administrator to staff
     */
    public function demoteFromAdmin($userId) {
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        if ($user['role'] === 'super_admin') {
            return ['success' => false, 'error' => 'Cannot demote Super Admin'];
        }

        if ($user['role'] !== 'administrator') {
            return ['success' => false, 'error' => 'User is not an administrator'];
        }

        $stmt = $this->db->prepare("UPDATE users SET role = 'staff' WHERE id = ?");
        $result = $stmt->execute([$userId]);

        if ($result) {
            $this->logger->log($_SESSION['user_id'], 'user_demoted', $userId, "Demoted user from administrator role");
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Failed to demote user'];
    }

    /**
     * Promote user to Super Admin
     */
    public function promoteToSuperAdmin($userId) {
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        if ($user['role'] === 'super_admin') {
            return ['success' => false, 'error' => 'User is already Super Admin'];
        }

        $stmt = $this->db->prepare("UPDATE users SET role = 'super_admin' WHERE id = ?");
        $result = $stmt->execute([$userId]);

        if ($result) {
            $this->logger->log($_SESSION['user_id'], 'user_promoted_to_super', $userId, "Promoted user to Super Admin role");
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Failed to promote user'];
    }

    /**
     * Activate user account
     */
    public function activateUser($userId) {
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        if ($user['role'] === 'super_admin') {
            return ['success' => false, 'error' => 'Cannot modify Super Admin status'];
        }

        $stmt = $this->db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $result = $stmt->execute([$userId]);

        if ($result) {
            $this->logger->log($_SESSION['user_id'], 'user_activated', $userId, "Activated user account");
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Failed to activate user'];
    }

    /**
     * Deactivate user account
     */
    public function deactivateUser($userId) {
        $stmt = $this->db->prepare("SELECT id, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        if ($user['id'] == $_SESSION['user_id']) {
            return ['success' => false, 'error' => 'Cannot deactivate your own account'];
        }

        if ($user['role'] === 'super_admin') {
            return ['success' => false, 'error' => 'Cannot modify Super Admin status'];
        }

        $stmt = $this->db->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
        $result = $stmt->execute([$userId]);

        if ($result) {
            $this->logger->log($_SESSION['user_id'], 'user_deactivated', $userId, "Deactivated user account");
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Failed to deactivate user'];
    }

    /**
     * Delete user (soft delete)
     */
    public function deleteUser($userId) {
        $stmt = $this->db->prepare("SELECT id, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        if ($user['id'] == $_SESSION['user_id']) {
            return ['success' => false, 'error' => 'Cannot delete your own account'];
        }

        if ($user['role'] === 'super_admin') {
            return ['success' => false, 'error' => 'Cannot delete Super Admin'];
        }

        // Check if deleted_at column exists
        $columns = $this->db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_ASSOC);
        $hasDeletedAt = false;
        foreach ($columns as $col) {
            if ($col['Field'] === 'deleted_at') {
                $hasDeletedAt = true;
                break;
            }
        }

        if ($hasDeletedAt) {
            $stmt = $this->db->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$userId]);
        } else {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $result = $stmt->execute([$userId]);
        }

        if ($result) {
            $this->logger->log($_SESSION['user_id'], 'user_deleted', $userId, "Deleted user account");
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Failed to delete user'];
    }
    
    /**
     * Get system configuration
     */
    public function getSystemConfig() {
        $config = [];
        
        // Get system settings from config
        $config['php_version'] = phpversion();
        $config['mysql_version'] = $this->db->query("SELECT VERSION()")->fetchColumn();
        $config['server_time'] = date('Y-m-d H:i:s');
        $config['timezone'] = date_default_timezone_get();
        
        // Get database stats
        $config['database_size'] = $this->db->query("
            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
        ")->fetchColumn();
        
        // Get user stats
        $config['total_users'] = $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $config['active_users'] = $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
        
        // Get document stats
        $config['total_documents'] = $this->db->query("SELECT COUNT(*) FROM legislative_documents WHERE deleted_at IS NULL")->fetchColumn();
        $config['total_storage'] = $this->db->query("SELECT ROUND(SUM(file_size) / 1024 / 1024, 2) as storage_mb FROM legislative_documents WHERE deleted_at IS NULL")->fetchColumn();
        
        return $config;
    }
    
    /**
     * Get editable system configuration
     */
    public function getEditableConfig() {
        $config = [];

        // Check if system_settings table exists
        $tables = $this->db->query("SHOW TABLES LIKE 'system_settings'")->fetchAll(PDO::FETCH_ASSOC);
        $hasSettingsTable = count($tables) > 0;

        if ($hasSettingsTable) {
            $stmt = $this->db->query("SELECT setting_key, setting_value FROM system_settings");
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($settings as $setting) {
                $config[$setting['setting_key']] = $setting['setting_value'];
            }
        }

        // Default values
        $defaults = [
            'session_timeout' => '2',
            'otp_expiry' => '1',
            'max_file_size' => '10',
            'allowed_file_types' => 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
            'maintenance_mode' => '0',
            'site_name' => 'Legislative Records Management System',
            'backup_retention_days' => '30'
        ];

        return array_merge($defaults, $config);
    }

    /**
     * Update system configuration
     */
    public function updateSystemConfig($configData) {
        // Check if system_settings table exists
        $tables = $this->db->query("SHOW TABLES LIKE 'system_settings'")->fetchAll(PDO::FETCH_ASSOC);
        $hasSettingsTable = count($tables) > 0;

        // Create table if it doesn't exist
        if (!$hasSettingsTable) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS system_settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    setting_key VARCHAR(100) UNIQUE NOT NULL,
                    setting_value TEXT,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    updated_by INT
                )
            ");
        }

        // Update each setting
        foreach ($configData as $key => $value) {
            $stmt = $this->db->prepare("
                INSERT INTO system_settings (setting_key, setting_value, updated_by)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE setting_value = ?, updated_by = ?, updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$key, $value, $_SESSION['user_id'], $value, $_SESSION['user_id']]);
        }

        $this->logger->log($_SESSION['user_id'], 'config_updated', null, "Updated system configuration");

        return ['success' => true, 'message' => 'Configuration updated successfully'];
    }

    /**
     * Reset system configuration to defaults
     */
    public function resetSystemConfig() {
        // Check if system_settings table exists
        $tables = $this->db->query("SHOW TABLES LIKE 'system_settings'")->fetchAll(PDO::FETCH_ASSOC);
        if (count($tables) > 0) {
            $this->db->query("TRUNCATE TABLE system_settings");
        }

        $this->logger->log($_SESSION['user_id'], 'config_reset', null, "Reset system configuration to defaults");

        return ['success' => true, 'message' => 'Configuration reset to defaults'];
    }
    
    /**
     * Create database backup
     */
    public function createBackup() {
        $backupDir = __DIR__ . '/../../../storage/backups';
        if (!is_dir($backupDir)) {
            if (!mkdir($backupDir, 0755, true)) {
                error_log("Failed to create backup directory: $backupDir");
                return ['success' => false, 'error' => 'Failed to create backup directory'];
            }
        }

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupDir . '/' . $filename;

        // Try PHP-based backup directly (more reliable on Windows/XAMPP)
        $result = $this->createPHPBackup($filepath);

        if ($result['success']) {
            return $result;
        }

        // If PHP backup fails, try mysqldump
        $dbConfig = require __DIR__ . '/../config/database.php';

        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s 2>&1',
            $dbConfig['host'],
            $dbConfig['username'],
            $dbConfig['password'],
            $dbConfig['database'],
            $filepath
        );

        $output = [];
        $returnCode = 0;
        @exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($filepath) && filesize($filepath) > 0) {
            $this->logger->log($_SESSION['user_id'], 'database_backup', null, "Created database backup: $filename");
            return ['success' => true, 'filename' => $filename];
        }

        // Return the PHP backup error if both failed
        return $result;
    }
    
    /**
     * PHP-based backup (fallback)
     */
    private function createPHPBackup($filepath) {
        try {
            $tables = [];
            $result = $this->db->query("SHOW TABLES");
            if ($result) {
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $tables[] = $row[0];
                }
            }

            if (empty($tables)) {
                return ['success' => false, 'error' => 'No tables found in database'];
            }

            $sql = '';
            foreach ($tables as $table) {
                $sql .= "DROP TABLE IF EXISTS `$table`;\n";

                $createTable = $this->db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
                if ($createTable && isset($createTable['Create Table'])) {
                    $sql .= $createTable['Create Table'] . ";\n\n";
                }

                $rows = $this->db->query("SELECT * FROM `$table`");
                if ($rows) {
                    while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                        $values = array_map(function($val) {
                            return $val === null ? 'NULL' : "'" . addslashes($val) . "'";
                        }, array_values($row));

                        $sql .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                    }
                }
                $sql .= "\n\n";
            }

            if (file_put_contents($filepath, $sql)) {
                $this->logger->log($_SESSION['user_id'], 'database_backup', null, "Created PHP backup: " . basename($filepath));
                return ['success' => true, 'filename' => basename($filepath)];
            }

            return ['success' => false, 'error' => 'Failed to write backup file'];
        } catch (Exception $e) {
            error_log("PHP backup error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Backup failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get list of backups with additional info
     */
    public function getBackups() {
        $backupDir = __DIR__ . '/../../../storage/backups';
        if (!is_dir($backupDir)) {
            return [];
        }

        $backups = [];
        foreach (glob($backupDir . '/*.sql') as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => filesize($file),
                'created' => date('Y-m-d H:i:s', filemtime($file)),
                'verified' => $this->verifyBackup($file)
            ];
        }

        // Sort by creation date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created']) - strtotime($a['created']);
        });

        return $backups;
    }

    /**
     * Verify backup file integrity
     */
    private function verifyBackup($filepath) {
        if (!file_exists($filepath)) {
            return false;
        }

        // Check if file is readable and not empty
        if (!is_readable($filepath) || filesize($filepath) === 0) {
            return false;
        }

        // Basic SQL syntax check
        $content = file_get_contents($filepath);
        if (empty($content)) {
            return false;
        }

        // Check for basic SQL patterns
        $hasCreateTable = preg_match('/CREATE TABLE/i', $content);
        $hasInsert = preg_match('/INSERT INTO/i', $content);

        return $hasCreateTable || $hasInsert;
    }

    /**
     * Restore database from backup
     */
    public function restoreBackup($filename) {
        $backupDir = __DIR__ . '/../../../storage/backups';
        $filepath = $backupDir . '/' . $filename;

        if (!file_exists($filepath)) {
            return ['success' => false, 'error' => 'Backup file not found'];
        }

        // Verify backup before restore
        if (!$this->verifyBackup($filepath)) {
            return ['success' => false, 'error' => 'Backup file is corrupted or invalid'];
        }

        // Get database credentials
        $dbConfig = require __DIR__ . '/../config/database.php';

        // Use mysql command if available
        $command = sprintf(
            'mysql -h%s -u%s -p%s %s < %s',
            $dbConfig['host'],
            $dbConfig['username'],
            $dbConfig['password'],
            $dbConfig['database'],
            $filepath
        );

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $this->logger->log($_SESSION['user_id'], 'database_restore', null, "Restored database from backup: $filename");
            return ['success' => true, 'message' => 'Database restored successfully'];
        }

        // Fallback: PHP-based restore
        return $this->restoreFromPHP($filepath);
    }

    /**
     * PHP-based restore (fallback)
     */
    private function restoreFromPHP($filepath) {
        $sql = file_get_contents($filepath);
        if (empty($sql)) {
            return ['success' => false, 'error' => 'Backup file is empty'];
        }

        // Split SQL into individual statements
        $statements = explode(';', $sql);
        $successCount = 0;
        $errorCount = 0;

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement)) {
                continue;
            }

            try {
                $this->db->exec($statement);
                $successCount++;
            } catch (PDOException $e) {
                $errorCount++;
                error_log("SQL restore error: " . $e->getMessage());
            }
        }

        if ($successCount > 0) {
            $this->logger->log($_SESSION['user_id'], 'database_restore_php', null, "Restored database using PHP method");
            return ['success' => true, 'message' => "Database restored successfully ($successCount statements executed, $errorCount errors)"];
        }

        return ['success' => false, 'error' => 'Failed to restore database'];
    }

    /**
     * Clean up old backups based on retention policy
     */
    public function cleanupOldBackups($retentionDays = 30) {
        $backupDir = __DIR__ . '/../../../storage/backups';
        if (!is_dir($backupDir)) {
            return ['success' => true, 'deleted' => 0];
        }

        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$retentionDays days"));
        $deletedCount = 0;

        foreach (glob($backupDir . '/*.sql') as $file) {
            $fileDate = date('Y-m-d H:i:s', filemtime($file));
            if (strtotime($fileDate) < strtotime($cutoffDate)) {
                if (unlink($file)) {
                    $deletedCount++;
                    $this->logger->log($_SESSION['user_id'], 'backup_cleanup', null, "Deleted old backup: " . basename($file));
                }
            }
        }

        return ['success' => true, 'deleted' => $deletedCount];
    }

    /**
     * Get backup statistics
     */
    public function getBackupStats() {
        $backupDir = __DIR__ . '/../../../storage/backups';
        if (!is_dir($backupDir)) {
            return [
                'total_count' => 0,
                'total_size' => 0,
                'total_size_mb' => 0,
                'latest_backup' => null,
                'oldest_backup' => null
            ];
        }

        $backups = glob($backupDir . '/*.sql');
        if (empty($backups)) {
            return [
                'total_count' => 0,
                'total_size' => 0,
                'total_size_mb' => 0,
                'latest_backup' => null,
                'oldest_backup' => null
            ];
        }

        $totalSize = 0;
        $latestTime = 0;
        $oldestTime = PHP_INT_MAX;
        $latestBackup = null;
        $oldestBackup = null;

        foreach ($backups as $file) {
            $size = filesize($file);
            $totalSize += $size;
            $time = filemtime($file);

            if ($time > $latestTime) {
                $latestTime = $time;
                $latestBackup = basename($file);
            }

            if ($time < $oldestTime) {
                $oldestTime = $time;
                $oldestBackup = basename($file);
            }
        }

        return [
            'total_count' => count($backups),
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'latest_backup' => $latestBackup ? [
                'filename' => $latestBackup,
                'created' => date('Y-m-d H:i:s', $latestTime)
            ] : null,
            'oldest_backup' => $oldestBackup ? [
                'filename' => $oldestBackup,
                'created' => date('Y-m-d H:i:s', $oldestTime)
            ] : null
        ];
    }
    
    /**
     * Delete backup
     */
    public function deleteBackup($filename) {
        $backupDir = __DIR__ . '/../../../storage/backups';
        $filepath = $backupDir . '/' . $filename;
        
        if (file_exists($filepath)) {
            if (unlink($filepath)) {
                $this->logger->log($_SESSION['user_id'], 'backup_deleted', null, "Deleted backup: $filename");
                return ['success' => true];
            }
        }
        
        return ['success' => false, 'error' => 'Backup not found'];
    }
}
