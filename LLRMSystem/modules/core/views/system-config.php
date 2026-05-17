<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/middleware/PermissionMiddleware.php';

// Check permissions
$permissions = new PermissionMiddleware(getDatabase());
$permissions->requireLogin();
$permissions->requirePermission('system.config');

require_once __DIR__ . '/../controllers/SuperAdminController.php';
$controller = new SuperAdminController();

try {
    $config = $controller->getSystemConfig();
    $editableConfig = $controller->getEditableConfig();
    $sessionTimeout = (intval($editableConfig['session_timeout'] ?? 2)) * 60; // Convert minutes to seconds
} catch (Exception $e) {
    error_log("Error getting system config: " . $e->getMessage());
    $config = [];
    $editableConfig = [];
    $sessionTimeout = 120; // Default 2 minutes
}

$config = array_merge([
    'php_version' => phpversion(),
    'mysql_version' => 'Unavailable',
    'server_time' => date('Y-m-d H:i:s'),
    'timezone' => date_default_timezone_get(),
    'database_size' => 0,
    'total_users' => 0,
    'active_users' => 0,
    'total_documents' => 0,
    'total_storage' => 0,
], $config);

$pageTitle = 'System Configuration';
$currentPage = 'system-config';
require_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php require_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php require_once __DIR__ . '/../../core/layouts/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-950 p-6">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-6 text-white transform hover:scale-[1.01] transition-all duration-300 relative overflow-hidden animate-fade-in">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 relative z-10">
                <div class="transform transition-all duration-300 animate-slide-in-left">
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">System Configuration</h1>
                    <p class="text-red-100">View and manage system settings</p>
                </div>
                <div class="flex gap-3 animate-slide-in-right">
                    <button onclick="resetConfig()" class="flex items-center px-6 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-xl font-bold transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95 shadow-sm border border-white/20">
                        <i class="bi bi-arrow-counterclockwise mr-2"></i> Reset to Defaults
                    </button>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <form id="configForm" class="space-y-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Server Information (Read-only) -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 animate-fade-in-up">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 flex items-center">
                    <i class="bi bi-cpu mr-2 text-red-600"></i>Server Information
                </h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-gray-600 dark:text-gray-400">PHP Version</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-white"><?= $config['php_version'] ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-gray-600 dark:text-gray-400">MySQL Version</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-white"><?= $config['mysql_version'] ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-gray-600 dark:text-gray-400">Server Time</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-white"><?= $config['server_time'] ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-gray-600 dark:text-gray-400">Timezone</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-white"><?= $config['timezone'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Database Information (Read-only) -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 flex items-center">
                    <i class="bi bi-database mr-2 text-red-600"></i>Database Information
                </h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-gray-600 dark:text-gray-400">Database Size</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-white"><?= $config['database_size'] ?> MB</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-gray-600 dark:text-gray-400">Total Users</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-white"><?= $config['total_users'] ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-gray-600 dark:text-gray-400">Active Users</span>
                        <span class="font-mono font-bold text-green-600 dark:text-green-400"><?= $config['active_users'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Document Statistics (Read-only) -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 flex items-center">
                    <i class="bi bi-file-earmark-text mr-2 text-red-600"></i>Document Statistics
                </h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-gray-600 dark:text-gray-400">Total Documents</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-white"><?= $config['total_documents'] ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-gray-600 dark:text-gray-400">Storage Used</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-white"><?= $config['total_storage'] ?> MB</span>
                    </div>
                </div>
            </div>

            <!-- Security Settings (Editable) -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 flex items-center">
                    <i class="bi bi-shield-lock mr-2 text-red-600"></i>Security Settings
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">OTP Expiry (minutes)</label>
                        <input type="number" name="otp_expiry" value="<?= htmlspecialchars($editableConfig['otp_expiry'] ?? '1') ?>" min="1" max="60" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Session Timeout (minutes)</label>
                        <input type="number" name="session_timeout" value="<?= htmlspecialchars($editableConfig['session_timeout'] ?? '2') ?>" min="1" max="1440" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-gray-600 dark:text-gray-400">Encryption</span>
                        <span class="font-mono font-bold text-green-600 dark:text-green-400">AES-256</span>
                    </div>
                </div>
            </div>

            <!-- General Settings (Editable) -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 flex items-center">
                    <i class="bi bi-gear mr-2 text-red-600"></i>General Settings
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Site Name</label>
                        <input type="text" name="site_name" value="<?= htmlspecialchars($editableConfig['site_name'] ?? 'Legislative Records Management System') ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max File Size (MB)</label>
                        <input type="number" name="max_file_size" value="<?= htmlspecialchars($editableConfig['max_file_size'] ?? '10') ?>" min="1" max="100" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Allowed File Types</label>
                        <input type="text" name="allowed_file_types" value="<?= htmlspecialchars($editableConfig['allowed_file_types'] ?? 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png') ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="Comma-separated extensions">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Backup Retention (days)</label>
                        <input type="number" name="backup_retention_days" value="<?= htmlspecialchars($editableConfig['backup_retention_days'] ?? '30') ?>" min="1" max="365" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Maintenance Mode (Editable) -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 animate-fade-in-up animation-delay-500">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 flex items-center">
                    <i class="bi bi-tools mr-2 text-red-600"></i>Maintenance Mode
                </h2>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div>
                            <span class="text-gray-700 dark:text-gray-300 font-medium">Enable Maintenance Mode</span>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Prevent non-admin users from accessing the system</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="maintenance_mode" value="1" <?= ($editableConfig['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?> class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        </form>

        <!-- Save Button -->
        <div class="flex justify-end gap-3 mb-6">
            <button onclick="saveConfig()" class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95 shadow-md">
                <i class="bi bi-check-lg mr-2"></i> Save Configuration
            </button>
        </div>
    </main>
</div>

<script>
function saveConfig() {
    const form = document.getElementById('configForm');
    const formData = new FormData(form);
    const config = {};

    formData.forEach((value, key) => {
        if (key === 'maintenance_mode') {
            config[key] = value;
        } else {
            config[key] = value;
        }
    });

    // Handle checkbox separately
    const maintenanceCheckbox = form.querySelector('[name="maintenance_mode"]');
    config['maintenance_mode'] = maintenanceCheckbox.checked ? '1' : '0';

    fetch('<?php echo BASE_URL; ?>/modules/core/api/system-config.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'update',
            config: config
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Configuration saved successfully!');
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function resetConfig() {
    if (confirm('Are you sure you want to reset all configuration to default values? This cannot be undone.')) {
        fetch('<?php echo BASE_URL; ?>/modules/core/api/system-config.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'reset'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Configuration reset to defaults successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}
</script>

<script>
// Sidebar Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const toggleIcon = document.getElementById('sidebar-toggle-icon');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('sidebar-collapsed');
            sidebar.classList.toggle('sidebar-expanded');
            
            // Rotate icon animation
            if (toggleIcon) {
                toggleIcon.classList.toggle('rotate-180');
            }
        });
    }
});
</script>
</div>
