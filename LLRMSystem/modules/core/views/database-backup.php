<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/middleware/PermissionMiddleware.php';

// Check permissions
$permissions = new PermissionMiddleware(getDatabase());
$permissions->requireLogin();
$permissions->requirePermission('database.backup');

require_once __DIR__ . '/../controllers/SuperAdminController.php';
$controller = new SuperAdminController();

try {
    $backups = $controller->getBackups();
    $stats = $controller->getBackupStats();
    $editableConfig = $controller->getEditableConfig();
    $sessionTimeout = (intval($editableConfig['session_timeout'] ?? 2)) * 60; // Convert minutes to seconds
} catch (Exception $e) {
    error_log("Error getting backups: " . $e->getMessage());
    $backups = [];
    $stats = [
        'total_count' => 0,
        'total_size_mb' => 0,
        'latest_backup' => null,
        'oldest_backup' => null
    ];
    $sessionTimeout = 120; // Default 2 minutes
}

$pageTitle = 'Database Backup & Restore';
$currentPage = 'database-backup';
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
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">Database Backup & Restore</h1>
                    <p class="text-red-100">Create and manage database backups</p>
                </div>
                <div class="flex flex-wrap gap-3 animate-slide-in-right">
                    <button onclick="createBackup()" class="flex items-center px-6 py-2.5 bg-white hover:bg-red-50 text-red-600 rounded-xl font-bold transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95 shadow-sm border border-white/10">
                        <i class="bi bi-download mr-2"></i> Create Backup
                    </button>
                    <button onclick="cleanupBackups()" class="flex items-center px-6 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-xl font-bold transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95 shadow-sm border border-white/20">
                        <i class="bi bi-trash3 mr-2"></i> Cleanup Old
                    </button>
                </div>
            </div>
        </div>

        <!-- Backup Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 animate-fade-in-up">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Total Backups</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= $stats['total_count'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/40 rounded-full flex items-center justify-center">
                        <i class="bi bi-database text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 animate-fade-in-up animation-delay-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Total Size</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= $stats['total_size_mb'] ?> MB</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/40 rounded-full flex items-center justify-center">
                        <i class="bi bi-hdd text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 animate-fade-in-up animation-delay-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Latest Backup</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-white truncate">
                            <?= $stats['latest_backup'] ? date('M d, H:i', strtotime($stats['latest_backup']['created'])) : 'Never' ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/40 rounded-full flex items-center justify-center">
                        <i class="bi bi-clock-history text-purple-600 dark:text-purple-400 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 animate-fade-in-up animation-delay-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Oldest Backup</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-white truncate">
                            <?= $stats['oldest_backup'] ? date('M d, H:i', strtotime($stats['oldest_backup']['created'])) : 'Never' ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/40 rounded-full flex items-center justify-center">
                        <i class="bi bi-calendar text-orange-600 dark:text-orange-400 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backups List -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 animate-fade-in-up animation-delay-400">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 flex items-center">
                <i class="bi bi-hdd-stack mr-2 text-red-600"></i>Available Backups
            </h2>

            <?php if (empty($backups)): ?>
            <div class="text-center py-12">
                <i class="bi bi-inbox text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400">No backups found. Create your first backup.</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Filename</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Size</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Created</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <i class="bi bi-file-earmark-zip text-2xl text-red-600"></i>
                                    <span class="font-mono text-gray-800 dark:text-white"><?= htmlspecialchars($backup['filename']) ?></span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300">
                                <?= number_format($backup['size'] / 1024 / 1024, 2) ?> MB
                            </td>
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300">
                                <?= date('M d, Y g:i A', strtotime($backup['created'])) ?>
                            </td>
                            <td class="py-4 px-4">
                                <?php if ($backup['verified']): ?>
                                <span class="px-3 py-1 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300 rounded-full text-xs font-bold">
                                    <i class="bi bi-check-circle mr-1"></i>Verified
                                </span>
                                <?php else: ?>
                                <span class="px-3 py-1 bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300 rounded-full text-xs font-bold">
                                    <i class="bi bi-exclamation-triangle mr-1"></i>Invalid
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex gap-2">
                                    <a href="<?php echo BASE_URL; ?>/storage/backups/<?= htmlspecialchars($backup['filename']) ?>" download class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/40 hover:bg-blue-200 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 rounded-lg text-sm font-medium transition-colors" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <?php if ($backup['verified']): ?>
                                    <button onclick="restoreBackup('<?= htmlspecialchars($backup['filename']) ?>')" class="px-3 py-1.5 bg-green-100 dark:bg-green-900/40 hover:bg-green-200 dark:hover:bg-green-900/60 text-green-700 dark:text-green-300 rounded-lg text-sm font-medium transition-colors" title="Restore">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button onclick="deleteBackup('<?= htmlspecialchars($backup['filename']) ?>')" class="px-3 py-1.5 bg-red-100 dark:bg-red-900/40 hover:bg-red-200 dark:hover:bg-red-900/60 text-red-700 dark:text-red-300 rounded-lg text-sm font-medium transition-colors" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
function createBackup() {
    if (!confirm('This will create a new database backup. Continue?')) return;

    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split mr-2"></i>Creating...';

    fetch('<?php echo BASE_URL; ?>/modules/core/api/database-backup.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ action: 'create' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Backup created successfully: ' + data.filename);
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-download mr-2"></i> Create Backup';
    });
}

function restoreBackup(filename) {
    if (!confirm(`WARNING: This will replace the current database with the backup "${filename}". All current data will be lost. Continue?`)) return;

    if (!confirm(`This action is irreversible. Are you absolutely sure you want to restore from "${filename}"?`)) return;

    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split mr-2"></i>Restoring...';

    fetch('<?php echo BASE_URL; ?>/modules/core/api/database-backup.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'restore',
            filename: filename
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Database restored successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i>';
    });
}

function deleteBackup(filename) {
    if (!confirm(`Are you sure you want to delete backup "${filename}"?`)) return;

    fetch('<?php echo BASE_URL; ?>/modules/core/api/database-backup.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'delete',
            filename: filename
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Backup deleted successfully');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function cleanupBackups() {
    const retentionDays = prompt('Delete backups older than how many days?', '30');
    if (!retentionDays || isNaN(retentionDays)) return;

    if (!confirm(`This will delete all backups older than ${retentionDays} days. Continue?`)) return;

    fetch('<?php echo BASE_URL; ?>/modules/core/api/database-backup.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'cleanup',
            retention_days: parseInt(retentionDays)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Cleanup completed. ${data.deleted} backup(s) deleted.`);
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>
