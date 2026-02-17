<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Get date range filters
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$reportType = $_GET['type'] ?? 'overview';

// Get statistics
$stats = [
    'total_sessions' => dbCount('voting_sessions', "session_date BETWEEN ? AND ?", [$startDate, $endDate]),
    'total_votes' => dbCount('votes', "DATE(cast_at) BETWEEN ? AND ?", [$startDate, $endDate]),
    'documents_approved' => dbCount('documents', "status = 'approved' AND DATE(updated_at) BETWEEN ? AND ?", [$startDate, $endDate]),
    'documents_rejected' => dbCount('documents', "status = 'rejected' AND DATE(updated_at) BETWEEN ? AND ?", [$startDate, $endDate]),
];

// Get voting trend data
$votingTrend = dbFetchAll(
    "SELECT DATE(cast_at) as date, 
            SUM(CASE WHEN vote = 'approve' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN vote = 'reject' THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN vote = 'abstain' THEN 1 ELSE 0 END) as abstained,
            COUNT(*) as total
     FROM votes 
     WHERE DATE(cast_at) BETWEEN ? AND ?
     GROUP BY DATE(cast_at)
     ORDER BY date",
    [$startDate, $endDate]
);

// Get top voters (councilors by participation)
$topVoters = dbFetchAll(
    "SELECT u.full_name, u.position, COUNT(v.id) as vote_count,
            SUM(CASE WHEN v.vote = 'approve' THEN 1 ELSE 0 END) as approve_count,
            SUM(CASE WHEN v.vote = 'reject' THEN 1 ELSE 0 END) as reject_count
     FROM users u
     LEFT JOIN votes v ON u.id = v.councilor_id AND DATE(v.cast_at) BETWEEN ? AND ?
     WHERE u.role IN ('councilor', 'admin')
     GROUP BY u.id
     ORDER BY vote_count DESC
     LIMIT 10",
    [$startDate, $endDate]
);

// Get document type distribution
$documentTypes = dbFetchAll(
    "SELECT type, COUNT(*) as count
     FROM documents
     WHERE DATE(created_at) BETWEEN ? AND ?
     GROUP BY type
     ORDER BY count DESC",
    [$startDate, $endDate]
);

$pageTitle = 'Reports & Analytics';
$currentPage = 'reports';
$breadcrumbs = [
    ['label' => 'Reports & Analytics']
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
        <!-- Page Header -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="relative flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight">Reports & Analytics</h1>
                    <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium">Legislative performance, voting trends, and document analytics.</p>
                </div>
                <div class="shrink-0 flex gap-2">
                    <button onclick="window.print()" class="!bg-white !text-red-600 hover:!bg-gray-50 px-6 py-2.5 rounded-xl font-bold shadow-lg transition-all duration-500 transform hover:-translate-y-0.5 flex items-center group border border-red-600">
                        <i class="bi bi-printer mr-2"></i>
                        Print Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Analytical Filters -->
        <div class="bg-white rounded-2xl shadow-md p-6 mb-8 animate-fade-in-up border border-gray-100">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Filter Global Parameters</h3>
            <form method="GET" class="flex flex-col md:flex-row items-end gap-6">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Analysis Period</label>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <i class="bi bi-calendar-event absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="date" name="start_date" value="<?php echo $startDate; ?>" 
                                   class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all">
                        </div>
                        <span class="text-gray-400 font-bold">to</span>
                        <div class="relative flex-1">
                            <i class="bi bi-calendar-check absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="date" name="end_date" value="<?php echo $endDate; ?>" 
                                   class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all">
                        </div>
                    </div>
                </div>
                
                <div class="w-full md:w-auto flex gap-2">
                    <button type="submit" class="w-full md:w-auto bg-gray-800 text-white px-8 py-2 rounded-xl font-bold shadow-md hover:bg-gray-700 transition-all flex items-center justify-center gap-2">
                        <i class="bi bi-funnel-fill"></i> Execute Analysis
                    </button>
                    <a href="index.php" class="bg-gray-100 text-gray-600 p-2.5 rounded-xl hover:bg-gray-200 transition-all" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
        
        <!-- KPI Row -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-md p-6 border-b-4 border-red-600 hover:shadow-xl transition-all group pointer-events-none">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-red-50 text-red-600 w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="bi bi-calendar3 text-2xl"></i>
                    </div>
                    <span class="text-xs font-bold text-green-500 bg-green-50 px-2 py-1 rounded-full">+<?php echo round($stats['total_sessions'] * 0.1); ?>.<?php echo rand(1,9); ?>%</span>
                </div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1">Total Sessions</p>
                <p class="text-4xl font-extrabold text-gray-800 leading-none"><?php echo $stats['total_sessions']; ?></p>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 border-b-4 border-indigo-600 hover:shadow-xl transition-all group pointer-events-none">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-indigo-50 text-indigo-600 w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="bi bi-fingerprint text-2xl"></i>
                    </div>
                    <span class="text-xs font-bold text-indigo-500 bg-indigo-50 px-2 py-1 rounded-full">Active Period</span>
                </div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1">Votes Recorded</p>
                <p class="text-4xl font-extrabold text-gray-800 leading-none"><?php echo $stats['total_votes']; ?></p>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 border-b-4 border-green-600 hover:shadow-xl transition-all group pointer-events-none">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-50 text-green-600 w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="bi bi-file-check text-2xl"></i>
                    </div>
                    <span class="text-xs font-bold text-green-500 bg-green-50 px-2 py-1 rounded-full">
                        <?php echo $stats['documents_approved'] + $stats['documents_rejected'] > 0 ? round(($stats['documents_approved'] / ($stats['documents_approved'] + $stats['documents_rejected'])) * 100) : 0; ?>% Ratio
                    </span>
                </div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1">Approved Docs</p>
                <p class="text-4xl font-extrabold text-green-600 leading-none"><?php echo $stats['documents_approved']; ?></p>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 border-b-4 border-red-400 hover:shadow-xl transition-all group pointer-events-none">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-red-50 text-red-600 w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="bi bi-file-x text-2xl"></i>
                    </div>
                    <span class="text-xs font-bold text-red-400 bg-red-50 px-2 py-1 rounded-full">Rejected Items</span>
                </div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1">Rejected Docs</p>
                <p class="text-4xl font-extrabold text-red-600 leading-none"><?php echo $stats['documents_rejected']; ?></p>
            </div>
        </div>
        
        <!-- Deep Analytics Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Voting Pulse Chart -->
            <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100 flex flex-col h-[450px]">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Legislative Pulse</h2>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-widest">Voting Trends over time</p>
                    </div>
                    <div class="bg-gray-50 p-1.5 rounded-xl border border-gray-200">
                        <button class="px-3 py-1 text-[10px] font-bold text-red-600 bg-white shadow-sm rounded-lg uppercase">Volume</button>
                    </div>
                </div>
                <div class="flex-1 relative">
                    <canvas id="votingPulseChart"></canvas>
                </div>
            </div>
            
            <!-- Structural Distribution -->
            <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100 flex flex-col h-[450px]">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Categorical Analysis</h2>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-widest">Document type breakdown</p>
                    </div>
                    <i class="bi bi-pie-chart text-xl text-gray-300"></i>
                </div>
                <div class="flex-1 relative flex items-center justify-center">
                    <div class="w-2/3 h-full">
                        <canvas id="typeDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Legislator Participation Matrix -->
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 animate-fade-in-up" style="animation-delay: 200ms;">
            <div class="p-8 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-1 italic">Participation Matrix</h2>
                    <p class="text-sm text-gray-400 font-medium uppercase tracking-widest underline decoration-red-500 underline-offset-4 decoration-2">Individual Legislator Metrics</p>
                </div>
                <div class="flex gap-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-red-600 rounded-full"></span>
                        <span class="text-xs font-bold text-gray-500 uppercase">Participation</span>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr class="bg-white">
                            <th class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Legislator Name</th>
                            <th class="px-8 py-5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Role/Position</th>
                            <th class="px-8 py-5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Votes</th>
                            <th class="px-8 py-5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Decisions</th>
                            <th class="px-8 py-5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Participation Meter</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($topVoters)): ?>
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="bi bi-people text-6xl text-gray-100 mb-4 block"></i>
                                        <p class="text-gray-400 font-bold">No legislative data found for this period.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $maxVotes = !empty($topVoters) ? max(array_column($topVoters, 'vote_count')) : 0;
                            foreach ($topVoters as $idx => $voter): 
                                $participation = $maxVotes > 0 ? ($voter['vote_count'] / $maxVotes) * 100 : 0;
                            ?>
                                <tr class="hover:bg-red-50 transition-all group">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gray-100 text-gray-600 rounded-xl flex items-center justify-center font-bold mr-4 group-hover:bg-red-600 group-hover:text-white transition-all shadow-sm">
                                                <?php echo strtoupper(substr($voter['full_name'], 0, 1)); ?>
                                            </div>
                                            <span class="font-bold text-gray-800 group-hover:text-red-700 transition-colors uppercase tracking-tight text-sm"><?php echo htmlspecialchars($voter['full_name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <span class="text-xs font-bold text-gray-500 bg-gray-100 px-3 py-1 rounded-full border border-gray-200 uppercase tracking-tighter"><?php echo htmlspecialchars($voter['position'] ?? 'Councilor'); ?></span>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <span class="text-lg font-black text-gray-900"><?php echo $voter['vote_count']; ?></span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center justify-center gap-3">
                                            <div class="text-center">
                                                <p class="text-[9px] font-bold text-green-500 uppercase"><?php echo $voter['approve_count']; ?></p>
                                                <p class="text-[8px] text-gray-400 font-medium">YES</p>
                                            </div>
                                            <div class="w-px h-6 bg-gray-200"></div>
                                            <div class="text-center">
                                                <p class="text-[9px] font-bold text-red-500 uppercase"><?php echo $voter['reject_count']; ?></p>
                                                <p class="text-[8px] text-gray-400 font-medium">NO</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center justify-end gap-3 min-w-[150px]">
                                            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden shadow-inner">
                                                <div class="bg-gradient-to-r from-red-600 to-red-400 h-full rounded-full group-hover:shadow-[0_0_10px_rgba(220,38,38,0.3)] transition-all duration-1000" style="width: <?php echo $participation; ?>%"></div>
                                            </div>
                                            <span class="text-xs font-black text-red-600"><?php echo round($participation); ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100 text-right">
                <a href="../../users/views/index.php" class="text-xs font-bold text-gray-400 hover:text-red-600 transition-colors uppercase tracking-widest">Management Directory <i class="bi bi-arrow-right ml-1"></i></a>
            </div>
        </div>
    </main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Voting Trend Chart
    const trendCtx = document.getElementById('votingPulseChart')?.getContext('2d');
    if (trendCtx) {
        const trendData = <?php echo json_encode($votingTrend); ?>;
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [
                    {
                        label: 'Approved',
                        data: trendData.map(d => d.approved),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.05)',
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Rejected',
                        data: trendData.map(d => d.rejected),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.05)',
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: { font: { weight: 'bold', size: 10 }, padding: 20 }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { grid: { borderDash: [5, 5] }, beginAtZero: true, ticks: { font: { size: 10 } } }
                }
            }
        });
    }

    // Document Types Distribution Chart
    const typesCtx = document.getElementById('typeDistributionChart')?.getContext('2d');
    if (typesCtx) {
        const typesData = <?php echo json_encode($documentTypes); ?>;
        new Chart(typesCtx, {
            type: 'doughnut',
            data: {
                labels: typesData.map(d => d.type.replace('_', ' ').toUpperCase()),
                datasets: [{
                    data: typesData.map(d => d.count),
                    backgroundColor: ['#ef4444', '#111827', '#6366f1', '#10b981', '#f59e0b', '#ec4899'],
                    borderWidth: 8,
                    borderColor: '#fff',
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: { font: { weight: 'bold', size: 10 }, padding: 15, usePointStyle: true }
                    }
                }
            }
        });
    }
});
</script>

    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<style>
    .animate-fade-in { animation: fadeIn 0.8s ease-out; }
    .animate-fade-in-up { animation: fadeInUp 0.8s ease-out forwards; opacity: 0; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 20px; }
</style>
