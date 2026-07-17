<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

// Check authentication and session validity
checkAuth();

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
    <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-900 p-2 md:p-6">
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
                <div class="flex w-full md:w-auto gap-2 md:gap-3 animate-slide-in-right mt-3 md:mt-0">
                    <?php 
                    $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                    if (!in_array($userRole, ['viewer'])): 
                    ?>
                    <a href="<?php echo DOCUMENTS_INDEX_URL; ?>?upload=true" class="flex-1 md:flex-none justify-center !bg-white text-red-600 px-4 py-2.5 rounded-xl font-bold hover:bg-red-50 transition-all shadow-md flex items-center transform hover:scale-[1.02] active:scale-95 text-sm border-none">
                        <i class="bi bi-upload mr-2"></i>
                        <span>Upload</span>
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo SEARCH_URL; ?>/views/index.php" class="flex-1 md:flex-none justify-center bg-red-600 dark:bg-red-700 text-white px-4 py-2.5 rounded-xl font-bold hover:bg-red-700 dark:hover:bg-red-800 border border-white/10 shadow-lg transition-all flex items-center transform hover:scale-[1.02] active:scale-95 text-sm">
                        <i class="bi bi-search mr-2"></i>
                        <span>Search</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-2 md:gap-6 mb-6">
            <!-- Total Documents -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-3 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-100 group cursor-pointer border border-transparent dark:border-gray-700">
                <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-1 sm:gap-2 text-center sm:text-left">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-[10px] md:text-sm font-medium mb-0.5 transition-colors group-hover:text-red-600 dark:group-hover:text-red-400">Total Documents</p>
                        <h3 class="text-lg md:text-3xl font-bold text-gray-800 dark:text-white transform transition-all group-hover:scale-110"><?php echo number_format($stats['total_documents']); ?></h3>
                        <p class="<?php echo $stats['growth_percentage'] >= 0 ? 'text-green-600' : 'text-red-600'; ?> text-[10px] md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-arrow-<?php echo $stats['growth_percentage'] >= 0 ? 'up' : 'down'; ?>"></i> 
                            <?php echo abs($stats['growth_percentage']); ?>%
                        </p>
                    </div>
                    <div class="bg-red-100 dark:bg-red-900/30 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-file-earmark-text text-red-600 dark:text-red-500 text-sm md:text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- New Documents -->
            <a href="<?php echo DOCUMENTS_INDEX_URL; ?>" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-3 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-150 group cursor-pointer border border-transparent dark:border-gray-700">
                <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-1 sm:gap-2 text-center sm:text-left">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-[10px] md:text-sm font-medium mb-0.5 transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400">New Documents</p>
                        <h3 class="text-lg md:text-3xl font-bold text-gray-800 dark:text-white transform transition-all group-hover:scale-110"><?php echo number_format($stats['new_documents']); ?></h3>
                        <p class="text-blue-600 dark:text-blue-500 text-[10px] md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-bell"></i> unread
                        </p>
                    </div>
                    <div class="bg-blue-100 dark:bg-blue-900/30 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-bell text-blue-600 dark:text-blue-500 text-sm md:text-2xl"></i>
                    </div>
                </div>
            </a>
            
            <!-- Pending Review -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-3 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-200 group cursor-pointer border border-transparent dark:border-gray-700">
                <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-1 sm:gap-2 text-center sm:text-left">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-[10px] md:text-sm font-medium mb-0.5 transition-colors group-hover:text-yellow-600 dark:group-hover:text-yellow-400">Pending Review</p>
                        <h3 class="text-lg md:text-3xl font-bold text-gray-800 dark:text-white transform transition-all group-hover:scale-110"><?php echo number_format($stats['pending_documents']); ?></h3>
                        <p class="text-yellow-600 dark:text-yellow-500 text-[10px] md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-clock"></i> <?php echo $stats['urgent_pending']; ?> urgent
                        </p>
                    </div>
                    <div class="bg-yellow-100 dark:bg-yellow-900/30 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-hourglass-split text-yellow-600 dark:text-yellow-500 text-sm md:text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Approved Today -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-3 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-300 group cursor-pointer border border-transparent dark:border-gray-700">
                <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-1 sm:gap-2 text-center sm:text-left">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-[10px] md:text-sm font-medium mb-0.5 transition-colors group-hover:text-green-600 dark:group-hover:text-green-400">Approved Today</p>
                        <h3 class="text-lg md:text-3xl font-bold text-gray-800 dark:text-white transform transition-all group-hover:scale-110"><?php echo number_format($stats['approved_today']); ?></h3>
                        <p class="text-green-600 dark:text-green-500 text-[10px] md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-check-circle"></i> <?php echo $stats['approved_today'] > 0 ? 'On track' : 'No approvals'; ?>
                        </p>
                    </div>
                    <div class="bg-green-100 dark:bg-green-900/30 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-check-circle text-green-600 dark:text-green-500 text-sm md:text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Storage Used -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-3 md:p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-400 group cursor-pointer border border-transparent dark:border-gray-700">
                <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between gap-1 sm:gap-2 text-center sm:text-left">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-[10px] md:text-sm font-medium mb-0.5 transition-colors group-hover:text-purple-600 dark:group-hover:text-purple-400">Storage Used</p>
                        <h3 class="text-lg md:text-3xl font-bold text-gray-800 dark:text-white transform transition-all group-hover:scale-110"><?php echo $stats['storage_used_gb']; ?> <span class="text-sm md:text-lg">GB</span></h3>
                        <p class="text-gray-600 dark:text-gray-400 text-[10px] md:text-sm mt-1 hidden sm:block">
                            <i class="bi bi-hdd"></i> <?php echo $stats['storage_percentage']; ?>%
                        </p>
                    </div>
                    <div class="bg-purple-100 dark:bg-purple-900/30 rounded-full p-2 md:p-4 transform transition-all group-hover:scale-110 group-hover:rotate-3">
                        <i class="bi bi-hdd-stack text-purple-600 dark:text-purple-500 text-sm md:text-2xl"></i>
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
                    <button type="button" onclick="location.reload()" class="text-gray-500 hover:text-gray-700 text-sm" title="Refresh">
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
                                                <?php echo e(ucfirst($doc['status'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($doc['created_at'])); ?>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <button onclick="viewDocument(<?php echo $doc['id']; ?>)" 
                                               class="text-red-600 hover:text-red-700 mr-2" title="View">
                                                <i class="bi bi-eye"></i>
                                            </button>
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
                <div class="quick-actions-card bg-white dark:bg-gray-800 rounded-xl shadow-md dark:shadow-none p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-800">
                    <h2 class="quick-actions-title text-lg font-bold text-gray-800 dark:text-white mb-4">Quick Actions</h2>
                    <div class="space-y-2">
                        <?php if (!in_array($userRole, ['viewer'])): ?>
                        <a href="<?php echo DOCUMENTS_INDEX_URL; ?>?upload=true" class="quick-action-btn flex items-center p-3 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-lg transition-all duration-200">
                            <div class="icon-red bg-red-100 dark:bg-red-900/30 rounded-lg p-2 mr-3">
                                <i class="bi bi-upload text-red-600 dark:text-red-400"></i>
                            </div>
                            <span class="quick-action-label text-sm font-medium text-gray-700 dark:text-gray-300">Upload New Document</span>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo SEARCH_URL; ?>/views/index.php" class="quick-action-btn flex items-center p-3 hover:bg-green-50 dark:hover:bg-green-900/10 rounded-lg transition-all duration-200">
                            <div class="icon-green bg-green-100 dark:bg-green-900/30 rounded-lg p-2 mr-3">
                                <i class="bi bi-search text-green-600 dark:text-green-500"></i>
                            </div>
                            <span class="quick-action-label text-sm font-medium text-gray-700 dark:text-gray-300">Advanced Search</span>
                        </a>
                        <?php if (in_array($userRole, ['administrator', 'admin', 'officer'])): ?>
                        <a href="<?php echo REPORTS_URL; ?>/views/index.php" class="quick-action-btn flex items-center p-3 hover:bg-purple-50 dark:hover:bg-purple-900/10 rounded-lg transition-all duration-200">
                            <div class="icon-purple bg-purple-100 dark:bg-purple-900/30 rounded-lg p-2 mr-3">
                                <i class="bi bi-graph-up text-purple-600 dark:text-purple-400"></i>
                            </div>
                            <span class="quick-action-label text-sm font-medium text-gray-700 dark:text-gray-300">Generate Report</span>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo DOCUMENTS_INDEX_URL; ?>" class="quick-action-btn flex items-center p-3 hover:bg-orange-50 dark:hover:bg-orange-900/10 rounded-lg transition-all duration-200">
                            <div class="icon-orange bg-orange-100 dark:bg-orange-900/30 rounded-lg p-2 mr-3">
                                <i class="bi bi-folder text-orange-600 dark:text-orange-400"></i>
                            </div>
                            <span class="quick-action-label text-sm font-medium text-gray-700 dark:text-gray-300">Browse Documents</span>
                        </a>
                    </div>
                </div>
                
                <!-- System Status -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md dark:shadow-none p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-900">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">System Status</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">API Integration</span>
                            <span class="badge badge-<?php echo $systemStatus['api']['class']; ?>">
                                <i class="bi bi-<?php echo $systemStatus['api']['status'] === 'online' ? 'check-circle' : 'x-circle'; ?> mr-1"></i>
                                <?php echo e(ucfirst($systemStatus['api']['status'])); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Database</span>
                            <span class="badge badge-<?php echo $systemStatus['database']['class']; ?>">
                                <i class="bi bi-<?php echo $systemStatus['database']['status'] === 'healthy' ? 'check-circle' : 'exclamation-circle'; ?> mr-1"></i>
                                <?php echo e(ucfirst($systemStatus['database']['status'])); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Storage</span>
                            <span class="badge badge-<?php echo $systemStatus['storage']['class']; ?>">
                                <i class="bi bi-<?php echo $systemStatus['storage']['class'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?> mr-1"></i>
                                <?php echo e($systemStatus['storage']['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<!-- Document Preview Modal (same as All Documents page) -->
<div id="preview-modal" class="hidden fixed inset-0 z-[100002] overflow-y-auto">
    <div class="flex items-stretch justify-center min-h-screen sm:items-center sm:p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm pointer-events-none"></div>
        <div id="preview-modal-panel" class="modal-panel-mobile relative z-10 bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-3xl shadow-2xl max-w-6xl w-full sm:h-[85vh] sm:max-h-[85vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800">
        <div class="modal-drag-handle sm:hidden w-full flex justify-center pt-3 pb-1 bg-white dark:bg-gray-900 translate-y-[1px]" data-close-fn="closePreviewModal">
            <div class="drag-bar w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        </div>
        <div class="modal-sticky-header flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 z-10">
            <div class="flex items-center">
                <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded-xl mr-3">
                    <i class="bi bi-file-earmark-pdf text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest">Document Preview</h3>
                </div>
            </div>
            <button type="button" onclick="closePreviewModal()" class="w-11 h-11 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-all transform-none">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div id="preview-content" class="modal-body-scroll overflow-y-auto overflow-x-hidden flex-1 min-h-0 bg-white dark:bg-gray-900" style="-webkit-overflow-scrolling: touch; overscroll-behavior: contain;"></div>
        </div>
    </div>
</div>

<!-- Activity History Modal -->
<div id="activity-modal" class="hidden fixed inset-0 z-[100003] overflow-y-auto">
    <div class="flex items-stretch justify-center min-h-screen sm:items-center sm:p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm pointer-events-none"></div>
        <div id="activity-modal-content" class="modal-panel-mobile relative z-10 bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-2xl shadow-2xl max-w-lg w-full max-h-[92vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 sm:scale-95 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800">
            <div class="modal-drag-handle sm:hidden w-full flex justify-center pt-3 pb-1 bg-white dark:bg-gray-900 translate-y-[1px]" data-close-fn="closeActivityModal">
                <div class="drag-bar w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
            </div>
            <div class="modal-sticky-header flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 z-10">
                <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest">Activity History</h3>
                <button type="button" onclick="closeActivityModal()" class="w-11 h-11 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-all transform-none">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div id="activity-content" class="modal-body-scroll overflow-y-auto flex-1 min-h-0 custom-scrollbar"></div>
        </div>
    </div>
</div>

<!-- Original File Preview Modal -->
<div id="original-file-preview-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-[100004] flex items-stretch sm:items-center justify-center sm:p-4" onclick="if(event.target===this) closeOriginalFilePreviewModal()">
    <div id="original-file-preview-content" class="modal-panel-mobile bg-white dark:bg-gray-900 sm:rounded-2xl shadow-2xl max-w-6xl w-full max-h-[100dvh] sm:h-[92vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 sm:scale-95 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800">
        <div class="modal-drag-handle sm:hidden w-full flex justify-center pt-3 pb-1 bg-white dark:bg-gray-900 translate-y-[1px]" data-close-fn="closeOriginalFilePreviewModal"><div class="drag-bar w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div></div>
        <div class="mobile-preview-header flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex-shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex-shrink-0">
                    <i class="bi bi-file-earmark-text text-emerald-600 dark:text-emerald-400 text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 id="original-file-preview-title" class="header-title text-xs sm:text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest truncate">Document Preview</h3>
                    <p id="original-file-preview-type" class="hidden sm:block text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider"></p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a id="original-file-preview-newtab" href="#" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <i class="bi bi-box-arrow-up-right"></i> New Tab
                </a>
                <a id="original-file-preview-download" href="#" class="inline-flex items-center justify-center gap-1.5 h-11 w-11 sm:w-auto sm:px-4 rounded-lg text-[10px] font-bold uppercase tracking-wider text-white bg-red-600 hover:bg-red-700 transition-colors">
                    <i class="bi bi-download text-base"></i>
                    <span class="hidden sm:inline">Download</span>
                </a>
                <button type="button" onclick="closeOriginalFilePreviewModal()" class="h-11 w-11 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                    <i class="bi bi-x-lg text-base"></i>
                </button>
            </div>
        </div>
        <div id="original-file-preview-body" class="mobile-preview-body flex-1 overflow-y-auto overflow-x-hidden bg-gray-100 dark:bg-gray-950 min-h-0" style="-webkit-overflow-scrolling: touch; overscroll-behavior: contain;"></div>
    </div>
</div>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>

<script src="<?php echo asset('js/document-view-modal.js'); ?>?v=<?php echo time(); ?>"></script>
<script>
// User role for access control in view modal
var currentUserRole = '<?php echo $userRole; ?>';

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

// Mobile-friendly original-file preview (overrides document-view-modal.js default)
function openOriginalFilePreviewModal(docId, fileName, fileType) {
    const modal = document.getElementById('original-file-preview-modal');
    const body = document.getElementById('original-file-preview-body');
    const titleEl = document.getElementById('original-file-preview-title');
    const typeEl = document.getElementById('original-file-preview-type');
    const newTabLink = document.getElementById('original-file-preview-newtab');
    const downloadLink = document.getElementById('original-file-preview-download');

    const previewUrl = App.apiUrl('documents', 'preview.php?id=' + docId);
    const downloadUrl = App.apiUrl('documents', 'download.php?id=' + docId);

    titleEl.textContent = fileName || 'Document Preview';
    typeEl.textContent = (fileType || '').replace('application/', '').replace('image/', 'img/');

    newTabLink.href = previewUrl;
    downloadLink.href = downloadUrl;

    const ft = (fileType || '').toLowerCase();
    const ext = ((fileName || '').split('.').pop() || '').toLowerCase();
    const isPdf = ft === 'application/pdf' || ft === 'pdf' || ext === 'pdf';
    const isDocx = ft === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || ft === 'word' || ext === 'docx' || ext === 'doc';
    const isImage = ft.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(ft.replace('image/', '')) || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(ext);

    if (isPdf || isDocx) {
        const wrapper = document.createElement('div');
        wrapper.className = 'preview-iframe-wrapper relative w-full h-full';
        const loader = document.createElement('div');
        loader.className = 'iframe-loader';
        loader.innerHTML = '<div class="inline-block w-8 h-8 border-4 border-gray-200 border-t-red-600 rounded-full animate-spin"></div>';
        const iframe = document.createElement('iframe');
        iframe.src = previewUrl;
        iframe.title = 'Document Preview';
        iframe.setAttribute('frameborder', '0');
        iframe.setAttribute('scrolling', 'auto');
        iframe.setAttribute('allowfullscreen', '');
        iframe.className = 'preview-iframe w-full h-full border-0 block';
        iframe.onload = function() { loader.remove(); };
        wrapper.appendChild(loader);
        wrapper.appendChild(iframe);
        body.innerHTML = '';
        body.appendChild(wrapper);
    } else if (isImage) {
        body.innerHTML = '<div class="flex items-center justify-center h-full p-4 overflow-auto"><img src="' + previewUrl + '" alt="' + escapeHtml(fileName) + '" class="max-w-full max-h-full object-contain rounded-lg shadow-lg"></div>';
    } else {
        const ext = (fileName || '').split('.').pop().toUpperCase();
        body.innerHTML =
            '<div class="flex flex-col items-center justify-center h-full p-12 text-center">' +
                '<div class="w-20 h-20 rounded-2xl bg-gray-200 dark:bg-gray-800 flex items-center justify-center mb-5">' +
                    '<i class="bi bi-file-earmark-x text-4xl text-gray-400 dark:text-gray-600"></i>' +
                '</div>' +
                '<h4 class="text-base font-bold text-gray-700 dark:text-gray-300 mb-2">Cannot preview ' + ext + ' files in browser</h4>' +
                '<p class="text-sm text-gray-400 dark:text-gray-500 max-w-md mb-6">This file type cannot be displayed directly in the web browser. You can download it to view the full document.</p>' +
                '<a href="' + downloadUrl + '" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold uppercase tracking-widest text-[11px] shadow-lg transition-all active:scale-95">' +
                    '<i class="bi bi-download text-base"></i> Download File' +
                '</a>' +
            '</div>';
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    const content = document.getElementById('original-file-preview-content');
    setTimeout(function() {
        if (content) {
            content.classList.remove('translate-y-full', 'sm:scale-95', 'opacity-0');
            content.classList.add('translate-y-0', 'sm:scale-100', 'opacity-100');
        }
    }, 10);
}

// Swipe-to-close gesture for mobile modal drag handles
function setupSwipeToClose() {
    const handles = document.querySelectorAll('[data-close-fn]');
    handles.forEach(function(handle) {
        let startY = 0;
        let startTime = 0;
        handle.addEventListener('touchstart', function(e) {
            startY = e.touches[0].clientY;
            startTime = Date.now();
        }, { passive: true });
        handle.addEventListener('touchend', function(e) {
            const endY = e.changedTouches[0].clientY;
            const diffY = endY - startY;
            const elapsed = Date.now() - startTime;
            if (diffY > 60 && elapsed < 600) {
                const fnName = handle.getAttribute('data-close-fn');
                if (typeof window[fnName] === 'function') {
                    window[fnName]();
                }
            }
        }, { passive: true });
    });
}
setupSwipeToClose();
</script>
