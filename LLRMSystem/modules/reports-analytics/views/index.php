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
$topApprovers = $controller->getTopApprovers(5);
$activityTrend = $controller->getActivityTrend();
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
    
    <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-950 p-6">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-6 text-white transform hover:scale-[1.01] transition-all duration-300 animate-fade-in relative overflow-hidden">
            <!-- Background Decoration -->
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 relative z-10">
                <div class="transform transition-all duration-300">
                    <h1 class="text-2xl md:text-3xl font-bold mb-2 animate-slide-in-left">Reports & Analytics</h1>
                    <p class="text-red-100 animate-slide-in-left animation-delay-100">Comprehensive insights and statistical analysis</p>
                </div>
                <div class="flex flex-wrap gap-3 animate-slide-in-right">
                    <button type="button" onclick="showExportModal()" class="no-ripple inline-flex items-center justify-center px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold hover:shadow-lg transition-shadow duration-200 shadow-sm border border-white/10 min-w-[160px] h-10 flex-shrink-0 transform-none hover:transform-none active:transform-none">
                        <i class="bi bi-download mr-2"></i> Export Reports
                    </button>
                    <button type="button" onclick="window.print()" class="no-ripple inline-flex items-center justify-center px-6 py-2.5 !bg-white hover:!bg-red-50 !text-red-600 rounded-xl font-bold hover:shadow-lg transition-shadow duration-200 shadow-sm border border-transparent min-w-[120px] h-10 flex-shrink-0 transform-none hover:transform-none active:transform-none">
                        <i class="bi bi-printer mr-2"></i> Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-100 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-file-earmark-text-fill text-blue-600 dark:text-blue-400 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200 group-hover:text-blue-600 dark:group-hover:text-blue-400">Total Documents</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110"><?php echo number_format($stats['total_documents']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-200 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-check-circle-fill text-green-600 dark:text-green-400 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200 group-hover:text-green-600 dark:group-hover:text-green-400">Approved</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110"><?php echo number_format($stats['approved_documents']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-300 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-people-fill text-indigo-600 dark:text-indigo-400 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Active Users</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110"><?php echo number_format($stats['active_users']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-400 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-hdd-fill text-amber-600 dark:text-amber-400 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200 group-hover:text-amber-600 dark:group-hover:text-amber-400">Storage Used</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110">
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

        <!-- KPI Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-150 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-hourglass-split text-yellow-600 dark:text-yellow-400 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200 group-hover:text-yellow-600 dark:group-hover:text-yellow-400">Pending Review</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110"><?php echo number_format($stats['pending_documents']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-200 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-x-circle-fill text-red-600 dark:text-red-400 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200 group-hover:text-red-600 dark:group-hover:text-red-400">Rejected</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110"><?php echo number_format($stats['rejected_documents']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-250 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-percent text-purple-600 dark:text-purple-400 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200 group-hover:text-purple-600 dark:group-hover:text-purple-400">Approval Rate</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110"><?php echo $stats['approval_rate']; ?>%</div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-300 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-stopwatch text-teal-600 dark:text-teal-400 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200 group-hover:text-teal-600 dark:group-hover:text-teal-400">Avg Approval Time</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110"><?php echo $stats['average_approval_time']; ?>h</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Extra Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-450 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-calendar-check-fill text-cyan-600 dark:text-cyan-400 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200 group-hover:text-cyan-600 dark:group-hover:text-cyan-400">New Documents (30 Days)</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110"><?php echo number_format($stats['new_documents_30days']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:scale-105 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-500 group cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="bi bi-lightning-charge-fill text-pink-600 dark:text-pink-400 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200 group-hover:text-pink-600 dark:group-hover:text-pink-400">Activities (24 Hours)</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white transform transition-all duration-300 group-hover:scale-110"><?php echo number_format($stats['activities_24h']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Documents by Type -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-500 group">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 transition-colors duration-200 group-hover:text-red-600">Documents by Type</h3>
                <div class="relative" style="height: 280px;">
                    <canvas id="documentsByTypeChart"></canvas>
                </div>
            </div>

            <!-- Documents by Status -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-600 group">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 transition-colors duration-200 group-hover:text-red-600">Documents by Status</h3>
                <div class="relative" style="height: 280px;">
                    <canvas id="documentsByStatusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Timeline Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6 transform hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-700 group">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 transition-colors duration-200 group-hover:text-red-600">Document Upload Timeline (Last 5 Years)</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="timelineChart"></canvas>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Activity by Action -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-800 group">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 transition-colors duration-200 group-hover:text-red-600">Activity by Action (30 Days)</h3>
                <div class="relative" style="height: 280px;">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>

            <!-- Documents by Department -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transform hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-900 group">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 transition-colors duration-200 group-hover:text-red-600">Documents by Department</h3>
                <div class="relative" style="height: 280px;">
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Activity Trend Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6 transform hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fade-in-up animation-delay-950 group">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 transition-colors duration-200 group-hover:text-red-600">Activity Trend (Last 30 Days)</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="activityTrendChart"></canvas>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Top Uploaders -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Top Uploaders</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">User</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Documents</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($topUploaders as $uploader): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                                <i class="bi bi-person-fill text-blue-600 dark:text-blue-400"></i>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-bold text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($uploader['full_name'] ?? $uploader['name']); ?></div>
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-widest font-bold"><?php echo htmlspecialchars($uploader['department'] ?? 'N/A'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-red-600 dark:text-red-400">
                                        <?php echo number_format($uploader['document_count']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Storage by Type -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Storage Usage by Type</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Type</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Size</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($storageByType as $type): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $type['document_type']))); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-blue-600 dark:text-blue-400">
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

            <!-- Top Approvers -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Top Approvers</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">User</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Approved</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($topApprovers as $approver): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                                <i class="bi bi-person-fill text-green-600 dark:text-green-400"></i>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-bold text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($approver['full_name'] ?? $approver['name']); ?></div>
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-widest font-bold"><?php echo htmlspecialchars($approver['department'] ?? 'N/A'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-green-600 dark:text-green-400">
                                        <?php echo number_format($approver['approved_count']); ?>
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
<div id="exportModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-end sm:items-center justify-center sm:p-4">
    <div id="exportModalContent" class="bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 dark:border-gray-800 max-h-[92vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 sm:scale-95 opacity-0 sm:opacity-100">
        <div class="sm:hidden w-full flex justify-center pt-3 pb-1"><div class="w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div></div>
        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-red-200 dark:border-gray-800 flex justify-between items-center rounded-t-2xl bg-red-50 dark:bg-gray-800/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white dark:bg-red-900/20 flex items-center justify-center shadow-sm">
                    <i class="bi bi-file-earmark-arrow-down text-red-600 text-lg"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Export Report</h3>
            </div>
            <button type="button" onclick="closeExportModal()" class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-red-100 dark:hover:bg-gray-700 text-gray-500 hover:text-gray-700 dark:hover:text-gray-200 transition-all">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>
        
        <form method="GET" action="<?php echo REPORTS_URL; ?>/api/export.php" class="p-6 overflow-y-auto flex-1 min-h-0">
            <div class="space-y-5">
                <div class="relative z-30">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Report Type</label>
                    <div class="relative custom-select-container">
                        <div id="report-type-trigger" class="w-full px-4 py-3 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all shadow-sm hover:border-red-400 dark:hover:border-gray-600 cursor-pointer flex items-center justify-between" style="min-height: 48px;">
                            <span id="report-type-value">Select Report...</span>
                            <i class="bi bi-chevron-down text-gray-400"></i>
                        </div>
                        <div id="report-type-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-700 rounded-xl shadow-xl z-[100] max-h-64 overflow-y-auto">
                            <div class="p-2 space-y-1">
                                <div class="report-type-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="">Select Report...</div>
                                <div class="report-type-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="user_activity">User Activity Report</div>
                                <div class="report-type-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="document_access">Document Access Report</div>
                                <div class="report-type-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="top_uploaders">Top Uploaders Report</div>
                            </div>
                        </div>
                        <input type="hidden" name="report_type" id="report-type-input" required>
                    </div>
                </div>
                
                <div class="relative z-30">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Export Format</label>
                    <div class="relative custom-select-container">
                        <div id="export-format-trigger" class="w-full px-4 py-3 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all shadow-sm hover:border-red-400 dark:hover:border-gray-600 cursor-pointer flex items-center justify-between" style="min-height: 48px;">
                            <span id="export-format-value">Select Format...</span>
                            <i class="bi bi-chevron-down text-gray-400"></i>
                        </div>
                        <div id="export-format-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-700 rounded-xl shadow-xl z-[100] max-h-64 overflow-y-auto">
                            <div class="p-2 space-y-1">
                                <div class="export-format-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="">Select Format...</div>
                                <div class="export-format-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="pdf">PDF Document</div>
                                <div class="export-format-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="excel">Excel Spreadsheet (.xlsx)</div>
                                <div class="export-format-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="word">Word Document (.docx)</div>
                                <div class="export-format-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="csv">CSV File</div>
                            </div>
                        </div>
                        <input type="hidden" name="format" id="export-format-input" required>
                    </div>
                </div>
                
                <div class="pt-2">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Date Range (Optional)</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Start Date</label>
                            <input type="date" name="start_date" class="w-full px-3 py-2.5 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all text-sm shadow-sm hover:border-red-400 dark:hover:border-gray-600 [color-scheme:light] dark:[color-scheme:dark]">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">End Date</label>
                            <input type="date" name="end_date" class="w-full px-3 py-2.5 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all text-sm shadow-sm hover:border-red-400 dark:hover:border-gray-600 [color-scheme:light] dark:[color-scheme:dark]">
                        </div>
                    </div>
                </div>

                <!-- Modal Footer Buttons -->
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeExportModal()" class="flex-1 px-4 py-3 border-2 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-all active:scale-95">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg shadow-red-200 dark:shadow-none transform transition-all hover:-translate-y-0.5 active:scale-95 flex items-center justify-center gap-2">
                        <i class="bi bi-download"></i>
                        Generate
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Custom Dropdown Helper Function
function initCustomDropdown(triggerId, dropdownId, valueId, inputId, optionClass, defaultValue) {
    const trigger = document.getElementById(triggerId);
    const dropdown = document.getElementById(dropdownId);
    const valueDisplay = document.getElementById(valueId);
    const hiddenInput = document.getElementById(inputId);
    const options = document.querySelectorAll(optionClass);
    
    if (!trigger || !dropdown || !valueDisplay || !hiddenInput) return;
    
    // Set initial value
    const selectedValue = hiddenInput.value;
    if (selectedValue) {
        const selectedOption = document.querySelector(`${optionClass}[data-value="${selectedValue}"]`);
        if (selectedOption) {
            valueDisplay.textContent = selectedOption.textContent;
        }
    }
    
    // Toggle dropdown
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });
    
    // Handle option selection
    options.forEach(option => {
        option.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            const text = this.textContent;
            
            valueDisplay.textContent = text;
            hiddenInput.value = value;
            dropdown.classList.add('hidden');
        });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

// Initialize custom dropdowns when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initCustomDropdown('report-type-trigger', 'report-type-dropdown', 'report-type-value', 'report-type-input', '.report-type-option', 'Select Report...');
    initCustomDropdown('export-format-trigger', 'export-format-dropdown', 'export-format-value', 'export-format-input', '.export-format-option', 'Select Format...');
});

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
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($documentsByStatus, 'status')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($documentsByStatus, 'count')); ?>,
            backgroundColor: [chartColors.green, chartColors.yellow, chartColors.blue, chartColors.red, chartColors.purple],
            cutout: '60%'
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

// Activity Trend Chart
const activityTrendChart = new Chart(document.getElementById('activityTrendChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($activityTrend, 'day')); ?>,
        datasets: [{
            label: 'Daily Activities',
            data: <?php echo json_encode(array_column($activityTrend, 'count')); ?>,
            borderColor: chartColors.purple,
            backgroundColor: 'rgba(168, 85, 247, 0.1)',
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
                    font: { size: 12 },
                    color: getLabelColor()
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    font: { size: 11 },
                    color: getLabelColor()
                },
                grid: { color: getGridColor() }
            },
            x: {
                ticks: {
                    font: { size: 11 },
                    color: getLabelColor()
                },
                grid: { color: getGridColor() }
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
                    color: getLabelColor(),
                    maxRotation: 0,
                    minRotation: 0
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
    const modal = document.getElementById('exportModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    const c=document.getElementById('exportModalContent');
    setTimeout(()=>{if(c){c.classList.remove('translate-y-full','sm:scale-95','opacity-0');c.classList.add('translate-y-0','sm:scale-100','opacity-100');}},10);
}

function closeExportModal() {
    const modal = document.getElementById('exportModal');
    const c=document.getElementById('exportModalContent');
    if(c){c.classList.add('translate-y-full','sm:scale-95','opacity-0');c.classList.remove('translate-y-0','sm:scale-100','opacity-100');}
    setTimeout(()=>{modal.classList.add('hidden');modal.classList.remove('flex');document.body.style.overflow='auto';},300);
}

// Close modal when clicking outside
document.getElementById('exportModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeExportModal();
    }
});
</script>

<script src="<?php echo asset('js/reports.js'); ?>"></script>
