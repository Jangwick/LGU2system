<!-- Top Navbar -->
<header class="bg-white shadow-md border-b border-gray-200 sticky top-0 z-40">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Left Side: Toggle & Breadcrumbs -->
            <div class="flex items-center">
                <!-- Sidebar Toggle -->
                <button id="sidebar-toggle" class="text-gray-600 hover:text-gray-900 focus:outline-none p-2 hover:bg-gray-100 rounded-lg transition-all mr-4" title="Toggle Sidebar">
                    <i class="bi bi-list text-xl"></i>
                </button>
                
                <!-- Mobile Logo -->
                <div class="md:hidden flex items-center">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="w-8 h-8 mr-2">
                    <span class="font-bold text-blue-800">VDM</span>
                </div>
                
                <!-- Breadcrumbs (Desktop) -->
                <nav class="hidden md:flex items-center text-sm" aria-label="Breadcrumb">
                    <a href="<?php echo DASHBOARD_INDEX_URL; ?>" class="text-gray-500 hover:text-blue-600 transition-colors">
                        <i class="bi bi-house-door"></i>
                    </a>
                    <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                        <?php foreach ($breadcrumbs as $crumb): ?>
                            <i class="bi bi-chevron-right text-gray-400 mx-2 text-xs"></i>
                            <?php if (isset($crumb['url'])): ?>
                                <a href="<?php echo $crumb['url']; ?>" class="text-gray-500 hover:text-blue-600 transition-colors">
                                    <?php echo htmlspecialchars($crumb['label']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-800 font-medium"><?php echo htmlspecialchars($crumb['label']); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </nav>
            </div>
            
            <!-- Center: Page Title (Mobile) -->
            <div class="md:hidden">
                <h1 class="text-lg font-bold text-gray-800"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
            </div>
            
            <!-- Right Side: Actions -->
            <div class="flex items-center space-x-2 md:space-x-4">
                <!-- Search (Desktop) -->
                <div class="hidden lg:block relative">
                    <input type="text" 
                           id="global-search" 
                           placeholder="Search documents, sessions..." 
                           class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                
                <!-- Active Voting Indicator -->
                <?php if (isset($activeVotingSession) && $activeVotingSession): ?>
                <a href="<?php echo VOTING_URL; ?>/views/cast-vote.php" class="hidden md:flex items-center px-3 py-1.5 bg-green-100 text-green-700 rounded-full text-sm font-medium animate-pulse">
                    <i class="bi bi-broadcast mr-1"></i>
                    <span>Live Voting</span>
                </a>
                <?php endif; ?>
                
                <!-- Notifications -->
                <div class="relative">
                    <button id="notifications-btn" class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all">
                        <i class="bi bi-bell text-xl"></i>
                        <span id="notification-badge" class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <!-- Notifications Dropdown -->
                    <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
                            <button class="text-xs text-blue-600 hover:text-blue-700">Mark all read</button>
                        </div>
                        <div class="max-h-96 overflow-y-auto" id="notifications-list">
                            <div class="p-4 hover:bg-gray-50 border-b border-gray-100 cursor-pointer">
                                <div class="flex items-start space-x-3">
                                    <div class="bg-blue-100 rounded-full p-2">
                                        <i class="bi bi-hand-thumbs-up text-blue-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-800">New voting session scheduled</p>
                                        <p class="text-xs text-gray-500 mt-1">5 minutes ago</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 hover:bg-gray-50 border-b border-gray-100 cursor-pointer">
                                <div class="flex items-start space-x-3">
                                    <div class="bg-green-100 rounded-full p-2">
                                        <i class="bi bi-check-circle text-green-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-800">Ordinance 2025-001 approved</p>
                                        <p class="text-xs text-gray-500 mt-1">1 hour ago</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 border-t border-gray-200">
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View all notifications</a>
                        </div>
                    </div>
                </div>
                
                <!-- User Profile Dropdown -->
                <div class="relative">
                    <button id="profile-btn" class="flex items-center space-x-2 p-2 hover:bg-gray-100 rounded-lg transition-all">
                        <div class="bg-blue-600 rounded-full w-8 h-8 flex items-center justify-center text-white">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <span class="hidden md:block text-sm font-medium text-gray-700">
                            <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                        </span>
                        <i class="bi bi-chevron-down text-gray-400 text-xs hidden md:inline"></i>
                    </button>
                    
                    <!-- Profile Dropdown -->
                    <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                        <div class="p-4 border-b border-gray-200">
                            <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
                            <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'User')); ?></p>
                        </div>
                        <div class="py-2">
                            <a href="<?php echo BASE_URL; ?>/modules/profile/views/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="bi bi-person mr-2"></i>My Profile
                            </a>
                            <a href="<?php echo BASE_URL; ?>/modules/profile/views/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="bi bi-gear mr-2"></i>Settings
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
</header>

<script>
// Toggle dropdowns
document.getElementById('notifications-btn')?.addEventListener('click', function(e) {
    e.stopPropagation();
    document.getElementById('notifications-dropdown').classList.toggle('hidden');
    document.getElementById('profile-dropdown').classList.add('hidden');
});

document.getElementById('profile-btn')?.addEventListener('click', function(e) {
    e.stopPropagation();
    document.getElementById('profile-dropdown').classList.toggle('hidden');
    document.getElementById('notifications-dropdown').classList.add('hidden');
});

// Close dropdowns when clicking outside
document.addEventListener('click', function() {
    document.getElementById('notifications-dropdown')?.classList.add('hidden');
    document.getElementById('profile-dropdown')?.classList.add('hidden');
});

// Sidebar toggle
document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    sidebar?.classList.toggle('sidebar-collapsed');
});
</script>
