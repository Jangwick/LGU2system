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
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Sidebar Toggle Button (Desktop) -->
            <button id="sidebar-toggle" class="hidden md:flex items-center justify-center w-10 h-10 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-red-600 focus:outline-none transition-all duration-200" title="Toggle Sidebar">
                <i class="bi bi-layout-sidebar-inset text-xl"></i>
            </button>
            
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-gray-600 hover:text-gray-900 focus:outline-none">
                <i class="bi bi-list text-2xl"></i>
            </button>
            
            <!-- Logo (Mobile) -->
            <div class="md:hidden flex items-center">
                <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Valenzuela" class="w-10 h-10 object-contain">
            </div>
            
            <!-- Page Title & Breadcrumb -->
            <div class="flex-1 flex items-center justify-center md:justify-start min-w-0">
                <div class="ml-0 md:ml-4 min-w-0">
                    <h2 class="text-base md:text-xl font-bold text-gray-800"><?php echo $pageTitle ?? 'Dashboard'; ?></h2>
                    <?php if (isset($breadcrumbs)): ?>
                    <nav class="hidden md:flex text-sm text-gray-600 mt-1" aria-label="Breadcrumb">
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
            </div>
            
            <!-- Right Side Actions -->
            <div class="flex items-center space-x-1 md:space-x-4">
                
                <!-- Dark/Light Mode Toggle -->
                <button id="theme-toggle" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                    <i class="bi bi-moon-fill text-lg md:text-xl dark-mode-icon"></i>
                    <i class="bi bi-sun-fill text-xl light-mode-icon hidden"></i>
                </button>
                
                <!-- Notifications -->
                <div class="relative" id="notifications-container">
                    <button id="notifications-btn" class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                        <i class="bi bi-bell text-xl"></i>
                        <span id="notification-badge" class="hidden absolute top-0 right-0 min-w-[18px] h-[18px] bg-red-500 rounded-full text-white text-xs font-bold flex items-center justify-center px-1">0</span>
                    </button>
                    
                    <!-- Notifications Dropdown -->
                    <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 md:w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50" style="background-color: white;">
                        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
                            <button id="mark-all-read-btn" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Mark all as read</button>
                        </div>
                        <div id="notifications-list" class="max-h-96 overflow-y-auto">
                            <!-- Notifications will be loaded dynamically -->
                            <div class="p-8 text-center text-gray-500">
                                <i class="bi bi-bell-slash text-3xl mb-2"></i>
                                <p class="text-sm">No notifications</p>
                            </div>
                        </div>
                        <div class="p-3 border-t border-gray-200 flex items-center justify-between">
                            <a href="<?php echo BASE_URL; ?>/modules/notifications/views/index.php" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View all notifications</a>
                            <span id="notification-count-text" class="text-xs text-gray-500">0 unread</span>
                        </div>
                    </div>
                </div>
                
                <!-- User Profile Dropdown -->
                <div class="relative">
                    <button id="profile-btn" class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-lg transition">
                        <?php if (!empty($navProfilePicture)): ?>
                            <img src="<?php echo BASE_URL; ?>/storage/profiles/<?php echo htmlspecialchars($navProfilePicture); ?>" 
                                 alt="Profile" 
                                 class="w-8 h-8 rounded-full object-cover border-2 border-red-600">
                        <?php else: ?>
                            <div class="bg-red-600 rounded-full w-8 h-8 flex items-center justify-center text-white">
                                <i class="bi bi-person-fill"></i>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
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
    const darkModeIcon = document.querySelector('.dark-mode-icon');
    const lightModeIcon = document.querySelector('.light-mode-icon');
    
    // Check for saved theme preference or default to light mode
    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark') {
        htmlElement.classList.add('dark');
        if (darkModeIcon) darkModeIcon.classList.add('hidden');
        if (lightModeIcon) lightModeIcon.classList.remove('hidden');
    }
    
    // Toggle theme
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            htmlElement.classList.toggle('dark');
            
            if (htmlElement.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
                if (darkModeIcon) darkModeIcon.classList.add('hidden');
                if (lightModeIcon) lightModeIcon.classList.remove('hidden');
            } else {
                localStorage.setItem('theme', 'light');
                if (darkModeIcon) darkModeIcon.classList.remove('hidden');
                if (lightModeIcon) lightModeIcon.classList.add('hidden');
            }
        });
    }
});
</script>
