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
                                    
                                    <button onclick="viewAnalytics(<?php echo $s['id']; ?>)" class="block w-full text-center bg-slate-900 dark:bg-slate-700 text-white py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-red-600 transition-all shadow-lg hover:shadow-red-500/20">
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
            <div class="animate-fade-in">
                <!-- Dashboard Header -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                    <div>
                        <a href="results.php" class="text-red-500 hover:text-red-600 font-black text-xs mb-4 inline-flex items-center group uppercase tracking-widest">
                            <i class="bi bi-arrow-left-circle mr-2 transition-transform group-hover:-translate-x-1 text-lg"></i>
                            Back to Analytics List
                        </a>
                        <h1 class="text-3xl md:text-4xl font-black vdm-heading tracking-tight uppercase leading-none mb-2"><?php echo e($session['title']); ?></h1>
                        <div class="flex items-center gap-3">
                            <span class="vdm-text-muted font-bold tracking-widest uppercase text-xs">Analytics Dashboard</span>
                            <span class="w-1.5 h-1.5 bg-slate-300 dark:bg-slate-700 rounded-full"></span>
                            <span class="text-red-500 font-bold uppercase text-xs tracking-widest"><?php echo e($session['session_number']); ?></span>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="window.print()" class="vdm-card border shadow-sm px-6 py-3 rounded-xl font-black vdm-text-muted text-[10px] uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all flex items-center gap-2">
                            <i class="bi bi-printer text-sm"></i> Print Report
                        </button>
                        <button class="bg-slate-900 dark:bg-slate-700 text-white px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-red-600 transition-all shadow-lg flex items-center gap-2">
                            <i class="bi bi-download text-sm"></i> Export Data
                        </button>
                    </div>
                </div>

                <!-- Statistics Row -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10">
                    <div class="vdm-card p-6 rounded-2xl shadow-xl border-none relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-red-500/5 rounded-full -mr-8 -mt-8"></div>
                        <p class="text-[10px] vdm-text-muted font-black uppercase tracking-widest mb-3 opacity-60">Total Items</p>
                        <p class="text-4xl font-black vdm-heading tracking-tighter mb-1"><?php echo $summary['total_docs']; ?></p>
                        <p class="text-[10px] vdm-text-muted font-bold uppercase tracking-tighter">Items Processed</p>
                    </div>
                    <div class="vdm-card p-6 rounded-2xl shadow-xl border-none relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-green-500/5 rounded-full -mr-8 -mt-8"></div>
                        <p class="text-[10px] text-green-500/60 font-black uppercase tracking-widest mb-3">Items Passed</p>
                        <p class="text-4xl font-black text-green-500 tracking-tighter mb-1"><?php echo $summary['passed_docs']; ?></p>
                        <div class="inline-flex items-center px-2 py-0.5 bg-green-500/10 text-green-500 rounded text-[10px] font-black uppercase tracking-tighter">
                            <?php echo $summary['total_docs'] > 0 ? round(($summary['passed_docs'] / $summary['total_docs']) * 100) : 0; ?>% Rate
                        </div>
                    </div>
                    <div class="vdm-card p-6 rounded-2xl shadow-xl border-none relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-red-500/5 rounded-full -mr-8 -mt-8"></div>
                        <p class="text-[10px] text-red-500/60 font-black uppercase tracking-widest mb-3">Items Failed</p>
                        <p class="text-4xl font-black text-red-500 tracking-tighter mb-1"><?php echo $summary['failed_docs']; ?></p>
                        <p class="text-[10px] vdm-text-muted font-bold uppercase tracking-tighter">Action Required</p>
                    </div>
                    <div class="vdm-card p-6 rounded-2xl shadow-xl border-none relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-slate-500/5 rounded-full -mr-8 -mt-8"></div>
                        <p class="text-[10px] vdm-text-muted font-black uppercase tracking-widest mb-3 opacity-60">Total Votes</p>
                        <p class="text-4xl font-black vdm-heading tracking-tighter mb-1"><?php echo $summary['total_approve'] + $summary['total_reject'] + $summary['total_abstain']; ?></p>
                        <p class="text-[10px] vdm-text-muted font-bold uppercase tracking-tighter">By <?php echo $session['attendee_count']; ?> Members</p>
                    </div>
                </div>

                <!-- Visual Analytics Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
                    <!-- Global Voting Distribution Chart -->
                    <div class="vdm-card p-8 rounded-3xl shadow-xl border-none flex flex-col">
                        <h3 class="font-black vdm-heading text-sm mb-10 flex items-center gap-3 uppercase tracking-widest">
                            <i class="bi bi-pie-chart-fill text-red-500 text-lg"></i>
                            Session Distribution
                        </h3>
                        <div class="max-w-[220px] mx-auto mb-10 relative">
                            <canvas id="votingChart"></canvas>
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <span class="text-3xl font-black vdm-heading"><?php echo $summary['total_docs'] > 0 ? round(($summary['passed_docs'] / $summary['total_docs']) * 100) : 0; ?><span class="text-xs">%</span></span>
                            </div>
                        </div>
                        <div class="space-y-3 mt-auto">
                            <div class="flex items-center justify-between p-4 bg-green-500/5 rounded-2xl border border-green-500/10">
                                <span class="flex items-center text-[10px] font-black uppercase tracking-widest text-green-600"><span class="w-3 h-3 bg-green-500 rounded-full mr-3 shadow-md shadow-green-500/20"></span> Approvals</span>
                                <span class="font-black vdm-heading"><?php echo $summary['total_approve']; ?></span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-red-500/5 rounded-2xl border border-red-500/10">
                                <span class="flex items-center text-[10px] font-black uppercase tracking-widest text-red-600"><span class="w-3 h-3 bg-red-500 rounded-full mr-3 shadow-md shadow-red-500/20"></span> Rejections</span>
                                <span class="font-black vdm-heading"><?php echo $summary['total_reject']; ?></span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-slate-500/5 rounded-2xl border border-slate-500/10">
                                <span class="flex items-center text-[10px] font-black uppercase tracking-widest text-slate-500"><span class="w-3 h-3 bg-slate-400 rounded-full mr-3 shadow-md shadow-slate-400/20"></span> Abstentions</span>
                                <span class="font-black vdm-heading"><?php echo $summary['total_abstain']; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Table of Decisions -->
                    <div class="lg:col-span-2 vdm-card rounded-3xl shadow-xl border-none flex flex-col overflow-hidden">
                        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/30">
                            <h3 class="font-black vdm-heading text-sm uppercase tracking-widest flex items-center gap-3">
                                <i class="bi bi-list-columns-reverse text-red-500 text-lg"></i>
                                Itemized Outcomes
                            </h3>
                            <span class="text-[9px] font-black vdm-text-muted bg-white dark:bg-slate-700 px-3 py-1.5 rounded-full border border-slate-200 dark:border-slate-600 uppercase tracking-widest shadow-sm">Legislative Items</span>
                        </div>
                        <div class="overflow-y-auto flex-1 custom-scrollbar max-h-[500px]">
                            <table class="w-full">
                                <thead class="text-[9px] vdm-text-muted uppercase font-black tracking-widest bg-slate-50/80 dark:bg-slate-800/80 backdrop-blur-md sticky top-0 z-10 border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th class="px-8 py-5 text-left">Document / Legislative Item</th>
                                        <th class="px-8 py-5 text-center">Final Result</th>
                                        <th class="px-8 py-5 text-center">App</th>
                                        <th class="px-8 py-5 text-center">Rej</th>
                                        <th class="px-8 py-5 text-center">Abs</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    <?php foreach ($documents as $doc): ?>
                                        <tr class="hover:bg-red-500/[0.02] transition-colors group">
                                            <td class="px-8 py-5">
                                                <div class="max-w-md">
                                                    <p class="text-[9px] font-black text-red-500 mb-1 tracking-widest uppercase opacity-70"><?php echo e($doc['doc_number']); ?></p>
                                                    <h4 class="text-xs font-black vdm-heading uppercase group-hover:text-red-500 transition-colors tracking-tight leading-tight"><?php echo e($doc['title']); ?></h4>
                                                </div>
                                            </td>
                                            <td class="px-8 py-5 text-center">
                                                <?php
                                                $resClass = $doc['voting_status'] === 'passed' ? 'bg-green-500/10 text-green-500' : ($doc['voting_status'] === 'failed' ? 'bg-red-500/10 text-red-500' : 'bg-slate-100 dark:bg-slate-800 text-slate-500');
                                                ?>
                                                <span class="px-3 py-1 text-[9px] font-black rounded uppercase tracking-tighter shadow-sm border border-current <?php echo $resClass; ?>">
                                                    <?php echo $doc['voting_status']; ?>
                                                </span>
                                            </td>
                                            <td class="px-8 py-5 text-center text-xs font-black text-green-500"><?php echo $doc['approve_count']; ?></td>
                                            <td class="px-8 py-5 text-center text-xs font-black text-red-500"><?php echo $doc['reject_count']; ?></td>
                                            <td class="px-8 py-5 text-center text-xs font-black text-slate-400 dark:text-slate-600"><?php echo $doc['abstain_count']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Individual Votes Logs (Auditable) -->
                <?php if (hasRole(['admin', 'secretary'])): ?>
                    <div class="bg-slate-900 rounded-3xl shadow-2xl overflow-hidden mb-12 text-white p-10 border-4 border-slate-800">
                        <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-6 border-b border-slate-800 pb-8">
                            <div>
                                <h3 class="text-2xl font-black mb-1 flex items-center gap-3 uppercase tracking-tighter">
                                    <i class="bi bi-shield-lock-fill text-red-500"></i>
                                    Individual Audit Log
                                </h3>
                                <p class="text-slate-500 text-sm font-medium">Review specific decisions made by each legislator during this session.</p>
                            </div>
                            <div class="bg-slate-800 px-6 py-3 rounded-2xl border border-slate-700 shadow-inner">
                                <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest italic flex items-center">
                                    <i class="bi bi-info-circle mr-2 text-red-500"></i> Authorized Access Required
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
                                <div class="col-span-full py-20 text-center text-slate-600">
                                    <i class="bi bi-database-exclamation text-5xl mb-4 block"></i>
                                    <p class="font-black uppercase tracking-widest text-xs">No records found for this session</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($allVotes as $v): ?>
                                    <div class="bg-slate-800/50 rounded-2xl p-6 border-2 border-slate-800 hover:border-red-500 transition-all group shadow-lg">
                                        <div class="flex items-center justify-between mb-5">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 bg-slate-700 text-red-500 rounded-2xl flex items-center justify-center font-black text-xl shadow-inner border border-slate-600">
                                                    <?php echo strtoupper(substr($v['voter_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-black text-white uppercase tracking-tighter mb-0.5"><?php echo e($v['voter_name']); ?></p>
                                                    <p class="text-[9px] text-slate-500 uppercase font-black tracking-widest opacity-80"><?php echo e($v['position']); ?></p>
                                                </div>
                                            </div>
                                            <?php
                                            $vClass = $v['vote'] === 'approve' ? 'bg-green-500/10 text-green-400 border-green-500/20' : ($v['vote'] === 'reject' ? 'bg-red-500/10 text-red-400 border-red-500/20' : 'bg-slate-700 text-slate-400 border-slate-600');
                                            ?>
                                            <span class="px-2.5 py-1 text-[8px] font-black rounded uppercase border-2 tracking-widest <?php echo $vClass; ?>">
                                                <?php echo $v['vote']; ?>
                                            </span>
                                        </div>
                                        <div class="border-t border-slate-700 pt-4">
                                            <p class="text-[9px] text-slate-500 uppercase font-black mb-1.5 opacity-60 tracking-widest">Document Item</p>
                                            <p class="text-[11px] font-black text-slate-300 uppercase leading-snug tracking-tighter mb-4 line-clamp-1 group-hover:text-white transition-colors"><?php echo e($v['doc_title']); ?></p>
                                            <div class="flex items-center justify-between">
                                                <span class="text-[8px] bg-slate-900/50 px-2 py-1 rounded text-slate-500 font-bold tracking-tighter">ID: #<?php echo $v['id']; ?></span>
                                                <p class="text-[9px] text-slate-600 font-black uppercase tracking-tighter"><?php echo date('M d, H:i', strtotime($v['cast_at'])); ?></p>
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
