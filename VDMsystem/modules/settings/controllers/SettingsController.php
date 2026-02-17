<?php
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';

class SettingsController
{
    /**
     * Ensure the system_settings table exists
     */
    private function ensureTable()
    {
        $db = getDatabase();
        $db->exec("CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            setting_group VARCHAR(50) DEFAULT 'general',
            setting_type VARCHAR(20) DEFAULT 'text',
            description VARCHAR(255) DEFAULT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");

        // Seed default settings if empty
        $count = dbCount('system_settings');
        if ($count === 0) {
            $this->seedDefaults();
        }
    }

    /**
     * Seed default system settings
     */
    private function seedDefaults()
    {
        $defaults = [
            // General Settings
            ['site_name', 'VDM System', 'general', 'text', 'System display name'],
            ['site_description', 'Voting & Document Management System', 'general', 'text', 'Short system description'],
            ['lgu_name', 'Local Government Unit', 'general', 'text', 'LGU name for headers and reports'],
            ['timezone', 'Asia/Manila', 'general', 'text', 'System timezone'],
            
            // Session & Security
            ['session_timeout', '3600', 'security', 'number', 'Session timeout in seconds (default: 3600)'],
            ['max_login_attempts', '5', 'security', 'number', 'Max failed login attempts before lockout'],
            ['lockout_duration', '900', 'security', 'number', 'Account lockout duration in seconds (default: 900)'],
            ['password_min_length', '8', 'security', 'number', 'Minimum password length'],
            ['require_strong_password', '1', 'security', 'boolean', 'Require uppercase, lowercase, number, symbol'],
            
            // Voting Settings
            ['default_voting_method', 'roll_call', 'voting', 'text', 'Default voting method for new sessions'],
            ['auto_close_voting', '0', 'voting', 'boolean', 'Auto-close voting when all members have voted'],
            ['allow_vote_change', '0', 'voting', 'boolean', 'Allow councilors to change their vote'],
            ['voting_quorum_percentage', '50', 'voting', 'number', 'Minimum quorum percentage for valid voting'],
            
            // Notification Settings
            ['enable_email_notifications', '0', 'notifications', 'boolean', 'Enable email notifications'],
            ['notify_new_session', '1', 'notifications', 'boolean', 'Notify on new voting session created'],
            ['notify_vote_complete', '1', 'notifications', 'boolean', 'Notify when voting session completes'],
            
            // Audit Settings
            ['audit_retention_days', '365', 'audit', 'number', 'Days to retain audit logs (0 = forever)'],
            ['enable_hash_verification', '1', 'audit', 'boolean', 'Enable SHA-256 hash chain integrity'],
        ];

        $db = getDatabase();
        $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_group, setting_type, description) VALUES (?, ?, ?, ?, ?)");
        foreach ($defaults as $s) {
            $stmt->execute($s);
        }
    }

    /**
     * Get all settings grouped
     */
    public function index()
    {
        $this->ensureTable();

        $settings = dbFetchAll("SELECT * FROM system_settings ORDER BY setting_group, id");

        $grouped = [];
        foreach ($settings as $s) {
            $grouped[$s['setting_group']][] = $s;
        }

        $systemInfo = $this->getSystemInfo();

        return [
            'settings' => $settings,
            'grouped' => $grouped,
            'systemInfo' => $systemInfo
        ];
    }

    /**
     * Get a single setting value
     */
    public function get($key, $default = null)
    {
        $this->ensureTable();
        $row = dbFetchOne("SELECT setting_value FROM system_settings WHERE setting_key = ?", [$key]);
        return $row ? $row['setting_value'] : $default;
    }

    /**
     * Update settings (batch)
     */
    public function updateSettings($data, $userId)
    {
        $this->ensureTable();
        $db = getDatabase();

        $updated = [];
        $stmt = $db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");

        foreach ($data as $key => $value) {
            // Skip non-setting fields
            if ($key === 'csrf_token') continue;

            $existing = dbFetchOne("SELECT setting_key, setting_value FROM system_settings WHERE setting_key = ?", [$key]);
            if ($existing && $existing['setting_value'] !== $value) {
                $stmt->execute([$value, $key]);
                $updated[$key] = ['old' => $existing['setting_value'], 'new' => $value];
            }
        }

        if (!empty($updated)) {
            logAudit('settings_updated', $userId, 'settings', 'system_settings', null, 'Updated system settings', json_encode($updated));
        }

        return [
            'success' => true,
            'message' => !empty($updated) ? count($updated) . ' setting(s) updated successfully.' : 'No changes detected.',
            'changes' => $updated
        ];
    }

    /**
     * Get server/system information
     */
    private function getSystemInfo()
    {
        $db = getDatabase();
        $dbVersion = $db->query("SELECT VERSION()")->fetchColumn();

        return [
            'php_version' => PHP_VERSION,
            'db_version' => $dbVersion,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
            'os' => php_uname('s') . ' ' . php_uname('r'),
            'memory_limit' => ini_get('memory_limit'),
            'max_upload' => ini_get('upload_max_filesize'),
            'max_post' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'timezone' => date_default_timezone_get(),
            'disk_free' => function_exists('disk_free_space') ? $this->formatBytes(disk_free_space('.')) : 'N/A',
            'disk_total' => function_exists('disk_total_space') ? $this->formatBytes(disk_total_space('.')) : 'N/A',
        ];
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }
}
