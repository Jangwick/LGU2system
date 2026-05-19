    <!-- Footer -->
    <footer class="w-full bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-3 md:py-4">
            <!-- Desktop Layout -->
            <div class="hidden md:flex justify-between items-center gap-8">
                <div class="flex items-center space-x-3 flex-shrink-0">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Valenzuela" class="h-6 w-6 object-contain">
                    <div class="text-[10px] text-gray-400 font-black uppercase tracking-wider">&copy; <?php echo date('Y'); ?> LRMS. All rights reserved.</div>
                </div>
                <div class="flex items-center space-x-6 flex-shrink-0">
                    <a href="<?php echo HELP_URL; ?>/views/faq.php" class="text-[10px] font-black text-slate-400 hover:text-red-600 uppercase tracking-[0.2em] transition-colors">FAQ</a>
                    <a href="<?php echo HELP_URL; ?>/views/privacy.php" class="text-[10px] font-black text-slate-400 hover:text-red-600 uppercase tracking-[0.2em] transition-colors">Privacy</a>
                    <a href="<?php echo HELP_URL; ?>/views/terms.php" class="text-[10px] font-black text-slate-400 hover:text-red-600 uppercase tracking-[0.2em] transition-colors">Terms</a>
                    <a href="<?php echo HELP_URL; ?>/views/contact.php" class="text-[10px] font-black text-slate-400 hover:text-red-600 uppercase tracking-[0.2em] transition-colors">Support</a>
                </div>
            </div>
            
            <!-- Mobile Layout -->
            <div class="md:hidden flex flex-col items-center justify-center py-6 px-4 space-y-6 text-center">
                <div class="flex flex-col items-center space-y-2">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Valenzuela" class="h-8 w-8 object-contain opacity-70 mb-1">
                    <span class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em]">&copy; <?php echo date('Y'); ?> LRMS. ALL RIGHTS RESERVED.</span>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-x-10 gap-y-4 px-4">
                    <a href="<?php echo HELP_URL; ?>/views/faq.php" class="text-[10px] font-black text-slate-400 hover:text-red-600 uppercase tracking-[0.2em] transition-colors">FAQ</a>
                    <a href="<?php echo HELP_URL; ?>/views/privacy.php" class="text-[10px] font-black text-slate-400 hover:text-red-600 uppercase tracking-[0.2em] transition-colors">Privacy</a>
                    <a href="<?php echo HELP_URL; ?>/views/terms.php" class="text-[10px] font-black text-slate-400 hover:text-red-600 uppercase tracking-[0.2em] transition-colors">Terms</a>
                    <a href="<?php echo HELP_URL; ?>/views/contact.php" class="text-[10px] font-black text-slate-400 hover:text-red-600 uppercase tracking-[0.2em] transition-colors">Support</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button id="back-to-top" class="no-ripple fixed z-[999999] bg-red-600 text-white rounded-full border-3 border-white cursor-pointer shadow-lg shadow-red-600/50 items-center justify-center transition-all duration-300 hover:bg-red-700 hover:scale-110 active:scale-95"
            style="display: flex; bottom: 2rem; right: 1rem; width: 3.5rem; height: 3.5rem;"
            title="Back to top"
            aria-label="Scroll to top">
        <i class="bi bi-arrow-up text-2xl leading-none pointer-events-none"></i>
    </button>
    
    <script>
    // Back to Top Button - Immediate execution
    (function() {
        var btn = document.getElementById('back-to-top');
        if (!btn) return;
        
        function checkScroll() {
            var scrolled = false;
            
            // Check window scroll
            if (window.pageYOffset > 200 || document.documentElement.scrollTop > 200) {
                scrolled = true;
            }
            
            // Check main element scroll
            var main = document.querySelector('main');
            if (main && main.scrollTop > 200) {
                scrolled = true;
            }
            
            // Check any overflow-y-auto elements
            var scrollables = document.querySelectorAll('.overflow-y-auto');
            scrollables.forEach(function(el) {
                if (el.scrollTop > 200) {
                    scrolled = true;
                }
            });
            
            btn.style.display = scrolled ? 'flex' : 'none';
        }
        
        function scrollToTop() {
            // Scroll window
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            // Scroll main element
            var main = document.querySelector('main');
            if (main) main.scrollTo({ top: 0, behavior: 'smooth' });
            
            // Scroll any overflow-y-auto elements
            document.querySelectorAll('.overflow-y-auto').forEach(function(el) {
                el.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
        
        // Add click handler
        btn.onclick = scrollToTop;
        
        // Listen for scroll on window
        window.addEventListener('scroll', checkScroll, { passive: true });
        
        // Listen for scroll on main
        var main = document.querySelector('main');
        if (main) main.addEventListener('scroll', checkScroll, { passive: true });
        
        // Listen on overflow-y-auto elements
        document.querySelectorAll('.overflow-y-auto').forEach(function(el) {
            el.addEventListener('scroll', checkScroll, { passive: true });
        });
        
        // Initial check
        checkScroll();
        
        // Check again after DOM is fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            var main = document.querySelector('main');
            if (main) main.addEventListener('scroll', checkScroll, { passive: true });
            checkScroll();
        });
        
        // And again after everything loads
        window.addEventListener('load', function() {
            var main = document.querySelector('main');
            if (main) main.addEventListener('scroll', checkScroll, { passive: true });
            checkScroll();
        });
    })();
    </script>
    
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 md:hidden opacity-0 pointer-events-none transition-all duration-300 ease-out"></div>
    
    <!-- Mobile Sidebar -->
    <div id="mobile-sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full md:hidden w-72 bg-gradient-to-b from-red-800 to-red-900 text-white z-50 transition-transform duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] overflow-hidden flex flex-col shadow-2xl">
        <!-- Mobile sidebar header -->
        <div class="p-4 border-b border-red-700/50 sidebar-header">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 sidebar-logo">
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
            <a href="<?php echo DASHBOARD_INDEX_URL; ?>" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo ($currentPage ?? '') === 'dashboard' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-speedometer2 mr-3 text-lg"></i>
                <span>Dashboard</span>
            </a>
            
            <!-- Documents Section -->
            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300/80 uppercase tracking-wider">Documents</p>
            </div>
            
            <a href="<?php echo DOCUMENTS_INDEX_URL; ?>" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo ($currentPage ?? '') === 'documents' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-file-earmark-text mr-3 text-lg"></i>
                <span>All Documents</span>
            </a>
            
            <a href="<?php echo SEARCH_URL; ?>/views/index.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo ($currentPage ?? '') === 'search' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-search mr-3 text-lg"></i>
                <span>Advanced Search</span>
            </a>
            
            <!-- Reports & Analytics - Officer and Admin only -->
            <?php if (in_array($userRole, ['officer', 'administrator', 'admin'])): ?>
            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300/80 uppercase tracking-wider">Analytics</p>
            </div>
            
            <a href="<?php echo REPORTS_URL; ?>/views/index.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo ($currentPage ?? '') === 'reports' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-graph-up mr-3 text-lg"></i>
                <span>Reports & Analytics</span>
            </a>

            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300/80 uppercase tracking-wider">Research & Analysis</p>
            </div>
            
            <a href="<?php echo RESEARCH_URL; ?>/views/index.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo ($currentPage ?? '') === 'research-analysis' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-lightbulb mr-3 text-lg"></i>
                <span>Legislative Analysis</span>
            </a>
            <?php endif; ?>
            
            <!-- Administration - Admin only -->
            <?php if (in_array($userRole, ['administrator', 'admin'])): ?>
            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300/80 uppercase tracking-wider">Administration</p>
            </div>
            
            <a href="<?php echo USERS_URL; ?>/views/index.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo ($currentPage ?? '') === 'users' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-people mr-3 text-lg"></i>
                <span>User Management</span>
            </a>
            
            <a href="<?php echo AUDIT_URL; ?>/views/index.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo ($currentPage ?? '') === 'audit' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-shield-check mr-3 text-lg"></i>
                <span>Audit Log</span>
            </a>
            <?php endif; ?>

            <!-- Super Admin Section - Super Admin Only -->
            <?php if ($userRole === 'super_admin'): ?>
            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-purple-300/80 uppercase tracking-wider">Super Admin</p>
            </div>

            <a href="<?php echo CORE_URL; ?>/views/admin-management.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo ($currentPage ?? '') === 'admin-management' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-shield-lock mr-3 text-lg"></i>
                <span>Admin Management</span>
            </a>

            <a href="<?php echo CORE_URL; ?>/views/system-config.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo ($currentPage ?? '') === 'system-config' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-gear mr-3 text-lg"></i>
                <span>System Config</span>
            </a>

            <a href="<?php echo CORE_URL; ?>/views/database-backup.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo ($currentPage ?? '') === 'database-backup' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-database mr-3 text-lg"></i>
                <span>Database Backup</span>
            </a>

            <a href="<?php echo CORE_URL; ?>/views/audit-logs.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo ($currentPage ?? '') === 'audit-logs' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-journal-text mr-3 text-lg"></i>
                <span>Audit Logs</span>
            </a>
            <?php endif; ?>

            <!-- Integration Section - Mobile -->
            <?php if (in_array($userRole, ['officer', 'administrator', 'admin'])): ?>
            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300/80 uppercase tracking-wider">Integration</p>
            </div>
            
            <div class="px-2">
                <button onclick="toggleMobileDropdown('integrationMobileDropdown')" 
                        style="background-color: #991b1b !important;"
                        class="w-full flex items-center justify-between px-4 py-3 text-white hover:brightness-125 rounded-lg transition-all duration-200 group">
                    <div class="flex items-center">
                        <i class="bi bi-plug mr-3 text-lg text-white"></i>
                        <span class="font-semibold text-white">Integration Modules</span>
                    </div>
                    <i class="bi bi-chevron-down text-xs transition-transform duration-200 text-white" id="integrationMobileDropdown-icon"></i>
                </button>
                
                <div id="integrationMobileDropdown" class="hidden overflow-hidden transition-all duration-300 max-h-0 bg-red-950/30 rounded-xl mt-1.5 border border-red-700/10">
                    <a href="<?php echo BASE_URL; ?>/modules/integration/views/ordinances.php" class="flex items-center px-10 py-3 text-sm text-red-100 hover:text-white hover:bg-red-700/50 rounded-lg transition-all">
                        <i class="bi bi-journal-text mr-3"></i>
                        <span>Ordinances</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/integration/views/sessions.php" class="flex items-center px-10 py-3 text-sm text-red-100 hover:text-white hover:bg-red-700/50 rounded-lg transition-all">
                        <i class="bi bi-calendar3 mr-3"></i>
                        <span>Sessions</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/integration/views/agendas.php" class="flex items-center px-10 py-3 text-sm text-red-100 hover:text-white hover:bg-red-700/50 rounded-lg transition-all">
                        <i class="bi bi-list-check mr-3"></i>
                        <span>Agendas</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/integration/views/committees.php" class="flex items-center px-10 py-3 text-sm text-red-100 hover:text-white hover:bg-red-700/50 rounded-lg transition-all">
                        <i class="bi bi-people mr-3"></i>
                        <span>Committees</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/integration/views/voting.php" class="flex items-center px-10 py-3 text-sm text-red-100 hover:text-white hover:bg-red-700/50 rounded-lg transition-all">
                        <i class="bi bi-hand-thumbs-up mr-3"></i>
                        <span>Voting Records</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/integration/views/hearings.php" class="flex items-center px-10 py-3 text-sm text-red-100 hover:text-white hover:bg-red-700/50 rounded-lg transition-all">
                        <i class="bi bi-megaphone mr-3"></i>
                        <span>Public Hearings</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/integration/views/archives.php" class="flex items-center px-10 py-3 text-sm text-red-100 hover:text-white hover:bg-red-700/50 rounded-lg transition-all">
                        <i class="bi bi-archive mr-3"></i>
                        <span>Archives</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/integration/views/consultations.php" class="flex items-center px-10 py-3 text-sm text-red-100 hover:text-white hover:bg-red-700/50 rounded-lg transition-all">
                        <i class="bi bi-chat-dots mr-3"></i>
                        <span>Consultations</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/modules/integration/views/research.php" class="flex items-center px-10 py-3 text-sm text-red-100 hover:text-white hover:bg-red-700/50 rounded-lg transition-all">
                        <i class="bi bi-book mr-3"></i>
                        <span>Research</span>
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Help -->
            <div class="mt-4 mb-2 px-4">
                <p class="text-xs font-semibold text-red-300/80 uppercase tracking-wider">Support</p>
            </div>
            
            <a href="<?php echo HELP_URL; ?>/views/index.php" class="flex items-center px-4 py-3 text-white hover:bg-red-700/70 rounded-lg mb-1 transition-all duration-200 hover:translate-x-1 <?php echo ($currentPage ?? '') === 'help' ? 'bg-red-700' : ''; ?>">
                <i class="bi bi-question-circle mr-3 text-lg"></i>
                <span>Help & Support</span>
            </a>

            <!-- Theme Toggle - Hidden on mobile (already in navbar), visible on larger sidebar -->
            <div class="mt-6 px-4 pt-6 border-t border-red-700/30 hidden md:block">
                <button onclick="document.getElementById('theme-toggle').click();" class="w-full flex items-center justify-between px-4 py-3 bg-red-900/40 text-white rounded-xl border border-red-700/30 hover:bg-red-700/50 transition-all group">
                    <div class="flex items-center">
                        <i class="bi bi-moon-fill mr-3 text-lg dark-mode-icon"></i>
                        <i class="bi bi-sun-fill mr-3 text-lg light-mode-icon hidden"></i>
                        <span class="font-bold">Display Mode</span>
                    </div>
                    <span class="text-[10px] font-black bg-red-700/50 px-2 py-1 rounded-md uppercase tracking-widest group-hover:bg-red-600">Switch</span>
                </button>
            </div>
        </nav>
        
        <!-- Mobile User Profile Section - Fixed at Bottom -->
        <div class="p-3 mt-auto border-t border-red-700/40">
            <!-- User Info -->
            <div class="flex items-center space-x-2.5 mb-2.5">
                <div class="w-9 h-9 rounded-full bg-red-700 flex items-center justify-center">
                    <i class="bi bi-person-fill text-white text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest'); ?></p>
                    <p class="text-xs text-red-300 truncate"><?php echo ucfirst(htmlspecialchars($_SESSION['user_role'] ?? 'Viewer')); ?></p>
                </div>
            </div>
            
            <!-- Action Buttons - Side by Side -->
            <div class="flex gap-2">
                <a href="<?php echo USERS_URL; ?>/views/profile.php" class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium bg-red-700 hover:bg-red-600 text-white rounded-lg transition-colors">
                    <i class="bi bi-person-gear"></i>
                    <span>Profile</span>
                </a>
                <a href="<?php echo LOGOUT_URL; ?>" class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium bg-red-950 hover:bg-red-900 text-red-200 rounded-lg transition-colors">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
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
        // Mobile menu toggle with animations
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileSidebar = document.getElementById('mobile-sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const closeMobileSidebar = document.getElementById('close-mobile-sidebar');
        
        function toggleMobileDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const icon = document.getElementById(dropdownId + '-icon');
            
            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden');
                setTimeout(() => {
                    dropdown.style.maxHeight = '600px';
                }, 10);
                if (icon) icon.classList.add('rotate-180');
            } else {
                dropdown.style.maxHeight = '0';
                setTimeout(() => {
                    dropdown.classList.add('hidden');
                }, 300);
                if (icon) icon.classList.remove('rotate-180');
            }
        }
        
        function openMobileSidebar() {
            // Show overlay with fade and blur
            sidebarOverlay.classList.remove('opacity-0', 'pointer-events-none');
            sidebarOverlay.classList.add('opacity-100', 'pointer-events-auto');
            
            // Slide in sidebar
            mobileSidebar.classList.remove('-translate-x-full');
            mobileSidebar.classList.add('translate-x-0');
            
            // Animate menu items with stagger effect
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
            
            // Animate header
            const header = mobileSidebar.querySelector('.sidebar-header');
            if (header) {
                header.style.opacity = '0';
                header.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    header.style.transition = 'all 0.3s ease-out';
                    header.style.opacity = '1';
                    header.style.transform = 'translateY(0)';
                }, 100);
            }
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }
        
        function closeMobileSidebarFn() {
            // Hide overlay with fade
            sidebarOverlay.classList.add('opacity-0', 'pointer-events-none');
            sidebarOverlay.classList.remove('opacity-100', 'pointer-events-auto');
            
            // Slide out sidebar
            mobileSidebar.classList.add('-translate-x-full');
            mobileSidebar.classList.remove('translate-x-0');
            
            // Restore body scroll
            document.body.style.overflow = '';
        }
        
        mobileMenuBtn?.addEventListener('click', openMobileSidebar);
        closeMobileSidebar?.addEventListener('click', closeMobileSidebarFn);
        sidebarOverlay?.addEventListener('click', closeMobileSidebarFn);
        
        // Close sidebar on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !mobileSidebar.classList.contains('-translate-x-full')) {
                closeMobileSidebarFn();
            }
        });
        
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
        
        // =====================
        // Notification System
        // =====================
        (function() {
            const notificationBtn = document.getElementById('notifications-btn');
            const notificationDropdown = document.getElementById('notifications-dropdown');
            const notificationBadge = document.getElementById('notification-badge');
            const notificationsList = document.getElementById('notifications-list');
            const notificationCountText = document.getElementById('notification-count-text');
            const markAllReadBtn = document.getElementById('mark-all-read-btn');
            
            let currentNotifications = [];

            if (!notificationBtn || !notificationDropdown) return;
            
            // Get notification API URL using PHP BASE_URL
            const NOTIFICATION_API_URL = '<?php echo BASE_URL; ?>/modules/notifications/api/notifications.php';
            
            // Fetch notifications
            async function fetchNotifications() {
                try {
                    const response = await fetch(NOTIFICATION_API_URL + '?action=list&limit=10', {
                        credentials: 'same-origin'
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        currentNotifications = data.notifications;
                        updateNotificationBadge(data.unread_count);
                        renderNotifications(data.notifications);
                    } else {
                        console.log('Notification fetch:', data.message);
                    }
                } catch (error) {
                    console.error('Error fetching notifications:', error);
                }
            }
            
            // Update badge count
            function updateNotificationBadge(count) {
                if (count > 0) {
                    notificationBadge.textContent = count > 99 ? '99+' : count;
                    notificationBadge.classList.remove('hidden');
                    notificationBadge.classList.add('flex');
                } else {
                    notificationBadge.classList.add('hidden');
                    notificationBadge.classList.remove('flex');
                }
                
                if (notificationCountText) {
                    notificationCountText.textContent = count + ' unread';
                }
            }
            
            // Render notifications
            function renderNotifications(notifications) {
                if (!notifications || notifications.length === 0) {
                    notificationsList.innerHTML = `
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="bi bi-bell-slash text-3xl mb-2"></i>
                            <p class="text-sm">No notifications</p>
                        </div>`;
                    return;
                }
                
                notificationsList.innerHTML = notifications.map(n => {
                    const link = (n.data && n.data.link) ? n.data.link : '';
                    return `
                    <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-800 border-b border-gray-100 dark:border-gray-800 cursor-pointer notification-item ${n.is_read ? 'opacity-60' : ''}" 
                         data-id="${n.id}" onclick="handleNotificationClick(${n.id}, ${JSON.stringify(link).replace(/"/g, '&quot;')})">
                        <div class="flex items-start space-x-3">
                            <div class="${getNotificationIconBg(n.type)} rounded-full p-2 flex-shrink-0">
                                <i class="bi ${getNotificationIcon(n.type)} ${getNotificationIconColor(n.type)}"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">${escapeHtml(n.title)}</p>
                                    ${n.priority === 'urgent' || n.priority === 'high' ? 
                                        `<span class="ml-2 px-1.5 py-0.5 text-xs rounded ${n.priority === 'urgent' ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400'}">${escapeHtml(n.priority)}</span>` : ''}
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5 line-clamp-2">${escapeHtml(n.message)}</p>
                                <div class="flex items-center mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    <span>${timeAgo(n.created_at)}</span>
                                    ${n.source_module ? `<span class="mx-1">•</span><span>${escapeHtml(n.source_module)}</span>` : ''}
                                </div>
                            </div>
                            ${!n.is_read ? '<div class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></div>' : ''}
                        </div>
                    </div>
                `;
                }).join('');
            }
            
            // Handle clicking a notification (Read & Redirect)
            window.handleNotificationClick = async function(id, link = '') {
                const n = currentNotifications.find(item => item.id == id);
                if (!n) return;

                // Mark as read in background if unread
                if (!n.is_read) {
                    try {
                        await fetch(NOTIFICATION_API_URL + '?action=read', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            credentials: 'same-origin',
                            body: JSON.stringify({ notification_id: id })
                        });
                    } catch (error) {
                        console.error('Error marking read:', error);
                    }
                }

                // Redirect logic
                if (link) {
                    window.location.href = '<?php echo BASE_URL; ?>/' + link;
                } else if (n.source_module === 'document-management') {
                    window.location.href = '<?php echo BASE_URL; ?>/modules/document-management/views/index.php';
                } else {
                    window.location.href = '<?php echo BASE_URL; ?>/modules/document-management/views/index.php';
                }
            };

            // Get icon based on notification type
            function getNotificationIcon(type) {
                const icons = {
                    'file': 'bi-file-earmark',
                    'message': 'bi-chat-dots',
                    'alert': 'bi-exclamation-triangle',
                    'integration': 'bi-plug',
                    'system': 'bi-gear'
                };
                return icons[type] || 'bi-bell';
            }
            
            function getNotificationIconBg(type) {
                const bgs = {
                    'file': 'bg-blue-100',
                    'message': 'bg-green-100',
                    'alert': 'bg-yellow-100',
                    'integration': 'bg-purple-100',
                    'system': 'bg-gray-100'
                };
                return bgs[type] || 'bg-gray-100';
            }
            
            function getNotificationIconColor(type) {
                const colors = {
                    'file': 'text-blue-600',
                    'message': 'text-green-600',
                    'alert': 'text-yellow-600',
                    'integration': 'text-purple-600',
                    'system': 'text-gray-600'
                };
                return colors[type] || 'text-gray-600';
            }
            
            // Time ago function
            function timeAgo(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const seconds = Math.floor((now - date) / 1000);
                
                if (seconds < 60) return 'Just now';
                if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
                if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
                if (seconds < 604800) return Math.floor(seconds / 86400) + 'd ago';
                return date.toLocaleDateString();
            }
            
            // Escape HTML
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text || '';
                return div.innerHTML;
            }
            
            // Mark notification as read (legacy/other usage)
            window.markNotificationRead = async function(id) {
                try {
                    await fetch(NOTIFICATION_API_URL + '?action=read', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify({ notification_id: id })
                    });
                    fetchNotifications();
                } catch (error) {
                    console.error('Error marking notification as read:', error);
                }
            };
            
            // Mark all as read
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    try {
                        await fetch(NOTIFICATION_API_URL + '?action=read_all', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            credentials: 'same-origin'
                        });
                        fetchNotifications();
                    } catch (error) {
                        console.error('Error marking all as read:', error);
                    }
                });
            }
            
            // Toggle dropdown
            notificationBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('hidden');
                
                // Close profile dropdown if open
                const profileDropdown = document.getElementById('profile-dropdown');
                if (profileDropdown) {
                    profileDropdown.classList.add('hidden');
                }
                
                if (!notificationDropdown.classList.contains('hidden')) {
                    fetchNotifications();
                }
            });
            
            // Prevent dropdown from closing when clicking inside
            notificationDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                const container = document.getElementById('notifications-container');
                if (container && !container.contains(e.target)) {
                    notificationDropdown.classList.add('hidden');
                }
            });
            
            // Initial fetch and periodic refresh
            fetchNotifications();
            setInterval(fetchNotifications, 60000); // Refresh every minute
        })();
    </script>

    <!-- AI Chatbot Assistant -->
    <?php include_once __DIR__ . '/../../chatbot/views/chat_widget.php'; ?>

    </div> <!-- Close COL-2/WRAPPER from view files -->
    </div> <!-- Close flex container from header -->
</body>
</html>