    </div> <!-- Close flex container from header -->
    
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-sm text-gray-600">
                    &copy; <?php echo date('Y'); ?> Legislative Records Management System. All rights reserved.
                </div>
                <div class="flex items-center space-x-6 mt-2 md:mt-0">
                    <a href="/modules/help/views/privacy.php" class="text-sm text-gray-600 hover:text-blue-600">Privacy Policy</a>
                    <a href="/modules/help/views/terms.php" class="text-sm text-gray-600 hover:text-blue-600">Terms of Service</a>
                    <a href="/modules/help/views/contact.php" class="text-sm text-gray-600 hover:text-blue-600">Contact Support</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"></div>
    
    <!-- Mobile Sidebar -->
    <div id="mobile-sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full md:hidden w-64 bg-gradient-to-b from-blue-800 to-blue-900 text-white z-50 transition-transform duration-300 ease-in-out overflow-y-auto">
        <!-- Mobile sidebar content (same as desktop sidebar) -->
        <div class="p-6 border-b border-blue-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white rounded-lg p-2">
                        <i class="bi bi-file-earmark-text text-blue-800 text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold">LRMS</h1>
                        <p class="text-xs text-blue-200">Legislative Records</p>
                    </div>
                </div>
                <button id="close-mobile-sidebar" class="text-white">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>
    
    <!-- Global JavaScript -->
    <script src="/LLRMSystem/public/assets/js/main.js"></script>
    
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
                info: 'bg-blue-500'
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
    </script>
</body>
</html>
