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
$params = [
    'search' => $_GET['search'] ?? null,
    'role' => $_GET['role'] ?? null,
    'status' => $_GET['status'] ?? null,
    'department' => $_GET['department'] ?? null,
    'sort_by' => $_GET['sort_by'] ?? 'created_at',
    'sort_order' => $_GET['sort_order'] ?? 'DESC',
    'page' => $_GET['page'] ?? 1,
    'per_page' => $_GET['per_page'] ?? 10
];

// Get session timeout from system config
$editableConfig = $controller->getEditableConfig();
$sessionTimeout = (intval($editableConfig['session_timeout'] ?? 2)) * 60; // Convert minutes to seconds

try {
    $result = $controller->getAdministrators($params);
    $administrators = $result['data'];
    $total = $result['total'];
    $page = $result['page'];
    $perPage = $result['per_page'];
    $totalPages = $result['total_pages'];
} catch (Exception $e) {
    error_log("Error getting administrators: " . $e->getMessage());
    $administrators = [];
    $total = 0;
    $page = 1;
    $perPage = 10;
    $totalPages = 1;
}

$pageTitle = 'Administrator Management';
$currentPage = 'admin-management';
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
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">Administrator Management</h1>
                    <p class="text-red-100">Manage administrator and Super Admin accounts</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="<?php echo USERS_URL; ?>/views/create.php" class="flex items-center px-6 py-2.5 bg-white hover:bg-red-50 text-red-600 rounded-xl font-bold transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95 shadow-sm border border-white/10">
                        <i class="bi bi-person-plus mr-2"></i> Add Administrator
                    </a>
                    <button onclick="exportToCSV()" class="flex items-center px-6 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-xl font-bold transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95 shadow-sm border border-white/20">
                        <i class="bi bi-download mr-2"></i> Export CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                    <input type="text" id="searchInput" placeholder="Name, email, or employee ID" value="<?= htmlspecialchars($params['search'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                    <select id="roleFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <option value="">All Roles</option>
                        <option value="administrator" <?= ($params['role'] ?? '') === 'administrator' ? 'selected' : '' ?>>Administrator</option>
                        <option value="super_admin" <?= ($params['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <option value="">All Status</option>
                        <option value="active" <?= ($params['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($params['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                    <input type="text" id="departmentFilter" placeholder="Filter by department" value="<?= htmlspecialchars($params['department'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <button onclick="applyFilters()" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                    <i class="bi bi-search mr-2"></i> Apply Filters
                </button>
                <button onclick="clearFilters()" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors">
                    <i class="bi bi-x-circle mr-2"></i> Clear
                </button>
            </div>
        </div>

        <!-- Administrators Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 animate-fade-in-up animation-delay-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Administrators & Super Admins</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <?= count($administrators) ?> of <?= $total ?> results
                </span>
            </div>

            <?php if (empty($administrators)): ?>
            <div class="text-center py-12">
                <i class="bi bi-person-gear text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400">No administrators found. Create your first administrator.</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400 cursor-pointer hover:text-red-600" onclick="sortBy('full_name')">
                                Name <i class="bi bi-arrow-down-up ml-1"></i>
                            </th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400 cursor-pointer hover:text-red-600" onclick="sortBy('email')">
                                Email <i class="bi bi-arrow-down-up ml-1"></i>
                            </th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400 cursor-pointer hover:text-red-600" onclick="sortBy('role')">
                                Role <i class="bi bi-arrow-down-up ml-1"></i>
                            </th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400 cursor-pointer hover:text-red-600" onclick="sortBy('status')">
                                Status <i class="bi bi-arrow-down-up ml-1"></i>
                            </th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400 cursor-pointer hover:text-red-600" onclick="sortBy('department')">
                                Department <i class="bi bi-arrow-down-up ml-1"></i>
                            </th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-400 cursor-pointer hover:text-red-600" onclick="sortBy('last_login')">
                                Last Login <i class="bi bi-arrow-down-up ml-1"></i>
                            </th>
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
                                <?= isset($admin['last_login']) && $admin['last_login'] ? date('M d, Y g:i A', strtotime($admin['last_login'])) : 'Never' ?>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex gap-2">
                                    <a href="<?php echo CORE_URL; ?>/views/edit-administrator.php?id=<?= $admin['id'] ?>" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($admin['role'] === 'administrator'): ?>
                                    <button onclick="promoteToSuperAdmin(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['full_name']) ?>')" class="px-3 py-1.5 bg-purple-100 dark:bg-purple-900/40 hover:bg-purple-200 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 rounded-lg text-sm font-medium transition-colors" title="Promote to Super Admin">
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                    <button onclick="demoteAdmin(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['full_name']) ?>')" class="px-3 py-1.5 bg-orange-100 dark:bg-orange-900/40 hover:bg-orange-200 dark:hover:bg-orange-900/60 text-orange-700 dark:text-orange-300 rounded-lg text-sm font-medium transition-colors" title="Demote to Staff">
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($admin['status'] === 'active' && $admin['role'] !== 'super_admin'): ?>
                                    <button onclick="deactivateUser(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['full_name']) ?>')" class="px-3 py-1.5 bg-red-100 dark:bg-red-900/40 hover:bg-red-200 dark:hover:bg-red-900/60 text-red-700 dark:text-red-300 rounded-lg text-sm font-medium transition-colors" title="Deactivate">
                                        <i class="bi bi-dash-circle"></i>
                                    </button>
                                    <?php elseif ($admin['status'] === 'inactive' && $admin['role'] !== 'super_admin'): ?>
                                    <button onclick="activateUser(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['full_name']) ?>')" class="px-3 py-1.5 bg-green-100 dark:bg-green-900/40 hover:bg-green-200 dark:hover:bg-green-900/60 text-green-700 dark:text-green-300 rounded-lg text-sm font-medium transition-colors" title="Activate">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($admin['role'] !== 'super_admin'): ?>
                                    <button onclick="deleteUser(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['full_name']) ?>')" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-red-200 dark:hover:bg-red-900/60 text-gray-700 dark:text-gray-300 hover:text-red-700 rounded-lg text-sm font-medium transition-colors" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="flex justify-between items-center mt-6">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Page <?= $page ?> of <?= $totalPages ?>
                </div>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                    <button onclick="goToPage(<?= $page - 1 ?>)" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                        Previous
                    </button>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                    <button class="px-4 py-2 bg-red-600 text-white rounded-lg">
                        <?= $i ?>
                    </button>
                    <?php elseif ($i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
                    <button onclick="goToPage(<?= $i ?>)" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                        <?= $i ?>
                    </button>
                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                    <span class="px-4 py-2 text-gray-400">...</span>
                    <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                    <button onclick="goToPage(<?= $page + 1 ?>)" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                        Next
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
let currentSortBy = '<?= $params['sort_by'] ?>';
let currentSortOrder = '<?= $params['sort_order'] ?>';

function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const role = document.getElementById('roleFilter').value;
    const status = document.getElementById('statusFilter').value;
    const department = document.getElementById('departmentFilter').value;

    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (role) params.append('role', role);
    if (status) params.append('status', status);
    if (department) params.append('department', department);
    params.append('sort_by', currentSortBy);
    params.append('sort_order', currentSortOrder);

    window.location.href = '?' + params.toString();
}

function clearFilters() {
    window.location.href = '?';
}

function sortBy(column) {
    if (currentSortBy === column) {
        currentSortOrder = currentSortOrder === 'ASC' ? 'DESC' : 'ASC';
    } else {
        currentSortBy = column;
        currentSortOrder = 'ASC';
    }

    const params = new URLSearchParams(window.location.search);
    params.set('sort_by', currentSortBy);
    params.set('sort_order', currentSortOrder);

    window.location.href = '?' + params.toString();
}

function goToPage(page) {
    const params = new URLSearchParams(window.location.search);
    params.set('page', page);

    window.location.href = '?' + params.toString();
}

function promoteToSuperAdmin(userId, userName) {
    if (confirm(`Are you sure you want to promote "${userName}" to Super Admin?`)) {
        fetch('<?php echo BASE_URL; ?>/modules/core/api/admin-management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'promote_to_super',
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User promoted to Super Admin successfully');
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

function activateUser(userId, userName) {
    if (confirm(`Are you sure you want to activate "${userName}"?`)) {
        fetch('<?php echo BASE_URL; ?>/modules/core/api/admin-management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'activate',
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User activated successfully');
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

function deactivateUser(userId, userName) {
    if (confirm(`Are you sure you want to deactivate "${userName}"?`)) {
        fetch('<?php echo BASE_URL; ?>/modules/core/api/admin-management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'deactivate',
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deactivated successfully');
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

function deleteUser(userId, userName) {
    if (confirm(`Are you sure you want to delete "${userName}"? This action cannot be undone.`)) {
        fetch('<?php echo BASE_URL; ?>/modules/core/api/admin-management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'delete',
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully');
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

function exportToCSV() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.location.href = '<?php echo BASE_URL; ?>/modules/core/api/admin-management.php?' + params.toString();
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

        <a href="<?php echo CORE_URL; ?>/views/admin-management.php" class="flex items-center px-4 py-3 text-white bg-red-700 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
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

        <a href="<?php echo CORE_URL; ?>/views/audit-logs.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1">
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
