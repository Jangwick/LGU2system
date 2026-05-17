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
} catch (Exception $e) {
    error_log("Error getting system config: " . $e->getMessage());
    $config = [];
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
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-6 text-white transform hover:scale-[1.01] transition-all duration-300 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 relative z-10">
                <div class="transform transition-all duration-300">
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">System Configuration</h1>
                    <p class="text-red-100">View and manage system settings</p>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Server Information -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
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

            <!-- Database Information -->
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

            <!-- Document Statistics -->
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

            <!-- Security Settings -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 flex items-center">
                    <i class="bi bi-shield-lock mr-2 text-red-600"></i>Security Settings
                </h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-gray-600 dark:text-gray-400">OTP Expiry</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-white">1 minute</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-gray-600 dark:text-gray-400">Session Timeout</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-white">2 minutes</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <span class="text-gray-600 dark:text-gray-400">Encryption</span>
                        <span class="font-mono font-bold text-green-600 dark:text-green-400">AES-256</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
