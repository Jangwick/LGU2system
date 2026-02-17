<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../controllers/VotingController.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo '<div class="p-6 text-center text-red-500 font-bold">Unauthorized access.</div>';
    exit;
}

$sessionId = $_GET['id'] ?? null;
if (!$sessionId) {
    echo '<div class="p-6 text-center text-red-500 font-bold">Session ID missing.</div>';
    exit;
}

$voting = new VotingController();
$session = $voting->getSession($sessionId);

if (!$session) {
    echo '<div class="p-6 text-center text-red-500 font-bold">Session not found.</div>';
    exit;
}

$summary = $voting->getSessionResultsSummary($sessionId);
$documents = $voting->getSessionDocuments($sessionId);
$userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));

// Role helper
function hasRoleLocal($roles) {
    global $userRole;
    return in_array($userRole, (array)$roles);
}
?>

<!-- Session Results Dashboard Content -->
<div class="animate-fade-in p-8 md:p-12 space-y-10" data-session-num="<?php echo e($session['session_number']); ?>">
    
    <!-- Hero Info Row (Replaces the big header card for a cleaner look inside modal) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 pb-10 border-b border-slate-200 dark:border-slate-800">
        <div>
            <h1 class="text-4xl md:text-6xl font-black vdm-heading tracking-tighter uppercase leading-[0.9] mb-4"><?php echo e($session['title']); ?></h1>
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2 px-3 py-1 bg-slate-900 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">
                    <i class="bi bi-calendar3"></i>
                    <?php echo formatDate($session['session_date']); ?>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 bg-red-600 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">
                    <i class="bi bi-clock-history"></i>
                    <?php echo $session['start_time']; ?>
                </div>
                <span class="w-1.5 h-1.5 bg-slate-300 dark:bg-slate-700 rounded-full"></span>
                <span class="vdm-text-muted font-black uppercase tracking-[0.2em] text-[10px] opacity-60">Authorized Analytics Export v1.0</span>
            </div>
        </div>
        <div class="flex gap-3">
             <button onclick="window.print()" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm px-8 py-4 rounded-2xl font-black vdm-text-muted text-[10px] uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-700 transition-all flex items-center gap-3">
                <i class="bi bi-printer-fill text-lg text-red-600"></i> Print Full Report
            </button>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="vdm-card p-8 rounded-[2.5rem] shadow-xl border-none relative overflow-hidden group hover:shadow-2xl transition-all duration-500 bg-white dark:bg-slate-900">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-500/5 rounded-full blur-2xl group-hover:bg-red-500/10 transition-all"></div>
            <div class="relative">
                <p class="text-[10px] vdm-text-muted font-black uppercase tracking-widest mb-4 opacity-50 flex items-center gap-2">
                    <i class="bi bi-layers-fill text-red-500"></i> Total Items
                </p>
                <p class="text-6xl font-black vdm-heading tracking-tighter mb-2"><?php echo $summary['total_docs']; ?></p>
                <p class="text-[10px] vdm-text-muted font-bold uppercase tracking-tight opacity-70 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span> Items Processed
                </p>
            </div>
        </div>
        <div class="vdm-card p-8 rounded-[2.5rem] shadow-xl border-none relative overflow-hidden group hover:shadow-2xl transition-all duration-500 bg-white dark:bg-slate-900">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-green-500/5 rounded-full blur-2xl group-hover:bg-green-500/10 transition-all"></div>
            <div class="relative">
                <p class="text-[10px] text-green-600 dark:text-green-500/60 font-black uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="bi bi-shield-check"></i> Items Passed
                </p>
                <p class="text-6xl font-black text-green-600 dark:text-green-500 tracking-tighter mb-2"><?php echo $summary['passed_docs']; ?></p>
                <div class="inline-flex items-center px-3 py-1 bg-green-500/10 text-green-600 rounded-lg text-[10px] font-black uppercase tracking-tighter shadow-sm border border-green-500/20">
                    <?php echo $summary['total_docs'] > 0 ? round(($summary['passed_docs'] / $summary['total_docs']) * 100) : 0; ?>% Approval
                </div>
            </div>
        </div>
        <div class="vdm-card p-8 rounded-[2.5rem] shadow-xl border-none relative overflow-hidden group hover:shadow-2xl transition-all duration-500 bg-white dark:bg-slate-900">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-orange-500/5 rounded-full blur-2xl group-hover:bg-orange-500/10 transition-all"></div>
            <div class="relative">
                <p class="text-[10px] text-orange-600 dark:text-orange-500/60 font-black uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="bi bi-shield-x"></i> Items Failed
                </p>
                <p class="text-6xl font-black text-orange-600 dark:text-orange-500 tracking-tighter mb-2"><?php echo $summary['failed_docs']; ?></p>
                <p class="text-[10px] vdm-text-muted font-bold uppercase tracking-tight opacity-70 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-orange-500 rounded-full"></span> Action Required
                </p>
            </div>
        </div>
        <div class="vdm-card p-8 rounded-[2.5rem] shadow-xl border-none relative overflow-hidden group hover:shadow-2xl transition-all duration-500 bg-white dark:bg-slate-900">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-slate-500/5 rounded-full blur-2xl group-hover:bg-slate-500/10 transition-all"></div>
            <div class="relative">
                <p class="text-[10px] vdm-text-muted font-black uppercase tracking-widest mb-4 opacity-50 flex items-center gap-2">
                    <i class="bi bi-people-fill text-slate-500"></i> Total Votes
                </p>
                <p class="text-6xl font-black vdm-heading tracking-tighter mb-2"><?php echo $summary['total_approve'] + $summary['total_reject'] + $summary['total_abstain']; ?></p>
                <p class="text-[10px] vdm-text-muted font-bold uppercase tracking-tight opacity-70 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-slate-500 rounded-full"></span> By <?php echo $session['attendee_count'] ?? 0; ?> Members
                </p>
            </div>
        </div>
    </div>

    <!-- Visual Analytics Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Global Voting Distribution Chart -->
        <div class="vdm-card p-10 rounded-[2.5rem] shadow-xl border-none flex flex-col bg-white dark:bg-slate-900">
            <div class="flex items-center gap-3 mb-10 pb-6 border-b border-slate-50 dark:border-slate-800">
                <div class="w-10 h-10 bg-red-500/10 text-red-600 rounded-xl flex items-center justify-center">
                    <i class="bi bi-pie-chart-fill"></i>
                </div>
                <h3 class="font-black vdm-heading text-sm uppercase tracking-widest">Session Distribution</h3>
            </div>
            
            <div class="flex-1 flex flex-col items-center justify-center">
                <div class="max-w-[240px] w-full mx-auto relative mb-12 transform hover:scale-105 transition-transform duration-500">
                    <canvas id="modalVotingChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-4xl font-black vdm-heading leading-none"><?php echo $summary['total_docs'] > 0 ? round(($summary['passed_docs'] / $summary['total_docs']) * 100) : 0; ?><span class="text-sm border-b-2 border-red-500 ml-0.5">%</span></span>
                        <span class="text-[8px] vdm-text-muted font-black uppercase tracking-widest mt-1 opacity-50">Overall Success</span>
                    </div>
                </div>

                <div class="w-full space-y-3">
                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800 group hover:border-green-500/30 transition-all">
                        <span class="flex items-center text-[10px] font-black uppercase tracking-[0.15em] vdm-text-muted"><span class="w-2 h-2 bg-green-500 rounded-full mr-3 shadow-sm"></span> Approvals</span>
                        <span class="font-black vdm-heading text-green-500"><?php echo $summary['total_approve']; ?></span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800 group hover:border-red-500/30 transition-all">
                        <span class="flex items-center text-[10px] font-black uppercase tracking-[0.15em] vdm-text-muted"><span class="w-2 h-2 bg-red-500 rounded-full mr-3 shadow-sm"></span> Rejections</span>
                        <span class="font-black vdm-heading text-red-500"><?php echo $summary['total_reject']; ?></span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800 group hover:border-slate-500/30 transition-all">
                        <span class="flex items-center text-[10px] font-black uppercase tracking-[0.15em] vdm-text-muted"><span class="w-2 h-2 bg-slate-400 rounded-full mr-3 shadow-sm"></span> Abstentions</span>
                        <span class="font-black vdm-heading vdm-text-muted opacity-60"><?php echo $summary['total_abstain']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Table of Decisions -->
        <div class="lg:col-span-2 vdm-card rounded-[2.5rem] shadow-xl border-none flex flex-col overflow-hidden bg-white dark:bg-slate-900">
            <div class="p-8 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between bg-slate-50/30 dark:bg-slate-900/30 backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-500/10 text-blue-600 rounded-xl flex items-center justify-center">
                        <i class="bi bi-list-columns-reverse"></i>
                    </div>
                    <h3 class="font-black vdm-heading text-sm uppercase tracking-widest">Itemized Outcomes</h3>
                </div>
                <div class="flex gap-2">
                    <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-[8px] font-black vdm-text-muted rounded-lg uppercase tracking-widest border border-slate-200 dark:border-slate-700 shadow-sm">Total: <?php echo count($documents); ?></span>
                </div>
            </div>
            
            <div class="overflow-y-auto flex-1 custom-scrollbar max-h-[550px]">
                <table class="w-full">
                    <thead class="text-[9px] vdm-text-muted uppercase font-black tracking-widest bg-slate-50/50 dark:bg-slate-800/50 backdrop-blur-md sticky top-0 z-10 border-b border-slate-50 dark:border-slate-800">
                        <tr>
                            <th class="px-10 py-6 text-left">Document & Legislative Identity</th>
                            <th class="px-6 py-6 text-center">Protocol Result</th>
                            <th class="px-6 py-6 text-center">App</th>
                            <th class="px-6 py-6 text-center">Rej</th>
                            <th class="px-6 py-6 text-center">Abs</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        <?php if (empty($documents)): ?>
                            <tr>
                                <td colspan="5" class="py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-4 border border-slate-100 dark:border-slate-700">
                                            <i class="bi bi-folder-x text-2xl text-slate-300"></i>
                                        </div>
                                        <p class="text-xs font-black vdm-text-muted uppercase tracking-widest opacity-40">No items registered for this session</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($documents as $doc): ?>
                                <tr class="hover:bg-red-500/[0.02] dark:hover:bg-red-500/[0.04] transition-all group cursor-default">
                                    <td class="px-10 py-6">
                                        <div class="max-w-md">
                                            <div class="flex items-center gap-2 mb-1.5">
                                                <span class="text-[8px] font-black text-red-500 tracking-widest uppercase px-2 py-0.5 bg-red-500/5 rounded"><?php echo e($doc['doc_number']); ?></span>
                                                <span class="text-[8px] font-black vdm-text-muted tracking-widest uppercase opacity-30">•</span>
                                                <span class="text-[8px] font-black vdm-text-muted tracking-widest uppercase opacity-40"><?php echo e($doc['type']); ?></span>
                                            </div>
                                            <h4 class="text-sm font-black vdm-heading uppercase group-hover:text-red-600 transition-colors tracking-tight leading-tight"><?php echo e($doc['title']); ?></h4>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 text-center">
                                        <?php
                                        $status = $doc['voting_status'] ?? 'pending';
                                        $resClass = $status === 'passed' ? 'bg-green-500/10 text-green-500 border-green-500/20' : ($status === 'failed' ? 'bg-red-500/10 text-red-500 border-red-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700');
                                        ?>
                                        <span class="px-4 py-1.5 text-[9px] font-black rounded-lg uppercase tracking-tighter shadow-sm border <?php echo $resClass; ?>">
                                            <?php echo $status; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-6 text-center">
                                        <div class="text-xs font-black text-green-600 dark:text-green-500"><?php echo $doc['approve_count']; ?></div>
                                        <div class="text-[8px] font-bold vdm-text-muted uppercase opacity-30 mt-0.5">App</div>
                                    </td>
                                    <td class="px-6 py-6 text-center">
                                        <div class="text-xs font-black text-red-600 dark:text-red-500"><?php echo $doc['reject_count']; ?></div>
                                        <div class="text-[8px] font-bold vdm-text-muted uppercase opacity-30 mt-0.5">Rej</div>
                                    </td>
                                    <td class="px-6 py-6 text-center">
                                        <div class="text-xs font-black text-slate-400 dark:text-slate-600"><?php echo $doc['abstain_count']; ?></div>
                                        <div class="text-[8px] font-bold vdm-text-muted uppercase opacity-30 mt-0.5">Abs</div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Individual Votes Logs (Auditable) -->
    <?php if (hasRoleLocal(['admin', 'secretary'])): ?>
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 custom-scrollbar max-h-[500px] overflow-y-auto pr-4">
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

    <!-- Script to initialize the chart inside the modal -->
    <script>
    (function() {
        const ctx = document.getElementById('modalVotingChart').getContext('2d');
        if (window.modalChartInstance) window.modalChartInstance.destroy();
        
        window.modalChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Approve', 'Reject', 'Abstain'],
                datasets: [{
                    data: [<?php echo $summary['total_approve']; ?>, <?php echo $summary['total_reject']; ?>, <?php echo $summary['total_abstain']; ?>],
                    backgroundColor: ['#10b981', '#ef4444', '#94a3b8'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                cutout: '85%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' votes';
                            }
                        }
                    }
                }
            }
        });
    })();
    </script>
</div>
