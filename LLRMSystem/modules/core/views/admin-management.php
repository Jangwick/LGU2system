<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/PermissionMiddleware.php';

// Check permissions
$permissions = new PermissionMiddleware(getDatabase());
$permissions->requireLogin();
$permissions->requirePermission('admin.manage');

require_once __DIR__ . '/../controllers/SuperAdminController.php';
$controller = new SuperAdminController();

$administrators = $controller->getAdministrators();

$pageTitle = 'Administrator Management';
$currentPage = 'admin-management';
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
                    <h1 class="text-2xl md:text-3xl font-bold mb-2 animate-slide-in-left">Administrator Management</h1>
                    <p class="text-red-100 animate-slide-in-left animation-delay-100">Manage administrator and Super Admin accounts</p>
                </div>
                <div class="flex flex-wrap gap-3 animate-slide-in-right">
                    <a href="<?php echo USERS_URL; ?>/views/create.php" class="flex items-center px-6 py-2.5 bg-white hover:bg-red-50 text-red-600 rounded-xl font-bold transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95 shadow-sm border border-white/10">
                        <i class="bi bi-person-plus mr-2"></i> Add Administrator
                    </a>
                </div>
            </div>
        </div>

        <!-- Administrators Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 animate-fade-in-up">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Administrators & Super Admins</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Name</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Email</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Role</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Department</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Last Login</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($administrators as $admin): ?>
                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center text-white font-bold">
                                        <?= strtoupper(substr($admin['full_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white"><?= htmlspecialchars($admin['full_name']) ?></p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($admin['employee_id']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300"><?= htmlspecialchars($admin['email']) ?></td>
                            <td class="py-4 px-4">
                                <?php if ($admin['role'] === 'super_admin'): ?>
                                <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-300 rounded-full text-xs font-bold uppercase">
                                    <i class="bi bi-shield-lock mr-1"></i>Super Admin
                                </span>
                                <?php else: ?>
                                <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 rounded-full text-xs font-bold uppercase">
                                    <i class="bi bi-shield mr-1"></i>Administrator
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-3 py-1 <?= $admin['status'] === 'active' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300' ?> rounded-full text-xs font-bold uppercase">
                                    <?= ucfirst($admin['status']) ?>
                                </span>
                            </td>
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300"><?= htmlspecialchars($admin['department']) ?></td>
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300">
                                <?= $admin['last_login'] ? date('M d, Y g:i A', strtotime($admin['last_login'])) : 'Never' ?>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex gap-2">
                                    <a href="<?php echo USERS_URL; ?>/views/edit.php?id=<?= $admin['id'] ?>" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($admin['role'] === 'administrator'): ?>
                                    <button onclick="demoteAdmin(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['full_name']) ?>')" class="px-3 py-1.5 bg-red-100 dark:bg-red-900/40 hover:bg-red-200 dark:hover:bg-red-900/60 text-red-700 dark:text-red-300 rounded-lg text-sm font-medium transition-colors">
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
function demoteAdmin(userId, userName) {
    if (confirm(`Are you sure you want to demote "${userName}" from Administrator to Staff?`)) {
        fetch('<?php echo BASE_URL; ?>/modules/core/api/admin-management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'demote',
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User demoted successfully');
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
