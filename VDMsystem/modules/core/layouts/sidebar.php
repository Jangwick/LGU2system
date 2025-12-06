<!-- Sidebar - VDM System (Same design as LLRM - Red Theme) -->
<aside id="sidebar" class="sidebar-expanded w-64 bg-gradient-to-b from-red-800 to-red-900 text-white flex-shrink-0 hidden md:flex flex-col transition-all duration-300 ease-in-out animate-slide-in-left min-h-full">
    <!-- Logo Section -->
    <div class="p-6 mb-2 border-b border-red-700 animate-fade-in sidebar-logo">
        <a href="<?php echo DASHBOARD_INDEX_URL; ?>" class="flex items-center space-x-3 hover:opacity-80 transition-all duration-300 transform hover:scale-105 group">
            <div class="bg-white rounded-full shadow-md flex items-center justify-center overflow-hidden transform transition-all duration-300 group-hover:scale-110 group-hover:rotate-6" style="width: 70px; height: 70px;">
                <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Valenzuela Logo" style="width: 120%; height: 120%;" class="object-cover">
            </div>
            <div class="transform transition-all duration-300 group-hover:translate-x-1 sidebar-text">
                <h1 class="text-lg font-bold">VDM System</h1>
                <p class="text-xs text-red-200">Voting & Decisions</p>
            </div>
        </a>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-2">
        <?php 
        // Get user role for permission checks
        $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
        $canVote = in_array($userRole, ['councilor', 'admin', 'administrator', 'secretary']);
        $canManage = in_array($userRole, ['secretary', 'admin', 'administrator']);
        $isAdmin = in_array($userRole, ['admin', 'administrator']);
        ?>
        <div class="px-4 space-y-1">
            <!-- Dashboard -->
            <a href="<?php echo DASHBOARD_INDEX_URL; ?>" class="nav-item <?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>
            
            <!-- Voting Section -->
            <div class="pt-4 pb-2 sidebar-text">
                <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">Voting</p>
            </div>
            
            <a href="<?php echo VOTING_URL; ?>/views/sessions.php" class="nav-item <?php echo ($currentPage ?? '') === 'sessions' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-check"></i>
                <span class="sidebar-text">Voting Sessions</span>
            </a>
            
            <?php if ($canVote): ?>
            <a href="<?php echo VOTING_URL; ?>/views/cast-vote.php" class="nav-item <?php echo ($currentPage ?? '') === 'cast-vote' ? 'active' : ''; ?>">
                <i class="bi bi-hand-thumbs-up"></i>
                <span class="sidebar-text">Cast Vote</span>
            </a>
            <?php endif; ?>
            
            <a href="<?php echo VOTING_URL; ?>/views/results.php" class="nav-item <?php echo ($currentPage ?? '') === 'results' ? 'active' : ''; ?>">
                <i class="bi bi-bar-chart"></i>
                <span class="sidebar-text">Vote Results</span>
            </a>
            
            <!-- Documents Section -->
            <div class="pt-4 pb-2 sidebar-text">
                <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">Documents</p>
            </div>
            
            <a href="<?php echo DOCUMENTS_INDEX_URL; ?>" class="nav-item <?php echo ($currentPage ?? '') === 'documents' ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-text"></i>
                <span class="sidebar-text">All Documents</span>
            </a>
            
            <?php if ($canManage): ?>
            <a href="<?php echo BASE_URL; ?>/modules/documents/views/create.php" class="nav-item <?php echo ($currentPage ?? '') === 'create-document' ? 'active' : ''; ?>">
                <i class="bi bi-file-plus"></i>
                <span class="sidebar-text">New Document</span>
            </a>
            <?php endif; ?>
            
            <!-- Reports & Analytics Section - Available for Secretary and Admin -->
            <?php if ($canManage): ?>
            <div class="pt-4 pb-2 sidebar-text">
                <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">Analytics</p>
            </div>
            
            <a href="<?php echo REPORTS_URL; ?>/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'reports' ? 'active' : ''; ?>">
                <i class="bi bi-graph-up"></i>
                <span class="sidebar-text">Reports & Analytics</span>
            </a>
            <?php endif; ?>
            
            <!-- Management Section - Admin Only -->
            <?php if ($isAdmin): ?>
            <div class="pt-4 pb-2 sidebar-text">
                <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">Administration</p>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/modules/users/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'users' ? 'active' : ''; ?>">
                <i class="bi bi-person-gear"></i>
                <span class="sidebar-text">User Management</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/modules/committees/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'committees' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>
                <span class="sidebar-text">Committees</span>
            </a>
            
            <a href="<?php echo AUDIT_URL; ?>/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'audit' ? 'active' : ''; ?>">
                <i class="bi bi-shield-check"></i>
                <span class="sidebar-text">Audit Logs</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/modules/settings/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'settings' ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i>
                <span class="sidebar-text">Settings</span>
            </a>
            <?php endif; ?>
            
            <!-- Integration Section - Dropdown (Secretary and Admin only) -->
            <?php if ($canManage): ?>
            <div class="pt-4 pb-2 sidebar-text">
                <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">Integration</p>
            </div>
            
            <div class="dropdown-section">
                <button onclick="toggleDropdown('integrationDropdown')" class="nav-item w-full text-left">
                    <i class="bi bi-plug"></i>
                    <span class="flex-1 sidebar-text">Integration Modules</span>
                    <i class="bi bi-chevron-down dropdown-icon sidebar-text" id="integrationDropdown-icon"></i>
                </button>
                
                <div id="integrationDropdown" class="dropdown-content hidden">
                    <a href="<?php echo BASE_URL; ?>/modules/integration/views/lrms.php" class="nav-item nav-item-sub <?php echo ($currentPage ?? '') === 'lrms-integration' ? 'active' : ''; ?>">
                        <i class="bi bi-journal-text"></i>
                        <span class="sidebar-text">LRMS Records</span>
                    </a>
                    
                    <a href="<?php echo BASE_URL; ?>/modules/integration/views/ordinances.php" class="nav-item nav-item-sub <?php echo ($currentPage ?? '') === 'ordinances' ? 'active' : ''; ?>">
                        <i class="bi bi-file-text"></i>
                        <span class="sidebar-text">Ordinances</span>
                    </a>
                    
                    <a href="<?php echo BASE_URL; ?>/modules/integration/views/resolutions.php" class="nav-item nav-item-sub <?php echo ($currentPage ?? '') === 'resolutions' ? 'active' : ''; ?>">
                        <i class="bi bi-file-check"></i>
                        <span class="sidebar-text">Resolutions</span>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </nav>
    
    <!-- User Profile Section -->
    <div class="p-4 border-t border-red-700 sidebar-user">
        <div class="flex items-center space-x-3">
            <div class="bg-red-600 rounded-full w-10 h-10 flex items-center justify-center flex-shrink-0">
                <i class="bi bi-person-fill text-xl"></i>
            </div>
            <div class="flex-1 min-w-0 sidebar-text">
                <p class="text-sm font-medium truncate"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest User'); ?></p>
                <p class="text-xs text-red-300 truncate"><?php echo htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'Guest')); ?></p>
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
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        font-size: 0.875rem;
        background-color: transparent;
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    .nav-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 3px;
        background: white;
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }
    
    .nav-item:hover::before {
        transform: scaleY(1);
    }
    
    .nav-item:hover {
        background-color: rgba(220, 38, 38, 0.4);
        color: white;
        transform: translateX(4px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .nav-item i {
        transition: transform 0.3s ease;
    }
    
    .nav-item:hover i {
        transform: scale(1.15) rotate(5deg);
    }
    
    .nav-item.active {
        background-color: rgba(220, 38, 38, 0.5);
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
    
    /* Collapsed sidebar styles */
    .sidebar-collapsed {
        width: 70px !important;
    }
    
    .sidebar-collapsed .sidebar-text,
    .sidebar-collapsed .sidebar-logo div:last-child,
    .sidebar-collapsed .sidebar-user .flex-1 {
        display: none !important;
    }
    
    .sidebar-collapsed .sidebar-logo a {
        justify-content: center;
    }
    
    .sidebar-collapsed .sidebar-logo a > div:first-child {
        width: 45px !important;
        height: 45px !important;
    }
    
    .sidebar-collapsed .nav-item {
        justify-content: center;
        padding: 0.75rem 0.5rem;
    }
    
    .sidebar-collapsed .nav-item i {
        margin-right: 0;
    }
    
    .sidebar-collapsed .sidebar-user {
        justify-content: center;
    }
    
    .sidebar-collapsed .sidebar-user > div {
        justify-content: center;
    }
    
    .sidebar-collapsed .dropdown-section {
        display: none;
    }
    
    .sidebar-collapsed .pt-4.pb-2 {
        display: none;
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
