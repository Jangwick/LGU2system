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
     * Get all administrators
     */
    public function getAdministrators() {
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

        $stmt = $this->db->query("
            SELECT $selectColumns
            FROM users
            WHERE role IN ('administrator', 'super_admin')
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
     * Update system configuration
     */
    public function updateSystemConfig($configData) {
        // For now, this is a placeholder
        // In a real implementation, this would update a system_settings table
        return ['success' => true, 'message' => 'Configuration updated'];
    }
    
    /**
     * Create database backup
     */
    public function createBackup() {
        $backupDir = __DIR__ . '/../../../storage/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupDir . '/' . $filename;
        
        // Get database credentials
        $dbConfig = require __DIR__ . '/../config/database.php';
        
        // Use mysqldump if available
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s',
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
            $this->logger->log($_SESSION['user_id'], 'database_backup', null, "Created database backup: $filename");
            return ['success' => true, 'filename' => $filename];
        }
        
        // Fallback: PHP-based backup (simplified)
        return $this->createPHPBackup($filepath);
    }
    
    /**
     * PHP-based backup (fallback)
     */
    private function createPHPBackup($filepath) {
        $tables = [];
        $result = $this->db->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        $sql = '';
        foreach ($tables as $table) {
            $sql .= "DROP TABLE IF EXISTS $table;\n";
            
            $createTable = $this->db->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_ASSOC);
            $sql .= $createTable['Create Table'] . ";\n\n";
            
            $rows = $this->db->query("SELECT * FROM $table");
            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $values = array_map(function($val) {
                    return $val === null ? 'NULL' : "'" . addslashes($val) . "'";
                }, array_values($row));
                
                $sql .= "INSERT INTO $table VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n\n";
        }
        
        if (file_put_contents($filepath, $sql)) {
            $this->logger->log($_SESSION['user_id'], 'database_backup', null, "Created PHP backup: " . basename($filepath));
            return ['success' => true, 'filename' => basename($filepath)];
        }
        
        return ['success' => false, 'error' => 'Failed to create backup'];
    }
    
    /**
     * Get list of backups
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
                'created' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        // Sort by creation date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created']) - strtotime($a['created']);
        });
        
        return $backups;
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
