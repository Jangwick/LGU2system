    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-3 md:py-4">
            <div class="flex flex-col md:flex-row justify-between items-center gap-2 md:gap-0">
                <div class="flex items-center space-x-2 md:space-x-3">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Valenzuela" class="w-8 h-8 md:w-10 md:h-10 object-contain">
                    <div class="text-xs md:text-sm text-gray-600 text-center md:text-left">
                        &copy; <?php echo date('Y'); ?> <span class="hidden sm:inline">City Government of Valenzuela - </span>LRMS<span class="hidden md:inline">. All rights reserved.</span>
                    </div>
                </div>
                <div class="flex items-center space-x-3 md:space-x-6">
                    <a href="/modules/help/views/privacy.php" class="text-xs md:text-sm text-gray-600 hover:text-red-600">Privacy</a>
                    <a href="/modules/help/views/terms.php" class="text-xs md:text-sm text-gray-600 hover:text-red-600">Terms</a>
                    <a href="/modules/help/views/contact.php" class="text-xs md:text-sm text-gray-600 hover:text-red-600">Support</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"></div>
    
    <!-- Mobile Sidebar -->
    <div id="mobile-sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full md:hidden w-64 bg-gradient-to-b from-red-800 to-red-900 text-white z-50 transition-transform duration-300 ease-in-out overflow-y-auto">
        <!-- Mobile sidebar header -->
        <div class="p-4 border-b border-red-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white rounded-full p-1 shadow-md">
                        <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Valenzuela Logo" class="w-10 h-10 object-contain">
                    </div>
                    <div>
                        <h1 class="text-lg font-bold">LRMS</h1>
                        <p class="text-xs text-red-200">Legislative Records</p>
                    </div>
                </div>
                <button id="close-mobile-sidebar" class="text-white p-2 hover:bg-red-700 rounded-lg">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Navigation Menu -->
        <nav class="flex-1 py-4 px-3">
            <?php 
            $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
            ?>
            
            <!-- Dashboard -->
            <a href="<?php echo DASHBOARD_INDEX_URL; ?>" class="flex items-center px-4 py-3 text-white hover:bg-red-700 rounded-lg mb-1 transition-colors <?php echo ($currentPage ?? '') === 'dashboard' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-speedometer2 mr-3 text-lg"></i>
                <span>Dashboard</span>
            </a>
            
            <!-- Documents Section -->
            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300 uppercase tracking-wider">Documents</p>
            </div>
            
            <a href="<?php echo DOCUMENTS_INDEX_URL; ?>" class="flex items-center px-4 py-3 text-white hover:bg-red-700 rounded-lg mb-1 transition-colors <?php echo ($currentPage ?? '') === 'documents' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-file-earmark-text mr-3 text-lg"></i>
                <span>All Documents</span>
            </a>
            
            <a href="<?php echo SEARCH_URL; ?>/views/index.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700 rounded-lg mb-1 transition-colors <?php echo ($currentPage ?? '') === 'search' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-search mr-3 text-lg"></i>
                <span>Advanced Search</span>
            </a>
            
            <!-- Reports & Analytics - Officer and Admin only -->
            <?php if (in_array($userRole, ['officer', 'administrator', 'admin'])): ?>
            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300 uppercase tracking-wider">Analytics</p>
            </div>
            
            <a href="<?php echo REPORTS_URL; ?>/views/index.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700 rounded-lg mb-1 transition-colors <?php echo ($currentPage ?? '') === 'reports' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-graph-up mr-3 text-lg"></i>
                <span>Reports & Analytics</span>
            </a>
            <?php endif; ?>
            
            <!-- Administration - Admin only -->
            <?php if (in_array($userRole, ['administrator', 'admin'])): ?>
            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300 uppercase tracking-wider">Administration</p>
            </div>
            
            <a href="<?php echo USERS_URL; ?>/views/index.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700 rounded-lg mb-1 transition-colors <?php echo ($currentPage ?? '') === 'users' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-people mr-3 text-lg"></i>
                <span>User Management</span>
            </a>
            
            <a href="<?php echo AUDIT_URL; ?>/views/index.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700 rounded-lg mb-1 transition-colors <?php echo ($currentPage ?? '') === 'audit' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-shield-check mr-3 text-lg"></i>
                <span>Audit Log</span>
            </a>
            <?php endif; ?>
            
            <!-- Help -->
            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300 uppercase tracking-wider">Support</p>
            </div>
            
            <a href="<?php echo HELP_URL; ?>/views/index.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700 rounded-lg mb-1 transition-colors <?php echo ($currentPage ?? '') === 'help' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-question-circle mr-3 text-lg"></i>
                <span>Help & Support</span>
            </a>
        </nav>
        
        <!-- Mobile User Profile Section -->
        <div class="p-4 border-t border-red-700 mt-auto">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-red-700 flex items-center justify-center">
                    <i class="bi bi-person text-white text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest'); ?></p>
                    <p class="text-xs text-red-200 truncate"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Viewer'); ?></p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="<?php echo USERS_URL; ?>/views/profile.php" class="flex-1 text-center py-2 text-xs bg-red-700 hover:bg-red-600 rounded-lg transition-colors">
                    <i class="bi bi-person mr-1"></i>Profile
                </a>
                <a href="<?php echo LOGOUT_URL; ?>" class="flex-1 text-center py-2 text-xs bg-red-950 hover:bg-red-900 rounded-lg transition-colors">
                    <i class="bi bi-box-arrow-right mr-1"></i>Logout
                </a>
            </div>
        </div>
    </div>
    
    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>
    
    <!-- Global JavaScript -->
    <script src="<?php echo asset('js/main.js'); ?>"></script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($pageScript)): ?>
        <script src="<?php echo htmlspecialchars($pageScript); ?>"></script>
    <?php endif; ?>
    
    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileSidebar = document.getElementById('mobile-sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const closeMobileSidebar = document.getElementById('close-mobile-sidebar');
        
        function openMobileSidebar() {
            mobileSidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
        }
        
        function closeMobileSidebarFn() {
            mobileSidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        }
        
        mobileMenuBtn?.addEventListener('click', openMobileSidebar);
        closeMobileSidebar?.addEventListener('click', closeMobileSidebarFn);
        sidebarOverlay?.addEventListener('click', closeMobileSidebarFn);
        
        // Loading state utilities
        window.showPageLoader = function() {
            document.getElementById('page-loader')?.classList.remove('hidden');
        };
        
        window.hidePageLoader = function() {
            document.getElementById('page-loader')?.classList.add('hidden');
        };
        
        // Show toast notification
        window.showToast = function(message, type = 'info') {
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-red-500'
            };
            const icons = {
                success: 'bi-check-circle-fill',
                error: 'bi-x-circle-fill',
                warning: 'bi-exclamation-triangle-fill',
                info: 'bi-info-circle-fill'
            };
            
            toast.className = `${colors[type]} text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 transform transition-all duration-300 translate-x-0 opacity-100 min-w-[300px]`;
            toast.innerHTML = `
                <i class="bi ${icons[type]} text-xl"></i>
                <span class="font-semibold">${message}</span>
            `;
            
            const container = document.getElementById('toast-container');
            container.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
            }, 10);
            
            // Remove after 3 seconds
            setTimeout(() => {
                toast.style.transform = 'translateX(400px)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        };
        
        // Create skeleton loader
        window.createSkeletonCard = function() {
            return `
                <div class="skeleton-card animate-shimmer">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="skeleton-circle w-12 h-12"></div>
                        <div class="flex-1">
                            <div class="skeleton-text w-3/4 mb-2"></div>
                            <div class="skeleton-text w-1/2"></div>
                        </div>
                    </div>
                    <div class="skeleton-text w-full mb-2"></div>
                    <div class="skeleton-text w-5/6"></div>
                </div>
            `;
        };
        
        // Show skeleton loaders for a container
        window.showSkeletonLoaders = function(containerId, count = 3) {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            container.innerHTML = '';
            for (let i = 0; i < count; i++) {
                container.innerHTML += createSkeletonCard();
            }
        };
        
        // AJAX fetch with loading state
        window.fetchWithLoading = async function(url, options = {}) {
            showPageLoader();
            try {
                const response = await fetch(url, options);
                const data = await response.json();
                return data;
            } catch (error) {
                showToast('An error occurred. Please try again.', 'error');
                throw error;
            } finally {
                hidePageLoader();
            }
        };
        
        // Hide page loader on page load
        window.addEventListener('load', () => {
            hidePageLoader();
        });
        
        // ========================================
        // Desktop Sidebar Toggle Functionality
        // ========================================
        (function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = sidebar?.nextElementSibling;
            
            if (!sidebarToggle || !sidebar) {
                return;
            }
            
            // Add transition class to main content for smooth animation
            if (mainContent) {
                mainContent.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            }
            
            // Check for saved sidebar state
            const sidebarState = localStorage.getItem('sidebarCollapsed');
            if (sidebarState === 'true') {
                sidebar.classList.remove('sidebar-expanded', 'w-64');
                sidebar.classList.add('sidebar-collapsed');
                sidebarToggle.classList.add('sidebar-hidden');
            }
            
            // Toggle sidebar on button click
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const isExpanded = sidebar.classList.contains('sidebar-expanded');
                
                // Add a subtle scale animation to the button
                this.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
                
                if (isExpanded) {
                    // Collapse sidebar with animation
                    sidebar.classList.remove('sidebar-expanded', 'w-64');
                    sidebar.classList.add('sidebar-collapsed');
                    this.classList.add('sidebar-hidden');
                    localStorage.setItem('sidebarCollapsed', 'true');
                    
                    // Animate main content expansion
                    if (mainContent) {
                        mainContent.style.transform = 'scale(1.005)';
                        setTimeout(() => {
                            mainContent.style.transform = '';
                        }, 400);
                    }
                } else {
                    // Expand sidebar with animation
                    sidebar.classList.remove('sidebar-collapsed');
                    sidebar.classList.add('sidebar-expanded', 'w-64');
                    this.classList.remove('sidebar-hidden');
                    localStorage.setItem('sidebarCollapsed', 'false');
                }
            });
        })();
    </script>
    </div> <!-- Close flex container from header -->
</body>
</html>