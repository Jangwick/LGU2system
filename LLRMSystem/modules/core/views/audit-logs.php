<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/middleware/PermissionMiddleware.php';

// Check permissions
$permissions = new PermissionMiddleware(getDatabase());
$permissions->requireLogin();
$permissions->requirePermission('admin.manage');

require_once __DIR__ . '/../controllers/SuperAdminController.php';
$controller = new SuperAdminController();

// Get filter parameters
$filters = [
    'user_id' => $_GET['user_id'] ?? null,
    'action' => $_GET['action'] ?? null,
    'table_name' => $_GET['table_name'] ?? null,
    'date_from' => $_GET['date_from'] ?? null,
    'date_to' => $_GET['date_to'] ?? null,
    'search' => $_GET['search'] ?? null
];

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;

try {
    $result = $controller->getAuditLogs($filters, $page, $perPage);
    $logs = $result['logs'];
    $total = $result['total'];
    $totalPages = $result['totalPages'];
    $users = $result['users'];
    $actions = $result['actions'];
    $tables = $result['tables'];
} catch (Exception $e) {
    error_log("Error getting audit logs: " . $e->getMessage());
    $logs = [];
    $total = 0;
    $totalPages = 0;
    $users = [];
    $actions = [];
    $tables = [];
}

// Get session timeout from system config
$editableConfig = $controller->getEditableConfig();
$sessionTimeout = (intval($editableConfig['session_timeout'] ?? 2)) * 60;

$pageTitle = 'Audit Logs';
$currentPage = 'audit-logs';
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
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">Audit Logs</h1>
                    <p class="text-red-100">View all system activity and audit trails</p>
                </div>
                <div class="flex flex-wrap gap-3 animate-slide-in-right">
                    <a href="<?php echo BASE_URL; ?>/modules/core/api/audit-logs.php?action=export_csv&<?php echo http_build_query($filters); ?>" class="flex items-center px-6 py-2.5 bg-white hover:bg-red-50 text-red-600 rounded-xl font-bold transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95 shadow-sm border border-white/10">
                        <i class="bi bi-download mr-2"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 mb-6 animate-fade-in-up">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                    <input type="text" id="searchInput" placeholder="Description, user, or email" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">User</label>
                    <select id="userFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <option value="">All Users</option>
                        <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= ($filters['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Action</label>
                    <select id="actionFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <option value="">All Actions</option>
                        <?php foreach ($actions as $action): ?>
                        <option value="<?= htmlspecialchars($action) ?>" <?= ($filters['action'] ?? '') === $action ? 'selected' : '' ?>>
                            <?= htmlspecialchars($action) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Table</label>
                    <select id="tableFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <option value="">All Tables</option>
                        <?php foreach ($tables as $table): ?>
                        <option value="<?= htmlspecialchars($table) ?>" <?= ($filters['table_name'] ?? '') === $table ? 'selected' : '' ?>>
                            <?= htmlspecialchars($table) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date From</label>
                    <input type="date" id="dateFrom" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date To</label>
                    <input type="date" id="dateTo" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            <div class="flex gap-3 mt-4">
                <button onclick="applyFilters()" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                    <i class="bi bi-search mr-2"></i> Apply Filters
                </button>
                <button onclick="clearFilters()" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors">
                    <i class="bi bi-x-circle mr-2"></i> Clear
                </button>
            </div>
        </div>

        <!-- Audit Logs Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 animate-fade-in-up animation-delay-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Activity Logs</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <?= count($logs) ?> of <?= $total ?> results
                </span>
            </div>

            <?php if (empty($logs)): ?>
            <div class="text-center py-12">
                <i class="bi bi-journal-x text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400">No audit logs found.</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Date/Time</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">User</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Action</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Table</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">Description</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300 text-sm">
                                <?= date('M d, Y g:i A', strtotime($log['created_at'])) ?>
                            </td>
                            <td class="py-4 px-4">
                                <div>
                                    <span class="font-medium text-gray-800 dark:text-white"><?= htmlspecialchars($log['full_name'] ?? $log['username'] ?? 'Unknown') ?></span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block"><?= htmlspecialchars($log['email'] ?? '') ?></span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 rounded-full text-xs font-bold">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300">
                                <?= htmlspecialchars($log['table_name']) ?>
                            </td>
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300 max-w-md truncate" title="<?= htmlspecialchars($log['description']) ?>">
                                <?= htmlspecialchars(substr($log['description'], 0, 100)) ?><?= strlen($log['description']) > 100 ? '...' : '' ?>
                            </td>
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300 text-sm font-mono">
                                <?= htmlspecialchars($log['ip_address']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="flex justify-center items-center gap-2 mt-6">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&<?= http_build_query($filters) ?>" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    Previous
                </a>
                <?php endif; ?>

                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);

                if ($startPage > 1) {
                    echo '<a href="?page=1&' . http_build_query($filters) . '" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">1</a>';
                    if ($startPage > 2) echo '<span class="px-2">...</span>';
                }

                for ($i = $startPage; $i <= $endPage; $i++) {
                    if ($i == $page) {
                        echo '<span class="px-4 py-2 bg-red-600 text-white rounded-lg font-bold">' . $i . '</span>';
                    } else {
                        echo '<a href="?page=' . $i . '&' . http_build_query($filters) . '" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">' . $i . '</a>';
                    }
                }

                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) echo '<span class="px-2">...</span>';
                    echo '<a href="?page=' . $totalPages . '&' . http_build_query($filters) . '" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">' . $totalPages . '</a>';
                }
                ?>

                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&<?= http_build_query($filters) ?>" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    Next
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
function applyFilters() {
    const params = new URLSearchParams();
    
    const search = document.getElementById('searchInput').value;
    const userId = document.getElementById('userFilter').value;
    const action = document.getElementById('actionFilter').value;
    const tableName = document.getElementById('tableFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;

    if (search) params.set('search', search);
    if (userId) params.set('user_id', userId);
    if (action) params.set('action', action);
    if (tableName) params.set('table_name', tableName);
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo) params.set('date_to', dateTo);

    window.location.href = '?' + params.toString();
}

function clearFilters() {
    window.location.href = '?';
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

<!-- Mobile Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 md:hidden opacity-0 pointer-events-none transition-all duration-300 ease-out"></div>

<!-- Mobile Sidebar -->
<div id="mobile-sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full md:hidden w-72 bg-gradient-to-b from-red-800 to-red-900 text-white z-50 transition-transform duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] overflow-hidden flex flex-col shadow-2xl">
    <!-- Mobile sidebar header -->
    <div class="p-4 border-b border-red-700/50">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="bg-white rounded-full p-1.5 shadow-lg">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Valenzuela Logo" class="w-9 h-9 object-contain">
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-tight">LRMS</h1>
                    <p class="text-xs text-red-200">Legislative Records</p>
                </div>
            </div>
            <button id="close-mobile-sidebar" class="text-white/80 p-2 hover:bg-red-700/50 hover:text-white rounded-lg transition-all duration-200 hover:rotate-90">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <nav class="flex-1 py-4 px-3 overflow-y-auto">
        <?php
        $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
        ?>

        <!-- Dashboard -->
        <a href="<?php echo DASHBOARD_INDEX_URL; ?>" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
            <i class="bi bi-speedometer2 mr-3 text-lg"></i>
            <span>Dashboard</span>
        </a>

        <!-- Documents Section -->
        <div class="mt-4 mb-2 px-4">
            <p class="text-xs font-semibold text-red-300/80 uppercase tracking-wider">Documents</p>
        </div>

        <a href="<?php echo DOCUMENTS_INDEX_URL; ?>" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
            <i class="bi bi-file-earmark-text mr-3 text-lg"></i>
            <span>All Documents</span>
        </a>

        <a href="<?php echo SEARCH_URL; ?>/views/index.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
            <i class="bi bi-search mr-3 text-lg"></i>
            <span>Advanced Search</span>
        </a>

        <!-- Super Admin Section - Super Admin Only -->
        <?php if ($userRole === 'super_admin'): ?>
        <div class="mt-4 mb-2 px-4">
            <p class="text-xs font-semibold text-purple-300/80 uppercase tracking-wider">Super Admin</p>
        </div>

        <a href="<?php echo CORE_URL; ?>/views/admin-management.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
            <i class="bi bi-shield-lock mr-3 text-lg"></i>
            <span>Admin Management</span>
        </a>

        <a href="<?php echo CORE_URL; ?>/views/system-config.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
            <i class="bi bi-gear mr-3 text-lg"></i>
            <span>System Config</span>
        </a>

        <a href="<?php echo CORE_URL; ?>/views/database-backup.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
            <i class="bi bi-database mr-3 text-lg"></i>
            <span>Database Backup</span>
        </a>

        <a href="<?php echo CORE_URL; ?>/views/audit-logs.php" class="flex items-center px-4 py-3 text-white bg-red-700 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
            <i class="bi bi-journal-text mr-3 text-lg"></i>
            <span>Audit Logs</span>
        </a>
        <?php endif; ?>
    </nav>
</div>

<script>
// Mobile Sidebar Functionality
const mobileMenuBtn = document.getElementById('mobile-menu-btn');
const mobileSidebar = document.getElementById('mobile-sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');
const closeMobileSidebar = document.getElementById('close-mobile-sidebar');

if (mobileMenuBtn && mobileSidebar) {
    mobileMenuBtn.addEventListener('click', function() {
        // Animate overlay
        sidebarOverlay.classList.remove('opacity-0', 'pointer-events-none');
        sidebarOverlay.classList.add('opacity-100', 'pointer-events-auto');

        // Animate sidebar with stagger effect for menu items
        setTimeout(() => {
            mobileSidebar.classList.remove('-translate-x-full');
            mobileSidebar.classList.add('translate-x-0');

            // Animate menu items
            const menuItems = mobileSidebar.querySelectorAll('nav a, nav > div');
            menuItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.3s ease-out';
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, 50 + (index * 30));
            });
        }, 10);
    });
}

if (closeMobileSidebar && mobileSidebar) {
    closeMobileSidebar.addEventListener('click', function() {
        mobileSidebar.classList.remove('translate-x-0');
        mobileSidebar.classList.add('-translate-x-full');
        sidebarOverlay.classList.remove('opacity-100', 'pointer-events-auto');
        sidebarOverlay.classList.add('opacity-0', 'pointer-events-none');
    });
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', function() {
        mobileSidebar.classList.remove('translate-x-0');
        mobileSidebar.classList.add('-translate-x-full');
        sidebarOverlay.classList.remove('opacity-100', 'pointer-events-auto');
        sidebarOverlay.classList.add('opacity-0', 'pointer-events-none');
    });
}
</script>
</div>
