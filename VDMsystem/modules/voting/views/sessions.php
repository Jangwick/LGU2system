<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../controllers/VotingController.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

$voting = new VotingController();
$filters = [
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

$sessions = $voting->getSessions($filters);
$stats = $voting->getStatistics();

$pageTitle = 'Voting Sessions';
$currentPage = 'sessions';
$breadcrumbs = [
    ['label' => 'Voting', 'url' => '#'],
    ['label' => 'Sessions']
];

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<!-- Sidebar -->
<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Top Navbar -->
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto bg-gray-100 p-3 md:p-6">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 animate-fade-in">
                <div class="flex items-center">
                    <i class="bi bi-check-circle text-green-500 mr-2 text-lg"></i>
                    <span class="text-green-700 font-medium"><?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Welcome/Header Banner -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white transform transition-all duration-500 ease-in-out animate-fade-in relative overflow-hidden">
            <!-- Subtle decorative background element -->
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl transition-opacity duration-500 dark:opacity-5"></div>
            
            <div class="relative flex items-center justify-between gap-4">
                <!-- Left Side: Title & Context -->
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight transition-all duration-500">
                        Voting Sessions
                    </h1>
                    <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium transition-all duration-500">
                        Manage, monitor, and conduct legislative voting sessions effectively.
                    </p>
                </div>
 
                <!-- Right Side: Action Button -->
                <div class="shrink-0">
                    <?php if (hasRole(['admin', 'secretary'])): ?>
                    <button type="button" onclick="openCreateSessionModal()" class="!bg-white !text-red-600 hover:!bg-gray-50 px-6 py-2.5 rounded-xl font-bold shadow-lg transition-all duration-500 transform hover:-translate-y-0.5 flex items-center group border border-red-600">
                        <i class="bi bi-plus-lg mr-2 transition-transform group-hover:rotate-90"></i>
                        New Voting Session
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Statistics Quick View -->
        <?php 
        // Ensure stats has default values to prevent calculation errors
        $sTotal = (int)($stats['total_sessions'] ?? 0);
        $sActive = (int)($stats['active_sessions'] ?? 0);
        $sComp = (int)($stats['completed_sessions'] ?? 0);
        $sSched = $sTotal - $sActive - $sComp;
        if ($sSched < 0) $sSched = 0;

        // Fetch data for modal
        $committees = dbFetchAll("SELECT id, name FROM committees WHERE is_active = 1 ORDER BY name");
        $pendingDocuments = dbFetchAll("SELECT id, doc_number, title, type FROM documents WHERE status = 'pending_vote' ORDER BY created_at DESC");
        $councilors = dbFetchAll("SELECT id, full_name, position FROM users WHERE role IN ('councilor', 'admin') AND is_active = 1 ORDER BY full_name");
        ?>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 animate-fade-in-up">
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-red-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Sessions</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $sTotal; ?></p>
                    </div>
                    <div class="bg-red-50 rounded-full p-2.5">
                        <i class="bi bi-calendar-event text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-green-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">In Progress</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $sActive; ?></p>
                    </div>
                    <div class="bg-green-50 rounded-full p-2.5">
                        <i class="bi bi-play-circle text-green-600 text-xl animate-pulse"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-indigo-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Scheduled</p>
                        <p class="text-2xl font-bold text-indigo-600"><?php echo $sSched; ?></p>
                    </div>
                    <div class="bg-indigo-50 rounded-full p-2.5">
                        <i class="bi bi-clock-history text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-purple-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Completed</p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo $sComp; ?></p>
                    </div>
                    <div class="bg-purple-50 rounded-full p-2.5">
                        <i class="bi bi-check2-all text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 animate-fade-in-up" style="animation-delay: 100ms;">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="relative group">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Search Sessions</label>
                    <div class="relative">
                        <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="<?php echo e($filters['search']); ?>" 
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all outline-none" 
                               placeholder="Title or Session #">
                    </div>
                </div>
                
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition-all">
                        <option value="">All Statuses</option>
                        <option value="scheduled" <?php echo $filters['status'] === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="in_progress" <?php echo $filters['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $filters['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Date Range</label>
                    <div class="flex gap-2">
                        <input type="date" name="date_from" value="<?php echo e($filters['date_from']); ?>" 
                               class="w-full px-2 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500 outline-none">
                        <input type="date" name="date_to" value="<?php echo e($filters['date_to']); ?>" 
                               class="w-full px-2 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500 outline-none">
                    </div>
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-red-700 dark:bg-red-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-red-800 dark:hover:bg-red-700 transition-all flex items-center justify-center shadow-sm">
                        <i class="bi bi-filter mr-2"></i> Apply Filters
                    </button>
                    <a href="sessions.php" class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-all border border-gray-200 dark:border-gray-700" title="Clear Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Sessions Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden animate-fade-in-up" style="animation-delay: 200ms;">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Session Details</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Schedule</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Committee</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Documents</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($sessions)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="bi bi-inbox text-5xl mb-4 block opacity-20"></i>
                                    <p class="text-lg">No voting sessions found matching your criteria.</p>
                                    <a href="sessions.php" class="text-red-600 font-bold hover:underline">Clear all filters</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sessions as $session): ?>
                                <tr class="hover:bg-red-50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="bg-red-100 text-red-700 w-10 h-10 rounded-lg flex items-center justify-center font-bold mr-4 group-hover:bg-red-600 group-hover:text-white transition-all transform group-hover:rotate-3 shadow-sm">
                                                <i class="bi bi-calendar-check"></i>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 group-hover:text-red-700 transition-colors"><?php echo e($session['title']); ?></div>
                                                <div class="text-xs text-gray-500"><?php echo e($session['session_number']); ?> • Added by <?php echo e($session['created_by_name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-700">
                                                <i class="bi bi-calendar3 mr-1 text-red-500"></i>
                                                <?php echo formatDate($session['session_date'], 'M d, Y'); ?>
                                            </span>
                                            <span class="text-xs text-gray-500 mt-1">
                                                <i class="bi bi-clock mr-1 text-gray-400"></i>
                                                <?php echo date('h:i A', strtotime($session['start_time'])); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="committee-badge text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded border border-gray-200">
                                            <?php echo e($session['committee_name'] ?? 'Plenary / General'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-bold bg-red-50 text-red-700 dark:text-red-400 w-8 h-8 rounded-full flex items-center justify-center border border-red-100 shadow-sm" title="Documents">
                                                <?php echo $session['document_count']; ?>
                                            </span>
                                            <span class="text-sm font-bold bg-blue-50 text-blue-700 dark:text-blue-400 w-8 h-8 rounded-full flex items-center justify-center border border-blue-100 shadow-sm" title="Attendees Present">
                                                <?php echo $session['attendee_count']; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php
                                        $statusClass = getStatusBadgeClass($session['status']);
                                        ?>
                                        <span class="px-3 py-1 text-xs font-bold rounded-full uppercase tracking-tighter shadow-sm <?php echo $statusClass; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $session['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button type="button" onclick="openSessionDetailsModal(<?php echo $session['id']; ?>)" 
                                               class="bg-white p-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-800 hover:text-white transition-all shadow-sm hover:shadow-md" 
                                               title="View Details">
                                                <i class="bi bi-eye-fill"></i>
                                            </button>
                                            
                                            <?php if ($session['status'] === 'in_progress' && hasRole(['councilor', 'admin'])): ?>
                                                <a href="cast-vote.php?session=<?php echo $session['id']; ?>" 
                                                   class="bg-green-600 text-white p-2 rounded-lg hover:bg-green-700 transition-all shadow-sm hover:shadow-md pulse-effect" 
                                                   title="Cast Vote">
                                                    <i class="bi bi-hand-thumbs-up-fill"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($session['status'] === 'completed'): ?>
                                                <a href="results.php?session=<?php echo $session['id']; ?>" 
                                                   class="bg-purple-600 text-white p-2 rounded-lg hover:bg-purple-700 transition-all shadow-sm hover:shadow-md" 
                                                   title="View Results">
                                                    <i class="bi bi-bar-chart-fill"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($session['status'] === 'scheduled' && hasRole(['admin', 'secretary'])): ?>
                                                <button type="button" onclick="openEditSessionModal(<?php echo $session['id']; ?>)" 
                                                   class="bg-yellow-500 text-white p-2 rounded-lg hover:bg-yellow-600 transition-all shadow-sm hover:shadow-md" 
                                                   title="Edit Session">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create/Configure Session Modal -->
        <div id="createSessionModal" class="fixed inset-0 z-[100] hidden overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300" onclick="closeCreateSessionModal()"></div>
                
                <!-- Modal Box -->
                <div class="relative bg-white w-full max-w-6xl rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all animate-modal-in flex flex-col max-h-[95vh] border border-gray-100">
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
                    <form id="sessionForm" action="create-session.php" method="POST" class="overflow-y-auto p-6 md:p-10 custom-scrollbar bg-gray-50/50">
                        <input type="hidden" name="session_id" id="modalSessionId" value="">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            
                            <!-- Left Section (Basic Info & Documents) -->
                            <div class="lg:col-span-2 space-y-8">
                                <!-- Basic Information -->
                                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                                    <div class="flex items-center gap-4 mb-8">
                                        <div class="w-11 h-11 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center text-lg">
                                            <i class="bi bi-info-circle-fill"></i>
                                        </div>
                                        <h3 class="text-xl font-black text-gray-800 tracking-tight">Basic Information</h3>
                                    </div>
                                    <div class="space-y-6">
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2">Session Title <span class="text-red-500">*</span></label>
                                            <input type="text" name="title" required class="w-full px-5 py-3.5 bg-gray-50 border border-transparent rounded-[1.25rem] focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none transition-all text-sm font-bold text-gray-700 placeholder:text-slate-400" placeholder="e.g. Regular Session - Resolution Planning">
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2">Date <span class="text-red-500">*</span></label>
                                                <input type="date" name="session_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-5 py-3.5 bg-gray-50 border border-transparent rounded-[1.25rem] focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none text-sm font-bold text-gray-700">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2">Start <span class="text-red-500">*</span></label>
                                                <input type="time" name="start_time" value="14:00" required class="w-full px-5 py-3.5 bg-gray-50 border border-transparent rounded-[1.25rem] focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none text-sm font-bold text-gray-700">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2">End</label>
                                                <input type="time" name="end_time" class="w-full px-5 py-3.5 bg-gray-50 border border-transparent rounded-[1.25rem] focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none text-sm font-bold text-gray-700">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2">Description</label>
                                            <textarea name="description" rows="3" class="w-full px-5 py-3.5 bg-gray-50 border border-transparent rounded-[1.25rem] focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none transition-all text-sm font-bold text-gray-700 resize-none" placeholder="Briefly describe the session agenda..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Legislative Items -->
                                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                                    <div class="flex items-center justify-between mb-8">
                                        <div class="flex items-center gap-4">
                                            <div class="w-11 h-11 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-lg">
                                                <i class="bi bi-file-earmark-text-fill"></i>
                                            </div>
                                            <h3 class="text-xl font-black text-gray-800 tracking-tight">Legislative Items</h3>
                                        </div>
                                        <span class="text-[10px] font-black bg-blue-50 text-blue-600 px-4 py-1.5 rounded-xl border border-blue-100 uppercase tracking-widest">Pending Vote</span>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 max-h-[300px] overflow-y-auto pr-3 custom-scrollbar">
                                        <?php if (empty($pendingDocuments)): ?>
                                            <div class="text-center py-12 bg-gray-50/50 rounded-[1.5rem] border-2 border-dashed border-gray-100">
                                                <i class="bi bi-inbox text-4xl text-slate-200 block mb-3"></i>
                                                <p class="text-sm font-bold text-slate-400">No pending legislative items found.</p>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($pendingDocuments as $doc): ?>
                                            <label class="flex items-center p-4 rounded-[1.25rem] border border-gray-50 bg-white hover:border-red-200 hover:bg-red-50/30 transition-all cursor-pointer group">
                                                <div class="mr-4">
                                                    <input type="checkbox" name="documents[]" value="<?php echo $doc['id']; ?>" class="w-5 h-5 text-red-600 rounded-lg border-slate-200 focus:ring-red-500/20 cursor-pointer">
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter"><?php echo e($doc['doc_number']); ?></span>
                                                        <span class="text-[8px] bg-white px-2 py-0.5 rounded-lg border border-slate-100 text-slate-400 font-black uppercase tracking-widest"><?php echo e($doc['type']); ?></span>
                                                    </div>
                                                    <h4 class="text-sm font-black text-slate-700 leading-tight group-hover:text-red-700 transition-colors"><?php echo e($doc['title']); ?></h4>
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
                                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6 border-b border-gray-50 pb-3">Settings</h3>
                                    <div class="space-y-6">
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Committee</label>
                                            <select name="committee_id" class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-[1.25rem] text-sm font-bold text-gray-700 outline-none focus:bg-white focus:ring-4 focus:ring-red-500/10 transition-all cursor-pointer">
                                                <option value="">-- Plenary Session --</option>
                                                <?php foreach ($committees as $c): ?>
                                                    <option value="<?php echo $c['id']; ?>"><?php echo e($c['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Vote Method</label>
                                            <select name="vote_type" class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-[1.25rem] text-sm font-bold text-gray-700 focus:bg-white outline-none focus:ring-4 focus:ring-red-500/10 cursor-pointer">
                                                <option value="roll_call">Roll Call Vote</option>
                                                <option value="voice">Voice Vote</option>
                                                <option value="ballot">Secret Ballot</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Quorum</label>
                                            <input type="number" name="quorum_required" value="5" class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-[1.25rem] text-sm font-bold text-gray-700 focus:bg-white outline-none focus:ring-4 focus:ring-red-500/10">
                                        </div>
                                    </div>
                                </div>

                                <!-- Attendees -->
                                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 min-h-[300px] flex flex-col">
                                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6 border-b border-gray-50 pb-3">Attendees</h3>
                                    <div class="overflow-y-auto space-y-3 flex-1 custom-scrollbar pr-2">
                                        <?php foreach ($councilors as $user): ?>
                                        <label class="flex items-center p-3 rounded-[1.1rem] hover:bg-gray-50 cursor-pointer transition-all border border-transparent hover:border-gray-100 group">
                                            <div class="mr-3">
                                                <input type="checkbox" name="attendees[]" value="<?php echo $user['id']; ?>" checked class="w-4.5 h-4.5 text-red-600 rounded-lg border-slate-200 focus:ring-red-500/20 cursor-pointer">
                                            </div>
                                            <div>
                                                <p class="text-[11px] font-black text-slate-700 leading-none group-hover:text-red-700 transition-colors"><?php echo e($user['full_name']); ?></p>
                                                <p class="text-[9px] text-slate-400 font-bold mt-1 uppercase opacity-70"><?php echo e($user['position']); ?></p>
                                            </div>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex items-center justify-between mt-10 pt-8 border-t border-gray-100 shrink-0">
                            <button type="button" onclick="closeCreateSessionModal()" class="px-8 py-3.5 text-sm font-black text-slate-400 hover:text-red-600 transition-all uppercase tracking-widest">Discard</button>
                            <button type="submit" class="bg-[#dc2626] text-white px-10 py-4 rounded-[1.5rem] font-black shadow-xl shadow-red-200 hover:bg-red-700 hover:-translate-y-1 transition-all flex items-center gap-3 text-sm uppercase tracking-tight">
                                Create Session <i class="bi bi-check2-circle"></i>
                            </button>
                        </div>
                    </form>
                </div> <!-- Modal Box End -->
            </div> <!-- Flex Wrapper End -->
        </div> <!-- Modal Container End -->

        <!-- Session Details Modal -->
        <div id="sessionDetailsModal" class="fixed inset-0 z-[110] hidden overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300" onclick="closeSessionDetailsModal()"></div>
                
                <!-- Modal Box -->
                <div class="relative bg-gray-50 w-full max-w-7xl rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all animate-modal-in flex flex-col max-h-[95vh] border border-gray-100">
                    <!-- Header -->
                    <div class="bg-white p-6 md:p-8 flex items-center justify-between shrink-0 border-b border-gray-100">
                        <div class="flex items-center gap-4">
                            <button onclick="closeSessionDetailsModal()" class="text-red-600 hover:text-red-700 font-black text-sm flex items-center group transition-all">
                                <i class="bi bi-arrow-left mr-2 transition-transform group-hover:-translate-x-1"></i> Back to Sessions
                            </button>
                        </div>
                        <div class="flex items-center gap-3">
                            <button class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-gray-100 transition-all text-gray-400">
                                <i class="bi bi-moon-stars"></i>
                            </button>
                            <button class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-gray-100 transition-all text-gray-400">
                                <i class="bi bi-bell"></i>
                            </button>
                            <div class="w-10 h-10 rounded-full bg-red-600 text-white flex items-center justify-center font-black">A</div>
                        </div>
                    </div>

                    <!-- Modal Body (Content Area) -->
                    <div id="detailsModalContent" class="overflow-y-auto p-6 md:p-10 custom-scrollbar">
                        <div class="flex items-center justify-center py-20">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-red-600 border-t-transparent"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<script>
    function openCreateSessionModal() {
        const modal = document.getElementById('createSessionModal');
        const form = document.getElementById('sessionForm');
        
        // Reset form for new session
        form.reset();
        form.action = 'create-session.php';
        document.getElementById('modalSessionId').value = '';
        
        // Update UI labels
        modal.querySelector('h2').innerText = 'Configure New Session';
        modal.querySelector('button[type="submit"]').innerHTML = 'Create Session <i class="bi bi-arrow-right-circle-fill"></i>';
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    async function openEditSessionModal(id) {
        const modal = document.getElementById('createSessionModal');
        const form = document.getElementById('sessionForm');
        
        try {
            // Fetch session data
            const response = await fetch(`get-session.php?id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                const s = data.session;
                
                // Populate form
                form.action = 'edit-session.php';
                document.getElementById('modalSessionId').value = s.id;
                form.querySelector('[name="title"]').value = s.title;
                form.querySelector('[name="session_date"]').value = s.session_date;
                form.querySelector('[name="start_time"]').value = s.start_time;
                form.querySelector('[name="end_time"]').value = s.end_time || '';
                form.querySelector('[name="description"]').value = s.description || '';
                form.querySelector('[name="committee_id"]').value = s.committee_id || '';
                form.querySelector('[name="vote_type"]').value = s.vote_type;
                form.querySelector('[name="quorum_required"]').value = s.quorum_required;
                
                // Handle checkboxes for documents
                const docIds = data.documents.map(d => d.document_id.toString());
                form.querySelectorAll('input[name="documents[]"]').forEach(cb => {
                    cb.checked = docIds.includes(cb.value);
                });
                
                // Handle checkboxes for attendees
                const attendeeIds = data.attendees.map(a => a.user_id.toString());
                form.querySelectorAll('input[name="attendees[]"]').forEach(cb => {
                    cb.checked = attendeeIds.includes(cb.value);
                });
                
                // Update UI labels
                modal.querySelector('h2').innerText = 'Edit Session Configuration';
                modal.querySelector('button[type="submit"]').innerHTML = 'Update Session <i class="bi bi-check2-circle-fill"></i>';
                
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Fetch error:', error);
            alert('Failed to load session details.');
        }
    }

    async function openSessionDetailsModal(id) {
        const modal = document.getElementById('sessionDetailsModal');
        const container = document.getElementById('detailsModalContent');
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        try {
            const response = await fetch(`get-session.php?id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                const s = data.session;
                const docs = data.documents;
                const atts = data.attendees;
                
                // Calculate stats
                const totalDocs = docs.length;
                const totalVotes = docs.reduce((acc, d) => acc + (parseInt(d.vote_count) || 0), 0);
                const totalPresent = atts.filter(a => a.status === 'present').length;
                const totalAbsent = atts.length - totalPresent;
                const passedDocs = docs.filter(d => d.voting_status === 'passed').length;
                const failedDocs = docs.filter(d => d.voting_status === 'failed').length;

                container.innerHTML = `
                    <div class="animate-fade-in">
                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-10">
                            <div>
                                <h1 class="text-3xl md:text-5xl font-black text-slate-900 tracking-tight leading-tight mb-2">${s.title}</h1>
                                <p class="text-slate-500 font-bold text-sm md:text-lg">
                                    <span class="text-slate-400">#${s.session_number}</span> &bull; Created by <span class="text-red-600">${s.created_by_name || 'Admin User'}</span>
                                </p>
                                <div class="flex flex-wrap items-center gap-3 mt-6">
                                    <span class="bg-indigo-50 text-indigo-600 border border-indigo-100 px-5 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-sm">${s.status.toUpperCase()}</span>
                                    <button class="bg-[#22c55e] text-white px-6 py-2.5 rounded-2xl font-black text-sm flex items-center gap-2 shadow-lg shadow-green-100 hover:-translate-y-0.5 transition-all">
                                        <i class="bi bi-play-fill text-lg"></i> Start Session
                                    </button>
                                    <button class="bg-[#facc15] text-white px-6 py-2.5 rounded-2xl font-black text-sm flex items-center gap-2 shadow-lg shadow-yellow-100 hover:-translate-y-0.5 transition-all">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </button>
                                    <button class="bg-[#4b5563] text-white px-6 py-2.5 rounded-2xl font-black text-sm flex items-center gap-2 shadow-lg shadow-gray-200 hover:-translate-y-0.5 transition-all">
                                        <i class="bi bi-x-circle-fill"></i> Cancel
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-6 mb-10">
                            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Documents</p>
                                    <p class="text-3xl font-black text-slate-900">${totalDocs}</p>
                                </div>
                                <div class="w-12 h-12 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-xl"><i class="bi bi-file-earmark-text"></i></div>
                            </div>
                            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Present</p>
                                    <p class="text-3xl font-black text-[#22c55e]">${totalPresent}/${atts.length}</p>
                                </div>
                                <div class="w-12 h-12 bg-green-50 text-[#22c55e] rounded-full flex items-center justify-center text-xl"><i class="bi bi-people"></i></div>
                            </div>
                            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Votes</p>
                                    <p class="text-3xl font-black text-indigo-600">${totalVotes}</p>
                                </div>
                                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-xl"><i class="bi bi-hand-thumbs-up"></i></div>
                            </div>
                            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Passed</p>
                                    <p class="text-3xl font-black text-teal-500">${passedDocs}</p>
                                </div>
                                <div class="w-12 h-12 bg-teal-50 text-teal-500 rounded-full flex items-center justify-center text-xl"><i class="bi bi-check2-circle"></i></div>
                            </div>
                            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Failed</p>
                                    <p class="text-3xl font-black text-red-500">${failedDocs}</p>
                                </div>
                                <div class="w-12 h-12 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-xl"><i class="bi bi-x-circle"></i></div>
                            </div>
                        </div>

                        <!-- Split Content -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
                            <div class="lg:col-span-2 bg-white rounded-[2.5rem] p-10 border border-gray-100 shadow-sm">
                                <h3 class="text-xl font-black text-slate-800 flex items-center gap-3 mb-10">
                                    <i class="bi bi-info-circle text-red-600"></i> Session Information
                                </h3>
                                <div class="grid grid-cols-2 gap-y-12 gap-x-10">
                                    <div class="flex items-start gap-5">
                                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400"><i class="bi bi-calendar2-event text-lg"></i></div>
                                        <div>
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Date</p>
                                            <p class="text-sm font-black text-slate-800">${s.session_date}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-5">
                                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400"><i class="bi bi-clock text-lg"></i></div>
                                        <div>
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Time</p>
                                            <p class="text-sm font-black text-slate-800">${s.start_time} - ${s.end_time || '05:00 PM'}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-5">
                                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400"><i class="bi bi-geo-alt text-lg"></i></div>
                                        <div>
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Location</p>
                                            <p class="text-sm font-black text-slate-800">${s.location || 'Session Hall'}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-5">
                                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400"><i class="bi bi-diagram-3 text-lg"></i></div>
                                        <div>
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Vote Type</p>
                                            <p class="text-sm font-black text-slate-800">${s.vote_type}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-5">
                                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400"><i class="bi bi-people text-lg"></i></div>
                                        <div>
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Quorum Required</p>
                                            <p class="text-sm font-black text-slate-800">${s.quorum_required} members</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-5">
                                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400"><i class="bi bi-building text-lg"></i></div>
                                        <div>
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Committee</p>
                                            <p class="text-sm font-black text-slate-800">${s.committee_name || 'Finance and Budget'}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm flex flex-col">
                                <div class="flex items-center justify-between mb-8">
                                    <h3 class="text-lg font-black text-slate-800 flex items-center gap-3">
                                        <i class="bi bi-people text-red-600"></i> Attendees
                                    </h3>
                                    <span class="text-[10px] font-black text-slate-400 tracking-tighter">${totalPresent}/${atts.length}</span>
                                </div>
                                <div class="space-y-6 overflow-y-auto pr-2 custom-scrollbar flex-1 max-h-[400px]">
                                    ${atts.map(a => `
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center font-black text-xs uppercase">${a.full_name.charAt(0)}</div>
                                                <div>
                                                    <p class="text-xs font-black text-slate-800 leading-none mb-1">${a.full_name}</p>
                                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">${a.position || 'Administrator'}</p>
                                                </div>
                                            </div>
                                            <span class="px-3 py-1 text-[8px] font-black rounded-lg ${a.status === 'present' ? 'bg-green-50 text-green-600' : 'bg-slate-50 text-slate-400'} uppercase tracking-widest border border-current opacity-70">${a.status || 'ABSENT'}</span>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>

                        <!-- Documents Section -->
                        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
                            <div class="p-8 border-b border-gray-50 flex items-center gap-4">
                                <i class="bi bi-file-earmark-text text-2xl text-red-600"></i>
                                <h3 class="text-xl font-black text-slate-800">Documents for Voting</h3>
                            </div>
                            <div class="p-16 text-center">
                                <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-slate-200 text-3xl">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <h4 class="text-xl font-black text-slate-800 mb-2">No Documents</h4>
                                <p class="text-slate-400 font-bold max-w-xs mx-auto">No documents have been assigned to this voting session for decision-making.</p>
                            </div>
                        </div>
                    </div>
                `;
            }
        } catch (error) {
            container.innerHTML = `<div class="text-center py-20 text-red-500 font-bold">Failed to load content. Please try again.</div>`;
        }
    }

    function closeSessionDetailsModal() {
        const modal = document.getElementById('sessionDetailsModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function closeCreateSessionModal() {
        const modal = document.getElementById('createSessionModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close on escape key
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCreateSessionModal();
            closeSessionDetailsModal();
        }
    });
</script>

<style>
    .animate-fade-in { animation: fadeIn 0.6s ease-out; }
    .animate-fade-in-up { animation: fadeInUp 0.6s ease-out forwards; opacity: 0; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    @keyframes modalIn {
        from { opacity: 0; transform: scale(0.95) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .animate-modal-in { animation: modalIn 0.3s ease-out forwards; }

    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 20px; }
</style>
<?php ?>
