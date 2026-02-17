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
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-lg md:rounded-2xl shadow-xl p-4 md:p-8 mb-3 md:mb-6 text-white transform hover:scale-[1.01] transition-all duration-300 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="animate-slide-in-left">
                    <h1 class="text-lg md:text-3xl font-bold mb-0.5 md:mb-2 line-clamp-1">
                        Hi, <?php echo e($_SESSION['user_name'] ?? 'User'); ?>! 👋
                    </h1>
                    <p class="text-red-100 text-xs md:text-base animate-slide-in-left animation-delay-100">
                        Voting & decision-making status for today.
                    </p>
                </div>
                <div class="flex w-full md:w-auto gap-2 md:gap-3 animate-slide-in-right mt-3 md:mt-0">
                    <?php if ($canManage): ?>
                    <a href="<?php echo VOTING_URL; ?>/views/create-session.php" class="flex-1 md:flex-none justify-center !bg-white text-red-600 px-4 py-2.5 rounded-xl font-bold hover:bg-red-50 transition-all shadow-md flex items-center transform hover:scale-[1.02] active:scale-95 text-sm border-none">
                        <i class="bi bi-plus-circle mr-2"></i>
                        <span>New Session</span>
                    </a>
                    <?php endif; ?>
                    <?php if ($canVote): ?>
                    <a href="<?php echo VOTING_URL; ?>/views/cast-vote.php" class="flex-1 md:flex-none justify-center bg-red-600 text-white px-4 py-2.5 rounded-xl font-bold hover:bg-red-700 border border-white/10 shadow-lg transition-all flex items-center transform hover:scale-[1.02] active:scale-95 text-sm">
                        <i class="bi bi-hand-thumbs-up mr-2"></i>
                        <span>Cast Vote</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 md:gap-6 mb-6">
            <!-- Total Sessions -->
            <div class="bg-white rounded-lg shadow-md p-3 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-100 group cursor-pointer">
                <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-1 sm:gap-2 text-center sm:text-left">
                    <div>
                        <p class="text-gray-500 text-[10px] md:text-sm font-medium mb-0.5 transition-colors group-hover:text-red-600">Total Sessions</p>
                        <h3 class="text-lg md:text-3xl font-bold text-gray-800 transform transition-all group-hover:scale-110"><?php echo number_format($stats['total_sessions']); ?></h3>
                        <p class="text-green-600 text-[10px] md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-broadcast"></i> <?php echo $stats['active_sessions']; ?> active
                        </p>
                    </div>
                    <div class="bg-red-100 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-calendar-check text-red-600 text-sm md:text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Pending Vote -->
            <div class="bg-white rounded-lg shadow-md p-3 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-200 group cursor-pointer">
                <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-1 sm:gap-2 text-center sm:text-left">
                    <div>
                        <p class="text-gray-500 text-[10px] md:text-sm font-medium mb-0.5 transition-colors group-hover:text-yellow-600">Pending Vote</p>
                        <h3 class="text-lg md:text-3xl font-bold text-gray-800 transform transition-all group-hover:scale-110"><?php echo number_format($stats['pending_vote']); ?></h3>
                        <p class="text-yellow-600 text-[10px] md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-clock"></i> <?php echo $stats['pending_vote']; ?> awaiting
                        </p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-hourglass-split text-yellow-600 text-sm md:text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Approved This Month -->
            <div class="bg-white rounded-lg shadow-md p-3 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-300 group cursor-pointer">
                <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-1 sm:gap-2 text-center sm:text-left">
                    <div>
                        <p class="text-gray-500 text-[10px] md:text-sm font-medium mb-0.5 transition-colors group-hover:text-green-600">Approved</p>
                        <h3 class="text-lg md:text-3xl font-bold text-gray-800 transform transition-all group-hover:scale-110"><?php echo number_format($stats['approved']); ?></h3>
                        <p class="text-green-600 text-[10px] md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-check-circle"></i> <?php echo $approvalRate; ?>% approval
                        </p>
                    </div>
                    <div class="bg-green-100 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-check-circle text-green-600 text-sm md:text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Total Votes -->
            <div class="bg-white rounded-lg shadow-md p-3 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-400 group cursor-pointer">
                <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-1 sm:gap-2 text-center sm:text-left">
                    <div>
                        <p class="text-gray-500 text-[10px] md:text-sm font-medium mb-0.5 transition-colors group-hover:text-purple-600">Total Votes</p>
                        <h3 class="text-lg md:text-3xl font-bold text-gray-800 transform transition-all group-hover:scale-110"><?php echo number_format($stats['total_votes']); ?></h3>
                        <p class="text-gray-600 text-[10px] md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-hand-thumbs-up"></i> All time
                        </p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-hand-thumbs-up text-purple-600 text-sm md:text-2xl"></i>
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

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>

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
