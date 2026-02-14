<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Load dashboard controller
require_once __DIR__ . '/../controllers/DashboardController.php';
$dashboardController = new DashboardController();

// Get dashboard data
$stats = $dashboardController->getStatistics();
$uploadTrend = $dashboardController->getUploadTrend();
$documentTypes = $dashboardController->getDocumentTypesDistribution();
$recentDocuments = $dashboardController->getRecentDocuments(5);
$systemStatus = $dashboardController->getSystemStatus();

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
    <main class="flex-1 overflow-y-auto bg-gray-100 p-2 md:p-6">
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-lg md:rounded-2xl shadow-xl p-4 md:p-8 mb-3 md:mb-6 text-white transform hover:scale-[1.01] transition-all duration-300 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="animate-slide-in-left">
                    <h1 class="text-lg md:text-3xl font-bold mb-0.5 md:mb-2 line-clamp-1">
                        Hi, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>! 👋
                    </h1>
                    <p class="text-red-100 text-xs md:text-base animate-slide-in-left animation-delay-100">
                        Legislative records status for today.
                    </p>
                </div>
                <div class="flex gap-2 animate-slide-in-right">
                    <?php 
                    $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                    if (!in_array($userRole, ['viewer'])): 
                    ?>
                    <a href="<?php echo DOCUMENTS_INDEX_URL; ?>?upload=true" style="background-color: #ffffff !important; color: #dc2626 !important;" class="bg-white text-red-600 px-3 py-2 rounded-lg font-semibold hover:bg-red-50 transition-all shadow-md flex items-center transform hover:scale-105 hover:shadow-lg active:scale-95 text-[10px] md:text-base">
                        <i class="bi bi-upload mr-1 md:mr-2"></i>
                        <span>Upload</span>
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo SEARCH_URL; ?>/views/index.php" style="background-color: #b91c1c !important; color: #ffffff !important;" class="bg-red-700 text-white px-3 py-2 rounded-lg font-semibold hover:bg-red-800 transition-all flex items-center transform hover:scale-105 hover:shadow-lg active:scale-95 text-[10px] md:text-base">
                        <i class="bi bi-search mr-1 md:mr-2"></i>
                        Search
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 md:gap-6 mb-6">
            <!-- Total Documents -->
            <div class="bg-white rounded-lg shadow-md p-3 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-100 group cursor-pointer">
                <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-1 sm:gap-2 text-center sm:text-left">
                    <div>
                        <p class="text-gray-500 text-[10px] md:text-sm font-medium mb-0.5 transition-colors group-hover:text-red-600">Total Documents</p>
                        <h3 class="text-lg md:text-3xl font-bold text-gray-800 transform transition-all group-hover:scale-110"><?php echo number_format($stats['total_documents']); ?></h3>
                        <p class="<?php echo $stats['growth_percentage'] >= 0 ? 'text-green-600' : 'text-red-600'; ?> text-[10px] md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-arrow-<?php echo $stats['growth_percentage'] >= 0 ? 'up' : 'down'; ?>"></i> 
                            <?php echo abs($stats['growth_percentage']); ?>%
                        </p>
                    </div>
                    <div class="bg-red-100 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-file-earmark-text text-red-600 text-sm md:text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Pending Review -->
            <div class="bg-white rounded-lg shadow-md p-3 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-200 group cursor-pointer">
                <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-1 sm:gap-2 text-center sm:text-left">
                    <div>
                        <p class="text-gray-500 text-[10px] md:text-sm font-medium mb-0.5 transition-colors group-hover:text-yellow-600">Pending Review</p>
                        <h3 class="text-lg md:text-3xl font-bold text-gray-800 transform transition-all group-hover:scale-110"><?php echo number_format($stats['pending_documents']); ?></h3>
                        <p class="text-yellow-600 text-[10px] md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-clock"></i> <?php echo $stats['urgent_pending']; ?> urgent
                        </p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-hourglass-split text-yellow-600 text-sm md:text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Approved Today -->
            <div class="bg-white rounded-lg shadow-md p-3 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-300 group cursor-pointer">
                <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-1 sm:gap-2 text-center sm:text-left">
                    <div>
                        <p class="text-gray-500 text-[10px] md:text-sm font-medium mb-0.5 transition-colors group-hover:text-green-600">Approved Today</p>
                        <h3 class="text-lg md:text-3xl font-bold text-gray-800 transform transition-all group-hover:scale-110"><?php echo number_format($stats['approved_today']); ?></h3>
                        <p class="text-green-600 text-[10px] md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-check-circle"></i> <?php echo $stats['approved_today'] > 0 ? 'On track' : 'No approvals'; ?>
                        </p>
                    </div>
                    <div class="bg-green-100 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-check-circle text-green-600 text-sm md:text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Storage Used -->
            <div class="bg-white rounded-lg shadow-md p-3 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-400 group cursor-pointer">
                <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-1 sm:gap-2 text-center sm:text-left">
                    <div>
                        <p class="text-gray-500 text-[10px] md:text-sm font-medium mb-0.5 transition-colors group-hover:text-purple-600">Storage Used</p>
                        <h3 class="text-lg md:text-3xl font-bold text-gray-800 transform transition-all group-hover:scale-110"><?php echo $stats['storage_used_gb']; ?> <span class="text-sm md:text-lg">GB</span></h3>
                        <p class="text-gray-600 text-[10px] md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-hdd"></i> <?php echo $stats['storage_percentage']; ?>%
                        </p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-hdd-stack text-purple-600 text-sm md:text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Document Uploads Chart -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-500">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Document Uploads (Last 7 Days)</h2>
                    <button onclick="location.reload()" class="text-gray-500 hover:text-gray-700 text-sm" title="Refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="uploadsChart"></canvas>
                </div>
            </div>
            
            <!-- Document Types Chart -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-600">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Document Types Distribution</h2>
                    <button class="text-gray-500 hover:text-gray-700">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="documentTypesChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity & Quick Links -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Documents -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-700">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Recent Documents</h2>
                    <a href="<?php echo DOCUMENTS_INDEX_URL; ?>" class="text-red-600 hover:text-red-700 text-sm font-medium">
                        View All <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Document</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($recentDocuments)): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        <i class="bi bi-inbox text-4xl mb-2"></i>
                                        <p>No documents found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentDocuments as $doc): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="bg-red-100 rounded p-2 mr-3">
                                                    <i class="bi bi-file-pdf text-red-600"></i>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($doc['title']); ?>
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        <?php echo htmlspecialchars($doc['reference_number']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="badge <?php echo $dashboardController->getTypeBadgeClass($doc['document_type']); ?>">
                                                <?php echo htmlspecialchars($dashboardController->formatDocumentType($doc['document_type'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="badge <?php echo $dashboardController->getStatusBadgeClass($doc['status']); ?>">
                                                <?php echo ucfirst($doc['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($doc['created_at'])); ?>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <a href="<?php echo DOCUMENTS_URL; ?>/views/view.php?id=<?php echo $doc['id']; ?>" 
                                               class="text-red-600 hover:text-red-700 mr-2" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?php echo DOCUMENTS_URL; ?>/api/download.php?id=<?php echo $doc['id']; ?>" 
                                               class="text-gray-600 hover:text-gray-700" title="Download">
                                                <i class="bi bi-download"></i>
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
                <div class="quick-actions-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-800">
                    <h2 class="quick-actions-title text-lg font-bold text-gray-800 mb-4">Quick Actions</h2>
                    <div class="space-y-2">
                        <?php if (!in_array($userRole, ['viewer'])): ?>
                        <a href="<?php echo DOCUMENTS_INDEX_URL; ?>?upload=true" class="quick-action-btn flex items-center p-3 hover:bg-red-50 rounded-lg transition-all duration-200">
                            <div class="icon-red bg-red-100 rounded-lg p-2 mr-3">
                                <i class="bi bi-upload text-red-600"></i>
                            </div>
                            <span class="quick-action-label text-sm font-medium text-gray-700">Upload New Document</span>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo SEARCH_URL; ?>/views/index.php" class="quick-action-btn flex items-center p-3 hover:bg-green-50 rounded-lg transition-all duration-200">
                            <div class="icon-green bg-green-100 rounded-lg p-2 mr-3">
                                <i class="bi bi-search text-green-600"></i>
                            </div>
                            <span class="quick-action-label text-sm font-medium text-gray-700">Advanced Search</span>
                        </a>
                        <?php if (in_array($userRole, ['administrator', 'admin', 'officer'])): ?>
                        <a href="<?php echo REPORTS_URL; ?>/views/index.php" class="quick-action-btn flex items-center p-3 hover:bg-purple-50 rounded-lg transition-all duration-200">
                            <div class="icon-purple bg-purple-100 rounded-lg p-2 mr-3">
                                <i class="bi bi-graph-up text-purple-600"></i>
                            </div>
                            <span class="quick-action-label text-sm font-medium text-gray-700">Generate Report</span>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo DOCUMENTS_INDEX_URL; ?>" class="quick-action-btn flex items-center p-3 hover:bg-orange-50 rounded-lg transition-all duration-200">
                            <div class="icon-orange bg-orange-100 rounded-lg p-2 mr-3">
                                <i class="bi bi-folder text-orange-600"></i>
                            </div>
                            <span class="quick-action-label text-sm font-medium text-gray-700">Browse Documents</span>
                        </a>
                    </div>
                </div>
                
                <!-- System Status -->
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-900">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">System Status</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">API Integration</span>
                            <span class="badge badge-<?php echo $systemStatus['api']['class']; ?>">
                                <i class="bi bi-<?php echo $systemStatus['api']['status'] === 'online' ? 'check-circle' : 'x-circle'; ?> mr-1"></i>
                                <?php echo ucfirst($systemStatus['api']['status']); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Database</span>
                            <span class="badge badge-<?php echo $systemStatus['database']['class']; ?>">
                                <i class="bi bi-<?php echo $systemStatus['database']['status'] === 'healthy' ? 'check-circle' : 'exclamation-circle'; ?> mr-1"></i>
                                <?php echo ucfirst($systemStatus['database']['status']); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Storage</span>
                            <span class="badge badge-<?php echo $systemStatus['storage']['class']; ?>">
                                <i class="bi bi-<?php echo $systemStatus['storage']['class'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?> mr-1"></i>
                                <?php echo $systemStatus['storage']['status']; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

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
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded');
        return;
    }

    // Document Uploads Line Chart
    const uploadsCanvas = document.getElementById('uploadsChart');
    let uploadsChart;
    if (uploadsCanvas) {
        const uploadsCtx = uploadsCanvas.getContext('2d');
        uploadsChart = new Chart(uploadsCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Documents Uploaded',
                    data: <?php echo json_encode($uploadTrend); ?>,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
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
                                return 'Uploaded: ' + context.parsed.y + ' documents';
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
                        grid: {
                            color: getGridColor()
                        }
                    },
                    x: {
                        ticks: {
                            color: getLabelColor()
                        },
                        grid: {
                            color: getGridColor()
                        }
                    }
                }
            }
        });
    }
    
    // Document Types Doughnut Chart
    const typesCanvas = document.getElementById('documentTypesChart');
    let typesChart;
    if (typesCanvas) {
        const typesCtx = typesCanvas.getContext('2d');
        const typeLabels = <?php echo json_encode(array_map(function($type) use ($dashboardController) { 
            return $dashboardController->formatDocumentType($type['document_type']); 
        }, $documentTypes)); ?>;
        const typeCounts = <?php echo json_encode(array_column($documentTypes, 'count')); ?>;
        
        typesChart = new Chart(typesCtx, {
            type: 'doughnut',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeCounts,
                    backgroundColor: [
                        'rgb(59, 130, 246)',   // Blue
                        'rgb(16, 185, 129)',   // Green
                        'rgb(245, 158, 11)',   // Yellow
                        'rgb(139, 92, 246)',   // Purple
                        'rgb(239, 68, 68)',    // Red
                        'rgb(236, 72, 153)',   // Pink
                        'rgb(249, 115, 22)',   // Orange
                        'rgb(107, 114, 128)',  // Gray
                        'rgb(20, 184, 166)',   // Teal
                        'rgb(99, 102, 241)'    // Indigo
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
                            font: {
                                size: 12
                            },
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
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Listen for theme changes and update all charts
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                const newLabelColor = getLabelColor();
                const newGridColor = getGridColor();
                
                // Update uploads chart
                if (uploadsChart) {
                    uploadsChart.options.scales.x.ticks.color = newLabelColor;
                    uploadsChart.options.scales.y.ticks.color = newLabelColor;
                    uploadsChart.options.scales.x.grid.color = newGridColor;
                    uploadsChart.options.scales.y.grid.color = newGridColor;
                    uploadsChart.update();
                }
                
                // Update types chart
                if (typesChart) {
                    typesChart.options.plugins.legend.labels.color = newLabelColor;
                    typesChart.update();
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
