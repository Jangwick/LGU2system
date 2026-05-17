<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/PermissionMiddleware.php';

// Check permissions
$permissions = new PermissionMiddleware(getDatabase());
$permissions->requireLogin();
$permissions->requirePermission('database.backup');

require_once __DIR__ . '/../controllers/SuperAdminController.php';
$controller = new SuperAdminController();

$backups = $controller->getBackups();

$pageTitle = 'Database Backup & Restore';
$currentPage = 'database-backup';
require_once __DIR__ . '/../layouts/header.php';
?>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-950 p-6">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-6 text-white transform hover:scale-[1.01] transition-all duration-300 animate-fade-in relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 relative z-10">
                <div class="transform transition-all duration-300">
                    <h1 class="text-2xl md:text-3xl font-bold mb-2 animate-slide-in-left">Database Backup & Restore</h1>
                    <p class="text-red-100 animate-slide-in-left animation-delay-100">Create and manage database backups</p>
                </div>
                <div class="flex flex-wrap gap-3 animate-slide-in-right">
                    <button onclick="createBackup()" class="flex items-center px-6 py-2.5 bg-white hover:bg-red-50 text-red-600 rounded-xl font-bold transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95 shadow-sm border border-white/10">
                        <i class="bi bi-download mr-2"></i> Create Backup
                    </button>
                </div>
            </div>
        </div>

        <!-- Backups List -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 animate-fade-in-up">
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
                                <div class="flex gap-2">
                                    <a href="<?php echo BASE_URL; ?>/storage/backups/<?= htmlspecialchars($backup['filename']) ?>" download class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/40 hover:bg-blue-200 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 rounded-lg text-sm font-medium transition-colors">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <button onclick="deleteBackup('<?= htmlspecialchars($backup['filename']) ?>')" class="px-3 py-1.5 bg-red-100 dark:bg-red-900/40 hover:bg-red-200 dark:hover:bg-red-900/60 text-red-700 dark:text-red-300 rounded-lg text-sm font-medium transition-colors">
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
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
