<?php
session_start();
require_once __DIR__ . '/../../../core/config/config.php';
require_once __DIR__ . '/../../../core/config/database.php';

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
    'documents_approved' => dbCount('documents', "status = 'approved' AND DATE(approved_at) BETWEEN ? AND ?", [$startDate, $endDate]),
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

include_once __DIR__ . '/../../../core/layouts/header.php';
?>

<!-- Sidebar -->
<?php include_once __DIR__ . '/../../../core/layouts/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Top Navbar -->
    <?php include_once __DIR__ . '/../../../core/layouts/navbar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto bg-gray-100 p-3 md:p-6">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Reports & Analytics</h1>
                <p class="text-gray-600 text-sm mt-1">Comprehensive voting and document analytics</p>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="bi bi-printer mr-1"></i> Print
                </button>
                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="bi bi-download mr-1"></i> Export
                </button>
            </div>
        </div>
        
        <!-- Date Range Filter -->
        <div class="bg-white rounded-xl shadow-md p-4 mb-6">
            <form method="GET" class="flex flex-col md:flex-row items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="<?php echo $startDate; ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" value="<?php echo $endDate; ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    <i class="bi bi-funnel mr-1"></i> Apply Filter
                </button>
                <a href="index.php" class="text-gray-500 hover:text-gray-700 px-4 py-2">Reset</a>
            </form>
        </div>
        
        <!-- Stats Overview -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Voting Sessions</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $stats['total_sessions']; ?></p>
                    </div>
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="bi bi-calendar-check text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Total Votes Cast</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $stats['total_votes']; ?></p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="bi bi-hand-thumbs-up text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Docs Approved</p>
                        <p class="text-3xl font-bold text-green-600"><?php echo $stats['documents_approved']; ?></p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="bi bi-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Docs Rejected</p>
                        <p class="text-3xl font-bold text-red-600"><?php echo $stats['documents_rejected']; ?></p>
                    </div>
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="bi bi-x-circle text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Voting Trend Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Voting Trend</h2>
                <div style="height: 300px;">
                    <canvas id="votingTrendChart"></canvas>
                </div>
            </div>
            
            <!-- Document Types Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Document Types Distribution</h2>
                <div style="height: 300px;">
                    <canvas id="documentTypesChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Top Voters Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Voting Participation</h2>
                <p class="text-sm text-gray-500">Councilor participation in voting sessions</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Councilor</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total Votes</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Approved</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rejected</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Participation</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($topVoters)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No voting data available for this period</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $maxVotes = max(array_column($topVoters, 'vote_count'));
                            foreach ($topVoters as $voter): 
                                $participation = $maxVotes > 0 ? ($voter['vote_count'] / $maxVotes) * 100 : 0;
                            ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($voter['full_name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($voter['position'] ?? 'Councilor'); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-semibold text-gray-800"><?php echo $voter['vote_count']; ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-green-600 font-medium"><?php echo $voter['approve_count']; ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-red-600 font-medium"><?php echo $voter['reject_count']; ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                                <div class="bg-red-600 h-2 rounded-full" style="width: <?php echo $participation; ?>%"></div>
                                            </div>
                                            <span class="text-sm text-gray-600"><?php echo round($participation); ?>%</span>
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
</div>

<script>
// Voting Trend Chart
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
                    data: trendData.map(d => d.approved),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Rejected',
                    data: trendData.map(d => d.rejected),
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

// Document Types Chart
const typesCtx = document.getElementById('documentTypesChart')?.getContext('2d');
if (typesCtx) {
    const typesData = <?php echo json_encode($documentTypes); ?>;
    new Chart(typesCtx, {
        type: 'doughnut',
        data: {
            labels: typesData.map(d => d.type.replace('_', ' ').toUpperCase()),
            datasets: [{
                data: typesData.map(d => d.count),
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}
</script>

<?php include_once __DIR__ . '/../../../core/layouts/footer.php'; ?>
