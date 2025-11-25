<!-- Top Navbar -->
<nav class="bg-white shadow-md border-b border-gray-200 sticky top-0 z-40">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-gray-600 hover:text-gray-900 focus:outline-none">
                <i class="bi bi-list text-2xl"></i>
            </button>
            
            <!-- Page Title & Breadcrumb -->
            <div class="flex-1 flex items-center">
                <div class="ml-4">
                    <h2 class="text-xl font-bold text-gray-800"><?php echo $pageTitle ?? 'Dashboard'; ?></h2>
                    <?php if (isset($breadcrumbs)): ?>
                    <nav class="flex text-sm text-gray-600 mt-1" aria-label="Breadcrumb">
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
            <div class="flex items-center space-x-4">
                <!-- Search Bar -->
                <div class="hidden lg:block">
                    <div class="relative">
                        <input type="text" 
                               id="quick-search" 
                               placeholder="Quick search documents..." 
                               class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                
                <!-- Notifications -->
                <div class="relative">
                    <button id="notifications-btn" class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                        <i class="bi bi-bell text-xl"></i>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <!-- Notifications Dropdown -->
                    <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <div class="p-4 hover:bg-gray-50 border-b border-gray-100 cursor-pointer">
                                <div class="flex items-start space-x-3">
                                    <div class="bg-blue-100 rounded-full p-2">
                                        <i class="bi bi-file-earmark-text text-blue-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-800">New ordinance document uploaded</p>
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
                                        <p class="text-sm text-gray-800">Document approved successfully</p>
                                        <p class="text-xs text-gray-500 mt-1">1 hour ago</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 border-t border-gray-200">
                            <a href="/views/notifications/index.php" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View all notifications</a>
                        </div>
                    </div>
                </div>
                
                <!-- User Profile Dropdown -->
                <div class="relative">
                    <button id="profile-btn" class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-lg transition">
                        <div class="bg-blue-600 rounded-full w-8 h-8 flex items-center justify-center text-white">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="hidden md:block text-left">
                            <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest User'); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Guest'); ?></p>
                        </div>
                        <i class="bi bi-chevron-down text-gray-600 text-xs"></i>
                    </button>
                    
                    <!-- Profile Dropdown -->
                    <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                        <div class="p-4 border-b border-gray-200">
                            <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'guest@lgu.gov'); ?></p>
                            <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($_SESSION['user_department'] ?? 'Legislative Office'); ?></p>
                        </div>
                        <div class="py-2">
                            <a href="/views/profile/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="bi bi-person mr-2"></i>My Profile
                            </a>
                            <a href="/views/profile/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="bi bi-gear mr-2"></i>Settings
                            </a>
                            <a href="/views/help/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="bi bi-question-circle mr-2"></i>Help & Support
                            </a>
                        </div>
                        <div class="border-t border-gray-200 py-2">
                            <a href="/controllers/Auth/LogoutController.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
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
    // Notifications dropdown toggle
    document.getElementById('notifications-btn')?.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('notifications-dropdown');
        dropdown.classList.toggle('hidden');
        document.getElementById('profile-dropdown').classList.add('hidden');
    });
    
    // Profile dropdown toggle
    document.getElementById('profile-btn')?.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('profile-dropdown');
        dropdown.classList.toggle('hidden');
        document.getElementById('notifications-dropdown').classList.add('hidden');
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.getElementById('notifications-dropdown')?.classList.add('hidden');
        document.getElementById('profile-dropdown')?.classList.add('hidden');
    });
</script>
