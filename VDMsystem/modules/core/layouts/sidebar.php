<!-- Sidebar - VDM System (Blue Theme) -->
<aside id="sidebar" class="sidebar-expanded w-64 bg-gradient-to-b from-blue-800 to-blue-900 text-white flex-shrink-0 hidden md:flex flex-col transition-all duration-300 ease-in-out animate-slide-in-left min-h-full">
    <!-- Logo Section -->
    <div class="p-6 mb-2 border-b border-blue-700 animate-fade-in sidebar-logo">
        <a href="<?php echo DASHBOARD_INDEX_URL; ?>" class="flex items-center space-x-3 hover:opacity-80 transition-all duration-300 transform hover:scale-105 group">
            <div class="bg-white rounded-full shadow-md flex items-center justify-center overflow-hidden transform transition-all duration-300 group-hover:scale-110 group-hover:rotate-6" style="width: 70px; height: 70px;">
                <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Valenzuela Logo" style="width: 120%; height: 120%;" class="object-cover">
            </div>
            <div class="transform transition-all duration-300 group-hover:translate-x-1 sidebar-text">
                <h1 class="text-lg font-bold">VDM System</h1>
                <p class="text-xs text-blue-200">Voting & Decisions</p>
            </div>
        </a>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-2">
        <?php 
        $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
        $canVote = in_array($userRole, ['councilor', 'admin', 'administrator']);
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
                <p class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Voting</p>
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
                <p class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Documents</p>
            </div>
            
            <a href="<?php echo DOCUMENTS_INDEX_URL; ?>" class="nav-item <?php echo ($currentPage ?? '') === 'documents' ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-text"></i>
                <span class="sidebar-text">All Documents</span>
            </a>
            
            <?php if ($canManage): ?>
            <a href="<?php echo WORKFLOWS_URL; ?>/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'workflows' ? 'active' : ''; ?>">
                <i class="bi bi-diagram-3"></i>
                <span class="sidebar-text">Workflows</span>
            </a>
            <?php endif; ?>
            
            <!-- Reports Section -->
            <?php if ($canManage || $isAdmin): ?>
            <div class="pt-4 pb-2 sidebar-text">
                <p class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Analytics</p>
            </div>
            
            <a href="<?php echo REPORTS_URL; ?>/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'reports' ? 'active' : ''; ?>">
                <i class="bi bi-graph-up"></i>
                <span class="sidebar-text">Reports & Analytics</span>
            </a>
            <?php endif; ?>
            
            <!-- Administration Section -->
            <?php if ($isAdmin): ?>
            <div class="pt-4 pb-2 sidebar-text">
                <p class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Administration</p>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/modules/users/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'users' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>
                <span class="sidebar-text">User Management</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/modules/committees/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'committees' ? 'active' : ''; ?>">
                <i class="bi bi-person-badge"></i>
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
            
            <!-- Integration Section -->
            <?php if ($canManage): ?>
            <div class="pt-4 pb-2 sidebar-text">
                <p class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider">Integration</p>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/modules/integration/views/index.php" class="nav-item <?php echo ($currentPage ?? '') === 'integration' ? 'active' : ''; ?>">
                <i class="bi bi-plug"></i>
                <span class="sidebar-text">Module Integration</span>
            </a>
            <?php endif; ?>
        </div>
    </nav>
    
    <!-- User Profile Section -->
    <div class="p-4 border-t border-blue-700 sidebar-user">
        <div class="flex items-center space-x-3">
            <div class="bg-blue-600 rounded-full w-10 h-10 flex items-center justify-center flex-shrink-0">
                <i class="bi bi-person-fill text-xl"></i>
            </div>
            <div class="flex-1 min-w-0 sidebar-text">
                <p class="text-sm font-medium truncate"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest User'); ?></p>
                <p class="text-xs text-blue-300 truncate"><?php echo htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'Guest')); ?></p>
            </div>
        </div>
    </div>
</aside>

<style>
    .nav-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        color: #bfdbfe;
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
        background-color: rgba(59, 130, 246, 0.4);
        color: white;
        transform: translateX(4px);
    }
    
    .nav-item i {
        width: 24px;
        font-size: 1.1rem;
        margin-right: 12px;
        transition: transform 0.3s ease;
    }
    
    .nav-item:hover i {
        transform: scale(1.2);
    }
    
    .nav-item.active {
        background-color: rgba(59, 130, 246, 0.6);
        color: white;
        font-weight: 600;
    }
    
    .nav-item.active::before {
        transform: scaleY(1);
    }
    
    /* Collapsed sidebar styles */
    .sidebar-collapsed {
        width: 70px !important;
    }
    
    .sidebar-collapsed .sidebar-text,
    .sidebar-collapsed .sidebar-logo div:last-child,
    .sidebar-collapsed .sidebar-user div:last-child {
        display: none;
    }
    
    .sidebar-collapsed .nav-item {
        justify-content: center;
        padding: 0.75rem;
    }
    
    .sidebar-collapsed .nav-item i {
        margin-right: 0;
    }
    
    .sidebar-collapsed .sidebar-logo a {
        justify-content: center;
    }
    
    .sidebar-collapsed .sidebar-logo a > div:first-child {
        width: 45px !important;
        height: 45px !important;
    }
</style>
