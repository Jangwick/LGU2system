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
        <!-- Header Banner -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white transform transition-all duration-500 ease-in-out animate-fade-in relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl transition-opacity duration-500 dark:opacity-5"></div>
            <div class="relative flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight">
                        <i class="bi bi-gear-fill mr-2"></i>Settings
                    </h1>
                    <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium">
                        Manage your account, security, and preferences.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Settings Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 sticky top-4">
                    <nav class="space-y-1">
                        <button onclick="switchTab('security')" id="tab-security" class="settings-tab active w-full flex items-center px-4 py-3 text-sm font-bold rounded-lg transition-all text-left">
                            <i class="bi bi-shield-lock mr-3 text-lg"></i>
                            <div>
                                <span class="block">Security</span>
                                <span class="text-[10px] font-medium opacity-70">Password & login</span>
                            </div>
                        </button>
                        <button onclick="switchTab('appearance')" id="tab-appearance" class="settings-tab w-full flex items-center px-4 py-3 text-sm font-bold rounded-lg transition-all text-left">
                            <i class="bi bi-palette mr-3 text-lg"></i>
                            <div>
                                <span class="block">Appearance</span>
                                <span class="text-[10px] font-medium opacity-70">Theme & display</span>
                            </div>
                        </button>
                        <button onclick="switchTab('notifications')" id="tab-notifications" class="settings-tab w-full flex items-center px-4 py-3 text-sm font-bold rounded-lg transition-all text-left">
                            <i class="bi bi-bell mr-3 text-lg"></i>
                            <div>
                                <span class="block">Notifications</span>
                                <span class="text-[10px] font-medium opacity-70">Email & alerts</span>
                            </div>
                        </button>
                        <button onclick="switchTab('account')" id="tab-account" class="settings-tab w-full flex items-center px-4 py-3 text-sm font-bold rounded-lg transition-all text-left">
                            <i class="bi bi-person-gear mr-3 text-lg"></i>
                            <div>
                                <span class="block">Account</span>
                                <span class="text-[10px] font-medium opacity-70">Profile & data</span>
                            </div>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Settings Content -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Security Tab -->
                <div id="content-security" class="settings-content">
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 animate-fade-in-up">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-red-50 dark:bg-red-900/20 rounded-xl flex items-center justify-center">
                                <i class="bi bi-shield-lock-fill text-red-600 text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Change Password</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Update your password to keep your account secure</p>
                            </div>
                        </div>
                        
                        <form id="passwordForm" onsubmit="changePassword(event)">
                            <div class="space-y-5 max-w-lg">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Current Password <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="password" name="current_password" id="currentPassword" required
                                               class="w-full px-4 py-2.5 pr-12 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                                        <button type="button" onclick="togglePassword('currentPassword')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">New Password <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="password" name="new_password" id="newPassword" required minlength="6"
                                               class="w-full px-4 py-2.5 pr-12 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all"
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
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Confirm New Password <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="password" name="confirm_password" id="confirmPassword" required minlength="6"
                                               class="w-full px-4 py-2.5 pr-12 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                                        <button type="button" onclick="togglePassword('confirmPassword')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <p id="password-match" class="text-xs mt-1 hidden"></p>
                                </div>
                            </div>
                            
                            <div class="flex justify-end mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button type="submit" id="changePasswordBtn" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg font-bold transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                                    <i class="bi bi-lock"></i>
                                    <span>Update Password</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Login Sessions Info -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-200">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                                <i class="bi bi-laptop text-blue-600 text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Active Session</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Your current login session</p>
                            </div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800/50 rounded-lg p-4">
                            <div class="flex items-center gap-4">
                                <div class="bg-green-100 dark:bg-green-900/30 rounded-full p-3">
                                    <i class="bi bi-display text-green-600 text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-800 dark:text-white">Current Device</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
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

                <!-- Appearance Tab -->
                <div id="content-appearance" class="settings-content hidden">
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 animate-fade-in-up">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-purple-50 dark:bg-purple-900/20 rounded-xl flex items-center justify-center">
                                <i class="bi bi-palette-fill text-purple-600 text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Theme</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Choose your preferred appearance</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Light Mode -->
                            <button onclick="setTheme('light')" id="theme-light" class="theme-option p-5 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-red-400 transition-all text-left group">
                                <div class="w-full h-24 rounded-lg bg-gradient-to-br from-white to-gray-100 border border-gray-200 mb-4 flex items-center justify-center shadow-inner">
                                    <i class="bi bi-sun-fill text-2xl text-yellow-500"></i>
                                </div>
                                <p class="font-bold text-gray-800 dark:text-white text-sm">Light Mode</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Clean and bright interface</p>
                            </button>
                            
                            <!-- Dark Mode -->
                            <button onclick="setTheme('dark')" id="theme-dark" class="theme-option p-5 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-red-400 transition-all text-left group">
                                <div class="w-full h-24 rounded-lg bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-600 mb-4 flex items-center justify-center shadow-inner">
                                    <i class="bi bi-moon-fill text-2xl text-indigo-400"></i>
                                </div>
                                <p class="font-bold text-gray-800 dark:text-white text-sm">Dark Mode</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Easy on the eyes</p>
                            </button>
                            
                            <!-- System -->
                            <button onclick="setTheme('system')" id="theme-system" class="theme-option p-5 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-red-400 transition-all text-left group">
                                <div class="w-full h-24 rounded-lg bg-gradient-to-br from-white via-gray-400 to-gray-900 border border-gray-300 mb-4 flex items-center justify-center shadow-inner">
                                    <i class="bi bi-laptop text-2xl text-gray-600"></i>
                                </div>
                                <p class="font-bold text-gray-800 dark:text-white text-sm">System Default</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Match your OS theme</p>
                            </button>
                        </div>
                    </div>

                    <!-- Sidebar Preference -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-orange-50 dark:bg-orange-900/20 rounded-xl flex items-center justify-center">
                                <i class="bi bi-layout-sidebar text-orange-600 text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Layout</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Customize your layout preferences</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <div>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white">Collapsed Sidebar</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Start with a compact sidebar</p>
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
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 animate-fade-in-up">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-green-50 dark:bg-green-900/20 rounded-xl flex items-center justify-center">
                                <i class="bi bi-bell-fill text-green-600 text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Notification Preferences</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Choose what notifications you'd like to receive</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                        <i class="bi bi-calendar-event text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white">New Voting Sessions</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Get notified when a new session is created</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" checked onchange="saveNotifPref('voting_sessions', this.checked)">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600"></div>
                                </label>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                                        <i class="bi bi-check-circle text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white">Vote Results</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Notify when voting results are published</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" checked onchange="saveNotifPref('vote_results', this.checked)">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600"></div>
                                </label>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                                        <i class="bi bi-file-earmark-text text-purple-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white">Document Updates</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Get alerted on document status changes</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" onchange="saveNotifPref('document_updates', this.checked)">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600"></div>
                                </label>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                                        <i class="bi bi-megaphone text-orange-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white">System Announcements</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Important system-wide announcements</p>
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
                <div id="content-account" class="settings-content hidden">
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 animate-fade-in-up">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl flex items-center justify-center">
                                <i class="bi bi-person-fill text-indigo-600 text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Account Information</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">View your account details</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Full Name</p>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['full_name']); ?></p>
                                </div>
                                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Email</p>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['email']); ?></p>
                                </div>
                                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Username</p>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['username'] ?? '—'); ?></p>
                                </div>
                                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Role</p>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo ucfirst(e($user['role'])); ?></p>
                                </div>
                                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Position</p>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['position'] ?? '—'); ?></p>
                                </div>
                                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Department</p>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($user['department'] ?? '—'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="<?php echo BASE_URL; ?>/modules/users/views/profile.php" class="inline-flex items-center gap-2 text-sm font-bold text-red-600 hover:text-red-700 transition-colors">
                                <i class="bi bi-pencil"></i>
                                Edit Profile Information
                            </a>
                        </div>
                    </div>

                    <!-- Data Export -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-200">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 bg-teal-50 dark:bg-teal-900/20 rounded-xl flex items-center justify-center">
                                <i class="bi bi-download text-teal-600 text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Data & Privacy</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Manage your data and privacy settings</p>
                            </div>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/50 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-info-circle-fill text-blue-600 text-lg mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-bold text-blue-900 dark:text-blue-300">Your Data is Secure</p>
                                    <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">All your data is stored securely within the City Government of Valenzuela's internal network. For data requests or concerns, please contact your system administrator.</p>
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
    // Hide all content
    document.querySelectorAll('.settings-content').forEach(el => el.classList.add('hidden'));
    // Deactivate all tabs
    document.querySelectorAll('.settings-tab').forEach(el => el.classList.remove('active'));
    
    // Show selected content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    // Activate selected tab
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

    // Check password match
    const confirmInput = document.getElementById('confirmPassword');
    if (confirmInput.value) {
        checkPasswordMatch();
    }
}

function checkPasswordMatch() {
    const newPw = document.getElementById('newPassword').value;
    const confirmPw = document.getElementById('confirmPassword').value;
    const matchText = document.getElementById('password-match');
    
    if (confirmPw.length === 0) {
        matchText.classList.add('hidden');
        return;
    }
    
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
    
    if (newPw !== confirmPw) {
        showToast('Passwords do not match', 'error');
        return;
    }
    
    const btn = document.getElementById('changePasswordBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Updating...';
    
    const formData = new FormData(event.target);
    
    fetch(App.apiUrl('users', 'change-password.php'), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Password changed successfully!', 'success');
            event.target.reset();
            // Reset strength indicator
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
    .catch(error => {
        showToast('Network error: ' + error.message, 'error');
    })
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
        // System
        localStorage.removeItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('dark', prefersDark);
        document.getElementById('theme-system').classList.add('selected');
    }
    
    // Update dark mode icons in navbar
    const darkModeIcon = document.querySelector('.dark-mode-icon');
    const lightModeIcon = document.querySelector('.light-mode-icon');
    const isDark = document.documentElement.classList.contains('dark');
    if (darkModeIcon) darkModeIcon.classList.toggle('hidden', isDark);
    if (lightModeIcon) lightModeIcon.classList.toggle('hidden', !isDark);
    
    showToast('Theme updated!', 'success');
}

// Initialize theme selection
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.getElementById('theme-dark')?.classList.add('selected');
    } else if (savedTheme === 'light') {
        document.getElementById('theme-light')?.classList.add('selected');
    } else {
        document.getElementById('theme-system')?.classList.add('selected');
    }
    
    // Initialize sidebar toggle state
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    const sidebarToggle = document.getElementById('sidebarCollapsedToggle');
    if (sidebarToggle) sidebarToggle.checked = sidebarCollapsed;
});

// Sidebar preference
function toggleSidebarPref(checkbox) {
    localStorage.setItem('sidebarCollapsed', checkbox.checked ? 'true' : 'false');
    showToast('Sidebar preference saved! Changes apply on next page load.', 'success');
}

// Notification preferences (localStorage-based for now)
function saveNotifPref(key, value) {
    localStorage.setItem('notif_' + key, value ? '1' : '0');
    showToast('Notification preference saved!', 'success');
}
</script>
