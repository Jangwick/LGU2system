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

// Get dashboard data
$stats = $dashboard->getStatistics();
$recentDocuments = $dashboard->getRecentDocuments(5);
$upcomingSessions = $dashboard->getUpcomingSessions(5);
$recentVotes = $dashboard->getRecentVotes(5);

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
$breadcrumbs = [
    ['label' => 'Dashboard']
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
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-xl md:rounded-2xl shadow-xl p-4 md:p-8 mb-4 md:mb-6 text-white transform hover:scale-[1.01] transition-all duration-300 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="animate-slide-in-left">
                    <h1 class="text-xl md:text-3xl font-bold mb-1 md:mb-2">
                        Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>! 👋
                    </h1>
                    <p class="text-red-100 text-sm md:text-base">
                        Here's what's happening with your legislative voting today.
                    </p>
                </div>
                <div class="flex gap-2 md:gap-3 animate-slide-in-right">
                    <?php if (hasRole(['secretary', 'admin'])): ?>
                    <a href="<?php echo VOTING_URL; ?>/views/create-session.php" class="bg-white text-red-600 px-3 md:px-6 py-2 md:py-3 rounded-lg font-semibold hover:bg-red-50 transition-all shadow-md flex items-center transform hover:scale-105 text-xs md:text-base">
                        <i class="bi bi-plus-circle mr-1 md:mr-2"></i>
                        <span class="hidden sm:inline">New </span>Session
                    </a>
                    <?php endif; ?>
                    <?php if (hasRole(['councilor', 'admin'])): ?>
                    <a href="<?php echo VOTING_URL; ?>/views/cast-vote.php" class="bg-red-700 text-white px-3 md:px-6 py-2 md:py-3 rounded-lg font-semibold hover:bg-red-800 transition-all flex items-center transform hover:scale-105 text-xs md:text-base">
                        <i class="bi bi-hand-thumbs-up mr-1 md:mr-2"></i>
                        Cast Vote
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6 mb-6">
            <!-- Total Documents -->
            <div class="bg-white rounded-xl shadow-md p-4 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-100 group cursor-pointer">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <div>
                        <p class="text-gray-500 text-xs md:text-sm font-medium mb-1 group-hover:text-red-600">Total Documents</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800"><?php echo number_format($stats['total_documents']); ?></h3>
                        <p class="text-red-600 text-xs md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-file-earmark-text"></i> All types
                        </p>
                    </div>
                    <div class="bg-red-100 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-file-earmark-text text-red-600 text-lg md:text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Pending Vote -->
            <div class="bg-white rounded-xl shadow-md p-4 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-200 group cursor-pointer">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <div>
                        <p class="text-gray-500 text-xs md:text-sm font-medium mb-1 group-hover:text-yellow-600">Pending Vote</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800"><?php echo number_format($stats['pending_vote']); ?></h3>
                        <p class="text-yellow-600 text-xs md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-clock"></i> Awaiting decision
                        </p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-hourglass-split text-yellow-600 text-lg md:text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Approved -->
            <div class="bg-white rounded-xl shadow-md p-4 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-300 group cursor-pointer">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <div>
                        <p class="text-gray-500 text-xs md:text-sm font-medium mb-1 group-hover:text-green-600">Approved</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800"><?php echo number_format($stats['approved']); ?></h3>
                        <p class="text-green-600 text-xs md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-check-circle"></i> This month
                        </p>
                    </div>
                    <div class="bg-green-100 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-check-circle text-green-600 text-lg md:text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Active Sessions -->
            <div class="bg-white rounded-xl shadow-md p-4 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-400 group cursor-pointer">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <div>
                        <p class="text-gray-500 text-xs md:text-sm font-medium mb-1 group-hover:text-purple-600">Active Sessions</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800"><?php echo number_format($stats['active_sessions']); ?></h3>
                        <p class="text-purple-600 text-xs md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-broadcast"></i> Live now
                        </p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-broadcast text-purple-600 text-lg md:text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Voting Statistics Chart -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-500">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Voting Statistics (Last 7 Days)</h2>
                    <button onclick="location.reload()" class="text-gray-500 hover:text-gray-700 text-sm" title="Refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="votingChart"></canvas>
                </div>
            </div>
            
            <!-- Document Status Distribution -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-600">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Document Status Distribution</h2>
                    <button class="text-gray-500 hover:text-gray-700">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="documentStatusChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Documents -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-700">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Recent Documents</h2>
                    <a href="<?php echo DOCUMENTS_INDEX_URL; ?>" class="text-red-600 hover:text-red-700 text-sm font-medium">
                        View All <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <div class="space-y-3">
                    <?php if (empty($recentDocuments)): ?>
                        <div class="text-center text-gray-500 py-4">
                            <i class="bi bi-inbox text-3xl mb-2"></i>
                            <p>No documents found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentDocuments as $doc): ?>
                            <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors">
                                <div class="bg-red-100 rounded-lg p-2">
                                    <i class="bi bi-file-earmark-text text-red-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate"><?php echo htmlspecialchars($doc['title']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo formatDate($doc['created_at']); ?></p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full <?php echo getStatusBadgeClass($doc['status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $doc['status'])); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Upcoming Sessions -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-800">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Upcoming Sessions</h2>
                    <a href="<?php echo VOTING_URL; ?>/views/sessions.php" class="text-red-600 hover:text-red-700 text-sm font-medium">
                        View All <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <div class="space-y-3">
                    <?php if (empty($upcomingSessions)): ?>
                        <div class="text-center text-gray-500 py-4">
                            <i class="bi bi-calendar-x text-3xl mb-2"></i>
                            <p>No upcoming sessions</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcomingSessions as $session): ?>
                            <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors">
                                <div class="bg-indigo-100 rounded-lg p-2">
                                    <i class="bi bi-calendar-event text-indigo-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate"><?php echo htmlspecialchars($session['title']); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <i class="bi bi-clock mr-1"></i>
                                        <?php echo formatDate($session['session_date'], 'M d, Y'); ?> at <?php echo date('h:i A', strtotime($session['start_time'])); ?>
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full <?php echo getStatusBadgeClass($session['status']); ?>">
                                    <?php echo ucfirst($session['status']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Votes -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-900">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Recent Votes</h2>
                    <a href="<?php echo VOTING_URL; ?>/views/results.php" class="text-red-600 hover:text-red-700 text-sm font-medium">
                        View All <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <div class="space-y-3">
                    <?php if (empty($recentVotes)): ?>
                        <div class="text-center text-gray-500 py-4">
                            <i class="bi bi-hand-thumbs-up text-3xl mb-2"></i>
                            <p>No recent votes</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentVotes as $vote): ?>
                            <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors">
                                <div class="rounded-lg p-2 <?php echo $vote['vote'] === 'approve' ? 'bg-green-100' : ($vote['vote'] === 'reject' ? 'bg-red-100' : 'bg-gray-100'); ?>">
                                    <i class="bi <?php echo $vote['vote'] === 'approve' ? 'bi-hand-thumbs-up text-green-600' : ($vote['vote'] === 'reject' ? 'bi-hand-thumbs-down text-red-600' : 'bi-dash-circle text-gray-600'); ?>"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate"><?php echo htmlspecialchars($vote['document_title'] ?? 'Document'); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo formatDateTime($vote['cast_at'], 'M d, h:i A'); ?></p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full <?php echo getVoteBadgeClass($vote['vote']); ?>">
                                    <?php echo ucfirst($vote['vote']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer inside the main content area -->
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>

<script>
// Chart.js - Voting Statistics
const votingCtx = document.getElementById('votingChart')?.getContext('2d');
if (votingCtx) {
    new Chart(votingCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [
                {
                    label: 'Approved',
                    data: [12, 19, 15, 25, 22, 10, 8],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Rejected',
                    data: [2, 3, 1, 4, 2, 1, 1],
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
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Chart.js - Document Status Distribution
const statusCtx = document.getElementById('documentStatusChart')?.getContext('2d');
if (statusCtx) {
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Draft', 'Under Review', 'Pending Vote', 'Approved', 'Rejected'],
            datasets: [{
                data: [15, 25, 20, 35, 5],
                backgroundColor: [
                    '#9ca3af',
                    '#fbbf24',
                    '#8b5cf6',
                    '#10b981',
                    '#ef4444'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}
</script>
