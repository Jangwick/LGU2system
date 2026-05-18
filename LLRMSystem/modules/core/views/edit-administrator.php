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

$userId = $_GET['id'] ?? null;

if (!$userId) {
    header('Location: ' . CORE_URL . '/views/admin-management.php');
    exit;
}

// Get user details
$stmt = getDatabase()->prepare("
    SELECT id, full_name, email, employee_id, department, role, status, created_at
    FROM users
    WHERE id = ?
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: ' . CORE_URL . '/views/admin-management.php');
    exit;
}

// Get session timeout from system config
$editableConfig = $controller->getEditableConfig();
$sessionTimeout = (intval($editableConfig['session_timeout'] ?? 2)) * 60;

$pageTitle = 'Edit Administrator';
$currentPage = 'admin-management';
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
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">Edit Administrator</h1>
                    <p class="text-red-100">Update administrator information</p>
                </div>
                <div class="flex flex-wrap gap-3 animate-slide-in-right">
                    <a href="<?php echo CORE_URL; ?>/views/admin-management.php" class="flex items-center px-6 py-2.5 bg-white hover:bg-red-50 text-red-600 rounded-xl font-bold transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95 shadow-sm border border-white/10">
                        <i class="bi bi-arrow-left mr-2"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 animate-fade-in-up">
            <form id="editForm" class="space-y-6">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Employee ID</label>
                        <input type="text" name="employee_id" value="<?= htmlspecialchars($user['employee_id']) ?>" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                        <input type="text" name="department" value="<?= htmlspecialchars($user['department']) ?>" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    </div>

                    <?php if ($user['role'] !== 'super_admin'): ?>
                    <div class="relative z-30">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                        <div class="relative custom-select-container">
                            <div id="role-trigger" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white cursor-pointer flex items-center justify-between" style="min-height: 48px;">
                                <span id="role-value"><?= $user['role'] === 'administrator' ? 'Administrator' : 'Staff' ?></span>
                                <i class="bi bi-chevron-down text-gray-400"></i>
                            </div>
                            <div id="role-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl z-[100] max-h-64 overflow-y-auto">
                                <div class="p-2 space-y-1">
                                    <div class="role-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="administrator">Administrator</div>
                                    <div class="role-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="staff">Staff</div>
                                </div>
                            </div>
                            <input type="hidden" name="role" id="role-input" value="<?= $user['role'] ?>">
                        </div>
                    </div>

                    <div class="relative z-30">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <div class="relative custom-select-container">
                            <div id="status-trigger" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white cursor-pointer flex items-center justify-between" style="min-height: 48px;">
                                <span id="status-value"><?= ucfirst($user['status']) ?></span>
                                <i class="bi bi-chevron-down text-gray-400"></i>
                            </div>
                            <div id="status-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl z-[100] max-h-64 overflow-y-auto">
                                <div class="p-2 space-y-1">
                                    <div class="status-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="active">Active</div>
                                    <div class="status-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="inactive">Inactive</div>
                                </div>
                            </div>
                            <input type="hidden" name="status" id="status-input" value="<?= $user['status'] ?>">
                        </div>
                    </div>
                    <?php else: ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                        <input type="text" value="Super Admin" disabled class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                        <input type="hidden" name="role" value="super_admin">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <input type="text" value="<?= ucfirst($user['status']) ?>" disabled class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                        <input type="hidden" name="status" value="<?= $user['status'] ?>">
                    </div>
                    <?php endif; ?>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="<?php echo CORE_URL; ?>/views/admin-management.php" class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-bold transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95 shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95 shadow-md">
                        <i class="bi bi-check-lg mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split mr-2"></i>Saving...';

    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    fetch('<?php echo BASE_URL; ?>/modules/core/api/admin-management.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'update_admin',
            ...data
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Administrator updated successfully');
            window.location.href = '<?php echo CORE_URL; ?>/views/admin-management.php';
        } else {
            alert('Error: ' + result.error);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg mr-2"></i> Save Changes';
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg mr-2"></i> Save Changes';
    });
});
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
// Custom Dropdown Helper Function
function initCustomDropdown(triggerId, dropdownId, valueId, inputId, optionClass, defaultValue) {
    const trigger = document.getElementById(triggerId);
    const dropdown = document.getElementById(dropdownId);
    const valueDisplay = document.getElementById(valueId);
    const hiddenInput = document.getElementById(inputId);
    const options = document.querySelectorAll(optionClass);
    
    if (!trigger || !dropdown || !valueDisplay || !hiddenInput) return;
    
    const selectedValue = hiddenInput.value;
    if (selectedValue) {
        const selectedOption = document.querySelector(`${optionClass}[data-value="${selectedValue}"]`);
        if (selectedOption) {
            valueDisplay.textContent = selectedOption.textContent;
        }
    }
    
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });
    
    options.forEach(option => {
        option.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            const text = this.textContent;
            valueDisplay.textContent = text;
            hiddenInput.value = value;
            dropdown.classList.add('hidden');
        });
    });
    
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initCustomDropdown('role-trigger', 'role-dropdown', 'role-value', 'role-input', '.role-option', 'Administrator');
    initCustomDropdown('status-trigger', 'status-dropdown', 'status-value', 'status-input', '.status-option', 'Active');
});

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
