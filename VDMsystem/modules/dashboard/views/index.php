<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Load dashboard controller
require_once __DIR__ . '/../controllers/DashboardController.php';
$dashboard = new DashboardController();

// Gather data
$stats            = $dashboard->getStatistics();
$votingTrend      = $dashboard->getVotingTrend(7);
$sessionDist      = $dashboard->getSessionStatusDistribution();
$upcomingSessions = $dashboard->getUpcomingSessions(5);
$recentVotes      = $dashboard->getRecentVotes(5);
$approvalRate     = $dashboard->getApprovalRate();

// Role helpers
$userRole   = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
$canVote    = in_array($userRole, ['councilor','admin','administrator','secretary']);
$canManage  = in_array($userRole, ['secretary','admin','administrator']);
$isAdmin    = in_array($userRole, ['admin','administrator']);

// System status checks
$systemStatus = [
    'database' => ['status' => 'healthy', 'class' => 'success'],
    'voting'   => ['status' => $stats['active_sessions'] > 0 ? 'active' : 'idle', 'class' => $stats['active_sessions'] > 0 ? 'success' : 'secondary'],
    'users'    => ['status' => $stats['total_users'] . ' active', 'class' => 'success'],
];

$pageTitle   = 'Dashboard';
$currentPage = 'dashboard';
$breadcrumbs = [['label' => 'Dashboard']];

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<!-- Sidebar -->
<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Top Navbar -->
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto bg-gray-100 p-2 md:p-6">
        <!-- Welcome Banner -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white transform transition-all duration-500 ease-in-out animate-fade-in relative overflow-hidden">
            <!-- Subtle decorative background element -->
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl transition-opacity duration-500 dark:opacity-5"></div>
            
            <div class="relative flex items-center justify-between gap-4">
                <!-- Left Side: Title & Context -->
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight transition-all duration-500">
                        Hi, <?php echo e($_SESSION['user_name'] ?? 'User'); ?>! 👋
                    </h1>
                    <p class="text-red-100 text-xs md:text-base opacity-90 font-medium transition-all duration-500">
                        Voting & decision-making status for today.
                    </p>
                </div>
                
                <!-- Right Side: Action Buttons -->
                <div class="shrink-0 flex gap-3">
                    <?php if ($canManage): ?>
                    <button type="button" onclick="openCreateSessionModal()" class="!bg-white !text-red-600 hover:!bg-gray-50 px-6 py-2.5 rounded-xl font-bold shadow-lg transition-all duration-500 transform hover:-translate-y-0.5 flex items-center group border border-red-600">
                        <i class="bi bi-plus-circle mr-2 transition-transform group-hover:rotate-90"></i>
                        <span>New Session</span>
                    </button>
                    <?php endif; ?>
                    <?php if ($canVote): ?>
                    <a href="<?php echo VOTING_URL; ?>/views/cast-vote.php" class="!bg-red-600 !text-white px-6 py-2.5 rounded-xl font-bold hover:!bg-red-700 border border-white/20 shadow-lg transition-all flex items-center transform hover:scale-[1.02] active:scale-95 text-sm">
                        <i class="bi bi-hand-thumbs-up mr-2"></i>
                        <span>Cast Vote</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 animate-fade-in-up">
            <!-- Total Sessions -->
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-red-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Sessions</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_sessions']); ?></p>
                    </div>
                    <div class="bg-red-50 rounded-full p-2.5">
                        <i class="bi bi-calendar-event text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Pending Vote -->
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-yellow-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Pending Vote</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['pending_vote']); ?></p>
                    </div>
                    <div class="bg-yellow-50 rounded-full p-2.5">
                        <i class="bi bi-hourglass-split text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Approved This Month -->
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-green-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Approved</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['approved']); ?></p>
                    </div>
                    <div class="bg-green-50 rounded-full p-2.5">
                        <i class="bi bi-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Total Votes -->
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-purple-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Votes</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_votes']); ?></p>
                    </div>
                    <div class="bg-purple-50 rounded-full p-2.5">
                        <i class="bi bi-hand-thumbs-up text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Voting Activity Chart -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-500">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Voting Activity (Last 7 Days)</h2>
                    <button onclick="location.reload()" class="text-gray-500 hover:text-gray-700 text-sm" title="Refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="votingChart"></canvas>
                </div>
            </div>
            
            <!-- Session Status Distribution -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-600">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Session Status Distribution</h2>
                    <button class="text-gray-500 hover:text-gray-700">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="sessionStatusChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity & Quick Links -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Votes Table -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-700">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Recent Votes</h2>
                    <a href="<?php echo VOTING_URL; ?>/views/results.php" class="text-red-600 hover:text-red-700 text-sm font-medium">
                        View All <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Document</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vote</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Session</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($recentVotes)): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        <i class="bi bi-hand-thumbs-up text-4xl mb-2"></i>
                                        <p>No votes recorded yet</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentVotes as $rv): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="bg-red-100 rounded p-2 mr-3">
                                                    <i class="bi bi-file-earmark-text text-red-600"></i>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 truncate max-w-[200px]">
                                                        <?php echo e($rv['document_title']); ?>
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        <?php echo e($rv['doc_number'] ?? ''); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo getVoteBadgeClass($rv['vote']); ?>">
                                                <?php echo ucfirst($rv['vote']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 truncate max-w-[150px]">
                                            <?php echo e($rv['session_title'] ?? '—'); ?>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo formatDateTime($rv['cast_at'], 'M d, Y'); ?>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <a href="<?php echo VOTING_URL; ?>/views/results.php" class="text-red-600 hover:text-red-700 mr-2" title="View Results">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?php echo VOTING_URL; ?>/views/sessions.php" class="text-gray-600 hover:text-gray-700" title="View Session">
                                                <i class="bi bi-calendar-check"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Quick Links & Activity -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-800">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h2>
                    <div class="space-y-2">
                        <?php if ($canVote): ?>
                        <a href="<?php echo VOTING_URL; ?>/views/cast-vote.php" class="flex items-center p-3 hover:bg-red-50 rounded-lg transition-all duration-200">
                            <div class="bg-red-100 rounded-lg p-2 mr-3">
                                <i class="bi bi-hand-thumbs-up text-red-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Cast Your Vote</span>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo VOTING_URL; ?>/views/sessions.php" class="flex items-center p-3 hover:bg-green-50 rounded-lg transition-all duration-200">
                            <div class="bg-green-100 rounded-lg p-2 mr-3">
                                <i class="bi bi-calendar-check text-green-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700">View Sessions</span>
                        </a>
                        <a href="<?php echo VOTING_URL; ?>/views/results.php" class="flex items-center p-3 hover:bg-purple-50 rounded-lg transition-all duration-200">
                            <div class="bg-purple-100 rounded-lg p-2 mr-3">
                                <i class="bi bi-bar-chart text-purple-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Vote Results</span>
                        </a>
                        <?php if ($canManage): ?>
                        <a href="<?php echo REPORTS_URL; ?>/views/index.php" class="flex items-center p-3 hover:bg-orange-50 rounded-lg transition-all duration-200">
                            <div class="bg-orange-100 rounded-lg p-2 mr-3">
                                <i class="bi bi-graph-up text-orange-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Reports & Analytics</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- System Status -->
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-900">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">System Status</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Voting Engine</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $systemStatus['voting']['class'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'; ?>">
                                <i class="bi bi-<?php echo $systemStatus['voting']['status'] === 'active' ? 'check-circle' : 'pause-circle'; ?> mr-1"></i>
                                <?php echo ucfirst($systemStatus['voting']['status']); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Database</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="bi bi-check-circle mr-1"></i>
                                <?php echo ucfirst($systemStatus['database']['status']); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Active Users</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="bi bi-check-circle mr-1"></i>
                                <?php echo $systemStatus['users']['status']; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php 
include_once __DIR__ . '/../../core/layouts/footer.php'; 

// Fetch data for Modal (Same as sessions.php)
$committeesData = dbFetchAll("SELECT id, name FROM committees WHERE is_active = 1 ORDER BY name");
$pendingDocsData = dbFetchAll("SELECT id, doc_number, title, type FROM documents WHERE status = 'pending_vote' ORDER BY created_at DESC");
$councilorsData = dbFetchAll("SELECT id, full_name, position FROM users WHERE role IN ('councilor', 'admin') AND is_active = 1 ORDER BY full_name");
?>

<!-- Create/Configure Session Modal -->
<div id="createSessionModal" class="fixed inset-0 z-[100] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300" onclick="closeCreateSessionModal()"></div>
        
        <!-- Modal Box -->
        <div class="relative bg-white dark:bg-slate-950 w-full max-w-6xl rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all animate-modal-in flex flex-col max-h-[95vh] border border-gray-100 dark:border-slate-800">
            <!-- Premium Header -->
            <div class="bg-[#dc2626] p-7 md:p-9 text-white flex items-center justify-between shrink-0 relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-3xl font-black tracking-tight leading-none mb-1">Configure New Session</h2>
                    <p class="text-red-100 text-sm font-medium opacity-90">Set up legislative sessions, documents, and expected attendees.</p>
                </div>
                <button onclick="closeCreateSessionModal()" class="relative z-10 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition-all group">
                    <i class="bi bi-x-lg text-xl transition-transform group-hover:rotate-90"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="sessionForm" action="<?php echo VOTING_URL; ?>/views/create-session.php" method="POST" class="vdm-form flex-1 overflow-y-auto p-6 md:p-10 custom-scrollbar vdm-page-bg">
                <input type="hidden" name="session_id" id="modalSessionId" value="">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Left Section (Basic Info & Documents) -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Basic Information -->
                        <div class="vdm-card rounded-[2rem] p-8 shadow-sm border">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="w-11 h-11 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-2xl flex items-center justify-center text-lg">
                                    <i class="bi bi-info-circle-fill"></i>
                                </div>
                                <h3 class="text-xl font-black vdm-heading tracking-tight">Basic Information</h3>
                            </div>
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-[10px] font-black vdm-label uppercase tracking-[0.15em] mb-2">Session Title <span class="text-red-500">*</span></label>
                                    <input type="text" name="title" required class="w-full px-5 py-3.5 vdm-input-field rounded-[1.25rem] border focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none text-sm font-black vdm-input" placeholder="e.g. Regular Session - Resolution Planning">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-[10px] font-black vdm-label uppercase tracking-[0.15em] mb-2">Date <span class="text-red-500">*</span></label>
                                        <input type="date" name="session_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-5 py-3.5 vdm-input-field rounded-[1.25rem] border focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none text-sm font-black vdm-input">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black vdm-label uppercase tracking-[0.15em] mb-2">Start <span class="text-red-500">*</span></label>
                                        <input type="time" name="start_time" value="14:00" required class="w-full px-5 py-3.5 vdm-input-field rounded-[1.25rem] border focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none text-sm font-black vdm-input">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black vdm-label uppercase tracking-[0.15em] mb-2">End</label>
                                        <input type="time" name="end_time" class="w-full px-5 py-3.5 vdm-input-field rounded-[1.25rem] border focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none text-sm font-black vdm-input">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black vdm-label uppercase tracking-[0.15em] mb-2">Description</label>
                                    <textarea name="description" rows="3" class="w-full px-5 py-3.5 vdm-input-field rounded-[1.25rem] border focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none text-sm font-black vdm-input resize-none" placeholder="Briefly describe the session agenda..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Legislative Items -->
                        <div class="vdm-card rounded-[2rem] p-8 shadow-sm border">
                            <div class="flex items-center justify-between mb-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center text-lg">
                                        <i class="bi bi-file-earmark-text-fill"></i>
                                    </div>
                                    <h3 class="text-xl font-black vdm-heading tracking-tight">Legislative Items</h3>
                                </div>
                                <span class="text-[10px] font-black bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-4 py-1.5 rounded-xl border border-blue-100 dark:border-blue-800 uppercase tracking-widest">Pending Vote</span>
                            </div>
                            <div class="grid grid-cols-1 gap-3 max-h-[300px] overflow-y-auto pr-3 custom-scrollbar">
                                <?php if (empty($pendingDocsData)): ?>
                                    <div class="text-center py-12 vdm-card rounded-[1.5rem] border-2 border-dashed">
                                        <i class="bi bi-inbox text-4xl text-slate-200 dark:text-slate-700 block mb-3"></i>
                                        <p class="text-sm font-black vdm-muted">No pending legislative items found.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($pendingDocsData as $doc): ?>
                                    <label class="flex items-center p-4 rounded-[1.25rem] vdm-card hover:border-red-200 dark:hover:border-red-900/50 transition-all cursor-pointer group border">
                                        <div class="mr-4">
                                            <input type="checkbox" name="documents[]" value="<?php echo $doc['id']; ?>" class="w-5 h-5 text-red-600 rounded-lg border-slate-200 focus:ring-red-500/20 cursor-pointer">
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-[9px] font-black vdm-muted uppercase tracking-tighter"><?php echo htmlspecialchars($doc['doc_number']); ?></span>
                                                <span class="text-[8px] vdm-badge px-2 py-0.5 rounded-lg border font-black uppercase tracking-widest"><?php echo htmlspecialchars($doc['type']); ?></span>
                                            </div>
                                            <h4 class="text-sm font-black vdm-heading leading-tight group-hover:text-red-700 dark:group-hover:text-red-500 transition-colors"><?php echo htmlspecialchars($doc['title']); ?></h4>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right Section (Settings & Attendees) -->
                    <div class="space-y-8">
                        <!-- Settings -->
                        <div class="vdm-card rounded-[2rem] p-8 shadow-sm border">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="w-11 h-11 bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 rounded-2xl flex items-center justify-center text-lg">
                                    <i class="bi bi-gear-fill"></i>
                                </div>
                                <h3 class="text-xl font-black vdm-heading tracking-tight">Session Settings</h3>
                            </div>
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-[10px] font-black vdm-label uppercase tracking-widest mb-2 ml-1">Committee</label>
                                    <select name="committee_id" class="w-full px-4 py-3 vdm-input-field rounded-[1.25rem] border text-sm font-black vdm-input outline-none focus:ring-4 focus:ring-red-500/10 cursor-pointer">
                                        <option value="">-- Plenary Session --</option>
                                        <?php foreach ($committeesData as $c): ?>
                                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black vdm-label uppercase tracking-widest mb-2 ml-1">Vote Method</label>
                                    <select name="vote_type" class="w-full px-4 py-3 vdm-input-field rounded-[1.25rem] border text-sm font-black vdm-input outline-none focus:ring-4 focus:ring-red-500/10 cursor-pointer">
                                        <option value="roll_call">Roll Call Vote</option>
                                        <option value="voice">Voice Vote</option>
                                        <option value="ballot">Secret Ballot</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black vdm-label uppercase tracking-widest mb-2 ml-1">Quorum</label>
                                    <input type="number" name="quorum_required" value="5" class="w-full px-4 py-3 vdm-input-field rounded-[1.25rem] border text-sm font-black vdm-input outline-none focus:ring-4 focus:ring-red-500/10">
                                </div>
                            </div>
                        </div>

                        <!-- Attendees -->
                        <div class="vdm-card rounded-[2rem] p-8 shadow-sm border min-h-[400px] flex flex-col">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="w-11 h-11 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-2xl flex items-center justify-center text-lg">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <h3 class="text-xl font-black vdm-heading tracking-tight">Attendees</h3>
                            </div>
                            <div class="overflow-y-auto space-y-2 flex-1 custom-scrollbar pr-2">
                                <?php foreach ($councilorsData as $user): ?>
                                <label class="flex items-center p-3.5 rounded-[1.25rem] vdm-card cursor-pointer hover:border-red-200 dark:hover:border-red-900/50 transition-all border group">
                                    <div class="mr-4">
                                        <input type="checkbox" name="attendees[]" value="<?php echo $user['id']; ?>" checked class="w-5 h-5 text-red-600 rounded-lg border-slate-200 dark:border-slate-700 focus:ring-red-500/20 cursor-pointer">
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-black vdm-heading leading-none group-hover:text-red-700 dark:group-hover:text-red-500 transition-colors"><?php echo htmlspecialchars($user['full_name']); ?></p>
                                        <p class="text-[9px] vdm-sub font-black mt-1 uppercase tracking-widest"><?php echo htmlspecialchars($user['position']); ?></p>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-between mt-10 pt-8 border-t shrink-0" style="border-color: var(--vdm-card-border)">
                    <button type="button" onclick="closeCreateSessionModal()" class="px-8 py-3.5 text-sm font-black vdm-muted hover:text-red-600 transition-all uppercase tracking-widest">Discard</button>
                    <button type="submit" class="bg-[#dc2626] text-white px-10 py-4 rounded-[1.5rem] font-black shadow-xl shadow-red-200 hover:bg-red-700 hover:-translate-y-1 transition-all flex items-center gap-3 text-sm uppercase tracking-tight">
                        Create Session <i class="bi bi-check2-circle"></i>
                    </button>
                </div>
            </form>
        </div> <!-- Modal Box End -->
    </div> <!-- Flex Wrapper End -->
</div>

<script>
function openCreateSessionModal() {
    document.getElementById('createSessionModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCreateSessionModal() {
    document.getElementById('createSessionModal').classList.add('hidden');
    document.body.style.overflow = '';
}
</script>

<script>
// Function to check if dark mode is active
function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

// Function to get label color based on theme
function getLabelColor() {
    return isDarkMode() ? '#ffffff' : '#374151';
}

// Function to get grid color based on theme
function getGridColor() {
    return isDarkMode() ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
}

// Wait for DOM and Chart.js to load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded');
        return;
    }

    // Voting Activity Line Chart
    const votingCanvas = document.getElementById('votingChart');
    let votingChart;
    if (votingCanvas) {
        const votingCtx = votingCanvas.getContext('2d');
        const trendData = <?php echo json_encode($votingTrend); ?>;

        votingChart = new Chart(votingCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.label),
                datasets: [{
                    label: 'Approved',
                    data: trendData.map(d => d.approved),
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Rejected',
                    data: trendData.map(d => d.rejected),
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Abstained',
                    data: trendData.map(d => d.abstained),
                    borderColor: 'rgb(156, 163, 175)',
                    backgroundColor: 'rgba(156, 163, 175, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + ' votes';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0,
                            color: getLabelColor()
                        },
                        grid: { color: getGridColor() }
                    },
                    x: {
                        ticks: { color: getLabelColor() },
                        grid: { color: getGridColor() }
                    }
                }
            }
        });
    }

    // Session Status Doughnut Chart
    const statusCanvas = document.getElementById('sessionStatusChart');
    let statusChart;
    if (statusCanvas) {
        const statusCtx = statusCanvas.getContext('2d');
        const distData = <?php echo json_encode($sessionDist); ?>;
        const statusLabels = Object.keys(distData).map(k => k.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()));
        const statusValues = Object.values(distData);

        statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: [
                        'rgb(99, 102, 241)',   // Scheduled - Indigo
                        'rgb(16, 185, 129)',   // In Progress - Green
                        'rgb(16, 185, 129)',   // Completed - Emerald
                        'rgb(239, 68, 68)'     // Cancelled - Red
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1.5,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 },
                            color: getLabelColor(),
                            generateLabels: function(chart) {
                                const data = chart.data;
                                const currentColor = getLabelColor();
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        return {
                                            text: label + ' (' + value + ')',
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            fontColor: currentColor,
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Listen for theme changes and update charts
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                const newLabelColor = getLabelColor();
                const newGridColor = getGridColor();
                
                if (votingChart) {
                    votingChart.options.scales.x.ticks.color = newLabelColor;
                    votingChart.options.scales.y.ticks.color = newLabelColor;
                    votingChart.options.scales.x.grid.color = newGridColor;
                    votingChart.options.scales.y.grid.color = newGridColor;
                    votingChart.update();
                }
                
                if (statusChart) {
                    statusChart.options.plugins.legend.labels.color = newLabelColor;
                    statusChart.update();
                }
            }
        });
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
});
</script>
