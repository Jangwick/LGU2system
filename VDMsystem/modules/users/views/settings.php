<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Fetch user data
$db = getDatabase();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['flash_error'] = 'User not found.';
    redirectToDashboard();
}

$pageTitle = 'Settings';
$currentPage = 'settings';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => DASHBOARD_INDEX_URL],
    ['label' => 'Settings']
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
        <!-- Container -->
        <div class="max-w-6xl mx-auto">
            <!-- Header Banner -->
            <div class="vdm-welcome-banner rounded-xl md:rounded-2xl shadow-xl p-5 md:p-8 mb-5 text-white animate-fade-in relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-56 h-56 bg-white opacity-5 rounded-full blur-3xl"></div>
                <div class="absolute right-0 bottom-0 w-40 h-40 bg-white opacity-5 rounded-full blur-2xl"></div>
                <div class="relative flex items-center justify-between gap-4">
                    <div>
                        <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight">
                            <i class="bi bi-gear-fill mr-2"></i>Settings
                        </h1>
                        <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium">
                            Manage your account, security, and preferences.
                        </p>
                    </div>
                    <div class="hidden md:flex items-center gap-2">
                        <div class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-sm flex items-center justify-center border border-white/20 overflow-hidden">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img src="<?php echo BASE_URL; ?>/storage/profiles/<?php echo e($user['profile_picture']); ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="bi bi-person-fill text-lg"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-sm font-bold"><?php echo e($user['full_name']); ?></p>
                            <p class="text-xs text-red-200 opacity-80"><?php echo ucfirst(e($user['role'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 animate-fade-in-up">
                <!-- Settings Navigation -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden sticky top-4">
                        <div class="p-3">
                            <nav class="space-y-1">
                                <button onclick="switchTab('security')" id="tab-security" class="settings-tab active w-full flex items-center px-3.5 py-3 text-sm font-bold rounded-lg transition-all text-left">
                                    <i class="bi bi-shield-lock mr-3 text-base"></i>
                                    <div>
                                        <span class="block text-[13px]">Security</span>
                                        <span class="text-[10px] font-medium opacity-60">Password & login</span>
                                    </div>
                                </button>
                                <button onclick="switchTab('appearance')" id="tab-appearance" class="settings-tab w-full flex items-center px-3.5 py-3 text-sm font-bold rounded-lg transition-all text-left">
                                    <i class="bi bi-palette mr-3 text-base"></i>
                                    <div>
                                        <span class="block text-[13px]">Appearance</span>
                                        <span class="text-[10px] font-medium opacity-60">Theme & display</span>
                                    </div>
                                </button>
                                <button onclick="switchTab('notifications')" id="tab-notifications" class="settings-tab w-full flex items-center px-3.5 py-3 text-sm font-bold rounded-lg transition-all text-left">
                                    <i class="bi bi-bell mr-3 text-base"></i>
                                    <div>
                                        <span class="block text-[13px]">Notifications</span>
                                        <span class="text-[10px] font-medium opacity-60">Email & alerts</span>
                                    </div>
                                </button>
                                <button onclick="switchTab('account')" id="tab-account" class="settings-tab w-full flex items-center px-3.5 py-3 text-sm font-bold rounded-lg transition-all text-left">
                                    <i class="bi bi-person-gear mr-3 text-base"></i>
                                    <div>
                                        <span class="block text-[13px]">Account</span>
                                        <span class="text-[10px] font-medium opacity-60">Profile & data</span>
                                    </div>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Settings Content -->
                <div class="lg:col-span-3 space-y-5">
                    <!-- Security Tab -->
                    <div id="content-security" class="settings-content space-y-5">
                        <!-- Change Password -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                                <i class="bi bi-shield-lock-fill text-red-500 text-lg"></i>
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Change Password</h2>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Update your password to keep your account secure</p>
                                </div>
                            </div>
                            <div class="p-6">
                                <form id="passwordForm" onsubmit="changePassword(event)">
                                    <div class="space-y-4 max-w-lg">
                                        <div>
                                            <label class="block text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1.5">Current Password <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <input type="password" name="current_password" id="currentPassword" required
                                                       class="w-full px-3.5 py-2.5 pr-12 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all text-sm">
                                                <button type="button" onclick="togglePassword('currentPassword')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1.5">New Password <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <input type="password" name="new_password" id="newPassword" required minlength="6"
                                                       class="w-full px-3.5 py-2.5 pr-12 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all text-sm"
                                                       oninput="checkPasswordStrength(this.value)">
                                                <button type="button" onclick="togglePassword('newPassword')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <!-- Password Strength Indicator -->
                                            <div class="mt-2">
                                                <div class="flex gap-1.5">
                                                    <div id="strength-1" class="h-1.5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700 transition-all"></div>
                                                    <div id="strength-2" class="h-1.5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700 transition-all"></div>
                                                    <div id="strength-3" class="h-1.5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700 transition-all"></div>
                                                    <div id="strength-4" class="h-1.5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700 transition-all"></div>
                                                </div>
                                                <p id="strength-text" class="text-xs mt-1 text-gray-400 dark:text-gray-500">Min 6 characters required</p>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1.5">Confirm New Password <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <input type="password" name="confirm_password" id="confirmPassword" required minlength="6"
                                                       class="w-full px-3.5 py-2.5 pr-12 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all text-sm">
                                                <button type="button" onclick="togglePassword('confirmPassword')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <p id="password-match" class="text-xs mt-1 hidden"></p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                                        <button type="submit" id="changePasswordBtn" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-bold text-sm transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                                            <i class="bi bi-lock"></i>
                                            <span>Update Password</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Active Session -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                                <i class="bi bi-laptop text-blue-500 text-lg"></i>
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Active Session</h2>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Your current login session</p>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800/50 rounded-lg p-4">
                                    <div class="flex items-center gap-4">
                                        <div class="bg-green-100 dark:bg-green-900/30 rounded-full p-3 flex-shrink-0">
                                            <i class="bi bi-display text-green-600 text-xl"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-gray-800 dark:text-white">Current Device</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                <?php echo e($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device'); ?>
                                            </p>
                                            <p class="text-xs text-green-600 dark:text-green-400 font-bold mt-1">
                                                <i class="bi bi-circle-fill text-[6px] mr-1"></i> Active now
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appearance Tab -->
                    <div id="content-appearance" class="settings-content hidden space-y-5">
                        <!-- Theme Selection -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                                <i class="bi bi-palette-fill text-purple-500 text-lg"></i>
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Theme</h2>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Choose your preferred appearance</p>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <button onclick="setTheme('light')" id="theme-light" class="theme-option p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-red-400 transition-all text-left group">
                                        <div class="w-full h-20 rounded-lg bg-gradient-to-br from-white to-gray-100 border border-gray-200 mb-3 flex items-center justify-center shadow-inner">
                                            <i class="bi bi-sun-fill text-2xl text-yellow-500"></i>
                                        </div>
                                        <p class="font-bold text-gray-800 dark:text-white text-sm">Light Mode</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Clean and bright interface</p>
                                    </button>
                                    <button onclick="setTheme('dark')" id="theme-dark" class="theme-option p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-red-400 transition-all text-left group">
                                        <div class="w-full h-20 rounded-lg bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-600 mb-3 flex items-center justify-center shadow-inner">
                                            <i class="bi bi-moon-fill text-2xl text-indigo-400"></i>
                                        </div>
                                        <p class="font-bold text-gray-800 dark:text-white text-sm">Dark Mode</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Easy on the eyes</p>
                                    </button>
                                    <button onclick="setTheme('system')" id="theme-system" class="theme-option p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-red-400 transition-all text-left group">
                                        <div class="w-full h-20 rounded-lg bg-gradient-to-br from-white via-gray-400 to-gray-900 border border-gray-300 mb-3 flex items-center justify-center shadow-inner">
                                            <i class="bi bi-laptop text-2xl text-gray-600"></i>
                                        </div>
                                        <p class="font-bold text-gray-800 dark:text-white text-sm">System Default</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Match your OS theme</p>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Layout Preference -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                                <i class="bi bi-layout-sidebar text-orange-500 text-lg"></i>
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Layout</h2>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Customize your layout preferences</p>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white">Collapsed Sidebar</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Start with a compact sidebar</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="sidebarCollapsedToggle" class="sr-only peer" onchange="toggleSidebarPref(this)">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications Tab -->
                    <div id="content-notifications" class="settings-content hidden">
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                                <i class="bi bi-bell-fill text-green-500 text-lg"></i>
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Notification Preferences</h2>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Choose what notifications you'd like to receive</p>
                                </div>
                            </div>
                            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                                <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-all">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                            <i class="bi bi-calendar-event text-blue-500"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white">New Voting Sessions</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">Get notified when a new session is created</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked onchange="saveNotifPref('voting_sessions', this.checked)">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-all">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-green-50 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                                            <i class="bi bi-check-circle text-green-500"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white">Vote Results</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">Notify when voting results are published</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked onchange="saveNotifPref('vote_results', this.checked)">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-all">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-purple-50 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                                            <i class="bi bi-file-earmark-text text-purple-500"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white">Document Updates</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">Get alerted on document status changes</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" onchange="saveNotifPref('document_updates', this.checked)">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-all">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-orange-50 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                                            <i class="bi bi-megaphone text-orange-500"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white">System Announcements</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">Important system-wide announcements</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked onchange="saveNotifPref('system_announcements', this.checked)">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Tab -->
                    <div id="content-account" class="settings-content hidden space-y-5">
                        <!-- Account Information -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i class="bi bi-person-fill text-indigo-500 text-lg"></i>
                                    <div>
                                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Account Information</h2>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">View your account details</p>
                                    </div>
                                </div>
                                <a href="<?php echo BASE_URL; ?>/modules/users/views/profile.php" class="text-xs font-bold text-red-500 hover:text-red-600 transition-colors flex items-center gap-1">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1">Full Name</p>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['full_name']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1">Email</p>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['email']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1">Username</p>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['username'] ?? '—'); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1">Role</p>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo ucfirst(e($user['role'])); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1">Position</p>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['position'] ?? '—'); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wider mb-1">Department</p>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['department'] ?? '—'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data & Privacy -->
                        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                                <i class="bi bi-shield-check text-teal-500 text-lg"></i>
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Data & Privacy</h2>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Manage your data and privacy settings</p>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i class="bi bi-info-circle-fill text-blue-500 text-lg mt-0.5 flex-shrink-0"></i>
                                        <div>
                                            <p class="text-sm font-bold text-blue-900 dark:text-blue-300">Your Data is Secure</p>
                                            <p class="text-xs text-blue-700 dark:text-blue-400 mt-1 leading-relaxed">All your data is stored securely within the City Government of Valenzuela's internal network. For data requests or concerns, please contact your system administrator.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-3 mt-4">
                                    <a href="<?php echo BASE_URL; ?>/modules/help/views/privacy.php" class="text-xs font-bold text-red-500 hover:text-red-600 transition-colors flex items-center gap-1">
                                        <i class="bi bi-shield-check"></i> Privacy Policy
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/modules/help/views/terms.php" class="text-xs font-bold text-red-500 hover:text-red-600 transition-colors flex items-center gap-1">
                                        <i class="bi bi-file-text"></i> Terms of Use
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<style>
    .settings-tab {
        color: #6b7280;
        background: transparent;
    }
    .settings-tab:hover {
        background-color: #f3f4f6;
        color: #374151;
    }
    .settings-tab.active {
        background-color: #fef2f2;
        color: #dc2626;
        border-left: 3px solid #dc2626;
    }
    html.dark .settings-tab {
        color: #9ca3af;
    }
    html.dark .settings-tab:hover {
        background-color: #1f2937;
        color: #e5e7eb;
    }
    html.dark .settings-tab.active {
        background-color: rgba(220, 38, 38, 0.1);
        color: #ef4444;
        border-left: 3px solid #ef4444;
    }
    .theme-option.selected {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }
</style>

<script>
// Tab Switching
function switchTab(tabName) {
    document.querySelectorAll('.settings-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.settings-tab').forEach(el => el.classList.remove('active'));
    document.getElementById('content-' + tabName).classList.remove('hidden');
    document.getElementById('tab-' + tabName).classList.add('active');
}

// Password toggle
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) strength++;
    
    const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
    const labels = ['Weak', 'Fair', 'Good', 'Strong'];
    const textColors = ['text-red-500', 'text-orange-500', 'text-yellow-500', 'text-green-500'];
    
    for (let i = 1; i <= 4; i++) {
        const bar = document.getElementById('strength-' + i);
        bar.className = 'h-1.5 flex-1 rounded-full transition-all';
        if (i <= strength) {
            bar.classList.add(colors[strength - 1]);
        } else {
            bar.classList.add('bg-gray-200', 'dark:bg-gray-700');
        }
    }
    
    const text = document.getElementById('strength-text');
    if (password.length === 0) {
        text.textContent = 'Min 6 characters required';
        text.className = 'text-xs mt-1 text-gray-400 dark:text-gray-500';
    } else {
        text.textContent = labels[strength - 1] || 'Too short';
        text.className = 'text-xs mt-1 font-bold ' + (textColors[strength - 1] || 'text-red-500');
    }

    const confirmInput = document.getElementById('confirmPassword');
    if (confirmInput.value) checkPasswordMatch();
}

function checkPasswordMatch() {
    const newPw = document.getElementById('newPassword').value;
    const confirmPw = document.getElementById('confirmPassword').value;
    const matchText = document.getElementById('password-match');
    
    if (confirmPw.length === 0) { matchText.classList.add('hidden'); return; }
    
    matchText.classList.remove('hidden');
    if (newPw === confirmPw) {
        matchText.textContent = '✓ Passwords match';
        matchText.className = 'text-xs mt-1 font-bold text-green-500';
    } else {
        matchText.textContent = '✗ Passwords do not match';
        matchText.className = 'text-xs mt-1 font-bold text-red-500';
    }
}

document.getElementById('confirmPassword')?.addEventListener('input', checkPasswordMatch);

// Change password
function changePassword(event) {
    event.preventDefault();
    
    const newPw = document.getElementById('newPassword').value;
    const confirmPw = document.getElementById('confirmPassword').value;
    
    if (newPw !== confirmPw) { showToast('Passwords do not match', 'error'); return; }
    
    const btn = document.getElementById('changePasswordBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Updating...';
    
    fetch(App.apiUrl('users', 'change-password.php'), {
        method: 'POST',
        body: new FormData(event.target)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Password changed successfully!', 'success');
            event.target.reset();
            for (let i = 1; i <= 4; i++) {
                document.getElementById('strength-' + i).className = 'h-1.5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700 transition-all';
            }
            document.getElementById('strength-text').textContent = 'Min 6 characters required';
            document.getElementById('strength-text').className = 'text-xs mt-1 text-gray-400 dark:text-gray-500';
            document.getElementById('password-match').classList.add('hidden');
        } else {
            showToast('Error: ' + (data.error || 'Failed to change password'), 'error');
        }
    })
    .catch(error => showToast('Network error: ' + error.message, 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lock"></i> <span>Update Password</span>';
    });
}

// Theme selection
function setTheme(theme) {
    document.querySelectorAll('.theme-option').forEach(el => el.classList.remove('selected'));
    
    if (theme === 'light') {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
        document.getElementById('theme-light').classList.add('selected');
    } else if (theme === 'dark') {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
        document.getElementById('theme-dark').classList.add('selected');
    } else {
        localStorage.removeItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('dark', prefersDark);
        document.getElementById('theme-system').classList.add('selected');
    }
    
    const darkModeIcon = document.querySelector('.dark-mode-icon');
    const lightModeIcon = document.querySelector('.light-mode-icon');
    const isDark = document.documentElement.classList.contains('dark');
    if (darkModeIcon) darkModeIcon.classList.toggle('hidden', isDark);
    if (lightModeIcon) lightModeIcon.classList.toggle('hidden', !isDark);
    
    showToast('Theme updated!', 'success');
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') document.getElementById('theme-dark')?.classList.add('selected');
    else if (savedTheme === 'light') document.getElementById('theme-light')?.classList.add('selected');
    else document.getElementById('theme-system')?.classList.add('selected');
    
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    const sidebarToggle = document.getElementById('sidebarCollapsedToggle');
    if (sidebarToggle) sidebarToggle.checked = sidebarCollapsed;
});

function toggleSidebarPref(checkbox) {
    localStorage.setItem('sidebarCollapsed', checkbox.checked ? 'true' : 'false');
    showToast('Sidebar preference saved!', 'success');
}

function saveNotifPref(key, value) {
    localStorage.setItem('notif_' + key, value ? '1' : '0');
    showToast('Notification preference saved!', 'success');
}
</script>
