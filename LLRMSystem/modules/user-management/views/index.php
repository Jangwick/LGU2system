<?php
session_start();
require_once __DIR__ . '/../controllers/UserController.php';

$controller = new UserController();
$data = $controller->index();
$stats = $controller->getStatistics();

$pageTitle = 'User Management';
$currentPage = 'users';
require_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php require_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php require_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-6 text-white transform hover:scale-[1.01] transition-all duration-300 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="animate-slide-in-left">
                    <h1 class="text-2xl font-bold mb-2">User Management</h1>
                    <p class="text-red-100 animation-delay-100">Manage system users and permissions</p>
                </div>
                <button type="button" onclick="openCreateModal()" class="no-ripple inline-flex items-center justify-center bg-white text-red-600 dark:!bg-white dark:!text-red-700 dark:hover:!bg-red-50 px-6 py-3 rounded-xl font-bold hover:bg-red-50 hover:shadow-lg transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-md min-w-[160px] h-12 flex-shrink-0 animate-slide-in-right border-2 border-red-600">
                    <i class="bi bi-person-plus-fill mr-2 text-xl"></i> Add User
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-100 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-people-fill text-blue-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 transition-colors group-hover:text-blue-600">Total Users</div>
                        <div class="text-2xl font-bold text-gray-900 transform transition-all group-hover:scale-110"><?php echo number_format($stats['total_users']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-200 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-person-check-fill text-green-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 transition-colors group-hover:text-green-600">Active Users</div>
                        <div class="text-2xl font-bold text-gray-900 transform transition-all group-hover:scale-110"><?php echo number_format($stats['active_users']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-300 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-calendar-plus text-indigo-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 transition-colors group-hover:text-indigo-600">New (7 days)</div>
                        <div class="text-2xl font-bold text-gray-900 transform transition-all group-hover:scale-110"><?php echo number_format($stats['recent_users']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-400 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-shield-check text-amber-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 transition-colors group-hover:text-amber-600">Administrators</div>
                        <div class="text-2xl font-bold text-gray-900 transform transition-all group-hover:scale-110">
                            <?php 
                            $adminCount = 0;
                            foreach ($stats['users_by_role'] as $roleData) {
                                if ($roleData['role'] === 'administrator') {
                                    $adminCount = $roleData['count'];
                                    break;
                                }
                            }
                            echo number_format($adminCount);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-500 relative z-10">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <div class="relative custom-select-container">
                        <div id="role-filter-trigger" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all cursor-pointer flex items-center justify-between" style="min-height: 48px;">
                            <span id="role-filter-value">All Roles</span>
                            <i class="bi bi-chevron-down text-gray-400"></i>
                        </div>
                        <input type="hidden" name="role" id="role-filter-input" value="<?php echo $data['filters']['role'] ?? ''; ?>">
                    </div>
                </div>
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <div class="relative custom-select-container">
                        <div id="status-filter-trigger" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all cursor-pointer flex items-center justify-between" style="min-height: 48px;">
                            <span id="status-filter-value">All Status</span>
                            <i class="bi bi-chevron-down text-gray-400"></i>
                        </div>
                        <input type="hidden" name="status" id="status-filter-input" value="<?php echo $data['filters']['status'] ?? ''; ?>">
                    </div>
                </div>
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <div class="relative custom-select-container">
                        <div id="department-filter-trigger" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all cursor-pointer flex items-center justify-between" style="min-height: 48px;">
                            <span id="department-filter-value">All Departments</span>
                            <i class="bi bi-chevron-down text-gray-400"></i>
                        </div>
                        <input type="hidden" name="department" id="department-filter-input" value="<?php echo $data['filters']['department'] ?? ''; ?>">
                    </div>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full btn-primary bg-red-600 dark:bg-red-700 text-white">
                        <i class="bi bi-funnel mr-1"></i> Filter
                    </button>
                </div>
            </form>
            
            <!-- Filter Dropdowns -->
            <div id="role-filter-dropdown" class="hidden absolute bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl z-[1000] max-h-64 overflow-y-auto w-64">
                <div class="p-2 space-y-1">
                    <div class="role-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="">All Roles</div>
                    <div class="role-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="officer">Officer</div>
                    <div class="role-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="staff">Staff</div>
                    <div class="role-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="viewer">Viewer</div>
                </div>
            </div>
            <div id="status-filter-dropdown" class="hidden absolute bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl z-[1000] max-h-64 overflow-y-auto w-64">
                <div class="p-2 space-y-1">
                    <div class="status-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="">All Status</div>
                    <div class="status-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="active">Active</div>
                    <div class="status-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="inactive">Inactive</div>
                    <div class="status-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="suspended">Suspended</div>
                </div>
            </div>
            <div id="department-filter-dropdown" class="hidden absolute bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl z-[1000] max-h-64 overflow-y-auto w-64">
                <div class="p-2 space-y-1">
                    <div class="department-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="">All Departments</div>
                    <?php foreach ($data['departments'] as $dept): ?>
                        <div class="department-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="<?php echo htmlspecialchars($dept); ?>">
                            <?php echo htmlspecialchars($dept); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Search -->
            <form method="GET" class="mt-4">
                <div class="flex gap-2">
                    <input type="text" name="search" 
                           class="flex-1 input-field" 
                           placeholder="Search by name, email, username..." 
                           value="<?php echo htmlspecialchars($data['filters']['search'] ?? ''); ?>">
                    <button type="submit" class="btn-primary bg-red-600 dark:bg-red-700 text-white">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <?php if (!empty($data['filters']['search'])): ?>
                        <a href="?" class="btn-danger">
                            <i class="bi bi-x"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-600 relative z-0">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Users List
                        <span class="ml-2 px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-full"><?php echo number_format($data['total']); ?> users</span>
                    </h2>
                    <div class="text-sm text-gray-600">
                        Showing <?php echo (($data['page'] - 1) * $data['perPage']) + 1; ?> 
                        to <?php echo min($data['page'] * $data['perPage'], $data['total']); ?>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto drag-scroll" id="users-table-scroll">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employee ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (empty($data['users'])): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <i class="bi bi-people text-gray-400 text-5xl block mb-3"></i>
                                    <p class="text-gray-500">No users found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $rowIndex = 0; foreach ($data['users'] as $user): $rowIndex++; ?>
                                <tr class="hover:bg-gray-50 transition-all duration-200 hover:shadow-sm animate-fade-in-up" style="animation-delay: <?php echo 700 + ($rowIndex * 50); ?>ms;">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center transform transition-all duration-200 hover:scale-110">
                                                <i class="bi bi-person-fill text-blue-600 text-xl"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($user['full_name'] ?? $user['name']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (!empty($user['employee_id'])): ?>
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800">
                                                <i class="bi bi-person-vcard mr-1"></i>
                                                <?php echo htmlspecialchars($user['employee_id']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $userRole = strtolower(trim($user['role'] ?? ''));
                                        $roleClass = match($userRole) {
                                            'administrator', 'admin' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800 dark:border',
                                            'officer', 'manager' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800 dark:border',
                                            'staff' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800 dark:border',
                                            'viewer', 'user' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:border',
                                            default => 'bg-gray-100 text-gray-800 text-opacity-70 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:border'
                                        };
                                        ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $roleClass; ?> transition-transform duration-200 hover:scale-105">
                                            <?php
                                            if (empty($userRole)) {
                                                echo '<span class="flex items-center text-red-500 font-bold"><i class="bi bi-exclamation-triangle-fill mr-1"></i>Unassigned</span>';
                                            } else {
                                                echo htmlspecialchars(match($userRole) {
                                                    'administrator', 'admin' => 'Administrator',
                                                    'officer' => 'Officer',
                                                    'manager' => 'Manager',
                                                    'staff' => 'Staff',
                                                    'viewer', 'user' => 'Viewer',
                                                    default => ucfirst($userRole)
                                                });
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo htmlspecialchars($user['department'] ?? '-'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $statusLower = strtolower($user['status'] ?? '');
                                        $statusClass = match($statusLower) {
                                            'active' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800 dark:border',
                                            'inactive' => 'status-badge-inactive',
                                            'suspended' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800 dark:border',
                                            'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800 dark:border',
                                            default => 'status-badge-inactive'
                                        };
                                        $statusText = $statusLower === '' ? 'Inactive' : ucfirst($statusLower);
                                        ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?> transition-transform duration-200 hover:scale-105">
                                            <?php echo htmlspecialchars($statusText); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button type="button" onclick="viewUser(<?php echo $user['id']; ?>)" class="no-ripple inline-flex items-center justify-center bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 px-3 py-2 rounded-lg font-semibold transition-all duration-300 transform hover:scale-105 active:scale-95 mr-2 shadow-sm hover:shadow-md dark:shadow-none dark:hover:shadow-none">
                                            <i class="bi bi-eye mr-1"></i> View
                                        </button>
                                        <button type="button" onclick="editUser(<?php echo $user['id']; ?>)" class="no-ripple inline-flex items-center justify-center bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/50 px-3 py-2 rounded-lg font-semibold transition-all duration-300 transform hover:scale-105 active:scale-95 mr-2 shadow-sm hover:shadow-md dark:shadow-none dark:hover:shadow-none">
                                            <i class="bi bi-pencil mr-1"></i> Edit
                                        </button>
                                        <button type="button" onclick="deleteUser(<?php echo $user['id']; ?>)" class="no-ripple inline-flex items-center justify-center bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 px-3 py-2 rounded-lg font-semibold transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-sm hover:shadow-md dark:shadow-none dark:hover:shadow-none">
                                            <i class="bi bi-trash mr-1"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($data['totalPages'] > 1): ?>
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <nav class="flex justify-center">
                        <ul class="flex items-center space-x-2">
                            <li>
                                <a href="?page=<?php echo $data['page'] - 1; ?>&<?php echo http_build_query($data['filters']); ?>" 
                                   class="<?php echo $data['page'] <= 1 ? 'pointer-events-none opacity-50' : 'hover:bg-gray-200'; ?> px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 transition-colors">
                                    Previous
                                </a>
                            </li>
                            
                            <?php for ($i = max(1, $data['page'] - 2); $i <= min($data['totalPages'], $data['page'] + 2); $i++): ?>
                                <li>
                                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($data['filters']); ?>" 
                                       class="<?php echo $i == $data['page'] ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-200'; ?> px-4 py-2 rounded-lg border border-gray-300 transition-colors">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li>
                                <a href="?page=<?php echo $data['page'] + 1; ?>&<?php echo http_build_query($data['filters']); ?>" 
                                   class="<?php echo $data['page'] >= $data['totalPages'] ? 'pointer-events-none opacity-50' : 'hover:bg-gray-200'; ?> px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 transition-colors">
                                    Next
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </main>

<?php require_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<!-- Create/Edit User Modal -->
<div id="userModal" class="hidden fixed inset-0 bg-gray-900/50 dark:bg-black/70 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-end sm:items-center justify-center sm:p-4">
    <div id="userModalContent" class="relative mx-auto p-6 border border-gray-200 dark:border-gray-700/50 w-full max-w-2xl shadow-2xl rounded-t-3xl sm:rounded-2xl bg-white dark:bg-gray-800/95 backdrop-blur-md max-h-[92vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 sm:scale-95 opacity-0 sm:opacity-100">
        <div class="sm:hidden w-full flex justify-center pt-3 pb-1"><div class="w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div></div>
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                    <i id="modalIcon" class="bi bi-person-plus-fill text-red-600 dark:text-red-400 text-xl"></i>
                </div>
                <h3 id="modalTitle" class="text-2xl font-bold text-gray-900 dark:text-gray-100">Add New User</h3>
            </div>
            <button type="button" onclick="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-all">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>
        
        <form id="userForm" onsubmit="saveUser(event)" class="overflow-y-auto flex-1 min-h-0">
            <input type="hidden" id="userId" name="id">
            
            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div id="employeeIdGroup" class="hidden">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Employee ID</label>
                        <div class="relative">
                            <i class="bi bi-person-vcard absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" id="userEmployeeId" name="employee_id" class="w-full pl-11 pr-4 py-3 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 cursor-not-allowed focus:outline-none" readonly placeholder="Auto-generated on save">
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Full Name *</label>
                        <div class="relative">
                            <i class="bi bi-person absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" id="userName" name="name" required class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all" oninput="document.getElementById('userFullName').value = this.value" placeholder="Enter full name">
                            <input type="hidden" id="userFullName" name="full_name">
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Email *</label>
                        <div class="relative">
                            <i class="bi bi-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="email" id="userEmail" name="email" required class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all" placeholder="user@example.com">
                        </div>
                    </div>
                
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Username</label>
                        <div class="relative">
                            <i class="bi bi-at absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" id="userUsername" name="username" class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all" placeholder="Username">
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Password <span id="passwordRequired" class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="bi bi-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" id="userPassword" name="password" class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all" placeholder="Enter password">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Leave blank to keep current password (when editing)</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Role *</label>
                        <div class="relative">
                            <i class="bi bi-shield absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <select id="userRole" name="role" required class="w-full pl-11 pr-10 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all appearance-none cursor-pointer">
                                <option value="viewer">Viewer</option>
                                <option value="staff">Staff</option>
                                <option value="officer">Officer</option>
                            </select>
                            <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-2 font-medium">Only Super Admin can assign Administrator role</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Department</label>
                        <div class="relative">
                            <i class="bi bi-building absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" id="userDepartment" name="department" class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all" placeholder="Department name">
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Status *</label>
                    <div class="relative">
                        <i class="bi bi-toggle-on absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select id="userStatus" name="status" required class="w-full pl-11 pr-10 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all appearance-none cursor-pointer">
                            <option value="active">Active (Approved)</option>
                            <option value="pending">Pending Approval</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended (Denied)</option>
                        </select>
                        <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeModal()" class="no-ripple px-6 py-3 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl font-bold transition-all transform hover:scale-105 active:scale-95">
                    Cancel
                </button>
                <button type="submit" class="no-ripple px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold transition-all transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl flex items-center">
                    <i class="bi bi-check-circle mr-2"></i> Save User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View User Modal -->
<div id="viewUserModal" class="hidden fixed inset-0 bg-gray-900/50 dark:bg-black/70 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-end sm:items-center justify-center sm:p-4">
    <div id="viewUserModalContent" class="relative mx-auto p-6 border border-gray-200 dark:border-gray-700/50 w-full max-w-2xl shadow-2xl rounded-t-3xl sm:rounded-2xl bg-white dark:bg-gray-800/95 backdrop-blur-md max-h-[92vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 sm:scale-95 opacity-0 sm:opacity-100">
        <div class="sm:hidden w-full flex justify-center pt-3 pb-1"><div class="w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div></div>
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                    <i class="bi bi-person-fill text-red-600 dark:text-red-400 text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">User Details</h3>
            </div>
            <button type="button" onclick="closeViewModal()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-all">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>
        
        <div id="viewUserContent" class="space-y-5 overflow-y-auto flex-1 min-h-0">
            <!-- User details will be loaded here -->
        </div>
        
        <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="closeViewModal()" class="no-ripple px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold transition-all transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl">
                Close
            </button>
        </div>
    </div>
</div>

<script>
// Custom Dropdown Helper Function
function initCustomDropdown(triggerId, dropdownId, valueId, inputId, optionClass, defaultValue) {
    const trigger = document.getElementById(triggerId);
    const dropdown = document.getElementById(dropdownId);
    const valueDisplay = document.getElementById(valueId);
    const hiddenInput = document.getElementById(inputId);
    const options = document.querySelectorAll(optionClass);
    
    if (!trigger || !dropdown || !valueDisplay || !hiddenInput) return;
    
    // Set initial value
    const selectedValue = hiddenInput.value;
    if (selectedValue) {
        const selectedOption = document.querySelector(`${optionClass}[data-value="${selectedValue}"]`);
        if (selectedOption) {
            valueDisplay.textContent = selectedOption.textContent;
        }
    }
    
    // Toggle dropdown and position it
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        
        const isHidden = dropdown.classList.contains('hidden');
        
        if (isHidden) {
            // Position the dropdown below the trigger
            const rect = trigger.getBoundingClientRect();
            const filterContainer = document.querySelector('.relative.z-10');
            const containerRect = filterContainer ? filterContainer.getBoundingClientRect() : { top: 0, left: 0 };
            
            dropdown.style.top = (rect.bottom - containerRect.top + 4) + 'px';
            dropdown.style.left = (rect.left - containerRect.left) + 'px';
            
            dropdown.classList.remove('hidden');
        } else {
            dropdown.classList.add('hidden');
        }
    });
    
    // Handle option selection
    options.forEach(option => {
        option.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            const text = this.textContent;
            
            valueDisplay.textContent = text;
            hiddenInput.value = value;
            dropdown.classList.add('hidden');
            
            // Trigger filter change
            hiddenInput.closest('form').submit();
        });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

// Initialize custom dropdowns when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initCustomDropdown('role-filter-trigger', 'role-filter-dropdown', 'role-filter-value', 'role-filter-input', '.role-filter-option', 'All Roles');
    initCustomDropdown('status-filter-trigger', 'status-filter-dropdown', 'status-filter-value', 'status-filter-input', '.status-filter-option', 'All Status');
    initCustomDropdown('department-filter-trigger', 'department-filter-dropdown', 'department-filter-value', 'department-filter-input', '.department-filter-option', 'All Departments');
});

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('modalIcon').className = 'bi bi-person-plus-fill text-red-600 dark:text-red-400 text-xl';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('userEmployeeId').value = '';
    document.getElementById('employeeIdGroup').classList.add('hidden');
    document.getElementById('userFullName').value = '';
    document.getElementById('userPassword').required = true;
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('userModal').classList.remove('hidden');
    document.body.style.overflow='hidden';
    const uc=document.getElementById('userModalContent');
    setTimeout(()=>{if(uc){uc.classList.remove('translate-y-full','sm:scale-95','opacity-0');uc.classList.add('translate-y-0','sm:scale-100','opacity-100');}},10);
}

function closeModal() {
    const uc=document.getElementById('userModalContent');
    if(uc){uc.classList.add('translate-y-full','sm:scale-95','opacity-0');uc.classList.remove('translate-y-0','sm:scale-100','opacity-100');}
    setTimeout(()=>{document.getElementById('userModal').classList.add('hidden');document.body.style.overflow='';},300);
}

function editUser(id) {
    fetch(App.apiUrl('users', `get-user.php?id=${id}`))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalTitle').textContent = 'Edit User';
                document.getElementById('modalIcon').className = 'bi bi-pencil-square text-amber-600 dark:text-amber-400 text-xl';
                document.getElementById('userId').value = data.user.id;
                document.getElementById('userEmployeeId').value = data.user.employee_id || 'Auto-generated';
                document.getElementById('employeeIdGroup').classList.remove('hidden');
                document.getElementById('userName').value = data.user.full_name || data.user.name;
                document.getElementById('userFullName').value = data.user.full_name || data.user.name;
                document.getElementById('userEmail').value = data.user.email;
                document.getElementById('userUsername').value = data.user.username || '';
                document.getElementById('userPassword').value = '';
                document.getElementById('userPassword').required = false;
                document.getElementById('passwordRequired').style.display = 'none';
                document.getElementById('userRole').value = data.user.role;
                document.getElementById('userDepartment').value = data.user.department || '';
                document.getElementById('userStatus').value = data.user.status;
                document.getElementById('userModal').classList.remove('hidden');
            } else {
                alert('Error: ' + (data.error || 'Failed to load user'));
            }
        })
        .catch(error => {
            alert('Network error: ' + error);
        });
}

function viewUser(id) {
    fetch(App.apiUrl('users', `get-user.php?id=${id}`))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                const statusColors = {
                    'active': 'bg-green-100 text-green-800',
                    'inactive': 'bg-gray-100 text-gray-800',
                    'suspended': 'bg-red-100 text-red-800',
                    'pending': 'bg-amber-100 text-amber-800'
                };
                const statusClass = statusColors[user.status] || 'bg-gray-100 text-gray-800';
                
                const roleColors = {
                    'administrator': 'bg-purple-100 text-purple-800',
                    'officer': 'bg-blue-100 text-blue-800',
                    'staff': 'bg-green-100 text-green-800',
                    'viewer': 'bg-gray-100 text-gray-800'
                };
                const roleClass = roleColors[user.role] || 'bg-gray-100 text-gray-800';
                
                document.getElementById('viewUserContent').innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Full Name</label>
                            <div class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 font-medium">
                                ${user.full_name || user.name || 'N/A'}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Email</label>
                            <div class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 font-medium">
                                ${user.email || 'N/A'}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Employee ID</label>
                            <div class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 font-medium">
                                ${user.employee_id || 'N/A'}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Username</label>
                            <div class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 font-medium">
                                ${user.username || 'N/A'}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Role</label>
                            <div class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${roleClass}">
                                    ${user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'N/A'}
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Status</label>
                            <div class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                    ${user.status ? user.status.charAt(0).toUpperCase() + user.status.slice(1) : 'N/A'}
                                </span>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Department</label>
                            <div class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 font-medium">
                                ${user.department || 'N/A'}
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Created At</label>
                            <div class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 font-medium">
                                ${user.created_at ? new Date(user.created_at).toLocaleString() : 'N/A'}
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('viewUserModal').classList.remove('hidden');
                document.body.style.overflow='hidden';
                const vc=document.getElementById('viewUserModalContent');
                setTimeout(()=>{if(vc){vc.classList.remove('translate-y-full','sm:scale-95','opacity-0');vc.classList.add('translate-y-0','sm:scale-100','opacity-100');}},10);
            } else {
                alert('Error: ' + (data.error || 'Failed to load user'));
            }
        })
        .catch(error => {
            alert('Network error: ' + error);
        });
}

function closeViewModal() {
    const vc=document.getElementById('viewUserModalContent');
    if(vc){vc.classList.add('translate-y-full','sm:scale-95','opacity-0');vc.classList.remove('translate-y-0','sm:scale-100','opacity-100');}
    setTimeout(()=>{document.getElementById('viewUserModal').classList.add('hidden');document.body.style.overflow='';},300);
}

function saveUser(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    // Copy name to full_name if not set
    if (!formData.get('full_name')) {
        formData.set('full_name', formData.get('name'));
    }
    
    const id = document.getElementById('userId').value;
    const url = id ? App.apiUrl('users', 'update-user.php') : App.apiUrl('users', 'create-user.php');
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(id ? 'User updated successfully!' : 'User created successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Network error: ' + error);
    });
}

function deleteUser(id, name) {
    if (confirm(`Are you sure you want to delete user "${name}"?`)) {
        fetch(App.apiUrl('users', 'delete-user.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
        alert('Network error: ' + error);
    });
    }
}
</script>