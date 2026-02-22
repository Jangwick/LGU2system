<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../controllers/ReportsController.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Only secretary & admin can access reports
if (!hasRole(['secretary', 'admin', 'administrator'])) {
    $_SESSION['flash_error'] = "Access denied. Reports are restricted to secretaries and administrators.";
    header('Location: ' . DASHBOARD_INDEX_URL);
    exit;
}

$reports = new ReportsController();

// Get date range filters
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date']   ?? date('Y-m-d');
$tab       = $_GET['tab']        ?? 'overview';

// Gather data from controller
$kpi               = $reports->getKPIStats($startDate, $endDate);
$votingTrend        = $reports->getVotingTrend($startDate, $endDate);
$voteDistribution   = $reports->getVoteDistribution($startDate, $endDate);
$monthlyComparison  = $reports->getMonthlyComparison(12);
$docTypes           = $reports->getDocumentTypeDistribution($startDate, $endDate);
$docStatuses        = $reports->getDocumentStatusDistribution($startDate, $endDate);
$sessionStatuses    = $reports->getSessionStatusBreakdown($startDate, $endDate);
$legislators        = $reports->getLegislatorParticipation($startDate, $endDate);
$attendance         = $reports->getAttendanceAnalytics($startDate, $endDate);
$sessions           = $reports->getSessionsList($startDate, $endDate);
$committees         = $reports->getCommitteeAnalytics($startDate, $endDate);
$recentActivity     = $reports->getRecentActivity($startDate, $endDate);
$docTimeline        = $reports->getDocumentTimeline($startDate, $endDate);
$votingByHour       = $reports->getVotingByHour($startDate, $endDate);

// Computed values
$maxLegVotes = !empty($legislators) ? max(array_column($legislators, 'total_votes')) : 0;

$pageTitle   = 'Reports & Analytics';
$currentPage = 'reports';
$breadcrumbs = [['label' => 'Reports & Analytics']];

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<!-- Sidebar -->
<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Top Navbar -->
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-[#1a1a1a] p-3 md:p-6">
        
        <!-- Page Header -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white transform transition-all duration-500 ease-in-out animate-fade-in relative overflow-hidden">
            <!-- Subtle decorative background element -->
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl transition-opacity duration-500 dark:opacity-5"></div>
            
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <!-- Left Side: Title & Context -->
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight transition-all duration-500">
                        Reports & Analytics
                    </h1>
                    <p class="text-red-100 text-sm opacity-90 font-medium transition-all duration-500">
                        Legislative performance, voting trends, attendance metrics, and document intelligence.
                    </p>
                </div>

                <!-- Right Side: Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <button onclick="exportReport()" class="!bg-white/10 hover:!bg-white/20 backdrop-blur-sm text-white px-4 md:px-6 py-3 rounded-xl font-bold shadow-lg transition-all duration-500 transform hover:-translate-y-0.5 flex items-center justify-center group border border-white/20 w-full md:w-auto text-sm">
                        <i class="bi bi-download mr-2"></i> Export
                    </button>
                    <button onclick="window.print()" class="!bg-white !text-red-600 hover:!bg-gray-50 px-4 md:px-6 py-3 rounded-xl font-bold shadow-lg transition-all duration-500 transform hover:-translate-y-0.5 flex items-center justify-center group border border-red-600 w-full md:w-auto text-sm">
                        <i class="bi bi-printer mr-2"></i> Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-md p-6 mb-6 border border-gray-100 dark:border-gray-800">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
                <div class="md:col-span-8 lg:col-span-9">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Analysis Period</label>
                    <div class="grid grid-cols-1 sm:grid-cols-[1fr,auto,1fr] items-center gap-3">
                        <div class="relative">
                            <i class="bi bi-calendar-event absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="date" name="start_date" value="<?php echo $startDate; ?>" 
                                   class="w-full pl-10 pr-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all text-sm text-gray-700 dark:text-gray-300">
                        </div>
                        <span class="text-gray-400 font-bold text-xs text-center">to</span>
                        <div class="relative">
                            <i class="bi bi-calendar-check absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="date" name="end_date" value="<?php echo $endDate; ?>" 
                                   class="w-full pl-10 pr-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all text-sm text-gray-700 dark:text-gray-300">
                        </div>
                    </div>
                </div>
                <div class="md:col-span-4 lg:col-span-3 flex gap-3">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-xl font-black shadow-md transition-all flex items-center justify-center gap-2 text-[11px] uppercase tracking-widest">
                        <i class="bi bi-funnel-fill"></i> Analyze
                    </button>
                    <a href="index.php" class="bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 p-2.5 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all border border-gray-200 dark:border-gray-700" title="Reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
            <div class="mt-3 flex items-center gap-2 flex-wrap">
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Quick:</span>
                <a href="?start_date=<?php echo date('Y-m-d'); ?>&end_date=<?php echo date('Y-m-d'); ?>&tab=<?php echo $tab; ?>" class="text-[10px] font-bold text-gray-500 hover:text-red-600 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-lg transition-colors">Today</a>
                <a href="?start_date=<?php echo date('Y-m-d', strtotime('-7 days')); ?>&end_date=<?php echo date('Y-m-d'); ?>&tab=<?php echo $tab; ?>" class="text-[10px] font-bold text-gray-500 hover:text-red-600 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-lg transition-colors">Last 7 Days</a>
                <a href="?start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-d'); ?>&tab=<?php echo $tab; ?>" class="text-[10px] font-bold text-gray-500 hover:text-red-600 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-lg transition-colors">This Month</a>
                <a href="?start_date=<?php echo date('Y-m-01', strtotime('-1 month')); ?>&end_date=<?php echo date('Y-m-t', strtotime('-1 month')); ?>&tab=<?php echo $tab; ?>" class="text-[10px] font-bold text-gray-500 hover:text-red-600 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-lg transition-colors">Last Month</a>
                <a href="?start_date=<?php echo date('Y-01-01'); ?>&end_date=<?php echo date('Y-m-d'); ?>&tab=<?php echo $tab; ?>" class="text-[10px] font-bold text-gray-500 hover:text-red-600 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-lg transition-colors">Year to Date</a>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="flex gap-1 mb-6 bg-white dark:bg-[#1a1a1a] rounded-xl p-1 shadow-sm border border-gray-100 dark:border-gray-800 overflow-x-auto custom-scrollbar">
            <?php 
            $tabs = [
                'overview'    => ['icon' => 'bi-grid-1x2-fill', 'label' => 'Overview'],
                'voting'      => ['icon' => 'bi-fingerprint', 'label' => 'Voting Analysis'],
                'documents'   => ['icon' => 'bi-file-earmark-text', 'label' => 'Documents'],
                'legislators' => ['icon' => 'bi-people-fill', 'label' => 'Legislators'],
                'sessions'    => ['icon' => 'bi-calendar3', 'label' => 'Sessions'],
            ];
            foreach ($tabs as $key => $t): 
                $active = $tab === $key;
            ?>
                <a href="?start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>&tab=<?php echo $key; ?>" 
                   class="flex items-center gap-2 px-4 py-2.5 rounded-lg font-bold text-xs uppercase tracking-wider transition-all whitespace-nowrap <?php echo $active ? 'bg-red-600 text-white shadow-md' : 'text-gray-500 hover:text-red-600 hover:bg-gray-50 dark:hover:bg-gray-700'; ?>">
                    <i class="bi <?php echo $t['icon']; ?>"></i>
                    <?php echo $t['label']; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- ============================================================ -->
        <!-- OVERVIEW TAB -->
        <!-- ============================================================ -->
        <?php if ($tab === 'overview'): ?>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Sessions -->
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border-l-4 border-red-600 hover:shadow-lg transition-all dark:border-red-700">
                <div class="flex items-center justify-between mb-3">
                    <div class="bg-red-50 dark:bg-red-500/10 text-red-600 w-11 h-11 rounded-xl flex items-center justify-center">
                        <i class="bi bi-calendar3 text-xl"></i>
                    </div>
                    <?php $sc = $kpi['session_change']; ?>
                    <span class="text-[10px] font-bold <?php echo $sc >= 0 ? 'text-green-500 bg-green-50 dark:bg-green-500/10' : 'text-red-500 bg-red-50 dark:bg-red-500/10'; ?> px-2 py-0.5 rounded-full">
                        <?php echo ($sc >= 0 ? '+' : '') . $sc; ?>%
                    </span>
                </div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Sessions</p>
                <p class="text-3xl font-black text-gray-800 dark:text-white"><?php echo $kpi['total_sessions']; ?></p>
                <p class="text-[10px] text-gray-400 mt-1"><?php echo $kpi['completed_sessions']; ?> completed</p>
            </div>

            <!-- Votes -->
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border-l-4 border-indigo-600 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between mb-3">
                    <div class="bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 w-11 h-11 rounded-xl flex items-center justify-center">
                        <i class="bi bi-fingerprint text-xl"></i>
                    </div>
                    <?php $vc = $kpi['vote_change']; ?>
                    <span class="text-[10px] font-bold <?php echo $vc >= 0 ? 'text-green-500 bg-green-50 dark:bg-green-500/10' : 'text-red-500 bg-red-50 dark:bg-red-500/10'; ?> px-2 py-0.5 rounded-full">
                        <?php echo ($vc >= 0 ? '+' : '') . $vc; ?>%
                    </span>
                </div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Votes Cast</p>
                <p class="text-3xl font-black text-gray-800 dark:text-white"><?php echo $kpi['total_votes']; ?></p>
                <p class="text-[10px] text-gray-400 mt-1"><?php echo $kpi['unique_voters']; ?> unique voters</p>
            </div>

            <!-- Approved -->
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border-l-4 border-green-600 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between mb-3">
                    <div class="bg-green-50 dark:bg-green-500/10 text-green-600 w-11 h-11 rounded-xl flex items-center justify-center">
                        <i class="bi bi-file-check text-xl"></i>
                    </div>
                    <span class="text-[10px] font-bold text-green-500 bg-green-50 dark:bg-green-500/10 px-2 py-0.5 rounded-full">
                        <?php echo $kpi['approval_rate']; ?>% rate
                    </span>
                </div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Approved</p>
                <p class="text-3xl font-black text-green-600"><?php echo $kpi['documents_approved']; ?></p>
                <p class="text-[10px] text-gray-400 mt-1"><?php echo $kpi['documents_rejected']; ?> rejected</p>
            </div>

            <!-- Attendance -->
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border-l-4 border-amber-500 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between mb-3">
                    <div class="bg-amber-50 dark:bg-amber-500/10 text-amber-600 w-11 h-11 rounded-xl flex items-center justify-center">
                        <i class="bi bi-person-check text-xl"></i>
                    </div>
                    <span class="text-[10px] font-bold text-amber-500 bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 rounded-full">
                        <?php echo $attendance['attendance_rate']; ?>% rate
                    </span>
                </div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Attendance</p>
                <p class="text-3xl font-black text-gray-800 dark:text-white"><?php echo $attendance['total_present']; ?></p>
                <p class="text-[10px] text-gray-400 mt-1">of <?php echo $attendance['total_expected']; ?> expected</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Voting Trend -->
            <div class="lg:col-span-2 bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-base font-bold text-gray-800 dark:text-white">Voting Trends</h2>
                        <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest">Daily vote distribution over period</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-black text-gray-800 dark:text-white"><?php echo $kpi['total_votes']; ?></p>
                        <p class="text-[10px] text-gray-400">total votes</p>
                    </div>
                </div>
                <div class="h-[280px]">
                    <canvas id="votingTrendChart"></canvas>
                </div>
            </div>

            <!-- Vote Distribution -->
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-6 border border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-800 dark:text-white mb-1">Vote Breakdown</h2>
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest mb-6">Overall distribution</p>
                <div class="h-[200px] flex items-center justify-center">
                    <canvas id="voteDistributionChart"></canvas>
                </div>
                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                            <span class="text-xs font-bold text-gray-600 dark:text-gray-400">Approve</span>
                        </div>
                        <span class="text-xs font-black text-gray-800 dark:text-white"><?php echo $voteDistribution['approve']; ?> <span class="text-gray-400 font-medium">(<?php echo $voteDistribution['approve_pct']; ?>%)</span></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                            <span class="text-xs font-bold text-gray-600 dark:text-gray-400">Reject</span>
                        </div>
                        <span class="text-xs font-black text-gray-800 dark:text-white"><?php echo $voteDistribution['reject']; ?> <span class="text-gray-400 font-medium">(<?php echo $voteDistribution['reject_pct']; ?>%)</span></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-gray-400 rounded-full"></span>
                            <span class="text-xs font-bold text-gray-600 dark:text-gray-400">Abstain</span>
                        </div>
                        <span class="text-xs font-black text-gray-800 dark:text-white"><?php echo $voteDistribution['abstain']; ?> <span class="text-gray-400 font-medium">(<?php echo $voteDistribution['abstain_pct']; ?>%)</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Row: Monthly + Session Status -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Monthly Comparison -->
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-6 border border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-800 dark:text-white mb-1">Monthly Comparison</h2>
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest mb-6">12-month legislative activity</p>
                <div class="h-[280px]">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <!-- Session Status + Doc Type -->
            <div class="grid grid-rows-2 gap-6">
                <!-- Session Status -->
                <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Session Status</h2>
                    <div class="grid grid-cols-4 gap-3">
                        <?php
                        $statusConfig = [
                            'scheduled'   => ['icon' => 'bi-clock', 'color' => 'blue', 'label' => 'Scheduled'],
                            'in_progress' => ['icon' => 'bi-play-circle', 'color' => 'green', 'label' => 'Active'],
                            'completed'   => ['icon' => 'bi-check-circle', 'color' => 'gray', 'label' => 'Completed'],
                            'cancelled'   => ['icon' => 'bi-x-circle', 'color' => 'red', 'label' => 'Cancelled'],
                        ];
                        foreach ($statusConfig as $sKey => $cfg):
                            $count = $sessionStatuses[$sKey] ?? 0;
                        ?>
                            <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-xl">
                                <i class="bi <?php echo $cfg['icon']; ?> text-<?php echo $cfg['color']; ?>-500 text-lg mb-1 block"></i>
                                <p class="text-xl font-black text-gray-800 dark:text-white"><?php echo $count; ?></p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest"><?php echo $cfg['label']; ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Document Types -->
                <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Document Types</h2>
                    <?php if (empty($docTypes)): ?>
                        <p class="text-xs text-gray-400 text-center py-4">No documents in this period.</p>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php 
                            $maxDocCount = !empty($docTypes) ? max(array_column($docTypes, 'count')) : 1;
                            foreach ($docTypes as $dt):
                                $pct = $maxDocCount > 0 ? ($dt['count'] / $maxDocCount) * 100 : 0;
                            ?>
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase w-28 truncate"><?php echo str_replace('_', ' ', $dt['type']); ?></span>
                                    <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded-full h-2 overflow-hidden">
                                        <div class="bg-red-600 h-full rounded-full transition-all" style="width: <?php echo $pct; ?>%"></div>
                                    </div>
                                    <span class="text-xs font-black text-gray-800 dark:text-white w-8 text-right"><?php echo $dt['count']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Committee Overview -->
        <?php if (!empty($committees)): ?>
        <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-6 border border-gray-100 dark:border-gray-700 mb-6">
            <h2 class="text-base font-bold text-gray-800 dark:text-white mb-1">Committee Performance</h2>
            <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest mb-5">Activity breakdown by committee</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($committees as $comm): ?>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-red-50 dark:bg-red-500/10 rounded-lg flex items-center justify-center text-red-600 shrink-0">
                                <i class="bi bi-building text-lg"></i>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white line-clamp-1"><?php echo e($comm['name']); ?></h3>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div>
                                <p class="text-lg font-black text-gray-800 dark:text-white"><?php echo $comm['session_count']; ?></p>
                                <p class="text-[8px] font-bold text-gray-400 uppercase">Sessions</p>
                            </div>
                            <div>
                                <p class="text-lg font-black text-gray-800 dark:text-white"><?php echo $comm['document_count']; ?></p>
                                <p class="text-[8px] font-bold text-gray-400 uppercase">Documents</p>
                            </div>
                            <div>
                                <p class="text-lg font-black text-gray-800 dark:text-white"><?php echo $comm['vote_count']; ?></p>
                                <p class="text-[8px] font-bold text-gray-400 uppercase">Votes</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Activity Feed -->
        <?php if (!empty($recentActivity)): ?>
        <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-6 border border-gray-100 dark:border-gray-700">
            <h2 class="text-base font-bold text-gray-800 dark:text-white mb-1">Activity Feed</h2>
            <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest mb-5">Recent system activity in period</p>
            <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                <?php foreach ($recentActivity as $act): ?>
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 flex items-center justify-center shrink-0 mt-0.5">
                            <?php
                            $eventIcon = match(true) {
                                str_contains($act['event_type'] ?? '', 'vote')    => '<i class="bi bi-check2-square text-sm"></i>',
                                str_contains($act['event_type'] ?? '', 'session') => '<i class="bi bi-calendar3 text-sm"></i>',
                                str_contains($act['event_type'] ?? '', 'login')   => '<i class="bi bi-box-arrow-in-right text-sm"></i>',
                                str_contains($act['module'] ?? '', 'document')    => '<i class="bi bi-file-earmark text-sm"></i>',
                                default => '<i class="bi bi-activity text-sm"></i>'
                            };
                            echo $eventIcon;
                            ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-gray-800 dark:text-white line-clamp-1"><?php echo e($act['action']); ?></p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] text-gray-400"><?php echo e($act['user_name'] ?? 'System'); ?></span>
                                <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                                <span class="text-[10px] text-gray-400"><?php echo formatDateTime($act['created_at'], 'M d, h:i A'); ?></span>
                            </div>
                        </div>
                        <span class="text-[9px] font-bold text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full uppercase shrink-0"><?php echo e($act['module'] ?? ''); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ============================================================ -->
        <!-- VOTING ANALYSIS TAB -->
        <!-- ============================================================ -->
        <?php elseif ($tab === 'voting'): ?>

        <!-- Vote Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700 text-center">
                <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-500/10 rounded-xl flex items-center justify-center mx-auto mb-3 text-indigo-600">
                    <i class="bi bi-fingerprint text-2xl"></i>
                </div>
                <p class="text-3xl font-black text-gray-800 dark:text-white"><?php echo $voteDistribution['total']; ?></p>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Total Votes</p>
            </div>
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700 text-center">
                <div class="w-12 h-12 bg-green-50 dark:bg-green-500/10 rounded-xl flex items-center justify-center mx-auto mb-3 text-green-600">
                    <i class="bi bi-hand-thumbs-up text-2xl"></i>
                </div>
                <p class="text-3xl font-black text-green-600"><?php echo $voteDistribution['approve']; ?></p>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Approved (<?php echo $voteDistribution['approve_pct']; ?>%)</p>
            </div>
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700 text-center">
                <div class="w-12 h-12 bg-red-50 dark:bg-red-500/10 rounded-xl flex items-center justify-center mx-auto mb-3 text-red-600">
                    <i class="bi bi-hand-thumbs-down text-2xl"></i>
                </div>
                <p class="text-3xl font-black text-red-600"><?php echo $voteDistribution['reject']; ?></p>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Rejected (<?php echo $voteDistribution['reject_pct']; ?>%)</p>
            </div>
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border border-gray-100 dark:border-gray-700 text-center">
                <div class="w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center mx-auto mb-3 text-gray-500">
                    <i class="bi bi-slash-circle text-2xl"></i>
                </div>
                <p class="text-3xl font-black text-gray-600 dark:text-gray-300"><?php echo $voteDistribution['abstain']; ?></p>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Abstained (<?php echo $voteDistribution['abstain_pct']; ?>%)</p>
            </div>
        </div>

        <!-- Voting Trend (large) + Hourly Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-6 border border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-800 dark:text-white mb-1">Voting Timeline</h2>
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest mb-6">Daily approve / reject / abstain</p>
                <div class="h-[320px]">
                    <canvas id="votingTrendChart"></canvas>
                </div>
            </div>
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-6 border border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-800 dark:text-white mb-1">Peak Hours</h2>
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest mb-6">When votes are cast</p>
                <div class="h-[320px]">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Bar Chart -->
        <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-6 border border-gray-100 dark:border-gray-700 mb-6">
            <h2 class="text-base font-bold text-gray-800 dark:text-white mb-1">12-Month Voting Summary</h2>
            <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest mb-6">Approved vs Rejected per month</p>
            <div class="h-[300px]">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- DOCUMENTS TAB -->
        <!-- ============================================================ -->
        <?php elseif ($tab === 'documents'): ?>

        <!-- Document KPIs -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border-l-4 border-blue-600">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Total Documents</p>
                <p class="text-3xl font-black text-gray-800 dark:text-white"><?php echo $kpi['total_documents']; ?></p>
            </div>
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border-l-4 border-green-600">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Approved</p>
                <p class="text-3xl font-black text-green-600"><?php echo $kpi['documents_approved']; ?></p>
            </div>
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border-l-4 border-red-500">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Rejected</p>
                <p class="text-3xl font-black text-red-600"><?php echo $kpi['documents_rejected']; ?></p>
            </div>
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border-l-4 border-amber-500">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Approval Rate</p>
                <p class="text-3xl font-black text-amber-600"><?php echo $kpi['approval_rate']; ?>%</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Document Status Pipeline -->
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-6 border border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-800 dark:text-white mb-1">Document Pipeline</h2>
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest mb-5">Status distribution</p>
                <div class="h-[280px]">
                    <canvas id="docStatusChart"></canvas>
                </div>
            </div>

            <!-- Document Type Doughnut -->
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-6 border border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-800 dark:text-white mb-1">Type Distribution</h2>
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest mb-5">Category breakdown</p>
                <div class="h-[280px] flex items-center justify-center">
                    <canvas id="docTypeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Document Type Detailed Table -->
        <?php if (!empty($docTypes)): ?>
        <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700 mb-6">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-800 dark:text-white">Document Type Details</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Type</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Approved</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Rejected</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pending</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php foreach ($docTypes as $dt): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-6 py-4 text-sm font-bold text-gray-800 dark:text-white uppercase"><?php echo str_replace('_', ' ', $dt['type']); ?></td>
                                <td class="px-6 py-4 text-center text-sm font-black text-gray-800 dark:text-white"><?php echo $dt['count']; ?></td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-green-600"><?php echo $dt['approved']; ?></td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-red-600"><?php echo $dt['rejected']; ?></td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-amber-600"><?php echo $dt['pending']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Document Timeline -->
        <?php if (!empty($docTimeline)): ?>
        <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-6 border border-gray-100 dark:border-gray-700">
            <h2 class="text-base font-bold text-gray-800 dark:text-white mb-1">Recent Documents</h2>
            <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest mb-5">Latest legislative items</p>
            <div class="space-y-3">
                <?php foreach ($docTimeline as $doc): ?>
                    <div class="flex items-center gap-4 p-3 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                        <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 flex items-center justify-center shrink-0">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-gray-800 dark:text-white line-clamp-1"><?php echo e($doc['title']); ?></p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] text-gray-400"><?php echo e($doc['doc_number'] ?? ''); ?></span>
                                <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                                <span class="text-[10px] text-gray-400 uppercase"><?php echo str_replace('_', ' ', $doc['type']); ?></span>
                                <?php if (!empty($doc['committee_name'])): ?>
                                    <span class="text-gray-300 dark:text-gray-600">&bull;</span>
                                    <span class="text-[10px] text-gray-400"><?php echo e($doc['committee_name']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-[9px] font-bold rounded-lg <?php echo getStatusBadgeClass($doc['status']); ?> shrink-0"><?php echo ucfirst(str_replace('_', ' ', $doc['status'])); ?></span>
                        <?php if ($doc['resolution_days'] !== null): ?>
                            <span class="text-[10px] font-bold text-gray-400 shrink-0"><?php echo $doc['resolution_days']; ?>d</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ============================================================ -->
        <!-- LEGISLATORS TAB -->
        <!-- ============================================================ -->
        <?php elseif ($tab === 'legislators'): ?>

        <!-- Attendance Summary -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border-l-4 border-green-500">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Attendance Rate</p>
                <p class="text-3xl font-black text-green-600"><?php echo $attendance['attendance_rate']; ?>%</p>
            </div>
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border-l-4 border-blue-500">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Active Voters</p>
                <p class="text-3xl font-black text-gray-800 dark:text-white"><?php echo $kpi['unique_voters']; ?></p>
            </div>
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border-l-4 border-indigo-500">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Avg Votes/Session</p>
                <p class="text-3xl font-black text-gray-800 dark:text-white"><?php echo $kpi['avg_votes_per_session']; ?></p>
            </div>
            <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border-l-4 border-amber-500">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Total Votes</p>
                <p class="text-3xl font-black text-gray-800 dark:text-white"><?php echo $kpi['total_votes']; ?></p>
            </div>
        </div>

        <!-- Participation Matrix Table -->
        <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700 mb-6">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Legislator Participation Matrix</h2>
                    <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest">Individual voting metrics and engagement</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Legislator</th>
                            <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Position</th>
                            <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sessions</th>
                            <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Votes</th>
                            <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Approve</th>
                            <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Reject</th>
                            <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Abstain</th>
                            <th class="px-6 py-4 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Activity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php if (empty($legislators)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <i class="bi bi-people text-5xl text-gray-200 dark:text-gray-600 mb-3 block"></i>
                                    <p class="text-gray-400 font-bold text-sm">No legislator data for this period.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($legislators as $leg): 
                                $legPct = $maxLegVotes > 0 ? ($leg['total_votes'] / $maxLegVotes) * 100 : 0;
                            ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg flex items-center justify-center font-bold text-sm group-hover:bg-red-600 group-hover:text-white transition-all">
                                                <?php echo strtoupper(substr($leg['full_name'], 0, 1)); ?>
                                            </div>
                                            <span class="text-sm font-bold text-gray-800 dark:text-white"><?php echo e($leg['full_name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-[10px] font-bold text-gray-500 bg-gray-100 dark:bg-gray-700 dark:text-gray-400 px-2 py-1 rounded-full uppercase"><?php echo e($leg['position'] ?? 'Councilor'); ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-gray-800 dark:text-white"><?php echo $leg['sessions_participated']; ?></td>
                                    <td class="px-6 py-4 text-center text-sm font-black text-gray-800 dark:text-white"><?php echo $leg['total_votes']; ?></td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-green-600"><?php echo $leg['approve_count']; ?></td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-red-600"><?php echo $leg['reject_count']; ?></td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-gray-500"><?php echo $leg['abstain_count']; ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-3 min-w-[140px]">
                                            <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                                <div class="bg-gradient-to-r from-red-600 to-red-400 h-full rounded-full transition-all" style="width: <?php echo $legPct; ?>%"></div>
                                            </div>
                                            <span class="text-[10px] font-black text-red-600 w-8"><?php echo round($legPct); ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Attendance Details -->
        <?php if (!empty($attendance['per_user'])): ?>
        <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-800 dark:text-white">Attendance Record</h2>
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest">Session attendance breakdown per legislator</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Name</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Position</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Present</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Absent</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php foreach ($attendance['per_user'] as $att):
                            $attRate = $att['total_assigned'] > 0 ? round(($att['times_present'] / $att['total_assigned']) * 100) : 0;
                        ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-6 py-3 text-sm font-bold text-gray-800 dark:text-white"><?php echo e($att['full_name']); ?></td>
                                <td class="px-6 py-3 text-center text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase"><?php echo e($att['position'] ?? 'Councilor'); ?></td>
                                <td class="px-6 py-3 text-center text-sm font-bold text-green-600"><?php echo $att['times_present']; ?></td>
                                <td class="px-6 py-3 text-center text-sm font-bold text-red-500"><?php echo $att['times_absent']; ?></td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="w-16 bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-full rounded-full <?php echo $attRate >= 80 ? 'bg-green-500' : ($attRate >= 50 ? 'bg-amber-500' : 'bg-red-500'); ?>" style="width: <?php echo $attRate; ?>%"></div>
                                        </div>
                                        <span class="text-xs font-black <?php echo $attRate >= 80 ? 'text-green-600' : ($attRate >= 50 ? 'text-amber-600' : 'text-red-600'); ?>"><?php echo $attRate; ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- ============================================================ -->
        <!-- SESSIONS TAB -->
        <!-- ============================================================ -->
        <?php elseif ($tab === 'sessions'): ?>

        <!-- Session KPIs -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <?php
            $sessStatusConfig = [
                'scheduled'   => ['icon' => 'bi-clock', 'color' => 'blue', 'label' => 'Scheduled'],
                'in_progress' => ['icon' => 'bi-play-circle', 'color' => 'green', 'label' => 'Active'],
                'completed'   => ['icon' => 'bi-check-circle', 'color' => 'gray', 'label' => 'Completed'],
                'cancelled'   => ['icon' => 'bi-x-circle', 'color' => 'red', 'label' => 'Cancelled'],
            ];
            foreach ($sessStatusConfig as $sKey => $cfg): ?>
                <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md p-5 border-l-4 border-<?php echo $cfg['color']; ?>-500">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="bi <?php echo $cfg['icon']; ?> text-<?php echo $cfg['color']; ?>-500 text-xl"></i>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest"><?php echo $cfg['label']; ?></p>
                    </div>
                    <p class="text-3xl font-black text-gray-800 dark:text-white"><?php echo $sessionStatuses[$sKey] ?? 0; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sessions List -->
        <div class="bg-white dark:bg-[#2d2d2d] rounded-2xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-800 dark:text-white">Session Registry</h2>
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest">All sessions within selected period</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Session</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Date</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Committee</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Documents</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Present</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Votes</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php if (empty($sessions)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <i class="bi bi-calendar-x text-5xl text-gray-200 dark:text-gray-600 mb-3 block"></i>
                                    <p class="text-gray-400 font-bold text-sm">No sessions found in this period.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sessions as $sess): 
                                $duration = '';
                                if (!empty($sess['actual_start_time']) && !empty($sess['actual_end_time'])) {
                                    $diff = strtotime($sess['actual_end_time']) - strtotime($sess['actual_start_time']);
                                    if ($diff > 0) {
                                        $hours = floor($diff / 3600);
                                        $mins = floor(($diff % 3600) / 60);
                                        $duration = ($hours > 0 ? $hours . 'h ' : '') . $mins . 'm';
                                    }
                                }
                            ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-bold text-gray-800 dark:text-white line-clamp-1"><?php echo e($sess['title']); ?></p>
                                        <p class="text-[10px] text-gray-400"><?php echo e($sess['session_number'] ?? ''); ?></p>
                                    </td>
                                    <td class="px-5 py-4 text-center text-xs text-gray-600 dark:text-gray-400"><?php echo formatDate($sess['session_date']); ?></td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="px-2 py-1 text-[9px] font-bold rounded-lg <?php echo getStatusBadgeClass($sess['status']); ?>"><?php echo ucfirst(str_replace('_', ' ', $sess['status'])); ?></span>
                                    </td>
                                    <td class="px-5 py-4 text-center text-xs text-gray-600 dark:text-gray-400"><?php echo e($sess['committee_name'] ?? 'Plenary'); ?></td>
                                    <td class="px-5 py-4 text-center text-sm font-bold text-gray-800 dark:text-white"><?php echo $sess['doc_count']; ?></td>
                                    <td class="px-5 py-4 text-center text-sm font-bold text-gray-800 dark:text-white"><?php echo $sess['present_count']; ?></td>
                                    <td class="px-5 py-4 text-center text-sm font-bold text-gray-800 dark:text-white"><?php echo $sess['vote_count']; ?></td>
                                    <td class="px-5 py-4 text-center text-xs font-bold text-gray-500"><?php echo $duration ?: '—'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php endif; ?>

    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#9ca3af' : '#6b7280';
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { weight: 'bold', size: 10 }, padding: 15, color: textColor, usePointStyle: true }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 }, color: textColor } },
            y: { grid: { color: gridColor, drawBorder: false }, beginAtZero: true, ticks: { font: { size: 10 }, color: textColor } }
        }
    };

    // === VOTING TREND CHART ===
    const trendCtx = document.getElementById('votingTrendChart')?.getContext('2d');
    if (trendCtx) {
        const trendData = <?php echo json_encode($votingTrend); ?>;
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [
                    {
                        label: 'Approved',
                        data: trendData.map(d => parseInt(d.approved)),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        borderWidth: 2.5,
                        pointRadius: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Rejected',
                        data: trendData.map(d => parseInt(d.rejected)),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.08)',
                        borderWidth: 2.5,
                        pointRadius: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Abstained',
                        data: trendData.map(d => parseInt(d.abstained)),
                        borderColor: '#9ca3af',
                        backgroundColor: 'rgba(156, 163, 175, 0.08)',
                        borderWidth: 2.5,
                        pointRadius: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: defaultOptions
        });
    }

    // === VOTE DISTRIBUTION (Doughnut) ===
    const distCtx = document.getElementById('voteDistributionChart')?.getContext('2d');
    if (distCtx) {
        new Chart(distCtx, {
            type: 'doughnut',
            data: {
                labels: ['Approve', 'Reject', 'Abstain'],
                datasets: [{
                    data: [<?php echo $voteDistribution['approve']; ?>, <?php echo $voteDistribution['reject']; ?>, <?php echo $voteDistribution['abstain']; ?>],
                    backgroundColor: ['#10b981', '#ef4444', '#9ca3af'],
                    borderWidth: 6,
                    borderColor: isDark ? '#2d2d2d' : '#fff',
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // === MONTHLY COMPARISON CHART ===
    const monthlyCtx = document.getElementById('monthlyChart')?.getContext('2d');
    if (monthlyCtx) {
        const monthlyData = <?php echo json_encode($monthlyComparison); ?>;
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(d => d.short),
                datasets: [
                    {
                        label: 'Sessions',
                        data: monthlyData.map(d => d.sessions),
                        backgroundColor: isDark ? 'rgba(239, 68, 68, 0.7)' : '#ef4444',
                        borderRadius: 6,
                        barPercentage: 0.6
                    },
                    {
                        label: 'Votes',
                        data: monthlyData.map(d => d.votes),
                        backgroundColor: isDark ? 'rgba(99, 102, 241, 0.7)' : '#6366f1',
                        borderRadius: 6,
                        barPercentage: 0.6
                    }
                ]
            },
            options: defaultOptions
        });
    }

    // === HOURLY CHART ===
    const hourlyCtx = document.getElementById('hourlyChart')?.getContext('2d');
    if (hourlyCtx) {
        const hourlyData = <?php echo json_encode($votingByHour); ?>;
        const hours = Array.from({length: 24}, (_, i) => i);
        const hourCounts = hours.map(h => {
            const found = hourlyData.find(d => parseInt(d.hour) === h);
            return found ? parseInt(found.count) : 0;
        });
        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: hours.map(h => h.toString().padStart(2, '0') + ':00'),
                datasets: [{
                    label: 'Votes',
                    data: hourCounts,
                    backgroundColor: isDark ? 'rgba(239, 68, 68, 0.5)' : 'rgba(239, 68, 68, 0.7)',
                    borderRadius: 4,
                    barPercentage: 0.7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 8 }, color: textColor, maxRotation: 90 } },
                    y: { grid: { color: gridColor, drawBorder: false }, beginAtZero: true, ticks: { font: { size: 9 }, color: textColor } }
                }
            }
        });
    }

    // === DOCUMENT STATUS CHART ===
    const docStatusCtx = document.getElementById('docStatusChart')?.getContext('2d');
    if (docStatusCtx) {
        const docStatuses = <?php echo json_encode($docStatuses); ?>;
        const statusLabels = Object.keys(docStatuses).map(s => s.replace(/_/g, ' ').toUpperCase());
        const statusValues = Object.values(docStatuses);
        const statusColors = ['#6b7280', '#3b82f6', '#8b5cf6', '#f59e0b', '#10b981', '#ef4444', '#9ca3af'];
        new Chart(docStatusCtx, {
            type: 'bar',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Documents',
                    data: statusValues,
                    backgroundColor: statusColors.slice(0, statusValues.length),
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { color: gridColor, drawBorder: false }, beginAtZero: true, ticks: { font: { size: 10 }, color: textColor } },
                    y: { grid: { display: false }, ticks: { font: { size: 9, weight: 'bold' }, color: textColor } }
                }
            }
        });
    }

    // === DOCUMENT TYPE DOUGHNUT ===
    const docTypeCtx = document.getElementById('docTypeChart')?.getContext('2d');
    if (docTypeCtx) {
        const typesData = <?php echo json_encode($docTypes); ?>;
        if (typesData.length > 0) {
            new Chart(docTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: typesData.map(d => d.type.replace(/_/g, ' ').toUpperCase()),
                    datasets: [{
                        data: typesData.map(d => d.count),
                        backgroundColor: ['#ef4444', '#111827', '#6366f1', '#10b981', '#f59e0b', '#ec4899', '#06b6d4'],
                        borderWidth: 6,
                        borderColor: isDark ? '#2d2d2d' : '#fff',
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { weight: 'bold', size: 9 }, padding: 12, usePointStyle: true, color: textColor }
                        }
                    }
                }
            });
        }
    }
});

// Export function
function exportReport() {
    const params = new URLSearchParams(window.location.search);
    const start = params.get('start_date') || '<?php echo $startDate; ?>';
    const end = params.get('end_date') || '<?php echo $endDate; ?>';
    
    const kpi = <?php echo json_encode($kpi); ?>;
    const legislators = <?php echo json_encode($legislators); ?>;
    const sessions = <?php echo json_encode($sessions); ?>;
    
    let csv = 'VDM System - Reports & Analytics Export\n';
    csv += 'Period: ' + start + ' to ' + end + '\n\n';
    csv += 'KEY PERFORMANCE INDICATORS\n';
    csv += 'Metric,Value\n';
    csv += 'Total Sessions,' + kpi.total_sessions + '\n';
    csv += 'Completed Sessions,' + kpi.completed_sessions + '\n';
    csv += 'Total Votes,' + kpi.total_votes + '\n';
    csv += 'Total Documents,' + kpi.total_documents + '\n';
    csv += 'Documents Approved,' + kpi.documents_approved + '\n';
    csv += 'Documents Rejected,' + kpi.documents_rejected + '\n';
    csv += 'Approval Rate,' + kpi.approval_rate + '%\n';
    csv += 'Unique Voters,' + kpi.unique_voters + '\n';
    csv += 'Avg Votes/Session,' + kpi.avg_votes_per_session + '\n\n';
    
    csv += 'LEGISLATOR PARTICIPATION\n';
    csv += 'Name,Position,Sessions,Total Votes,Approve,Reject,Abstain\n';
    legislators.forEach(l => {
        csv += '"' + l.full_name + '","' + (l.position || 'Councilor') + '",' + l.sessions_participated + ',' + l.total_votes + ',' + l.approve_count + ',' + l.reject_count + ',' + l.abstain_count + '\n';
    });

    csv += '\nSESSION REGISTRY\n';
    csv += 'Title,Session Number,Date,Status,Documents,Present,Votes\n';
    sessions.forEach(s => {
        csv += '"' + s.title + '","' + (s.session_number || '') + '","' + s.session_date + '","' + s.status + '",' + s.doc_count + ',' + s.present_count + ',' + s.vote_count + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'VDM_Report_' + start + '_to_' + end + '.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 20px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #404040; }
    
    @media print {
        .vdm-welcome-banner { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        nav, .sidebar, button, a[href="index.php"] { display: none !important; }
        main { padding: 0 !important; }
        .shadow-md, .shadow-lg, .shadow-xl { box-shadow: none !important; }
    }
</style>
