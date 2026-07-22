<!-- Top Navbar -->
<?php
// Include database connection
require_once __DIR__ . '/../../core/config/database.php';

// Fetch user profile picture for navbar
if (isset($_SESSION['user_id'])) {
    try {
        $navDb = getDatabase();
        $navStmt = $navDb->prepare("SELECT profile_picture FROM users WHERE id = ?");
        $navStmt->execute([$_SESSION['user_id']]);
        $navUserData = $navStmt->fetch(PDO::FETCH_ASSOC);
        $navProfilePicture = $navUserData['profile_picture'] ?? null;
    } catch (PDOException $e) {
        $navProfilePicture = null;
    }
}
?>
<nav class="bg-white shadow-md border-b border-gray-200 sticky top-0 z-40">
    <div class="px-2 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Left Side: Menu + Logo -->
            <div class="flex items-center flex-shrink-0">
                <!-- Sidebar Toggle Button (Desktop) -->
                <button id="sidebar-toggle" class="hidden md:flex items-center justify-center w-10 h-10 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-red-600 focus:outline-none transition-all duration-200" title="Toggle Sidebar">
                    <i id="sidebar-toggle-icon" class="bi bi-layout-sidebar-inset text-xl transition-transform duration-300"></i>
                </button>
                
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-1 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="bi bi-list text-2xl"></i>
                </button>
                
                <!-- Logo (Mobile) -->
                <div class="md:hidden flex items-center ml-1">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Valenzuela" class="w-6 h-6 object-contain">
                </div>
            </div>
            
            <!-- Center: Page Title -->
            <div class="flex-1 flex flex-col justify-center min-w-0 px-1 md:px-4 overflow-hidden">
                <h2 class="text-[14px] md:text-xl font-bold text-gray-800 leading-tight tracking-tight truncate">
                    <span class="md:hidden"><?php 
                        $mobTitle = $pageTitle ?? 'Dashboard';
                        $titleMap = [
                            'Document Management' => 'Documents',
                            'Advanced Search System' => 'Search',
                            'Reports & Analytics' => 'Reports',
                            'User Management' => 'Users',
                            'Activity Logs' => 'Logs',
                            'Research & Analysis' => 'Analysis',
                            'Legislative Research & Analysis' => 'Research',
                            'Legislative Cross-Reference Map' => 'Cross-Ref',
                            'Law Comparison Tool' => 'Compare',
                            'Administrator Management' => 'Admins',
                            'System Configuration' => 'Config',
                            'Database Backup & Restore' => 'Backup',
                            'Audit Logs' => 'Logs',
                            'Edit Administrator' => 'Edit Admin'
                        ];
                        echo e($titleMap[$mobTitle] ?? $mobTitle); 
                    ?></span>
                    <span class="hidden md:inline"><?php echo e($pageTitle ?? 'Dashboard'); ?></span>
                </h2>
                <?php if (isset($breadcrumbs)): ?>
                <nav class="hidden md:flex text-sm text-gray-600 mt-1 w-fit" aria-label="Breadcrumb">
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <?php if ($index > 0): ?>
                            <i class="bi bi-chevron-right mx-2 text-xs"></i>
                        <?php endif; ?>
                        <?php if (isset($crumb['url'])): ?>
                            <a href="<?php echo htmlspecialchars($crumb['url']); ?>" class="hover:text-blue-600">
                                <?php echo htmlspecialchars($crumb['label']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-gray-800 font-medium"><?php echo htmlspecialchars($crumb['label']); ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
                <?php endif; ?>
            </div>
            
            <!-- Right Side Actions -->
            <div class="flex items-center flex-shrink-0 space-x-0.5 md:space-x-4">
                
                <!-- Session Timeout Countdown (hidden from UI but kept for JS references) -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <div id="session-timer" class="hidden" aria-hidden="true">
                    <span id="session-countdown"></span>
                </div>
                <?php endif; ?>
                
                <!-- Dark/Light Mode Toggle -->
                <button id="theme-toggle" class="no-ripple inline-flex items-center justify-center w-7 h-7 md:w-10 md:h-10 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors overflow-hidden min-w-[28px] md:min-w-[40px] flex-shrink-0 transform-none hover:transform-none active:transform-none">
                    <i class="bi bi-moon-fill text-base md:text-xl dark-mode-icon"></i>
                    <i class="bi bi-sun-fill text-lg light-mode-icon hidden"></i>
                </button>
                
                <!-- Notifications -->
                <div class="relative" id="notifications-container">
                    <button id="notifications-btn" class="no-ripple inline-flex items-center justify-center relative w-7 h-7 md:w-10 md:h-10 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors overflow-hidden min-w-[28px] md:min-w-[40px] flex-shrink-0 transform-none hover:transform-none active:transform-none">
                        <i class="bi bi-bell text-base md:text-xl"></i>
                        <span id="notification-badge" class="hidden absolute top-0 right-0 min-w-[12px] h-[12px] md:min-w-[18px] md:h-[18px] bg-red-500 rounded-full text-white text-[8px] md:text-xs font-bold items-center justify-center px-0.5">0</span>
                    </button>
                    
                    <!-- Notifications Dropdown -->
                    <div id="notifications-dropdown" class="hidden fixed md:absolute left-4 right-4 md:left-auto md:right-0 top-16 md:top-auto mt-2 w-auto md:w-96 bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-800 z-50 overflow-hidden">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/50">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Notifications</h3>
                            <button id="mark-all-read-btn" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-bold uppercase tracking-wider">Mark all as read</button>
                        </div>
                        <div id="notifications-list" class="max-h-[60vh] md:max-h-96 overflow-y-auto">
                            <!-- Notifications will be loaded dynamically -->
                            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="bi bi-bell-slash text-3xl mb-2"></i>
                                <p class="text-sm">No notifications</p>
                            </div>
                        </div>
                        <div class="p-3 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/50">
                            <a href="<?php echo BASE_URL; ?>/modules/notifications/views/index.php" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-bold">View all notifications</a>
                            <span id="notification-count-text" class="text-xs text-gray-500 dark:text-gray-400 font-medium">0 unread</span>
                        </div>
                    </div>
                </div>
                
                <!-- User Profile Dropdown -->
                <div class="relative">
                    <button id="profile-btn" class="no-ripple inline-flex items-center space-x-1 md:space-x-3 p-1 md:p-2 hover:bg-gray-100 rounded-lg transition-colors shrink-0 min-w-[120px] h-12 transform-none hover:transform-none active:transform-none">
                        <?php if (!empty($navProfilePicture)): ?>
                            <img src="<?php echo BASE_URL; ?>/storage/profiles/<?php echo htmlspecialchars($navProfilePicture); ?>" 
                                 alt="Profile" 
                                 class="w-6 h-6 md:w-9 md:h-9 rounded-full object-cover border border-red-600">
                        <?php else: ?>
                            <div class="bg-red-600 rounded-full w-6 h-6 md:w-9 md:h-9 flex items-center justify-center text-white font-bold text-[9px] md:text-base">
                                <?php echo e(strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1))); ?>
                            </div>
                        <?php endif; ?>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-medium text-gray-800 truncate max-w-[120px] md:max-w-none"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest User'); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Guest'); ?></p>
                        </div>
                        <i class="bi bi-chevron-down text-gray-600 text-xs hidden sm:inline"></i>
                    </button>
                    
                    <!-- Profile Dropdown -->
                    <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50" style="background-color: white;">
                        <div class="p-4 border-b border-gray-200">
                            <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'guest@lgu.gov'); ?></p>
                            <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($_SESSION['user_department'] ?? 'Legislative Office'); ?></p>
                        </div>
                        <div class="py-2">
                            <a href="<?php echo USERS_URL; ?>/views/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="bi bi-person mr-2"></i>My Profile
                            </a>
                            <a href="<?php echo USERS_URL; ?>/views/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="bi bi-gear mr-2"></i>Settings
                            </a>
                            <a href="<?php echo HELP_URL; ?>/views/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="bi bi-question-circle mr-2"></i>Help & Support
                            </a>
                        </div>
                        <div class="border-t border-gray-200 py-2">
                            <a href="<?php echo LOGOUT_URL; ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="bi bi-box-arrow-right mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Session Timeout Modal -->
<?php if (isset($_SESSION['user_id'])): ?>
<div id="session-timeout-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-gray-200 dark:border-gray-800 transform transition-all duration-300">
        <div class="p-6 text-center">
            <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-exclamation-triangle-fill text-3xl text-amber-600 dark:text-amber-400"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Session Idle Timeout</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Your session has been idle for 5 minutes. Would you like to stay logged in or logout now?</p>
            <div class="flex flex-col sm:flex-row gap-3">
                <button id="session-stay-btn" class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold text-sm uppercase tracking-wider transition-all active:scale-95">
                    <i class="bi bi-shield-check mr-2"></i>Stay Logged In
                </button>
                <button id="session-logout-btn" class="flex-1 px-6 py-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-bold text-sm uppercase tracking-wider transition-all active:scale-95">
                    <i class="bi bi-box-arrow-right mr-2"></i>Logout Now
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sessionTimer = document.getElementById('session-timer');
    const sessionCountdown = document.getElementById('session-countdown');
    const timeoutModal = document.getElementById('session-timeout-modal');
    const stayBtn = document.getElementById('session-stay-btn');
    const logoutBtn = document.getElementById('session-logout-btn');

    if (!sessionTimer || !sessionCountdown) return;

    const timeoutSeconds = parseInt(<?php echo isset($sessionTimeout) ? $sessionTimeout : (defined('SESSION_TIMEOUT_MINUTES') ? SESSION_TIMEOUT_MINUTES * 60 : 300); ?>) || 300;
    let remainingTime = timeoutSeconds;
    let countdownInterval = null;
    let isModalShown = false;

    const activityEvents = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];

    function resetTimer() {
        if (isModalShown) return;
        remainingTime = timeoutSeconds;
    }

    activityEvents.forEach(function(evt) {
        document.addEventListener(evt, resetTimer, { passive: true });
    });

    function updateCountdown() {
        const minutes = Math.floor(remainingTime / 60);
        const seconds = remainingTime % 60;
        sessionCountdown.textContent = minutes + ':' + seconds.toString().padStart(2, '0');

        if (remainingTime <= 30) {
            sessionTimer.classList.remove('bg-amber-50', 'dark:bg-amber-900/30', 'border-amber-200', 'dark:border-amber-700');
            sessionTimer.classList.add('bg-red-50', 'dark:bg-red-900/30', 'border-red-200', 'dark:border-red-700');
            sessionCountdown.classList.remove('text-amber-700', 'dark:text-amber-300');
            sessionCountdown.classList.add('text-red-700', 'dark:text-red-300');
        } else {
            sessionTimer.classList.remove('bg-red-50', 'dark:bg-red-900/30', 'border-red-200', 'dark:border-red-700');
            sessionTimer.classList.add('bg-amber-50', 'dark:bg-amber-900/30', 'border-amber-200', 'dark:border-amber-700');
            sessionCountdown.classList.remove('text-red-700', 'dark:text-red-300');
            sessionCountdown.classList.add('text-amber-700', 'dark:text-amber-300');
        }

        if (remainingTime > 0) {
            remainingTime--;
        } else {
            showTimeoutModal();
        }
    }

    function showTimeoutModal() {
        if (isModalShown) return;
        isModalShown = true;
        if (timeoutModal) {
            timeoutModal.classList.remove('hidden');
        }
    }

    function hideTimeoutModal() {
        isModalShown = false;
        if (timeoutModal) {
            timeoutModal.classList.add('hidden');
        }
        remainingTime = timeoutSeconds;
    }

    if (stayBtn) {
        stayBtn.addEventListener('click', function() {
            hideTimeoutModal();
            // Ping server to keep session alive
            fetch('<?php echo AUTH_URL; ?>/controllers/ping.php', {
                method: 'POST',
                credentials: 'same-origin'
            }).catch(function() {});
        });
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            window.location.href = '<?php echo LOGOUT_URL; ?>';
        });
    }

    countdownInterval = setInterval(updateCountdown, 1000);
    updateCountdown();

    // Profile dropdown toggle only - notifications handled in footer.php
    const notificationsBtn = document.getElementById('notifications-btn');
    const notificationsDropdown = document.getElementById('notifications-dropdown');
    const profileBtn = document.getElementById('profile-btn');
    const profileDropdown = document.getElementById('profile-dropdown');
    
    // Profile dropdown toggle
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
            if (notificationsDropdown) {
                notificationsDropdown.classList.add('hidden');
            }
        });
    }
    
    // Close profile dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (profileDropdown && profileBtn && !profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.add('hidden');
        }
    });
    
    if (profileDropdown) {
        profileDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Dark/Light Mode Toggle
    const themeToggle = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement;
    const darkModeIcons = document.querySelectorAll('.dark-mode-icon');
    const lightModeIcons = document.querySelectorAll('.light-mode-icon');
    
    function updateIcons(isDark) {
        darkModeIcons.forEach(icon => {
            if (isDark) icon.classList.add('hidden');
            else icon.classList.remove('hidden');
        });
        lightModeIcons.forEach(icon => {
            if (isDark) icon.classList.remove('hidden');
            else icon.classList.add('hidden');
        });
    }

    // Toggle theme on button click
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            htmlElement.classList.toggle('dark');
            const isDark = htmlElement.classList.contains('dark');
            
            if (isDark) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
            updateIcons(isDark);
        });
    }
});
</script>
