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
                <button onclick="openCreateModal()" style="background-color: #ffffff !important; color: #dc2626 !important;" class="add-user-btn bg-white text-red-600 px-6 py-3 rounded-lg font-semibold hover:bg-red-50 transition-all shadow-md flex items-center transform hover:scale-105 hover:shadow-lg active:scale-95 animate-slide-in-right">
                    <i class="bi bi-person-plus mr-2"></i> Add New User
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
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-500">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select name="role" class="input-field">
                        <option value="">All Roles</option>
                        <option value="administrator" <?php echo $data['filters']['role'] == 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                        <option value="officer" <?php echo $data['filters']['role'] == 'officer' ? 'selected' : ''; ?>>Officer</option>
                        <option value="staff" <?php echo $data['filters']['role'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                        <option value="viewer" <?php echo $data['filters']['role'] == 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="input-field">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $data['filters']['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $data['filters']['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="suspended" <?php echo $data['filters']['status'] == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <select name="department" class="input-field">
                        <option value="">All Departments</option>
                        <?php foreach ($data['departments'] as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept); ?>" 
                                    <?php echo $data['filters']['department'] == $dept ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full btn-primary">
                        <i class="bi bi-funnel mr-1"></i> Filter
                    </button>
                </div>
            </form>
            
            <!-- Search -->
            <form method="GET" class="mt-4">
                <div class="flex gap-2">
                    <input type="text" name="search" 
                           class="flex-1 input-field" 
                           placeholder="Search by name, email, username..." 
                           value="<?php echo htmlspecialchars($data['filters']['search'] ?? ''); ?>">
                    <button type="submit" class="btn-primary">
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
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-600">
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
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($data['users'])): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
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
                                        <?php
                                        $userRole = strtolower(trim($user['role'] ?? ''));
                                        $roleClass = match($userRole) {
                                            'administrator', 'admin' => 'bg-purple-100 text-purple-800',
                                            'officer', 'manager' => 'bg-blue-100 text-blue-800',
                                            'staff' => 'bg-green-100 text-green-800',
                                            'viewer', 'user' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-gray-100 text-gray-800 text-opacity-70'
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
                                        $statusClass = match($user['status']) {
                                            'active' => 'bg-green-100 text-green-800',
                                            'inactive' => 'bg-gray-100 text-gray-800',
                                            'suspended' => 'bg-red-100 text-red-800',
                                            'pending' => 'bg-amber-100 text-amber-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?> transition-transform duration-200 hover:scale-105">
                                            <?php echo e(ucfirst($user['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button onclick="editUser(<?php echo $user['id']; ?>)" style="background:none;border:none;" class="text-red-600 hover:text-red-700 mr-3 transition-all duration-200 hover:scale-110 cursor-pointer">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')" 
                                                style="background:none;border:none;" class="text-red-600 hover:text-red-900 transition-all duration-200 hover:scale-110 cursor-pointer">
                                            <i class="bi bi-trash"></i> Delete
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
<div id="userModal" class="hidden fixed inset-0 bg-gray-900/50 dark:bg-black/70 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border border-gray-200 dark:border-gray-700/50 w-full max-w-2xl shadow-2xl rounded-2xl bg-white dark:bg-gray-800/95 backdrop-blur-md">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-xl font-semibold text-gray-900 dark:text-gray-100">Add New User</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        
        <form id="userForm" onsubmit="saveUser(event)">
            <input type="hidden" id="userId" name="id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name *</label>
                    <input type="text" id="userName" name="name" required class="input-field" oninput="document.getElementById('userFullName').value = this.value">
                    <input type="hidden" id="userFullName" name="full_name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email *</label>
                    <input type="email" id="userEmail" name="email" required class="input-field">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username</label>
                    <input type="text" id="userUsername" name="username" class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password <span id="passwordRequired">*</span></label>
                    <input type="password" id="userPassword" name="password" class="input-field">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave blank to keep current password (when editing)</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role *</label>
                    <select id="userRole" name="role" required class="input-field">
                        <option value="viewer">Viewer</option>
                        <option value="staff">Staff</option>
                        <option value="officer">Officer</option>
                        <option value="administrator">Administrator</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                    <input type="text" id="userDepartment" name="department" class="input-field">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status *</label>
                <select id="userStatus" name="status" required class="input-field">
                    <option value="active">Active (Approved)</option>
                    <option value="pending">Pending Approval</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended (Denied)</option>
                </select>
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal()" class="px-6 py-2 border-2 border-gray-400 dark:border-gray-600 text-gray-800 dark:text-gray-300 hover:!bg-gray-100 dark:hover:bg-gray-700 rounded-lg font-semibold transition-all">
                    Cancel
                </button>
                <button type="submit" style="background-color: #dc2626 !important; color: #ffffff !important;" class="px-6 py-2 rounded-lg font-semibold transition-all hover:opacity-90">
                    <i class="bi bi-save mr-2"></i> Save User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('userFullName').value = '';
    document.getElementById('userPassword').required = true;
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('userModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('userModal').classList.add('hidden');
}

function editUser(id) {
    fetch(App.apiUrl('users', `get-user.php?id=${id}`))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalTitle').textContent = 'Edit User';
                document.getElementById('userId').value = data.user.id;
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