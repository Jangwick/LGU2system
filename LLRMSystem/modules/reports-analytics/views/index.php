<?php
session_start();
require_once __DIR__ . '/../controllers/ReportController.php';

$controller = new ReportController();

// Handle export requests
if (isset($_GET['export'])) {
    $reportType = $_GET['export'];
    $startDate = $_GET['start_date'] ?? null;
    $endDate = $_GET['end_date'] ?? null;
    
    switch ($reportType) {
        case 'user_activity':
            $data = $controller->getUserActivityReport($startDate, $endDate);
            break;
        case 'document_access':
            $data = $controller->getDocumentAccessReport($startDate, $endDate);
            break;
        case 'top_uploaders':
            $data = $controller->getTopUploaders(50);
            break;
        default:
            $data = [];
    }
    
    $controller->exportToCSV($reportType, $data);
    exit;
}

// Get all dashboard data
$stats = $controller->getDashboardStats();
$documentsByType = $controller->getDocumentsByType();
$documentsByStatus = $controller->getDocumentsByStatus();
$timeline = $controller->getDocumentsTimeline();
$topUploaders = $controller->getTopUploaders(5);
$activityByAction = $controller->getActivityByAction();
$recentActivities = $controller->getRecentActivities(10);
$documentsByDepartment = $controller->getDocumentsByDepartment();
$storageByType = $controller->getStorageByType();
$monthlyGrowth = $controller->getMonthlyGrowth();

$pageTitle = 'Reports & Analytics';
$currentPage = 'reports';
require_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php require_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php require_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <!-- Page Header -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Reports & Analytics</h1>
                    <p class="text-gray-600">Comprehensive insights and statistical analysis</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="showExportModal()" class="btn-success flex items-center">
                        <i class="bi bi-download mr-2"></i> Export Reports
                    </button>
                    <button onclick="window.print()" class="btn-outline flex items-center">
                        <i class="bi bi-printer mr-2"></i> Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-file-earmark-text-fill text-blue-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600">Total Documents</div>
                        <div class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total_documents']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-check-circle-fill text-green-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600">Approved</div>
                        <div class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['approved_documents']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-people-fill text-indigo-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600">Active Users</div>
                        <div class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['active_users']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-hdd-fill text-amber-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600">Storage Used</div>
                        <div class="text-2xl font-bold text-gray-900">
                            <?php 
                            $storage = $stats['total_storage'];
                            if ($storage >= 1073741824) {
                                echo number_format($storage / 1073741824, 2) . ' GB';
                            } elseif ($storage >= 1048576) {
                                echo number_format($storage / 1048576, 2) . ' MB';
                            } else {
                                echo number_format($storage / 1024, 2) . ' KB';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Documents by Type -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Documents by Type</h3>
                <canvas id="documentsByTypeChart"></canvas>
            </div>

            <!-- Documents by Status -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Documents by Status</h3>
                <canvas id="documentsByStatusChart"></canvas>
            </div>
        </div>

        <!-- Timeline Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Document Upload Timeline (Last 12 Months)</h3>
            <canvas id="timelineChart"></canvas>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Activity by Action -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity by Action (30 Days)</h3>
                <canvas id="activityChart"></canvas>
            </div>

            <!-- Documents by Department -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Documents by Department</h3>
                <canvas id="departmentChart"></canvas>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Top Uploaders -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Top Uploaders</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Documents</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($topUploaders as $uploader): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="bi bi-person-fill text-blue-600"></i>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($uploader['full_name'] ?? $uploader['name']); ?></div>
                                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($uploader['department'] ?? 'N/A'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                        <?php echo number_format($uploader['document_count']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Storage by Type -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Storage Usage by Type</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Size</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($storageByType as $type): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo ucfirst(str_replace('_', ' ', $type['document_type'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                        <?php 
                                        $size = $type['total_size'];
                                        if ($size >= 1073741824) {
                                            echo number_format($size / 1073741824, 2) . ' GB';
                                        } elseif ($size >= 1048576) {
                                            echo number_format($size / 1048576, 2) . ' MB';
                                        } else {
                                            echo number_format($size / 1024, 2) . ' KB';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recentActivities as $activity): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($activity['full_name'] ?? $activity['username'] ?? 'Unknown'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $actionClass = match($activity['action']) {
                                        'create' => 'bg-green-100 text-green-800',
                                        'update' => 'bg-blue-100 text-blue-800',
                                        'delete' => 'bg-red-100 text-red-800',
                                        'login' => 'bg-indigo-100 text-indigo-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                    ?>
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $actionClass; ?>">
                                        <?php echo ucfirst($activity['action']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($activity['description']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Export Modal -->
<div id="exportModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-900">Export Report</h3>
            <button onclick="closeExportModal()" class="text-gray-400 hover:text-gray-600">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        
        <form method="GET">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                <select name="export" class="input-field" required>
                    <option value="">Select Report...</option>
                    <option value="user_activity">User Activity Report</option>
                    <option value="document_access">Document Access Report</option>
                    <option value="top_uploaders">Top Uploaders Report</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date (Optional)</label>
                <input type="date" name="start_date" class="input-field">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date (Optional)</label>
                <input type="date" name="end_date" class="input-field">
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeExportModal()" class="btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    <i class="bi bi-download mr-2"></i> Export CSV
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Chart.js configurations
const chartColors = {
    blue: 'rgb(59, 130, 246)',
    green: 'rgb(34, 197, 94)',
    red: 'rgb(239, 68, 68)',
    yellow: 'rgb(234, 179, 8)',
    purple: 'rgb(168, 85, 247)',
    indigo: 'rgb(99, 102, 241)',
    pink: 'rgb(236, 72, 153)',
    orange: 'rgb(249, 115, 22)'
};

// Documents by Type Chart
new Chart(document.getElementById('documentsByTypeChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($documentsByType, 'document_type')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($documentsByType, 'count')); ?>,
            backgroundColor: Object.values(chartColors)
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Documents by Status Chart
new Chart(document.getElementById('documentsByStatusChart'), {
    type: 'pie',
    data: {
        labels: <?php echo json_encode(array_column($documentsByStatus, 'status')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($documentsByStatus, 'count')); ?>,
            backgroundColor: [chartColors.green, chartColors.yellow, chartColors.blue, chartColors.red, chartColors.purple]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Timeline Chart
new Chart(document.getElementById('timelineChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($timeline, 'month')); ?>,
        datasets: [{
            label: 'Documents Uploaded',
            data: <?php echo json_encode(array_column($timeline, 'count')); ?>,
            borderColor: chartColors.blue,
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Activity by Action Chart
new Chart(document.getElementById('activityChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($activityByAction, 'action')); ?>,
        datasets: [{
            label: 'Activities',
            data: <?php echo json_encode(array_column($activityByAction, 'count')); ?>,
            backgroundColor: chartColors.indigo
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Documents by Department Chart
new Chart(document.getElementById('departmentChart'), {
    type: 'horizontalBar',
    data: {
        labels: <?php echo json_encode(array_column($documentsByDepartment, 'department')); ?>,
        datasets: [{
            label: 'Documents',
            data: <?php echo json_encode(array_column($documentsByDepartment, 'count')); ?>,
            backgroundColor: chartColors.green
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                beginAtZero: true
            }
        }
    }
});

function showExportModal() {
    document.getElementById('exportModal').classList.remove('hidden');
}

function closeExportModal() {
    document.getElementById('exportModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('exportModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeExportModal();
    }
});
</script>

<script src="<?php echo asset('js/reports.js'); ?>"></script>

<?php require_once __DIR__ . '/../../core/layouts/footer.php'; ?>
