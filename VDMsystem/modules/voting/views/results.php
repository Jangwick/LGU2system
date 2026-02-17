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
$sessionId = $_GET['session'] ?? null;
$userId = $_SESSION['user_id'];

// Get appropriate data
if (!$sessionId) {
    // List completed or in-progress sessions for results viewing
    $sessions = $voting->getSessions(['status' => ['completed', 'in_progress']]);
} else {
    // Single session detail
    $session = $voting->getSession($sessionId);
    if (!$session) {
        $_SESSION['flash_error'] = "Session not found.";
        header('Location: results.php');
        exit;
    }
    
    $summary = $voting->getSessionResultsSummary($sessionId);
    $documents = $voting->getSessionDocuments($sessionId);
}

$pageTitle = 'Voting Results';
$currentPage = 'results';
$breadcrumbs = [
    ['label' => 'Voting', 'url' => '#'],
    ['label' => 'Results']
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
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4 animate-fade-in">
                <div class="flex items-center">
                    <i class="bi bi-exclamation-triangle text-red-500 mr-2 text-lg"></i>
                    <span class="text-red-700 font-medium"><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="relative flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight">Voting Results</h1>
                    <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium">Review outcomes, analytics, and historical data of all voting sessions.</p>
                </div>
                <div class="shrink-0">
                    <a href="sessions.php" class="!bg-white !text-red-600 hover:!bg-gray-50 px-6 py-2.5 rounded-xl font-bold shadow-lg transition-all duration-500 transform hover:-translate-y-0.5 flex items-center group border border-red-600">
                        <i class="bi bi-list-ul mr-2"></i>
                        All Sessions
                    </a>
                </div>
            </div>
        </div>

        <?php if (!$sessionId): ?>
            <!-- Results List View -->
            <div class="animate-fade-in">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php if (empty($sessions)): ?>
                        <div class="lg:col-span-3 vdm-card rounded-3xl p-20 text-center border-2 border-dashed border-slate-200 dark:border-slate-800">
                            <i class="bi bi-graph-up text-7xl text-slate-200 dark:text-slate-800 mb-6 block"></i>
                            <h2 class="text-2xl font-bold vdm-heading">No Results Available</h2>
                            <p class="vdm-text-muted">Wait for sessions to complete or start to see voting results here.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($sessions as $s): ?>
                            <div class="vdm-card rounded-2xl overflow-hidden hover:shadow-2xl transition-all transform hover:-translate-y-2 group border-none">
                                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 flex justify-between items-center px-6">
                                    <span class="text-[10px] font-black vdm-text-muted uppercase tracking-widest bg-white dark:bg-slate-700 px-2 py-1 rounded shadow-sm">#<?php echo $s['session_number']; ?></span>
                                    <?php
                                    $statusClass = $s['status'] === 'completed' ? 'bg-purple-500/10 text-purple-500' : 'bg-green-500/10 text-green-500';
                                    ?>
                                    <span class="px-3 py-1 text-[9px] font-black rounded-full uppercase tracking-tighter border border-current <?php echo $statusClass; ?>">
                                        <?php echo $s['status']; ?>
                                    </span>
                                </div>
                                <div class="p-8">
                                    <h3 class="text-xl font-black vdm-heading mb-6 group-hover:text-red-500 transition-colors line-clamp-2 h-14 uppercase tracking-tighter leading-tight"><?php echo e($s['title']); ?></h3>
                                    
                                    <div class="grid grid-cols-3 gap-3 mb-8">
                                        <div class="vdm-page-bg rounded-xl p-3 text-center border border-slate-100 dark:border-slate-800 group-hover:border-red-500/30 transition-all">
                                            <p class="text-[9px] vdm-text-muted uppercase font-black mb-1 opacity-60">Items</p>
                                            <p class="font-black vdm-heading"><?php echo $s['document_count']; ?></p>
                                        </div>
                                        <div class="vdm-page-bg rounded-xl p-3 text-center border border-slate-100 dark:border-slate-800 group-hover:border-red-500/30 transition-all">
                                            <p class="text-[9px] vdm-text-muted uppercase font-black mb-1 opacity-60">Votes</p>
                                            <p class="font-black vdm-heading"><?php echo $s['vote_count']; ?></p>
                                        </div>
                                        <div class="vdm-page-bg rounded-xl p-3 text-center border border-slate-100 dark:border-slate-800 group-hover:border-red-500/30 transition-all">
                                            <p class="text-[9px] vdm-text-muted uppercase font-black mb-1 opacity-60">Quorum</p>
                                            <p class="font-black vdm-heading"><?php echo $s['attendee_count']; ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center text-xs vdm-text-muted mb-8 font-bold uppercase tracking-widest opacity-70">
                                        <i class="bi bi-calendar-check mr-2 text-red-500"></i>
                                        <?php echo formatDate($s['session_date']); ?>
                                    </div>
                                    
                                    <button onclick="viewAnalytics(<?php echo $s['id']; ?>)" class="block w-full text-center bg-slate-900 dark:bg-red-600 text-white py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-red-600 dark:hover:bg-red-700 transition-all shadow-lg hover:shadow-red-500/20">
                                        View Full Analytics <i class="bi bi-arrow-right ml-1"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Session Results Dashboard -->
            <div class="animate-fade-in" style="width: 100%; text-align: left;">
                <!-- Dashboard Header -->
                <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: flex-end; width: 100%;" class="mb-16 border-b border-slate-100 dark:border-slate-800 pb-16">
                    <!-- Left Side: Identity -->
                    <div style="flex: 1 1 0%; text-align: left;">
                        <a href="results.php" class="text-red-600 dark:text-red-500 hover:text-red-700 font-black text-[10px] mb-8 inline-flex items-center group uppercase tracking-[0.25em] bg-red-500/5 dark:bg-red-500/10 px-6 py-3 rounded-2xl border border-red-500/10 transition-all" style="display: inline-flex;">
                            <i class="bi bi-arrow-left-short mr-2 text-xl transition-transform group-hover:-translate-x-1"></i>
                            Return to Analytics List
                        </a>
                        <h1 class="text-5xl md:text-7xl font-black vdm-heading tracking-tighter uppercase leading-[0.75] mb-8 drop-shadow-sm" style="text-align: left;"><?php echo e($session['title']); ?></h1>
                        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-start; gap: 1rem;">
                             <div class="flex items-center gap-2 px-5 py-2 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl">
                                <i class="bi bi-calendar3 text-red-500"></i>
                                <?php echo formatDate($session['session_date']); ?>
                            </div>
                            <div class="flex items-center gap-2 px-5 py-2 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-2xl text-[10px] font-black uppercase tracking-widest border border-slate-200 dark:border-slate-700 shadow-lg">
                                <i class="bi bi-clock-history text-red-500"></i>
                                <?php echo $session['start_time']; ?>
                            </div>
                            <span class="w-2 h-2 bg-slate-200 dark:bg-slate-700 rounded-full"></span>
                            <span class="vdm-text-muted font-black uppercase tracking-[0.25em] text-[10px] opacity-60 flex items-center gap-2">
                                <i class="bi bi-hash text-red-500 text-sm"></i> <?php echo e($session['session_number']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Right Side: Actions -->
                    <div style="flex-shrink: 0; display: flex; flex-wrap: wrap; gap: 1rem; padding-bottom: 0.5rem;">
                        <button onclick="window.print()" class="border shadow-xl px-10 py-6 rounded-[2.5rem] font-black text-[10px] uppercase tracking-[0.15em] transition-all flex items-center gap-4 group bg-white hover:bg-slate-50 border-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:border-slate-600 dark:text-slate-200" style="color: inherit;">
                            <i class="bi bi-printer-fill text-xl text-red-500"></i> Print Full Report
                        </button>
                        <button class="px-10 py-6 rounded-[2.5rem] font-black text-[10px] uppercase tracking-[0.15em] transition-all shadow-2xl flex items-center gap-4 group bg-red-600 hover:bg-red-700 text-white border border-red-600 dark:bg-red-600 dark:hover:bg-red-700 dark:border-red-500 dark:text-white shadow-red-500/20">
                            <i class="bi bi-cloud-download-fill text-xl group-hover:translate-y-0.5 transition-transform"></i> Export Intelligence
                        </button>
                    </div>
                </div>

                <!-- Statistics Row -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
                    <div class="vdm-card p-10 rounded-[3rem] shadow-xl border-none relative overflow-hidden group hover:shadow-2xl transition-all duration-500">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-500/5 rounded-full blur-2xl group-hover:bg-red-500/10 transition-all"></div>
                        <div class="relative">
                            <p class="text-[10px] vdm-text-muted font-black uppercase tracking-widest mb-5 opacity-50 flex items-center gap-2">
                                <i class="bi bi-layers-fill text-red-500 text-sm"></i> Global Items
                            </p>
                            <p class="text-6xl font-black vdm-heading tracking-tighter mb-2"><?php echo $summary['total_docs']; ?></p>
                            <p class="text-[10px] vdm-text-muted font-bold uppercase tracking-tight opacity-70 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span> Legislative Scope
                            </p>
                        </div>
                    </div>
                    <div class="vdm-card p-10 rounded-[3rem] shadow-xl border-none relative overflow-hidden group hover:shadow-2xl transition-all duration-500">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-green-500/5 rounded-full blur-2xl group-hover:bg-green-500/10 transition-all"></div>
                        <div class="relative">
                            <p class="text-[10px] text-green-600 dark:text-green-500 font-black uppercase tracking-widest mb-5 flex items-center gap-2">
                                <i class="bi bi-shield-check text-sm"></i> Items Passed
                            </p>
                            <p class="text-6xl font-black text-green-600 dark:text-green-500 tracking-tighter mb-2"><?php echo $summary['passed_docs']; ?></p>
                            <div class="inline-flex items-center px-4 py-1 bg-green-500/10 text-green-600 rounded-xl text-[10px] font-black uppercase tracking-tighter shadow-sm border border-green-500/20">
                                <?php echo $summary['total_docs'] > 0 ? round(($summary['passed_docs'] / $summary['total_docs']) * 100) : 0; ?>% Approval
                            </div>
                        </div>
                    </div>
                    <div class="vdm-card p-10 rounded-[3rem] shadow-xl border-none relative overflow-hidden group hover:shadow-2xl transition-all duration-500">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-500/5 rounded-full blur-2xl group-hover:bg-red-500/10 transition-all"></div>
                        <div class="relative">
                            <p class="text-[10px] text-red-600 dark:text-red-500 font-black uppercase tracking-widest mb-5 flex items-center gap-2">
                                <i class="bi bi-shield-x text-sm"></i> Items Failed
                            </p>
                            <p class="text-6xl font-black text-red-600 dark:text-red-500 tracking-tighter mb-2"><?php echo $summary['failed_docs']; ?></p>
                            <p class="text-[10px] vdm-text-muted font-bold uppercase tracking-tight opacity-70 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> Action Required
                            </p>
                        </div>
                    </div>
                    <div class="vdm-card p-10 rounded-[3rem] shadow-xl border-none relative overflow-hidden group hover:shadow-2xl transition-all duration-500">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-slate-500/5 rounded-full blur-2xl group-hover:bg-slate-500/10 transition-all"></div>
                        <div class="relative">
                            <p class="text-[10px] vdm-text-muted font-black uppercase tracking-widest mb-5 opacity-50 flex items-center gap-2">
                                <i class="bi bi-people-fill text-slate-500 text-sm"></i> Total Votes
                            </p>
                            <p class="text-6xl font-black vdm-heading tracking-tighter mb-2"><?php echo $summary['total_approve'] + $summary['total_reject'] + $summary['total_abstain']; ?></p>
                            <p class="text-[10px] vdm-text-muted font-bold uppercase tracking-tight opacity-70 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-slate-500 rounded-full"></span> By <?php echo $session['attendee_count']; ?> Members
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Visual Analytics Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
                    <!-- Global Voting Distribution Chart -->
                    <div class="vdm-card p-10 rounded-[3rem] shadow-xl border-none flex flex-col bg-white dark:bg-slate-900 group hover:shadow-2xl transition-all duration-500">
                        <h3 class="font-black vdm-heading text-sm mb-12 flex items-center gap-4 uppercase tracking-[0.2em] border-b border-slate-50 dark:border-slate-800 pb-6">
                            <div class="w-10 h-10 bg-red-500/10 text-red-600 rounded-2xl flex items-center justify-center">
                                <i class="bi bi-pie-chart-fill"></i>
                            </div>
                            Session Distribution
                        </h3>
                        <div class="max-w-[260px] mx-auto mb-12 relative transform group-hover:scale-105 transition-transform duration-500">
                            <canvas id="votingChart"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-4xl font-black vdm-heading leading-none">
                                    <?php echo $summary['total_docs'] > 0 ? round(($summary['passed_docs'] / $summary['total_docs']) * 100) : 0; ?><span class="text-sm border-b-2 border-red-500 ml-1">%</span>
                                </span>
                                <span class="text-[8px] vdm-text-muted font-black uppercase tracking-widest mt-2 opacity-50">Success Rate</span>
                            </div>
                        </div>
                        <div class="space-y-4 mt-auto">
                            <div class="flex items-center justify-between p-5 bg-slate-50 dark:bg-slate-800/50 rounded-3xl border border-slate-100 dark:border-slate-800 hover:border-green-500/30 transition-all group/stat">
                                <span class="flex items-center text-[10px] font-black uppercase tracking-widest vdm-text-muted group-hover/stat:text-green-600 transition-colors">
                                    <span class="w-2.5 h-2.5 bg-green-500 rounded-full mr-4 shadow-sm shadow-green-500/20"></span> Approvals
                                </span>
                                <span class="font-black vdm-heading text-xl text-green-600"><?php echo $summary['total_approve']; ?></span>
                            </div>
                            <div class="flex items-center justify-between p-5 bg-slate-50 dark:bg-slate-800/50 rounded-3xl border border-slate-100 dark:border-slate-800 hover:border-red-500/30 transition-all group/stat">
                                <span class="flex items-center text-[10px] font-black uppercase tracking-widest vdm-text-muted group-hover/stat:text-red-500 transition-colors">
                                    <span class="w-2.5 h-2.5 bg-red-500 rounded-full mr-4 shadow-sm shadow-red-500/20"></span> Rejections
                                </span>
                                <span class="font-black vdm-heading text-xl text-red-500"><?php echo $summary['total_reject']; ?></span>
                            </div>
                            <div class="flex items-center justify-between p-5 bg-slate-50 dark:bg-slate-800/50 rounded-3xl border border-slate-100 dark:border-slate-800 hover:border-slate-500/30 transition-all group/stat">
                                <span class="flex items-center text-[10px] font-black uppercase tracking-widest vdm-text-muted group-hover/stat:text-slate-500 transition-colors">
                                    <span class="w-2.5 h-2.5 bg-slate-400 rounded-full mr-4 shadow-sm shadow-slate-400/20"></span> Abstentions
                                </span>
                                <span class="font-black vdm-heading text-xl vdm-text-muted opacity-60"><?php echo $summary['total_abstain']; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Table of Decisions -->
                    <div class="lg:col-span-2 vdm-card rounded-[3rem] shadow-xl border-none flex flex-col overflow-hidden bg-white dark:bg-slate-900">
                        <div class="p-10 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between bg-slate-50/30 dark:bg-slate-900/30 backdrop-blur-sm">
                            <h3 class="font-black vdm-heading text-sm uppercase tracking-[0.2em] flex items-center gap-4">
                                <div class="w-10 h-10 bg-blue-500/10 text-blue-600 rounded-2xl flex items-center justify-center">
                                    <i class="bi bi-list-columns-reverse"></i>
                                </div>
                                Itemized Outcomes
                            </h3>
                            <div class="flex items-center gap-3">
                                <span class="text-[9px] font-black vdm-text-muted bg-white dark:bg-slate-800 px-5 py-2 rounded-2xl border border-slate-200 dark:border-slate-700 uppercase tracking-widest shadow-sm shadow-black/5"><?php echo count($documents); ?> Items Total</span>
                            </div>
                        </div>
                        <div class="overflow-y-auto flex-1 custom-scrollbar max-h-[600px]">
                            <table class="w-full">
                                <thead class="text-[10px] vdm-text-muted uppercase font-black tracking-widest bg-slate-50/50 dark:bg-slate-800/50 backdrop-blur-md sticky top-0 z-10 border-b border-slate-50 dark:border-slate-800">
                                    <tr>
                                        <th class="px-10 py-8 text-left">Document & Legislative Identity</th>
                                        <th class="px-8 py-8 text-center uppercase tracking-tighter">Result</th>
                                        <th class="px-8 py-8 text-center">App</th>
                                        <th class="px-8 py-8 text-center">Rej</th>
                                        <th class="px-8 py-8 text-center">Abs</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                                    <?php foreach ($documents as $doc): ?>
                                        <tr class="hover:bg-red-500/[0.02] dark:hover:bg-red-500/[0.04] transition-all group">
                                            <td class="px-10 py-8">
                                                <div class="max-w-md">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="text-[8px] font-black text-red-600 dark:text-red-500 tracking-widest uppercase px-2.5 py-1 bg-red-500/5 dark:bg-red-500/10 rounded-lg border border-red-500/10"><?php echo e($doc['doc_number']); ?></span>
                                                        <span class="text-[8px] font-black vdm-text-muted tracking-widest uppercase opacity-30">•</span>
                                                        <span class="text-[8px] font-black vdm-text-muted tracking-widest uppercase opacity-40"><?php echo e($doc['type']); ?></span>
                                                    </div>
                                                    <h4 class="text-sm font-black vdm-heading uppercase group-hover:text-red-600 transition-colors tracking-tight leading-tight"><?php echo e($doc['title']); ?></h4>
                                                </div>
                                            </td>
                                            <td class="px-8 py-8 text-center">
                                                <?php
                                                $status = $doc['voting_status'] ?? 'pending';
                                                $resClass = $status === 'passed' ? 'bg-green-500/10 text-green-600 dark:text-green-500 border-green-500/20 shadow-[0_0_15px_rgba(16,185,129,0.1)]' : ($status === 'failed' ? 'bg-red-500/10 text-red-600 dark:text-red-500 border-red-500/20 shadow-[0_0_15px_rgba(239,68,68,0.1)]' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700');
                                                ?>
                                                <span class="px-5 py-2 text-[9px] font-black rounded-xl uppercase tracking-widest shadow-sm border <?php echo $resClass; ?>">
                                                    <?php echo $status; ?>
                                                </span>
                                            </td>
                                            <td class="px-8 py-8 text-center">
                                                <div class="text-lg font-black text-green-600 dark:text-green-500"><?php echo $doc['approve_count']; ?></div>
                                                <div class="text-[8px] font-bold vdm-text-muted uppercase opacity-30 mt-1 tracking-widest">App</div>
                                            </td>
                                            <td class="px-8 py-8 text-center">
                                                <div class="text-lg font-black text-red-600 dark:text-red-500"><?php echo $doc['reject_count']; ?></div>
                                                <div class="text-[8px] font-bold vdm-text-muted uppercase opacity-30 mt-1 tracking-widest">Rej</div>
                                            </td>
                                            <td class="px-8 py-8 text-center">
                                                <div class="text-lg font-black text-slate-400 dark:text-slate-600"><?php echo $doc['abstain_count']; ?></div>
                                                <div class="text-[8px] font-bold vdm-text-muted uppercase opacity-30 mt-1 tracking-widest">Abs</div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Individual Votes Logs (Auditable) -->
                <?php if (hasRole(['admin', 'secretary'])): ?>
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-xl overflow-hidden mb-12 text-slate-900 dark:text-white p-10 border border-slate-100 dark:border-slate-800">
                        <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-6 border-b border-slate-50 dark:border-slate-800 pb-8">
                            <div>
                                <h3 class="text-2xl font-black mb-1 flex items-center gap-3 uppercase tracking-tighter vdm-heading">
                                    <i class="bi bi-shield-lock-fill text-red-600"></i>
                                    Individual Audit Log
                                </h3>
                                <p class="vdm-text-muted text-sm font-medium opacity-70">Review specific decisions made by each legislator during this session.</p>
                            </div>
                            <div class="bg-slate-100 dark:bg-slate-800 px-6 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-black uppercase tracking-widest italic flex items-center">
                                    <i class="bi bi-info-circle mr-2 text-red-600"></i> Authorized Access Required
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 custom-scrollbar max-h-[500px] overflow-y-auto pr-4">
                            <?php 
                            // Fetch all votes for audit
                            $allVotes = dbFetchAll(
                                "SELECT v.*, u.full_name as voter_name, u.position, d.title as doc_title
                                 FROM votes v
                                 JOIN users u ON v.councilor_id = u.id
                                 JOIN documents d ON v.document_id = d.id
                                 WHERE v.session_id = ?
                                 ORDER BY v.cast_at DESC", 
                                [$sessionId]
                            );
                            
                            if (empty($allVotes)): ?>
                                <div class="col-span-full py-20 text-center">
                                    <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-slate-100 dark:border-slate-700">
                                        <i class="bi bi-database-exclamation text-3xl text-slate-300"></i>
                                    </div>
                                    <p class="font-black uppercase tracking-widest text-[10px] vdm-text-muted opacity-40">No records found for this session</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($allVotes as $v): ?>
                                    <div class="bg-slate-50/50 dark:bg-slate-800/30 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 hover:border-red-500 transition-all group shadow-sm">
                                        <div class="flex items-center justify-between mb-5">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 bg-white dark:bg-slate-700 text-red-600 rounded-2xl flex items-center justify-center font-black text-xl shadow-sm border border-slate-100 dark:border-slate-600">
                                                    <?php echo strtoupper(substr($v['voter_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-black vdm-heading uppercase tracking-tighter mb-0.5 group-hover:text-red-600 transition-colors"><?php echo e($v['voter_name']); ?></p>
                                                    <p class="text-[9px] vdm-text-muted uppercase font-black tracking-widest opacity-60"><?php echo e($v['position']); ?></p>
                                                </div>
                                            </div>
                                            <?php
                                            $vClass = $v['vote'] === 'approve' ? 'bg-green-500/10 text-green-600 dark:text-green-500 border-green-500/20' : ($v['vote'] === 'reject' ? 'bg-red-500/10 text-red-600 dark:text-red-500 border-red-500/20' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 border-slate-200 dark:border-slate-600');
                                            ?>
                                            <span class="px-2.5 py-1 text-[8px] font-black rounded-lg uppercase border tracking-widest <?php echo $vClass; ?>">
                                                <?php echo $v['vote']; ?>
                                            </span>
                                        </div>
                                        <div class="border-t border-slate-100 dark:border-slate-800 pt-4">
                                            <p class="text-[9px] vdm-text-muted uppercase font-black mb-1.5 opacity-40 tracking-widest">Document Item</p>
                                            <p class="text-[11px] font-black vdm-text-muted dark:text-slate-300 uppercase leading-snug tracking-tighter mb-4 line-clamp-1 group-hover:text-slate-900 dark:group-hover:text-white transition-colors"><?php echo e($v['doc_title']); ?></p>
                                            <div class="flex items-center justify-between">
                                                <span class="text-[8px] bg-white dark:bg-slate-900/50 px-2 py-1 rounded border border-slate-100 dark:border-slate-800 text-slate-400 font-bold tracking-tighter">ID: #<?php echo $v['id']; ?></span>
                                                <p class="text-[9px] vdm-text-muted font-black uppercase tracking-tighter opacity-60"><?php echo date('M d, H:i', strtotime($v['cast_at'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Chart Initialization Script -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('votingChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Approve', 'Reject', 'Abstain'],
                            datasets: [{
                                data: [
                                    <?php echo $summary['total_approve']; ?>, 
                                    <?php echo $summary['total_reject']; ?>, 
                                    <?php echo $summary['total_abstain']; ?>
                                ],
                                backgroundColor: ['#10b981', '#ef4444', '#9ca3af'],
                                borderWidth: 0,
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            cutout: '75%',
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });
                });
            </script>
        <?php endif; ?>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>

<!-- Analytics Modal -->
<div id="analyticsModal" class="fixed inset-0 z-[100] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300" onclick="closeAnalyticsModal()"></div>
        
        <!-- Modal Box -->
        <div class="relative bg-white dark:bg-slate-900 w-full max-w-6xl rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.3)] overflow-hidden transform transition-all animate-modal-in flex flex-col max-h-[92vh] border border-white/20 dark:border-slate-800">
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 p-8 md:p-10 text-white flex items-center justify-between shrink-0 relative overflow-hidden">
                <!-- Decorative elements -->
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-black/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 flex items-center gap-6">
                    <div class="w-16 h-16 bg-white/20 rounded-[2rem] flex items-center justify-center backdrop-blur-xl border border-white/30 shadow-inner group">
                        <i class="bi bi-bar-chart-line-fill text-3xl group-hover:scale-110 transition-transform duration-500"></i>
                    </div>
                    <div>
                        <h2 class="text-3xl font-black tracking-tight leading-none mb-2">Legislative Intelligence</h2>
                        <div class="flex items-center gap-3">
                            <span class="text-red-100 text-[10px] font-black uppercase tracking-[0.3em] opacity-80">Session Performance Analytics</span>
                            <span class="w-1 h-1 bg-white/40 rounded-full"></span>
                            <span id="modalSessionNum" class="text-white text-[10px] font-black uppercase tracking-widest">Loading...</span>
                        </div>
                    </div>
                </div>
                <button onclick="closeAnalyticsModal()" class="relative z-10 w-14 h-14 flex items-center justify-center rounded-2xl bg-white/10 hover:bg-white/20 transition-all group backdrop-blur-md border border-white/10">
                    <i class="bi bi-x-lg text-xl transition-transform group-hover:rotate-90"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div id="analyticsContent" class="flex-1 overflow-y-auto p-0 custom-scrollbar bg-slate-50 dark:bg-slate-950 min-h-[500px]">
                <div class="flex flex-col items-center justify-center h-[500px]">
                    <div class="relative">
                        <div class="animate-ping absolute inset-0 rounded-full bg-red-500/20"></div>
                        <div class="relative bg-white dark:bg-slate-800 p-6 rounded-3xl shadow-xl">
                            <i class="bi bi-cpu text-4xl text-red-600 animate-pulse"></i>
                        </div>
                    </div>
                    <p class="mt-8 vdm-text-muted font-black uppercase tracking-[0.4em] text-[10px] animate-pulse">Generating Report...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes modal-in {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
.animate-modal-in {
    animation: modal-in 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>

<script>
function viewAnalytics(sessionId) {
    const modal = document.getElementById('analyticsModal');
    const content = document.getElementById('analyticsContent');
    const sessionNum = document.getElementById('modalSessionNum');
    
    // Show modal and loading state
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    sessionNum.innerText = "Processing...";
    content.innerHTML = `
        <div class="flex flex-col items-center justify-center h-[500px]">
            <div class="relative">
                <div class="animate-ping absolute inset-0 rounded-full bg-red-500/20"></div>
                <div class="relative bg-white dark:bg-slate-800 p-6 rounded-3xl shadow-xl">
                    <i class="bi bi-cpu text-4xl text-red-600 animate-pulse"></i>
                </div>
            </div>
            <p class="mt-8 vdm-text-muted font-black uppercase tracking-[0.4em] text-[10px] animate-pulse">Generating Report...</p>
        </div>
    `;

    // Fetch analytics data
    fetch(`get-session-analytics.php?id=${sessionId}`)
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
            
            // Extract session number from loaded content if needed, 
            // or we can pass it from the button
            const loadedSessionNum = content.querySelector('[data-session-num]')?.getAttribute('data-session-num');
            if (loadedSessionNum) sessionNum.innerText = loadedSessionNum;

            // Execute any scripts in the loaded HTML (for Chart.js)
            const scripts = content.getElementsByTagName('script');
            for (let script of scripts) {
                const newScript = document.createElement('script');
                newScript.text = script.text;
                document.body.appendChild(newScript).parentNode.removeChild(newScript);
            }
        })
        .catch(error => {
            console.error('Error fetching analytics:', error);
            content.innerHTML = `
                <div class="p-10 text-center">
                    <i class="bi bi-exclamation-triangle text-5xl text-red-500 mb-4 block"></i>
                    <h3 class="text-xl font-black vdm-heading mb-2">Failed to load analytics</h3>
                    <p class="vdm-text-muted mb-6">There was an error retrieving the data for this session.</p>
                    <button onclick="viewAnalytics(${sessionId})" class="bg-red-600 text-white px-6 py-2 rounded-xl font-bold">Try Again</button>
                </div>
            `;
        });
}

function closeAnalyticsModal() {
    const modal = document.getElementById('analyticsModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

// Close on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
        closeAnalyticsModal();
    }
});
</script>
</div>

<style>
    .animate-fade-in { animation: fadeIn 0.8s ease-out; }
    .animate-fade-in-up { animation: fadeInUp 0.8s ease-out forwards; opacity: 0; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    
    .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 20px; }
    
    /* Document results table scroll styling for detail view */
    .custom-scrollbar { scrollbar-width: thin; scrollbar-color: #e5e7eb transparent; }
</style>
