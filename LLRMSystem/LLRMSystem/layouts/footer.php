    </div> <!-- Close flex container from header -->
    
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-sm text-gray-600">
                    &copy; <?php echo date('Y'); ?> Legislative Records Management System. All rights reserved.
                </div>
                <div class="flex items-center space-x-6 mt-2 md:mt-0">
                    <a href="/views/help/privacy.php" class="text-sm text-gray-600 hover:text-blue-600">Privacy Policy</a>
                    <a href="/views/help/terms.php" class="text-sm text-gray-600 hover:text-blue-600">Terms of Service</a>
                    <a href="/views/help/contact.php" class="text-sm text-gray-600 hover:text-blue-600">Contact Support</a>
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
        
        <!-- Copy navigation from sidebar.php -->
        <nav class="py-4">
            <div class="px-4 space-y-1">
                <!-- Same navigation items as desktop -->
            </div>
        </nav>
    </div>
    
    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>
    
    <!-- Global JavaScript -->
    <script src="/assets/js/main.js"></script>
    
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
    </script>
</body>
</html>
