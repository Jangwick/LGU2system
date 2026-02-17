<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../controllers/UserController.php';

// Check authentication & admin access
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}
if (!isAdmin()) {
    $_SESSION['flash_error'] = 'Access denied. Admin privileges required.';
    redirectToDashboard();
}

$controller = new UserController();
$data = $controller->index();
$stats = $controller->getStatistics();

$pageTitle = 'User Management';
$currentPage = 'users';
$breadcrumbs = [
    ['label' => 'Administration', 'url' => '#'],
    ['label' => 'User Management']
];

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<!-- Sidebar -->
<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Top Navbar -->
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-gray-950 p-3 md:p-6 custom-scrollbar">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4 animate-fade-in">
                <div class="flex items-center">
                    <i class="bi bi-check-circle text-green-500 mr-2 text-lg"></i>
                    <span class="text-green-700 dark:text-green-300 font-medium"><?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></span>
                </div>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4 animate-fade-in">
                <div class="flex items-center">
                    <i class="bi bi-exclamation-circle text-red-500 mr-2 text-lg"></i>
                    <span class="text-red-700 dark:text-red-300 font-medium"><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Header Banner -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white transform transition-all duration-500 ease-in-out animate-fade-in relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl transition-opacity duration-500 dark:opacity-5"></div>
            
            <div class="relative flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight transition-all duration-500">
                        User Management
                    </h1>
                    <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium transition-all duration-500">
                        Manage system users, roles, and permissions.
                    </p>
                </div>
                
                <div class="shrink-0">
                    <button type="button" onclick="openCreateModal()" class="!bg-white !text-red-600 hover:!bg-gray-50 px-6 py-2.5 rounded-xl font-bold shadow-lg transition-all duration-500 transform hover:-translate-y-0.5 flex items-center group border border-red-600">
                        <i class="bi bi-person-plus mr-2 transition-transform group-hover:rotate-12"></i>
                        Add New User
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 animate-fade-in-up">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-red-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Total Users</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo $stats['total_users']; ?></p>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-full p-2.5">
                        <i class="bi bi-people-fill text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-green-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Active</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $stats['active_users']; ?></p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-full p-2.5">
                        <i class="bi bi-person-check-fill text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-indigo-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">New (7 days)</p>
                        <p class="text-2xl font-bold text-indigo-600"><?php echo $stats['recent_users']; ?></p>
                    </div>
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-full p-2.5">
                        <i class="bi bi-person-plus-fill text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-purple-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Administrators</p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo $stats['admin_count']; ?></p>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-full p-2.5">
                        <i class="bi bi-shield-lock-fill text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 mb-6 animate-fade-in-up" style="animation-delay: 100ms;">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="relative group">
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1 block">Search Users</label>
                    <div class="relative">
                        <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="<?php echo e($data['filters']['search'] ?? ''); ?>" 
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all outline-none" 
                               placeholder="Name, email, username...">
                    </div>
                </div>
                
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1 block">Role</label>
                    <select name="role" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition-all">
                        <option value="">All Roles</option>
                        <option value="admin" <?php echo ($data['filters']['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="secretary" <?php echo ($data['filters']['role'] ?? '') === 'secretary' ? 'selected' : ''; ?>>Secretary</option>
                        <option value="councilor" <?php echo ($data['filters']['role'] ?? '') === 'councilor' ? 'selected' : ''; ?>>Councilor</option>
                        <option value="viewer" <?php echo ($data['filters']['role'] ?? '') === 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                    </select>
                </div>
                
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1 block">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition-all">
                        <option value="">All Statuses</option>
                        <option value="1" <?php echo ($data['filters']['status'] ?? '') === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo ($data['filters']['status'] ?? '') === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-red-700 dark:bg-red-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-red-800 dark:hover:bg-red-700 transition-all flex items-center justify-center shadow-sm">
                        <i class="bi bi-filter mr-2"></i> Apply
                    </button>
                    <a href="index.php" class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-all border border-gray-200 dark:border-gray-700" title="Clear Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md overflow-hidden animate-fade-in-up" style="animation-delay: 200ms;">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/80">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Users List
                        <span class="ml-2 px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-full"><?php echo number_format($data['total']); ?> users</span>
                    </h2>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Showing <?php echo (($data['page'] - 1) * $data['perPage']) + 1; ?> to <?php echo min($data['page'] * $data['perPage'], $data['total']); ?>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Position</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <?php if (empty($data['users'])): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <i class="bi bi-people text-5xl mb-4 block opacity-20"></i>
                                    <p class="text-lg">No users found matching your criteria.</p>
                                    <a href="index.php" class="text-red-600 font-bold hover:underline">Clear all filters</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['users'] as $user): ?>
                                <tr class="hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center transform transition-all duration-200 group-hover:scale-110">
                                                <?php if (!empty($user['profile_picture'])): ?>
                                                    <img src="<?php echo BASE_URL; ?>/storage/profiles/<?php echo e($user['profile_picture']); ?>" class="w-10 h-10 rounded-full object-cover">
                                                <?php else: ?>
                                                    <span class="text-red-600 font-bold text-sm"><?php echo strtoupper(substr($user['full_name'], 0, 2)); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-bold text-gray-900 dark:text-white group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors"><?php echo e($user['full_name']); ?></div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($user['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $roleClass = match(strtolower($user['role'] ?? '')) {
                                            'admin', 'administrator' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                                            'secretary' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                            'councilor' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                            'viewer' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                        };
                                        ?>
                                        <span class="px-3 py-1 text-xs font-bold rounded-full uppercase tracking-tighter <?php echo $roleClass; ?>">
                                            <?php echo ucfirst($user['role'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo e($user['position'] ?? '-'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($user['is_active']): ?>
                                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Active</span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo formatDate($user['created_at']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button type="button" onclick="editUser(<?php echo $user['id']; ?>)" 
                                               class="bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-amber-500 hover:text-white hover:border-amber-500 transition-all shadow-sm hover:shadow-md" 
                                               title="Edit User">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <button type="button" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo e($user['full_name']); ?>')" 
                                               class="bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-red-600 hover:text-white hover:border-red-600 transition-all shadow-sm hover:shadow-md" 
                                               title="Delete User">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($data['totalPages'] > 1): ?>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/80">
                    <nav class="flex justify-center">
                        <ul class="flex items-center space-x-2">
                            <li>
                                <a href="?page=<?php echo $data['page'] - 1; ?>&<?php echo http_build_query($data['filters']); ?>" 
                                   class="<?php echo $data['page'] <= 1 ? 'pointer-events-none opacity-50' : 'hover:bg-gray-200 dark:hover:bg-gray-700'; ?> px-3 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 transition-colors">
                                    Previous
                                </a>
                            </li>
                            
                            <?php for ($i = max(1, $data['page'] - 2); $i <= min($data['totalPages'], $data['page'] + 2); $i++): ?>
                                <li>
                                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($data['filters']); ?>" 
                                       class="<?php echo $i == $data['page'] ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'; ?> px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 transition-colors font-bold">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li>
                                <a href="?page=<?php echo $data['page'] + 1; ?>&<?php echo http_build_query($data['filters']); ?>" 
                                   class="<?php echo $data['page'] >= $data['totalPages'] ? 'pointer-events-none opacity-50' : 'hover:bg-gray-200 dark:hover:bg-gray-700'; ?> px-3 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 transition-colors">
                                    Next
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<!-- Create/Edit User Modal -->
<div id="userModal" class="hidden fixed inset-0 bg-gray-900/50 dark:bg-black/70 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border border-gray-200 dark:border-gray-700/50 w-full max-w-2xl shadow-2xl rounded-2xl bg-white dark:bg-gray-900 backdrop-blur-md mb-20">
        <div class="flex justify-between items-center mb-6">
            <h3 id="modalTitle" class="text-xl font-bold text-gray-900 dark:text-white">Add New User</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        
        <form id="userForm" onsubmit="saveUser(event)">
            <input type="hidden" id="userId" name="id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" id="userFullName" name="full_name" required 
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="userEmail" name="email" required 
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username</label>
                    <input type="text" id="userUsername" name="username" 
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password <span id="passwordRequired" class="text-red-500">*</span></label>
                    <input type="password" id="userPassword" name="password" 
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave blank to keep current password (when editing)</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role <span class="text-red-500">*</span></label>
                    <select id="userRole" name="role" required 
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                        <option value="viewer">Viewer</option>
                        <option value="councilor">Councilor</option>
                        <option value="secretary">Secretary</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Position</label>
                    <input type="text" id="userPosition" name="position" 
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all"
                           placeholder="e.g. Councilor, Secretary General">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                    <input type="text" id="userDepartment" name="department" 
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all"
                           placeholder="e.g. Legislative Affairs">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status <span class="text-red-500">*</span></label>
                    <select id="userStatus" name="is_active" required 
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeModal()" class="px-6 py-2.5 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg font-semibold transition-all">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition-all shadow-md hover:shadow-lg">
                    <i class="bi bi-save mr-2"></i> Save User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-gray-900/50 dark:bg-black/70 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-1/3 mx-auto p-6 border border-gray-200 dark:border-gray-700 w-full max-w-md shadow-2xl rounded-2xl bg-white dark:bg-gray-900">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                <i class="bi bi-exclamation-triangle-fill text-red-600 text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Delete User</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to delete <span id="deleteUserName" class="font-bold text-red-600"></span>? This action cannot be undone.</p>
            <input type="hidden" id="deleteUserId">
            <div class="flex justify-center gap-3">
                <button onclick="closeDeleteModal()" class="px-6 py-2.5 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg font-semibold transition-all">
                    Cancel
                </button>
                <button onclick="confirmDelete()" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition-all shadow-md">
                    <i class="bi bi-trash mr-2"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('userPassword').required = true;
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('userModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('userModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function editUser(id) {
    fetch(App.apiUrl('users', `get-user.php?id=${id}`))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const u = data.user;
                document.getElementById('modalTitle').textContent = 'Edit User';
                document.getElementById('userId').value = u.id;
                document.getElementById('userFullName').value = u.full_name || '';
                document.getElementById('userEmail').value = u.email || '';
                document.getElementById('userUsername').value = u.username || '';
                document.getElementById('userPassword').value = '';
                document.getElementById('userPassword').required = false;
                document.getElementById('passwordRequired').style.display = 'none';
                document.getElementById('userRole').value = u.role || 'viewer';
                document.getElementById('userPosition').value = u.position || '';
                document.getElementById('userDepartment').value = u.department || '';
                document.getElementById('userStatus').value = u.is_active ? '1' : '0';
                document.getElementById('userModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                showToast('Error: ' + (data.error || 'Failed to load user'), 'error');
            }
        })
        .catch(error => {
            showToast('Network error: ' + error.message, 'error');
        });
}

function saveUser(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const id = document.getElementById('userId').value;
    const url = id ? App.apiUrl('users', 'update-user.php') : App.apiUrl('users', 'create-user.php');
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || (id ? 'User updated!' : 'User created!'), 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('Error: ' + (data.error || 'Operation failed'), 'error');
        }
    })
    .catch(error => {
        showToast('Network error: ' + error.message, 'error');
    });
}

function deleteUser(id, name) {
    document.getElementById('deleteUserId').value = id;
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function confirmDelete() {
    const id = document.getElementById('deleteUserId').value;
    
    fetch(App.apiUrl('users', 'delete-user.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('User deleted successfully!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('Error: ' + (data.error || 'Delete failed'), 'error');
        }
        closeDeleteModal();
    })
    .catch(error => {
        showToast('Network error: ' + error.message, 'error');
        closeDeleteModal();
    });
}

// Close modals on escape key
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeModal();
        closeDeleteModal();
    }
});

// Close modals on backdrop click
document.getElementById('userModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>
