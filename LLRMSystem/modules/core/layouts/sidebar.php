<!-- Sidebar -->
<aside id="sidebar" class="w-64 bg-gradient-to-b from-blue-800 to-blue-900 text-white flex-shrink-0 hidden md:flex flex-col transition-all duration-300">
    <!-- Logo Section -->
    <div class="p-6 border-b border-blue-700">
        <a href="/LLRMSystem/modules/dashboard/views/index.php" class="flex items-center space-x-3 hover:opacity-80 transition">
            <div class="bg-white rounded-lg p-2">
                <i class="bi bi-file-earmark-text text-blue-800 text-2xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold">LRMS</h1>
                <p class="text-xs text-blue-200">Legislative Records</p>
            </div>
        </a>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-4">
        <div class="px-4 space-y-1">
            <!-- Dashboard -->
            <a href="/LLRMSystem/modules/dashboard/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            
            <!-- Documents Section -->
            <div class="pt-4 pb-2">
                <p class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Documents</p>
            </div>
            
            <a href="/LLRMSystem/modules/document-management/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'documents' ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-text"></i>
                <span>All Documents</span>
            </a>
            
            <?php 
            $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
            $canUpload = in_array($userRole, ['staff', 'officer', 'administrator', 'admin']);
            if ($canUpload): 
            ?>
            <a href="/LLRMSystem/modules/document-management/views/create.php" class="nav-item <?php echo ($currentPage ?? '') === 'documents-create' ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-plus"></i>
                <span>Upload Document</span>
            </a>
            <?php endif; ?>
            
            <a href="/LLRMSystem/modules/search/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'search' ? 'active' : ''; ?>">
                <i class="bi bi-search"></i>
                <span>Advanced Search</span>
            </a>
            
            <!-- Reports & Analytics Section - Available for Officer and Admin -->
            <?php 
            $canViewReports = in_array($userRole, ['officer', 'administrator', 'admin']);
            if ($canViewReports): 
            ?>
            <div class="pt-4 pb-2">
                <p class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Analytics</p>
            </div>
            
            <a href="/LLRMSystem/modules/reports-analytics/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'reports' ? 'active' : ''; ?>">
                <i class="bi bi-graph-up"></i>
                <span>Reports & Analytics</span>
            </a>
            <?php endif; ?>
            
            <!-- Management Section - Admin Only -->
            <?php 
            $isAdmin = in_array($userRole, ['administrator', 'admin']);
            if ($isAdmin): 
            ?>
            <div class="pt-4 pb-2">
                <p class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Administration</p>
            </div>
            
            <a href="/LLRMSystem/modules/user-management/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'users' ? 'active' : ''; ?>">
                <i class="bi bi-person-gear"></i>
                <span>User Management</span>
            </a>
            
            <a href="/LLRMSystem/modules/audit/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'audit' ? 'active' : ''; ?>">
                <i class="bi bi-shield-check"></i>
                <span>Audit Logs</span>
            </a>
            <?php endif; ?>
            
            <!-- Integration Section - Dropdown (Officer and Admin only) -->
            <?php 
            $canAccessIntegration = in_array($userRole, ['officer', 'administrator', 'admin']);
            if ($canAccessIntegration): 
            ?>
            <div class="pt-4 pb-2">
                <p class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Integration</p>
            </div>
            
            <div class="dropdown-section">
                <button onclick="toggleDropdown('integrationDropdown')" class="nav-item w-full text-left">
                    <i class="bi bi-plug"></i>
                    <span class="flex-1">Integration Modules</span>
                    <i class="bi bi-chevron-down dropdown-icon" id="integrationDropdown-icon"></i>
                </button>
                
                <div id="integrationDropdown" class="dropdown-content hidden">
                    <a href="/modules/integration-ordinances/views/index.php" class="nav-item nav-item-sub <?php echo ($currentPage ?? '') === 'ordinances' ? 'active' : ''; ?>">
                        <i class="bi bi-journal-text"></i>
                        <span>Ordinances</span>
                    </a>
                    
                    <a href="/modules/integration-sessions/views/index.php" class="nav-item nav-item-sub <?php echo ($currentPage ?? '') === 'sessions' ? 'active' : ''; ?>">
                        <i class="bi bi-calendar3"></i>
                        <span>Sessions</span>
                    </a>
                    
                    <a href="/modules/integration-agendas/views/index.php" class="nav-item nav-item-sub <?php echo ($currentPage ?? '') === 'agendas' ? 'active' : ''; ?>">
                        <i class="bi bi-list-check"></i>
                        <span>Agendas</span>
                    </a>
                    
                    <a href="/modules/integration-committees/views/index.php" class="nav-item nav-item-sub <?php echo ($currentPage ?? '') === 'committees' ? 'active' : ''; ?>">
                        <i class="bi bi-people"></i>
                        <span>Committees</span>
                    </a>
                    
                    <a href="/modules/integration-voting/views/index.php" class="nav-item nav-item-sub <?php echo ($currentPage ?? '') === 'voting' ? 'active' : ''; ?>">
                        <i class="bi bi-hand-thumbs-up"></i>
                        <span>Voting Records</span>
                    </a>
                    
                    <a href="/modules/integration-hearings/views/index.php" class="nav-item nav-item-sub <?php echo ($currentPage ?? '') === 'hearings' ? 'active' : ''; ?>">
                        <i class="bi bi-megaphone"></i>
                        <span>Public Hearings</span>
                    </a>
                    
                    <a href="/modules/integration-archives/views/index.php" class="nav-item nav-item-sub <?php echo ($currentPage ?? '') === 'archives' ? 'active' : ''; ?>">
                        <i class="bi bi-archive"></i>
                        <span>Archives</span>
                    </a>
                    
                    <a href="/modules/integration-consultations/views/index.php" class="nav-item nav-item-sub <?php echo ($currentPage ?? '') === 'consultations' ? 'active' : ''; ?>">
                        <i class="bi bi-chat-dots"></i>
                        <span>Consultations</span>
                    </a>
                    
                    <a href="/modules/integration-research/views/index.php" class="nav-item nav-item-sub <?php echo ($currentPage ?? '') === 'research' ? 'active' : ''; ?>">
                        <i class="bi bi-book"></i>
                        <span>Research</span>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </nav>
    
    <!-- User Profile Section -->
    <div class="p-4 border-t border-blue-700">
        <div class="flex items-center space-x-3">
            <div class="bg-blue-600 rounded-full w-10 h-10 flex items-center justify-center">
                <i class="bi bi-person-fill text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest User'); ?></p>
                <p class="text-xs text-blue-300 truncate"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Guest'); ?></p>
            </div>
        </div>
    </div>
</aside>

<style>
    .nav-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        color: #e0e7ff;
        border-radius: 0.5rem;
        transition: all 0.2s;
        text-decoration: none;
        font-size: 0.875rem;
    }
    
    .nav-item:hover {
        background-color: rgba(59, 130, 246, 0.3);
        color: white;
    }
    
    .nav-item.active {
        background-color: rgba(59, 130, 246, 0.5);
        color: white;
        font-weight: 600;
    }
    
    .nav-item i {
        margin-right: 0.75rem;
        font-size: 1.125rem;
        width: 1.5rem;
        text-align: center;
    }
    
    /* Dropdown styles */
    .dropdown-section {
        margin-bottom: 0.5rem;
    }
    
    .dropdown-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }
    
    .dropdown-content.show {
        max-height: 600px;
        transition: max-height 0.5s ease-in;
    }
    
    .nav-item-sub {
        padding-left: 2.5rem;
        font-size: 0.813rem;
    }
    
    .dropdown-icon {
        transition: transform 0.3s ease;
        font-size: 0.875rem;
    }
    
    .dropdown-icon.rotate {
        transform: rotate(180deg);
    }
</style>

<script>
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const icon = document.getElementById(dropdownId + '-icon');
    
    if (dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
        dropdown.classList.add('hidden');
        icon.classList.remove('rotate');
    } else {
        dropdown.classList.remove('hidden');
        dropdown.classList.add('show');
        icon.classList.add('rotate');
    }
}

// Auto-expand dropdown if a sub-item is active
document.addEventListener('DOMContentLoaded', function() {
    const activeSubItem = document.querySelector('.nav-item-sub.active');
    if (activeSubItem) {
        const dropdown = activeSubItem.closest('.dropdown-content');
        if (dropdown) {
            dropdown.classList.remove('hidden');
            dropdown.classList.add('show');
            const dropdownId = dropdown.id;
            const icon = document.getElementById(dropdownId + '-icon');
            if (icon) {
                icon.classList.add('rotate');
            }
        }
    }
});
</script>
