<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';
require_once __DIR__ . '/../controllers/VotingController.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

$voting = new VotingController();
$sessionId = $_GET['id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$sessionId) {
    $_SESSION['flash_error'] = "No session ID provided.";
    header('Location: sessions.php');
    exit;
}

$session = $voting->getSession($sessionId);
if (!$session) {
    $_SESSION['flash_error'] = "Session not found.";
    header('Location: sessions.php');
    exit;
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasRole(['admin', 'secretary'])) {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'start':
                $voting->startSession($sessionId, $userId);
                $_SESSION['flash_success'] = "Session started successfully! Voting is now open.";
                break;
            case 'end':
                $voting->endSession($sessionId, $userId);
                $_SESSION['flash_success'] = "Session ended. Results have been calculated.";
                break;
            case 'cancel':
                $voting->cancelSession($sessionId, $userId);
                $_SESSION['flash_success'] = "Session has been cancelled.";
                break;
            case 'mark_attendance':
                $attendeeId = $_POST['user_id'] ?? null;
                $isPresent = ($_POST['status'] ?? '') === 'present';
                if ($attendeeId) {
                    $voting->markAttendance($sessionId, $attendeeId, $isPresent);
                    $_SESSION['flash_success'] = "Attendance updated.";
                }
                break;
        }
    } catch (Exception $e) {
        $_SESSION['flash_error'] = $e->getMessage();
    }
    
    header("Location: session-details.php?id=$sessionId");
    exit;
}

// Refresh session data after potential updates
$session = $voting->getSession($sessionId);
$documents = $voting->getSessionDocuments($sessionId);
$attendees = $voting->getSessionAttendees($sessionId);

// Calculate summary
$totalDocs = count($documents);
$passedDocs = count(array_filter($documents, fn($d) => $d['voting_status'] === 'passed'));
$failedDocs = count(array_filter($documents, fn($d) => $d['voting_status'] === 'failed'));
$pendingDocs = count(array_filter($documents, fn($d) => $d['voting_status'] === 'pending'));
$totalPresent = count(array_filter($attendees, fn($a) => $a['status'] === 'present'));
$totalVotes = array_sum(array_column($documents, 'vote_count'));

$pageTitle = $session['title'] . ' - Details';
$currentPage = 'voting-sessions';
$breadcrumbs = [
    ['label' => 'Voting', 'url' => '#'],
    ['label' => 'Sessions', 'url' => 'sessions.php'],
    ['label' => $session['session_number']]
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
    <main class="flex-1 overflow-y-auto vdm-page-bg p-3 md:p-6 custom-scrollbar">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 animate-fade-in">
                <div class="flex items-center">
                    <i class="bi bi-check-circle text-green-500 mr-2 text-lg"></i>
                    <span class="text-green-700 font-medium"><?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4 animate-fade-in">
                <div class="flex items-center">
                    <i class="bi bi-exclamation-triangle text-red-500 mr-2 text-lg"></i>
                    <span class="text-red-700 font-medium"><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Breadcrumbs & Navigation -->
        <div class="mb-8">
            <nav class="text-xs font-black uppercase tracking-widest mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 vdm-text-muted">
                    <li><a href="#" class="hover:text-red-500 transition-colors">Voting</a></li>
                    <li><i class="bi bi-chevron-right text-[10px]"></i></li>
                    <li><a href="sessions.php" class="hover:text-red-500 transition-colors font-black">Sessions</a></li>
                    <li><i class="bi bi-chevron-right text-[10px]"></i></li>
                    <li class="vdm-heading font-black"><?php echo e($session['session_number']); ?></li>
                </ol>
            </nav>
            <a href="sessions.php" class="text-red-500 hover:text-red-600 text-xs font-black flex items-center group transition-all uppercase tracking-widest">
                <i class="bi bi-arrow-left mr-2 transition-transform group-hover:-translate-x-1 font-black"></i> Back to Sessions
            </a>
        </div>

        <!-- Session Header Title Section -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
            <div>
                <h1 class="text-3xl md:text-5xl font-black vdm-heading tracking-tighter leading-none uppercase mb-2">
                    <?php echo e($session['title']); ?>
                </h1>
                <div class="flex items-center gap-3">
                    <span class="vdm-text-muted font-bold tracking-widest uppercase text-xs">Administrative Terminal</span>
                    <span class="w-1.5 h-1.5 bg-slate-300 dark:bg-slate-700 rounded-full"></span>
                    <p class="vdm-text-muted text-xs font-medium">
                        Created by <span class="text-red-500 font-black uppercase"><?php echo e($session['created_by_name'] ?? 'Admin User'); ?></span>
                    </p>
                </div>
                
                <!-- Action Buttons / Status Badge -->
                <div class="flex flex-wrap items-center gap-4 mt-6">
                    <?php
                    $statusConfig = [
                        'scheduled' => ['bg' => 'bg-indigo-500/10', 'text' => 'text-indigo-500', 'label' => 'Scheduled'],
                        'in_progress' => ['bg' => 'bg-green-500/10', 'text' => 'text-green-500', 'label' => 'Live Now'],
                        'completed' => ['bg' => 'bg-purple-500/10', 'text' => 'text-purple-500', 'label' => 'Archived'],
                        'cancelled' => ['bg' => 'bg-slate-500/10', 'text' => 'text-slate-500', 'label' => 'Cancelled']
                    ];
                    $cfg = $statusConfig[$session['status']] ?? ['bg' => 'bg-slate-500/10', 'text' => 'text-slate-500', 'label' => 'Unknown'];
                    ?>
                    <span class="<?php echo $cfg['bg'] . ' ' . $cfg['text']; ?> px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-current">
                        <?php echo $cfg['label']; ?>
                    </span>

                    <?php if (hasRole(['admin', 'secretary'])): ?>
                        <?php if ($session['status'] === 'scheduled'): ?>
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="start">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl text-[10px] uppercase tracking-widest font-black transition-all shadow-lg shadow-green-600/20 flex items-center">
                                <i class="bi bi-play-fill mr-2 text-lg"></i> Start Session
                            </button>
                        </form>
                        <a href="edit-session.php?id=<?php echo $sessionId; ?>" class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-2.5 rounded-xl text-[10px] uppercase tracking-widest font-black transition-all shadow-lg shadow-amber-500/20 flex items-center">
                            <i class="bi bi-pencil-fill mr-2"></i> Edit
                        </a>
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="cancel">
                            <button type="submit" class="vdm-card border shadow-none hover:bg-slate-100 dark:hover:bg-slate-800 vdm-text-muted px-6 py-2.5 rounded-xl text-[10px] uppercase tracking-widest font-black transition-all flex items-center">
                                <i class="bi bi-x-circle-fill mr-2"></i> Cancel
                            </button>
                        </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Premium Statistics Cards Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-6 mb-10">
            <!-- Documents Card -->
            <div class="vdm-card rounded-2xl shadow-xl border-none p-6 flex items-center justify-between group hover:-translate-y-1 transition-all">
                <div>
                    <p class="text-[10px] font-black vdm-text-muted uppercase tracking-widest mb-1 opacity-60">Documents</p>
                    <p class="text-3xl font-black vdm-heading"><?php echo $totalDocs; ?></p>
                </div>
                <div class="bg-red-500/10 text-red-500 w-12 h-12 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-file-earmark-diff"></i>
                </div>
            </div>
            <!-- Present Card -->
            <div class="vdm-card rounded-2xl shadow-xl border-none p-6 flex items-center justify-between group hover:-translate-y-1 transition-all">
                <div>
                    <p class="text-[10px] font-black vdm-text-muted uppercase tracking-widest mb-1 opacity-60">Quorum</p>
                    <p class="text-3xl font-black text-green-500"><?php echo $totalPresent; ?><span class="text-sm vdm-text-muted ml-1">/<?php echo count($attendees); ?></span></p>
                </div>
                <div class="bg-green-500/10 text-green-500 w-12 h-12 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-people"></i>
                </div>
            </div>
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <!-- Total Votes Card -->
            <div class="vdm-card rounded-2xl shadow-xl border-none p-6 flex items-center justify-between group hover:-translate-y-1 transition-all">
                <div>
                    <p class="text-[10px] font-black vdm-text-muted uppercase tracking-widest mb-1 opacity-60">Total Votes</p>
                    <p class="text-3xl font-black text-purple-500"><?php echo $totalVotes; ?></p>
                </div>
                <div class="bg-purple-500/10 text-purple-500 w-12 h-12 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-hand-thumbs-up"></i>
                </div>
            </div>
            <!-- Passed Card -->
            <div class="vdm-card rounded-2xl shadow-xl border-none p-6 flex items-center justify-between group hover:-translate-y-1 transition-all">
                <div>
                    <p class="text-[10px] font-black vdm-text-muted uppercase tracking-widest mb-1 opacity-60">Passed</p>
                    <p class="text-3xl font-black text-teal-500"><?php echo $passedDocs; ?></p>
                </div>
                <div class="bg-teal-500/10 text-teal-500 w-12 h-12 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
            <!-- Failed Card -->
            <div class="vdm-card rounded-2xl shadow-xl border-none p-6 flex items-center justify-between group hover:-translate-y-1 transition-all">
                <div>
                    <p class="text-[10px] font-black vdm-text-muted uppercase tracking-widest mb-1 opacity-60">Failed</p>
                    <p class="text-3xl font-black text-orange-500"><?php echo $failedDocs; ?></p>
                </div>
                <div class="bg-orange-500/10 text-orange-500 w-12 h-12 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
        
        <!-- Info Split View -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
            <!-- Session Information -->
            <div class="lg:col-span-2 vdm-card rounded-3xl shadow-xl p-8 md:p-10 border-none relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-red-500/[0.02] rounded-full -mr-32 -mt-32 blur-3xl"></div>
                <h2 class="text-xl md:text-2xl font-black vdm-heading mb-10 flex items-center uppercase tracking-tighter">
                    <i class="bi bi-info-circle text-red-500 mr-4 text-2xl"></i>
                    Session Configuration
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-10 gap-x-12 relative z-10">
                    <div class="flex items-start group">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mr-4 group-hover:bg-red-500 transition-colors">
                            <i class="bi bi-calendar-event text-slate-500 group-hover:text-white transition-colors"></i>
                        </div>
                        <div>
                            <p class="text-[10px] vdm-text-muted uppercase font-black tracking-widest mb-1 opacity-60">Legislative Date</p>
                            <p class="vdm-heading font-black uppercase text-sm tracking-tight"><?php echo formatDate($session['session_date'], 'F d, Y'); ?></p>
                        </div>
                    </div>
                    <div class="flex items-start group">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mr-4 group-hover:bg-red-500 transition-colors">
                            <i class="bi bi-clock-history text-slate-500 group-hover:text-white transition-colors"></i>
                        </div>
                        <div>
                            <p class="text-[10px] vdm-text-muted uppercase font-black tracking-widest mb-1 opacity-60">Time Slot</p>
                            <p class="vdm-heading font-black uppercase text-sm tracking-tight">
                                <?php echo date('h:i A', strtotime($session['start_time'])); ?> - 
                                <?php echo $session['end_time'] ? date('h:i A', strtotime($session['end_time'])) : '05:00 PM'; ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start group">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mr-4 group-hover:bg-red-500 transition-colors">
                            <i class="bi bi-geo-alt-fill text-slate-500 group-hover:text-white transition-colors"></i>
                        </div>
                        <div>
                            <p class="text-[10px] vdm-text-muted uppercase font-black tracking-widest mb-1 opacity-60">Venue / Location</p>
                            <p class="vdm-heading font-black uppercase text-sm tracking-tight"><?php echo e($session['location'] ?? 'Legislative Hall'); ?></p>
                        </div>
                    </div>
                    <div class="flex items-start group">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mr-4 group-hover:bg-red-500 transition-colors">
                            <i class="bi bi-fingerprint text-slate-500 group-hover:text-white transition-colors"></i>
                        </div>
                        <div>
                            <p class="text-[10px] vdm-text-muted uppercase font-black tracking-widest mb-1 opacity-60">Protocol Type</p>
                            <p class="vdm-heading font-black uppercase text-sm tracking-tight"><?php echo str_replace('_', ' ', $session['vote_type'] ?? 'Roll Call'); ?></p>
                        </div>
                    </div>
                    <div class="flex items-start group">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mr-4 group-hover:bg-red-500 transition-colors">
                            <i class="bi bi-shield-check text-slate-500 group-hover:text-white transition-colors"></i>
                        </div>
                        <div>
                            <p class="text-[10px] vdm-text-muted uppercase font-black tracking-widest mb-1 opacity-60">Quorum Requirement</p>
                            <p class="vdm-heading font-black uppercase text-sm tracking-tight"><?php echo $session['quorum_required'] ?? 7; ?> MEMBERS MIN.</p>
                        </div>
                    </div>
                    <div class="flex items-start group">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mr-4 group-hover:bg-red-500 transition-colors">
                            <i class="bi bi-building-fill text-slate-500 group-hover:text-white transition-colors"></i>
                        </div>
                        <div>
                            <p class="text-[10px] vdm-text-muted uppercase font-black tracking-widest mb-1 opacity-60">Primary Committee</p>
                            <p class="vdm-heading font-black uppercase text-sm tracking-tight"><?php echo e($session['committee_name'] ?? 'General Assembly'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Attendees -->
            <div class="vdm-card rounded-3xl shadow-xl p-8 border-none flex flex-col">
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-sm font-black vdm-heading flex items-center uppercase tracking-widest">
                        <i class="bi bi-people-fill text-red-500 mr-3 text-lg"></i>
                        Quorum Watch
                    </h2>
                    <span class="text-[10px] font-black vdm-text-muted bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full"><?php echo $totalPresent; ?>/<?php echo count($attendees); ?></span>
                </div>
                
                <div class="space-y-5 max-h-[400px] overflow-y-auto pr-3 custom-scrollbar">
                    <?php if (empty($attendees)): ?>
                        <div class="text-center py-16 text-slate-300 dark:text-slate-700">
                            <i class="bi bi-person-x text-5xl mb-4 block"></i>
                            <p class="text-[10px] font-black uppercase tracking-widest">No attendees records</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($attendees as $attendee): ?>
                            <div class="flex items-center justify-between group">
                                <div class="flex items-center">
                                    <div class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center vdm-heading font-black text-sm mr-4 border border-slate-200 dark:border-slate-700 group-hover:border-red-500/30 transition-all">
                                        <?php echo strtoupper(substr($attendee['full_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-black vdm-heading uppercase tracking-tighter"><?php echo e($attendee['full_name']); ?></p>
                                        <p class="text-[9px] vdm-text-muted font-bold uppercase opacity-60 tracking-widest"><?php echo e($attendee['position'] ?? 'Member'); ?></p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 text-[8px] font-black rounded uppercase tracking-tighter border shadow-sm <?php echo $attendee['status'] === 'present' ? 'bg-green-500/10 text-green-500 border-green-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700'; ?>">
                                    <?php echo e($attendee['status'] ?? 'Absent'); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Documents Section -->
        <div class="vdm-card rounded-3xl shadow-2xl border-none overflow-hidden mb-12 animate-fade-in-up">
            <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h2 class="text-xl font-black vdm-heading flex items-center uppercase tracking-tighter">
                    <i class="bi bi-file-earmark-text-fill text-red-500 mr-4 text-2xl"></i>
                    Legislative Agenda & Documents
                </h2>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-black vdm-text-muted bg-slate-100 dark:bg-slate-800 px-4 py-1.5 rounded-full uppercase tracking-widest border border-slate-200 dark:border-slate-700">
                        Total Items: <?php echo count($documents); ?>
                    </span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50">
                            <th class="px-8 py-4 text-left text-[10px] font-black vdm-text-muted uppercase tracking-[0.2em] border-b border-slate-100 dark:border-slate-800">No.</th>
                            <th class="px-8 py-4 text-left text-[10px] font-black vdm-text-muted uppercase tracking-[0.2em] border-b border-slate-100 dark:border-slate-800">Document / Description</th>
                            <th class="px-8 py-4 text-center text-[10px] font-black vdm-text-muted uppercase tracking-[0.2em] border-b border-slate-100 dark:border-slate-800">Verification</th>
                            <th class="px-8 py-4 text-center text-[10px] font-black vdm-text-muted uppercase tracking-[0.2em] border-b border-slate-100 dark:border-slate-800">Status</th>
                            <th class="px-8 py-4 text-right text-[10px] font-black vdm-text-muted uppercase tracking-[0.2em] border-b border-slate-100 dark:border-slate-800">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php if (empty($documents)): ?>
                            <tr>
                                <td colspan="5" class="px-8 py-24 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-3xl flex items-center justify-center mb-4 text-slate-300 dark:text-slate-600">
                                            <i class="bi bi-inbox-fill text-3xl"></i>
                                        </div>
                                        <p class="text-[11px] font-black vdm-heading uppercase tracking-widest">No legislative items found</p>
                                        <p class="text-[10px] vdm-text-muted mt-1 uppercase font-bold opacity-60">Session is awaiting agenda upload</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($documents as $index => $doc): ?>
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                                    <td class="px-8 py-6 text-[11px] font-black vdm-heading text-slate-400 group-hover:text-red-500 transition-colors">
                                        <?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?>
                                    </td >
                                    <td class="px-8 py-6">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mr-4 group-hover:bg-red-500/10 transition-all border border-slate-200 dark:border-slate-700 group-hover:border-red-500/30">
                                                <i class="bi bi-file-earmark-pdf-fill text-red-500 text-lg"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-black vdm-heading leading-tight mb-1 group-hover:text-red-500 transition-colors"><?php echo e($doc['title']); ?></p>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[9px] font-black vdm-text-muted uppercase opacity-60 tracking-tighter"><?php echo e($doc['document_number'] ?? 'N/A'); ?></span>
                                                    <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                                                    <span class="text-[9px] font-black vdm-text-muted uppercase opacity-60 tracking-tighter"><?php echo e($doc['category'] ?? 'General Resolution'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="flex -space-x-2 mb-1">
                                                <div class="w-6 h-6 rounded-full border-2 border-white dark:border-slate-900 bg-teal-500 flex items-center justify-center text-[10px] text-white">
                                                    <i class="bi bi-patch-check-fill"></i>
                                                </div>
                                            </div>
                                            <span class="text-[9px] font-black text-teal-500 uppercase tracking-tighter">Council Verified</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="px-3 py-1 text-[9px] font-black rounded-full uppercase tracking-widest border <?php 
                                            echo $doc['voting_status'] === 'passed' ? 'bg-green-500/10 text-green-500 border-green-500/20' : 
                                                ($doc['voting_status'] === 'failed' ? 'bg-red-500/10 text-red-500 border-red-500/20' : 
                                                'bg-slate-100 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700'); 
                                        ?>">
                                            <?php echo $doc['voting_status'] ?? 'PENDING'; ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="view-document.php?id=<?php echo $doc['id']; ?>" class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($session['status'] === 'in_progress'): ?>
                                                <button onclick="openLiveVoteModal(<?php echo $doc['id']; ?>)" class="w-8 h-8 rounded-lg bg-red-500 flex items-center justify-center text-white hover:bg-black transition-all shadow-lg shadow-red-500/20">
                                                    <i class="bi bi-play-fill text-lg"></i>
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
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>
