<?php
session_start();
require_once __DIR__ . '/../controllers/ReportController.php';

$controller = new ReportController();

// Get all dashboard data
$stats = $controller->getDashboardStats();
$documentsByType = $controller->getDocumentsByType();
$documentsByStatus = $controller->getDocumentsByStatus();
$timeline = $controller->getDocumentsTimeline();
$topUploaders = $controller->getTopUploaders(5);
$activityByAction = $controller->getActivityByAction();
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
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-6 text-white transform hover:scale-[1.01] transition-all duration-300 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="transform transition-all duration-300">
                    <h1 class="text-2xl font-bold mb-2 animate-slide-in-left">Reports & Analytics</h1>
                    <p class="text-red-100 animate-slide-in-left animation-delay-100">Comprehensive insights and statistical analysis</p>
                </div>
                <div class="flex gap-3 animate-slide-in-right">
                    <button onclick="showExportModal()" class="btn-success flex items-center transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95">
                        <i class="bi bi-download mr-2 transition-transform group-hover:animate-bounce"></i> Export Reports
                    </button>
                    <button onclick="window.print()" class="bg-white text-red-600 hover:bg-red-50 font-semibold py-2 px-4 rounded-lg transition-all duration-200 flex items-center transform hover:scale-105 hover:shadow-lg active:scale-95">
                        <i class="bi bi-printer mr-2 transition-transform"></i> Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-100 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-file-earmark-text-fill text-blue-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 transition-colors duration-200 group-hover:text-blue-600">Total Documents</div>
                        <div class="text-2xl font-bold text-gray-900 transform transition-all duration-300 group-hover:scale-110"><?php echo number_format($stats['total_documents']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-200 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-check-circle-fill text-green-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 transition-colors duration-200 group-hover:text-green-600">Approved</div>
                        <div class="text-2xl font-bold text-gray-900 transform transition-all duration-300 group-hover:scale-110"><?php echo number_format($stats['approved_documents']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-300 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-people-fill text-indigo-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 transition-colors duration-200 group-hover:text-indigo-600">Active Users</div>
                        <div class="text-2xl font-bold text-gray-900 transform transition-all duration-300 group-hover:scale-110"><?php echo number_format($stats['active_users']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-400 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-hdd-fill text-amber-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 transition-colors duration-200 group-hover:text-amber-600">Storage Used</div>
                        <div class="text-2xl font-bold text-gray-900 transform transition-all duration-300 group-hover:scale-110">
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
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transform hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-500 group">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 transition-colors duration-200 group-hover:text-red-600">Documents by Type</h3>
                <div class="relative" style="height: 280px;">
                    <canvas id="documentsByTypeChart"></canvas>
                </div>
            </div>

            <!-- Documents by Status -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transform hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-600 group">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 transition-colors duration-200 group-hover:text-red-600">Documents by Status</h3>
                <div class="relative" style="height: 280px;">
                    <canvas id="documentsByStatusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Timeline Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 transform hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-700 group">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 transition-colors duration-200 group-hover:text-red-600">Document Upload Timeline (Last 12 Months)</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="timelineChart"></canvas>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Activity by Action -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transform hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-800 group">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 transition-colors duration-200 group-hover:text-red-600">Activity by Action (30 Days)</h3>
                <div class="relative" style="height: 280px;">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>

            <!-- Documents by Department -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transform hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-900 group">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 transition-colors duration-200 group-hover:text-red-600">Documents by Department</h3>
                <div class="relative" style="height: 280px;">
                    <canvas id="departmentChart"></canvas>
                </div>
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
    </main>

<?php require_once __DIR__ . '/../../core/layouts/footer.php'; ?>
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
        
        <form method="GET" action="<?php echo REPORTS_URL; ?>/api/export.php">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                <select name="report_type" class="input-field" required>
                    <option value="">Select Report...</option>
                    <option value="user_activity">User Activity Report</option>
                    <option value="document_access">Document Access Report</option>
                    <option value="top_uploaders">Top Uploaders Report</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                <select name="format" class="input-field" required>
                    <option value="">Select Format...</option>
                    <option value="pdf">PDF Document</option>
                    <option value="excel">Excel Spreadsheet (.xlsx)</option>
                    <option value="word">Word Document (.docx)</option>
                    <option value="csv">CSV File</option>
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
                    <i class="bi bi-download mr-2"></i> Export Report
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

// Documents by Type Chart
const docTypeChart = new Chart(document.getElementById('documentsByTypeChart'), {
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
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 12
                    },
                    color: getLabelColor()
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

// Documents by Status Chart
const docStatusChart = new Chart(document.getElementById('documentsByStatusChart'), {
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
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 12
                    },
                    color: getLabelColor()
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

// Timeline Chart
const timelineChart = new Chart(document.getElementById('timelineChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($timeline, 'month')); ?>,
        datasets: [{
            label: 'Documents Uploaded',
            data: <?php echo json_encode(array_column($timeline, 'count')); ?>,
            borderColor: chartColors.blue,
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 2,
            pointRadius: 3,
            pointHoverRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    font: {
                        size: 12
                    },
                    color: getLabelColor()
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    font: {
                        size: 11
                    },
                    color: getLabelColor()
                },
                grid: {
                    color: getGridColor()
                }
            },
            x: {
                ticks: {
                    font: {
                        size: 11
                    },
                    color: getLabelColor()
                },
                grid: {
                    color: getGridColor()
                }
            }
        }
    }
});

// Activity by Action Chart
const activityChart = new Chart(document.getElementById('activityChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($activityByAction, 'action')); ?>,
        datasets: [{
            label: 'Activities',
            data: <?php echo json_encode(array_column($activityByAction, 'count')); ?>,
            backgroundColor: chartColors.indigo,
            borderRadius: 6,
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    font: {
                        size: 11
                    },
                    color: getLabelColor()
                },
                grid: {
                    color: getGridColor()
                }
            },
            x: {
                ticks: {
                    font: {
                        size: 11
                    },
                    color: getLabelColor()
                },
                grid: {
                    color: getGridColor()
                }
            }
        }
    }
});

// Documents by Department Chart
const departmentChart = new Chart(document.getElementById('departmentChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($documentsByDepartment, 'department')); ?>,
        datasets: [{
            label: 'Documents',
            data: <?php echo json_encode(array_column($documentsByDepartment, 'count')); ?>,
            backgroundColor: chartColors.green,
            borderRadius: 6,
            borderWidth: 0
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    font: {
                        size: 11
                    },
                    color: getLabelColor()
                },
                grid: {
                    color: getGridColor()
                }
            },
            y: {
                ticks: {
                    font: {
                        size: 11
                    },
                    color: getLabelColor()
                },
                grid: {
                    color: getGridColor()
                }
            }
        }
    }
});

// Listen for theme changes and update all charts
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.attributeName === 'class') {
            const newLabelColor = getLabelColor();
            const newGridColor = getGridColor();
            
            // Update all chart labels and grids
            [docTypeChart, docStatusChart].forEach(chart => {
                chart.options.plugins.legend.labels.color = newLabelColor;
                chart.update();
            });
            
            [timelineChart, activityChart, departmentChart].forEach(chart => {
                chart.options.plugins.legend.labels.color = newLabelColor;
                chart.options.scales.x.ticks.color = newLabelColor;
                chart.options.scales.y.ticks.color = newLabelColor;
                chart.options.scales.x.grid.color = newGridColor;
                chart.options.scales.y.grid.color = newGridColor;
                chart.update();
            });
        }
    });
});

observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
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
