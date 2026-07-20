<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

checkAuth();

// Load controller
require_once __DIR__ . '/../controllers/DocumentController.php';
$controller = new DocumentController();
$data = $controller->index();

$pageTitle = 'Document Management';
$currentPage = 'documents';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => DASHBOARD_INDEX_URL],
    ['label' => 'Documents']
];

// Get user role for JavaScript
$userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));

// Helper functions
function getFileTypeByExt($fileName) {
    $ext = strtolower(pathinfo($fileName ?: '', PATHINFO_EXTENSION));
    $map = [
        'pdf' => 'pdf', 'doc' => 'word', 'docx' => 'word',
        'xls' => 'excel', 'xlsx' => 'excel', 'csv' => 'excel',
        'ppt' => 'powerpoint', 'pptx' => 'powerpoint'
    ];
    return $map[$ext] ?? null;
}

function getFileIcon($mimeType, $fileName = '') {
    if (strpos($mimeType, 'pdf') !== false) return 'bi bi-file-pdf text-red-600';
    if (strpos($mimeType, 'word') !== false) return 'bi bi-file-word text-blue-600';
    if (strpos($mimeType, 'excel') !== false || strpos($mimeType, 'spreadsheet') !== false) return 'bi bi-file-excel text-green-600';
    if (strpos($mimeType, 'powerpoint') !== false || strpos($mimeType, 'presentation') !== false) return 'bi bi-file-ppt text-orange-600';
    // Fallback: detect by file extension
    $extType = getFileTypeByExt($fileName);
    if ($extType === 'pdf') return 'bi bi-file-pdf text-red-600';
    if ($extType === 'word') return 'bi bi-file-word text-blue-600';
    if ($extType === 'excel') return 'bi bi-file-excel text-green-600';
    if ($extType === 'powerpoint') return 'bi bi-file-ppt text-orange-600';
    return 'bi bi-file-earmark text-gray-600';
}

function getFileIconClass($mimeType, $fileName = '') {
    if (strpos($mimeType, 'pdf') !== false) return 'bg-red-100 dark:bg-red-900/30';
    if (strpos($mimeType, 'word') !== false) return 'bg-blue-100 dark:bg-blue-900/30';
    if (strpos($mimeType, 'excel') !== false || strpos($mimeType, 'spreadsheet') !== false) return 'bg-green-100 dark:bg-green-900/30';
    if (strpos($mimeType, 'powerpoint') !== false || strpos($mimeType, 'presentation') !== false) return 'bg-orange-100 dark:bg-orange-900/30';
    // Fallback: detect by file extension
    $extType = getFileTypeByExt($fileName);
    if ($extType === 'pdf') return 'bg-red-100 dark:bg-red-900/30';
    if ($extType === 'word') return 'bg-blue-100 dark:bg-blue-900/30';
    if ($extType === 'excel') return 'bg-green-100 dark:bg-green-900/30';
    if ($extType === 'powerpoint') return 'bg-orange-100 dark:bg-orange-900/30';
    return 'bg-gray-100 dark:bg-gray-800';
}

function getStatusBadge($status, $compact = false) {
    if ($compact) {
        $badges = [
            'draft' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300"><i class="bi bi-pencil"></i>Draft</span>',
            'pending' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300"><i class="bi bi-clock"></i>Pending</span>',
            'approved' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"><i class="bi bi-check-circle"></i>Approved</span>',
            'rejected' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300"><i class="bi bi-x-circle"></i>Rejected</span>',
            'archived' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-gray-500 text-white dark:bg-gray-600"><i class="bi bi-archive"></i>Archived</span>'
        ];
        return $badges[$status] ?? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">' . ucfirst($status) . '</span>';
    }

    $badges = [
        'draft' => '<span class="badge badge-secondary"><i class="bi bi-pencil mr-1"></i>Draft</span>',
        'pending' => '<span class="badge badge-warning"><i class="bi bi-clock mr-1"></i>Pending</span>',
        'approved' => '<span class="badge badge-success"><i class="bi bi-check-circle mr-1"></i>Approved</span>',
        'rejected' => '<span class="badge badge-danger"><i class="bi bi-x-circle mr-1"></i>Rejected</span>',
        'archived' => '<span class="badge bg-gray-500 text-white"><i class="bi bi-archive mr-1"></i>Archived</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-info">' . ucfirst($status) . '</span>';
}

function getOcrBadge($ocrStatus, $compact = false) {
    if ($compact) {
        $badges = [
            'completed' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300" title="OCR Completed"><i class="bi bi-check-circle"></i>OCR</span>',
            'pending' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300" title="OCR Pending"><i class="bi bi-hourglass-split"></i>OCR</span>',
            'processing' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300" title="OCR Processing"><i class="bi bi-arrow-repeat"></i>OCR</span>',
            'failed' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300" title="OCR Failed"><i class="bi bi-x-circle"></i>OCR</span>',
            'skipped' => '',
        ];
        return $badges[$ocrStatus] ?? '';
    }

    $badges = [
        'completed' => '<span class="badge badge-success text-[10px]" title="OCR Completed"><i class="bi bi-check-circle mr-0.5"></i>OCR</span>',
        'pending' => '<span class="badge badge-warning text-[10px]" title="OCR Pending"><i class="bi bi-hourglass-split mr-0.5"></i>OCR</span>',
        'processing' => '<span class="badge badge-info text-[10px]" title="OCR Processing"><i class="bi bi-arrow-repeat mr-0.5"></i>OCR</span>',
        'failed' => '<span class="badge badge-danger text-[10px]" title="OCR Failed"><i class="bi bi-x-circle mr-0.5"></i>OCR</span>',
        'skipped' => '',
    ];
    return $badges[$ocrStatus] ?? '';
}

function getComplianceBadge($complianceStatus, $compact = false) {
    $status = $complianceStatus ?? 'pending';
    if ($compact) {
        $badges = [
            'pending' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300" title="Compliance check pending"><i class="bi bi-hourglass-split"></i>Pending</span>',
            'compliant' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300" title="Compliant"><i class="bi bi-shield-check"></i>Compliant</span>',
            'non_compliant' => '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-semibold uppercase tracking-wide whitespace-nowrap bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300" title="Non-Compliant"><i class="bi bi-shield-exclamation"></i>Non-Comp</span>',
        ];
        return $badges[$status] ?? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Unknown</span>';
    }

    $badges = [
        'pending' => '<span class="badge badge-warning" title="Compliance check pending"><i class="bi bi-hourglass-split mr-1"></i>Pending</span>',
        'compliant' => '<span class="badge badge-success" title="Compliant"><i class="bi bi-shield-check mr-1"></i>Compliant</span>',
        'non_compliant' => '<span class="badge badge-danger" title="Non-Compliant"><i class="bi bi-shield-exclamation mr-1"></i>Non-Compliant</span>',
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-900 p-6 animate-fade-in">
        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Document Management</h1>
                    <p class="text-gray-600 dark:text-gray-400">Manage all legislative documents in one place</p>
                </div>
                <div class="flex gap-3 relative">
                    <?php 
                    $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                    if (!in_array($userRole, ['viewer'])): 
                    ?>
                    <button type="button" onclick="openUploadModal()" class="no-ripple inline-flex items-center justify-center px-4 py-2 !bg-white !text-red-600 border border-red-600 rounded-lg font-bold hover:shadow-lg transition-shadow duration-200 shadow-sm min-w-[165px] h-10 flex-shrink-0 transform-none hover:transform-none active:transform-none">
                        <i class="bi bi-plus-circle mr-2"></i>
                        Upload Document
                    </button>
                    <?php endif; ?>
                    <?php if ($userRole !== 'viewer'): ?>
                    <div class="relative" id="export-dropdown">
                        <button type="button" onclick="toggleExportMenu(event)" class="no-ripple inline-flex items-center justify-center px-4 py-2 bg-red-600 dark:bg-red-700 text-white rounded-lg font-bold hover:shadow-lg transition-shadow duration-200 shadow-sm min-w-[120px] h-10 flex-shrink-0 transform-none hover:transform-none active:transform-none">
                            <i class="bi bi-download mr-2"></i>
                            Export
                            <i class="bi bi-chevron-down ml-2 transition-transform" id="export-chevron"></i>
                        </button>
                        <div id="export-menu" class="hidden bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700" style="position: fixed; width: 224px; z-index: 99999;">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide bg-gray-50 dark:bg-gray-900/50 border-b dark:border-gray-700">Export List</div>
                            <button type="button" onclick="exportList('csv')" 
                               class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-400 flex items-center transition-colors duration-150">
                                <i class="bi bi-filetype-csv mr-3 text-green-600 dark:text-green-500 text-lg"></i>
                                Export as CSV
                            </button>
                            <button type="button" onclick="exportList('excel')" 
                               class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-400 flex items-center transition-colors duration-150">
                                <i class="bi bi-file-earmark-excel mr-3 text-green-600 dark:text-green-500 text-lg"></i>
                                Export as Excel
                            </button>
                            <div class="border-t border-gray-200 dark:border-gray-700"></div>
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide bg-gray-50 dark:bg-gray-900/50 border-b dark:border-gray-700">Export Files</div>
                            <button type="button" onclick="exportSelectedFiles()" 
                               class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-400 flex items-center transition-colors duration-150">
                                <i class="bi bi-file-earmark-zip mr-3 text-blue-600 dark:text-blue-500 text-lg"></i>
                                Selected Files (ZIP)
                            </button>
                            <button type="button" onclick="exportAllFiles()" 
                               class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-400 flex items-center transition-colors duration-150">
                                <i class="bi bi-archive mr-3 text-purple-600 dark:text-purple-500 text-lg"></i>
                                All Files (ZIP)
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Mobile Filter Toggle Button -->
        <button id="mobile-filter-toggle" class="md:hidden w-full bg-white dark:bg-gray-800 rounded-xl shadow-md p-4 mb-4 flex items-center justify-between text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
            <span class="flex items-center">
                <i class="bi bi-funnel mr-2 text-red-600 dark:text-red-500"></i>
                <span class="font-medium">Filters & Search</span>
            </span>
            <i class="bi bi-chevron-down transition-transform" id="filter-toggle-icon"></i>
        </button>
        
        <!-- Filters Section -->
        <div id="filters-section" class="relative z-50 bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 md:p-6 mb-6 animate-fade-in-up hidden md:block border border-transparent dark:border-gray-800">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="sm:col-span-2 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Documents</label>
                    <div class="relative group">
                        <input type="text" 
                               id="main-search"
                               placeholder="Search by title, reference, or keywords..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                               class="w-full pl-10 pr-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200">
                        <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500 transition-all group-focus-within:text-red-500 group-focus-within:scale-110"></i>
                    </div>
                </div>
                
                <!-- Document Type Filter -->
                <div class="relative z-40">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Document Type</label>
                    <div class="relative custom-select-container">
                        <div id="type-filter-trigger" class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 hover:border-red-300 dark:hover:border-red-900 cursor-pointer flex items-center justify-between">
                            <span id="type-filter-value">All Types</span>
                            <i class="bi bi-chevron-down text-gray-400"></i>
                        </div>
                        <div id="type-filter-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-xl z-[100] max-h-64 overflow-y-auto">
                            <div class="p-2 space-y-1">
                                <div class="type-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm text-gray-700 dark:text-gray-200 transition-colors" data-value="">All Types</div>
                                <?php 
                                $types = ['ordinance', 'resolution', 'session', 'agenda', 'committee', 'other'];
                                $selectedType = $_GET['type'] ?? '';
                                foreach ($types as $t): ?>
                                    <div class="type-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm text-gray-700 dark:text-gray-200 transition-colors" data-value="<?php echo $t; ?>">
                                        <?php echo ucfirst($t === 'session' ? 'session minutes' : $t); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <input type="hidden" id="type-filter-input" name="type" value="<?php echo $selectedType; ?>">
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="relative z-40">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <div class="relative custom-select-container">
                        <div id="status-filter-trigger" class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 cursor-pointer flex items-center justify-between">
                            <span id="status-filter-value">All Status</span>
                            <i class="bi bi-chevron-down text-gray-400"></i>
                        </div>
                        <div id="status-filter-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-xl z-[100] max-h-64 overflow-y-auto">
                            <div class="p-2 space-y-1">
                                <div class="status-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm text-gray-700 dark:text-gray-200 transition-colors" data-value="">All Status</div>
                                <?php 
                                $statuses = [
                                    'draft' => 'Draft',
                                    'pending' => 'Pending Review',
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                    'archived' => 'Archived'
                                ];
                                $selectedStatus = $_GET['status'] ?? '';
                                foreach ($statuses as $value => $label): ?>
                                    <div class="status-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm text-gray-700 dark:text-gray-200 transition-colors" data-value="<?php echo $value; ?>">
                                        <?php echo $label; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <input type="hidden" id="status-filter-input" name="status" value="<?php echo $selectedStatus; ?>">
                    </div>
                </div>
            </div>
            
            <!-- Apply Filters Button -->
            <div class="mt-4 flex justify-end">
                <button type="button" onclick="applyFilters()" class="inline-flex items-center px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all shadow-sm font-bold">
                    <i class="bi bi-funnel-fill mr-2"></i>
                    Apply Filters
                </button>
            </div>
            
            <!-- Advanced Filters Toggle -->
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                <button id="advanced-filters-btn" type="button" onclick="toggleAdvancedFilters()" class="inline-flex items-center px-4 py-2 bg-red-50 dark:bg-red-900/10 text-red-700 dark:text-red-500 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/20 transition-all duration-200 font-bold border border-red-100 dark:border-red-900/30 shadow-sm cursor-pointer active:scale-95">
                    <i class="bi bi-funnel mr-2"></i>
                    Advanced Filters
                    <i class="bi bi-chevron-down ml-2 transition-transform duration-300" id="advanced-filters-chevron"></i>
                </button>
            </div>

            <!-- Advanced Filters Panel -->
            <div id="advanced-filters-panel" class="hidden mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date From</label>
                        <input type="date" id="filter-date-from" value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200">
                    </div>
                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date To</label>
                        <input type="date" id="filter-date-to" value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200">
                    </div>
                    <!-- Reference Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reference No.</label>
                        <input type="text" id="filter-reference" placeholder="e.g. 2023-001" value="<?php echo htmlspecialchars($_GET['reference'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200">
                    </div>
                    <!-- Tags -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tags</label>
                        <input type="text" id="filter-tags" placeholder="e.g. budget, land" value="<?php echo htmlspecialchars($_GET['tags'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200">
                    </div>
                    <!-- Compliance Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Compliance</label>
                        <select id="filter-compliance" class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200">
                            <option value="">All</option>
                            <option value="pending" <?php echo (($_GET['compliance_status'] ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="compliant" <?php echo (($_GET['compliance_status'] ?? '') === 'compliant') ? 'selected' : ''; ?>>Compliant</option>
                            <option value="non_compliant" <?php echo (($_GET['compliance_status'] ?? '') === 'non_compliant') ? 'selected' : ''; ?>>Non-Compliant</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4 flex flex-col sm:flex-row justify-end gap-3">
                    <button type="button" onclick="clearAdvancedFilters()" class="w-full sm:w-auto px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors bg-transparent border-0 font-medium">
                        Clear All
                    </button>
                    <button type="button" onclick="applyAdvancedFilters()" class="w-full sm:w-auto px-6 py-2.5 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-all shadow-sm font-bold flex items-center justify-center gap-2">
                        <i class="bi bi-check2-circle"></i>
                        Apply Advanced Filters
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Documents Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 animate-fade-in-up border border-transparent dark:border-gray-700">
            <!-- Table Header Actions -->
            <div class="px-4 md:px-6 py-3 md:py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50">
                <div class="flex flex-wrap items-center justify-between gap-y-4">
                    <!-- Left: Select All -->
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer group">
                            <div class="relative flex items-center justify-center">
                                <input type="checkbox" id="select-all-top" class="peer h-6 w-6 cursor-pointer appearance-none rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 checked:bg-red-600 checked:border-red-600 transition-all focus:ring-0 focus:ring-offset-0" onchange="toggleSelectAll(this)">
                                <i class="bi bi-check absolute text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity text-xl"></i>
                            </div>
                            <span class="ml-3 text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-red-600 transition-colors">Select All</span>
                        </label>
                    </div>
                    
                    <!-- Right: Bulk Actions -->
                    <div class="flex items-center gap-2">
                        <?php if ($userRole !== 'viewer'): ?>
                        <button class="w-10 h-10 flex items-center justify-center text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl transition-all" title="Download Selected">
                            <i class="bi bi-download text-lg"></i>
                        </button>
                        <?php endif; ?>
                        <?php if (in_array($userRole, ['administrator', 'officer'])): ?>
                        <button class="w-10 h-10 flex items-center justify-center text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 border border-red-100 dark:border-red-900/30 rounded-xl transition-all" title="Delete Selected">
                            <i class="bi bi-trash text-lg"></i>
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Bottom: Document Count (Full width on mobile) -->
                    <div class="w-full flex items-center justify-center pt-2 sm:pt-0 sm:w-auto sm:absolute sm:left-1/2 sm:-translate-x-1/2">
                        <div class="inline-flex items-center px-4 py-1.5 bg-gray-100 dark:bg-gray-900/80 text-gray-600 dark:text-gray-400 rounded-full border border-gray-200 dark:border-gray-700/50 text-[11px] font-black uppercase tracking-[0.1em] shadow-inner" id="selected-count">
                            <span id="total-docs" class="text-gray-900 dark:text-white mr-1"><?php echo count($data['documents'] ?? []); ?></span> documents found
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Table -->
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-2 py-2 text-left w-12">
                                <!-- Redundant checkbox removed -->
                            </th>
                            <th class="px-2 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">
                                Document
                            </th>
                            <th class="px-2 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-2 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">
                                Reference
                            </th>
                            <th class="px-2 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-2 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">
                                Compliance
                            </th>
                            <th class="px-2 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-2 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">
                                Size
                            </th>
                            <th class="px-2 py-2 text-right text-[10px] font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (isset($data['error'])): ?>
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="text-red-600">
                                        <i class="bi bi-exclamation-circle text-4xl mb-2"></i>
                                        <p><?php echo htmlspecialchars($data['message']); ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php elseif (empty($data['documents'])): ?>
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="text-gray-500">
                                        <i class="bi bi-inbox text-4xl mb-2"></i>
                                        <p>No documents found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['documents'] as $doc): ?>
                                <!-- Table Row -->
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" data-document-id="<?php echo $doc['id']; ?>">
                                    <td class="px-2 py-2 w-12 text-center">
                                        <input type="checkbox" class="document-checkbox w-4 h-4 text-red-600 dark:text-red-500 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded focus:ring-red-500 cursor-pointer" value="<?php echo $doc['id']; ?>">
                                    </td>
                                    <td class="px-4 md:px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="<?php echo getFileIconClass($doc['file_type'], $doc['file_name']); ?> rounded-lg p-1.5 mr-2 flex-shrink-0">
                                                <i class="<?php echo getFileIcon($doc['file_type'], $doc['file_name']); ?> text-lg"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-medium text-gray-900 dark:text-white truncate"><?php echo htmlspecialchars($doc['title']); ?></p>
                                                <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate"><?php echo htmlspecialchars($doc['file_name']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                        <span class="badge badge-primary"><?php echo e(ucfirst($doc['document_type'])); ?></span>
                                    </td>
                                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo htmlspecialchars($doc['reference_number']); ?>
                                    </td>
                                    <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap items-center gap-1">
                                            <?php echo getStatusBadge($doc['status']); ?>
                                            <?php echo getOcrBadge($doc['ocr_status'] ?? ''); ?>
                                        </div>
                                        <?php if (!empty($doc['status_changed_by_name'])): ?>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5" title="<?php echo !empty($doc['status_changed_at']) ? date('M d, Y H:i', strtotime($doc['status_changed_at'])) : ''; ?>">
                                            by <?php echo htmlspecialchars($doc['status_changed_by_name']); ?>
                                        </p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                        <?php echo getComplianceBadge($doc['compliance_status'] ?? 'pending'); ?>
                                        <?php if (($doc['compliance_status'] ?? 'pending') === 'pending' && strtolower(trim($_SESSION['user_role'] ?? 'viewer')) !== 'viewer'): ?>
                                        <button type="button" onclick="checkCompliance(<?php echo $doc['id']; ?>, this)" class="ml-1 inline-flex items-center p-1 text-xs text-blue-600 hover:text-blue-800" title="Run compliance check">
                                            <i class="bi bi-shield-check"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo date('M d, Y', strtotime($doc['document_date'])); ?>
                                    </td>
                                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo formatFileSize($doc['file_size']); ?>
                                    </td>
                                    <td class="px-2 py-2 text-right text-xs font-medium">
                                        <div class="flex justify-end gap-2">
                                            <button type="button" class="no-ripple inline-flex items-center justify-center bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 px-2 py-1 text-xs rounded-lg font-semibold transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-sm hover:shadow-md dark:shadow-none dark:hover:shadow-none" title="View" onclick="viewDocument(<?php echo $doc['id']; ?>)"><i class="bi bi-eye mr-1"></i> View</button>
                                            <?php 
                                            $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                                            if ($userRole !== 'viewer'): 
                                            ?>
                                            <a href="<?php echo DOCUMENTS_URL; ?>/api/download.php?id=<?php echo $doc['id']; ?>" class="no-ripple inline-flex items-center justify-center bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/50 px-2 py-1 text-xs rounded-lg font-semibold transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-sm hover:shadow-md dark:shadow-none dark:hover:shadow-none" title="Download"><i class="bi bi-download mr-1"></i> Download</a>
                                            <?php endif; ?>
                                            <?php 
                                            $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                                            $isDocOwner = ($doc['uploaded_by'] ?? 0) == ($_SESSION['user_id'] ?? 0);
                                            $isApproved = ($doc['status'] ?? '') === 'approved';
                                            $canEdit = (in_array($userRole, ['super_admin', 'superadmin', 'administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner)) && !$isApproved;
                                            $canDelete = (in_array($userRole, ['super_admin', 'superadmin', 'administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner)) && !$isApproved;
                                            ?>
                                            <?php if ($canEdit || $canDelete): ?>
                                            <div class="relative inline-block group">
                                                <button type="button" class="no-ripple inline-flex items-center justify-center bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/50 px-2 py-1 text-xs rounded-lg font-semibold transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-sm hover:shadow-md dark:shadow-none dark:hover:shadow-none">
                                                    <i class="bi bi-pencil mr-1"></i> Edit <i class="bi bi-caret-down-fill ml-1 text-[9px]"></i>
                                                </button>
                                                <div class="hidden group-hover:block absolute right-0 top-full mt-1 w-28 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-10 overflow-hidden">
                                                    <?php if ($canEdit): ?>
                                                    <button type="button" onclick="editDocument(<?php echo $doc['id']; ?>)" class="w-full text-left px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if ($canDelete): ?>
                                                    <button type="button" onclick="deleteDocument(<?php echo $doc['id']; ?>)" class="w-full text-left px-3 py-2 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
                
            <!-- Mobile Card View -->
            <div class="md:hidden space-y-4 p-2">
                <?php if (!empty($data['documents'])): ?>
                    <?php foreach ($data['documents'] as $doc): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mobile-doc-card transition-all active:scale-[0.98]" data-document-id="<?php echo $doc['id']; ?>">
                            <!-- Top: Type, Date & Status -->
                            <div class="px-4 py-3 bg-gray-50/50 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-700/50 flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" class="document-checkbox w-5 h-5 text-red-600 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-red-500 cursor-pointer bg-white dark:bg-gray-800" value="<?php echo $doc['id']; ?>">
                                    <span class="badge badge-primary !text-[10px] !py-0.5">
                                        <?php echo e(ucfirst($doc['document_type'])); ?>
                                    </span>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider">
                                        <i class="bi bi-calendar-event mr-1"></i><?php echo date('M d, Y', strtotime($doc['document_date'])); ?>
                                    </span>
                                    <div class="flex flex-nowrap items-center gap-1 overflow-x-auto">
                                        <?php echo getStatusBadge($doc['status'], true); ?>
                                        <?php echo getOcrBadge($doc['ocr_status'] ?? '', true); ?>
                                        <?php echo getComplianceBadge($doc['compliance_status'] ?? 'pending', true); ?>
                                        <?php if (($doc['compliance_status'] ?? 'pending') === 'pending' && strtolower(trim($_SESSION['user_role'] ?? 'viewer')) !== 'viewer'): ?>
                                        <button type="button" onclick="checkCompliance(<?php echo $doc['id']; ?>, this)" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 text-[10px] active:scale-95" title="Run compliance check">
                                            <i class="bi bi-shield-check"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Middle: Title, Filename & Icon -->
                            <div class="p-4 flex flex-col items-center gap-3">
                                <div class="min-w-0 w-full">
                                    <h4 class="text-sm font-black text-gray-900 dark:text-gray-100 mb-1 leading-tight line-clamp-2"><?php echo htmlspecialchars($doc['title']); ?></h4>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate font-medium opacity-80"><?php echo htmlspecialchars($doc['file_name']); ?></p>
                                </div>
                                <div class="w-full h-16 rounded-2xl <?php echo getFileIconClass($doc['file_type'], $doc['file_name']); ?> flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <i class="<?php echo getFileIcon($doc['file_type'], $doc['file_name']); ?> text-3xl"></i>
                                </div>
                            </div>

                            <!-- Bottom: Actions & Metadata -->
                            <div class="px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-50 dark:border-gray-700/50 flex items-center justify-between gap-3">
                                <?php if (!empty($doc['status_changed_by_name'])): ?>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate" title="<?php echo !empty($doc['status_changed_at']) ? date('M d, Y H:i', strtotime($doc['status_changed_at'])) : ''; ?>">
                                    by <?php echo htmlspecialchars($doc['status_changed_by_name']); ?>
                                </span>
                                <?php else: ?>
                                <span></span>
                                <?php endif; ?>
                                <?php 
                                $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                                $isDocOwner = ($doc['uploaded_by'] ?? 0) == ($_SESSION['user_id'] ?? 0);
                                $isApproved = ($doc['status'] ?? '') === 'approved';
                                $canEdit = (in_array($userRole, ['super_admin', 'superadmin', 'administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner)) && !$isApproved;
                                $canDelete = $canEdit;
                                ?>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-sm font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 active:scale-95 transition-all" title="View" onclick="viewDocument(<?php echo $doc['id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <?php if ($userRole !== 'viewer'): ?>
                                    <a href="<?php echo DOCUMENTS_URL; ?>/api/download.php?id=<?php echo $doc['id']; ?>" class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-sm font-bold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 active:scale-95 transition-all" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($canEdit): ?>
                                    <button type="button" class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-sm font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 active:scale-95 transition-all" title="Edit" onclick="editDocument(<?php echo $doc['id']; ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($canDelete): ?>
                                    <button type="button" class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-sm font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 active:scale-95 transition-all" title="Delete" onclick="deleteDocument(<?php echo $doc['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-3xl flex items-center justify-center mx-auto mb-4">
                            <i class="bi bi-file-earmark-text text-4xl text-gray-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No documents found</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Try adjusting your filters or search keywords</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if (isset($data['pagination']) && $data['pagination']['total_pages'] > 1): ?>
                <?php
                    $otherParams = array_diff_key($_GET, ['page' => '']);
                    $queryString = http_build_query($otherParams);
                    $querySeparator = $queryString ? '&' : '';
                ?>
                <div class="px-4 md:px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400 order-2 sm:order-1">
                        Showing <span class="font-medium text-gray-900 dark:text-white"><?php echo (($data['pagination']['current_page'] - 1) * $data['pagination']['per_page']) + 1; ?></span> 
                        to <span class="font-medium text-gray-900 dark:text-white"><?php echo min($data['pagination']['current_page'] * $data['pagination']['per_page'], $data['pagination']['total']); ?></span> 
                        of <span class="font-medium text-gray-900 dark:text-white"><?php echo number_format($data['pagination']['total']); ?></span> results
                    </div>
                    <div class="flex items-center gap-1.5 order-1 sm:order-2">
                        <?php if ($data['pagination']['current_page'] > 1): ?>
                            <a href="?page=<?php echo $data['pagination']['current_page'] - 1; ?><?php echo $querySeparator . $queryString; ?>" 
                               class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        <?php else: ?>
                            <button class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-400 dark:text-gray-600 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg opacity-50 cursor-not-allowed" disabled>
                                <i class="bi bi-chevron-left"></i>
                            </button>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $data['pagination']['current_page'] - 2); $i <= min($data['pagination']['total_pages'], $data['pagination']['current_page'] + 2); $i++): ?>
                            <?php if ($i == $data['pagination']['current_page']): ?>
                                <button class="inline-flex items-center justify-center w-9 h-9 text-sm font-semibold text-white bg-red-600 rounded-lg shadow-sm"><?php echo $i; ?></button>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?><?php echo $querySeparator . $queryString; ?>" 
                                   class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($data['pagination']['current_page'] < $data['pagination']['total_pages']): ?>
                            <a href="?page=<?php echo $data['pagination']['current_page'] + 1; ?><?php echo $querySeparator . $queryString; ?>" 
                               class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <button class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-400 dark:text-gray-600 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg opacity-50 cursor-not-allowed" disabled>
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>

<style>
.doc-preview-page {
    font-family: 'Georgia', 'Times New Roman', serif;
    line-height: 1.8;
    color: #1a1a1a;
    max-width: 100%;
    margin: 0 auto;
}
.doc-preview-page .doc-content h2 {
    font-family: 'Georgia', 'Times New Roman', serif;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.05em;
}
.doc-preview-page .doc-content h3 {
    font-family: 'Georgia', 'Times New Roman', serif;
    font-size: 13px;
    font-weight: 700;
}
.doc-preview-page .doc-content p {
    text-align: justify;
    hyphens: auto;
}
.doc-preview-page .doc-content ol,
.doc-preview-page .doc-content ul {
    margin-left: 0;
    padding-left: 1.5rem;
}
.doc-preview-page .doc-content li {
    margin-bottom: 0.4rem;
}
.doc-preview-page .doc-content ol li::marker {
    font-weight: 600;
}

@media (max-width: 640px) {
    #original-file-preview-content {
        height: 100% !important;
        max-height: 100% !important;
        position: relative !important;
        top: auto !important;
        bottom: auto !important;
        margin: 0 !important;
        border-radius: 0 !important;
    }
}
</style>

<script src="<?php echo asset('js/documents.js'); ?>?v=<?php echo time(); ?>"></script>
<script>
// User role for access control
const currentUserRole = '<?php echo $userRole; ?>';

// Essential Global Handlers (Redefined here for reliability)
function toggleAdvancedFilters() {
    const panel = document.getElementById('advanced-filters-panel');
    const chevron = document.getElementById('advanced-filters-chevron');
    if (panel) {
        const isHidden = panel.classList.toggle('hidden');
        if (chevron) {
            chevron.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }
}

function applyFilters() {
    if (window.docManager) window.docManager.applyFilters();
    else if (typeof window.applyFilters === 'function') window.applyFilters();
}

function applyAdvancedFilters() {
    if (window.docManager) window.docManager.applyAdvancedFilters();
}

function clearAdvancedFilters() {
    if (window.docManager) window.docManager.clearFilters();
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function toggleDocPreview(btn) {
    const container = document.getElementById('doc-preview-container');
    const icon = btn.querySelector('i');
    const label = btn.querySelector('span');
    if (container.classList.contains('max-h-72')) {
        container.classList.remove('max-h-72');
        container.classList.add('max-h-[2000px]');
        icon.classList.remove('bi-chevron-down');
        icon.classList.add('bi-chevron-up');
        label.textContent = 'Collapse';
    } else {
        container.classList.remove('max-h-[2000px]');
        container.classList.add('max-h-72');
        icon.classList.remove('bi-chevron-up');
        icon.classList.add('bi-chevron-down');
        label.textContent = 'Expand';
    }
}

function formatDocumentText(rawText) {
    if (!rawText) return '<p class="text-gray-400 italic">No content available.</p>';

    // Normalize line endings
    let text = rawText.replace(/\r\n/g, '\n').replace(/\r/g, '\n');

    // Remove OCR markers
    text = text.replace(/^\[OCR\]\s*\n?/i, '');
    text = text.replace(/\[Page OCR failed:.*?\]/g, '');
    text = text.replace(/--- Page Break ---/g, '\n\n');

    // Split into blocks separated by blank lines
    const blocks = text.split(/\n{2,}/);
    let html = '';
    let inList = false;
    let listType = '';
    let listItems = [];

    const closeList = () => {
        if (inList) {
            const tag = listType === 'ol' ? 'ol' : 'ul';
            const cls = listType === 'ol'
                ? 'list-decimal list-inside space-y-1.5 my-3 pl-2'
                : 'list-disc list-inside space-y-1.5 my-3 pl-2';
            html += `<${tag} class="${cls}">${listItems.join('')}</${tag}>`;
            inList = false;
            listType = '';
            listItems = [];
        }
    };

    for (let block of blocks) {
        block = block.trim();
        if (!block) continue;

        // Check for numbered list items (1., 2., etc.)
        const numberedMatch = block.match(/^(\d+)\.\s*(.+)/);

        // Check for bullet list items (-, *, •)
        const bulletMatch = block.match(/^[•·\-\*]\s*(.+)/);

        // Check for section headers: ROMAN NUMERAL + . or all caps short line
        const romanNumeralMatch = block.match(/^([IVXLCDM]+)\.\s*(.+)/i);
        const isShortAllCaps = block.length < 80 && block === block.toUpperCase() && /[A-Z]/.test(block) && !block.endsWith('.') && !numberedMatch;

        // Check for lettered items (A., B., etc.)
        const letteredMatch = block.match(/^([A-Z])\.\s*(.+)/);

        // Detect headings — lines that look like titles
        const isHeading = isShortAllCaps ||
            (block.length < 100 && block === block.toUpperCase() && /[A-Z]/.test(block)) ||
            /^(DETAILED\s|AN\s|ORDINANCE|RESOLUTION|REPUBLIC\s|CITY\s|MUNICIPAL|PROVINCIAL|BARANGAY|OFFICE\s|DEPARTMENT|COLLEGE|UNIVERSITY|SCHOOL|SECTION|ARTICLE|CHAPTER)/i.test(block) && block.length < 120;

        if (numberedMatch) {
            if (inList && listType !== 'ol') { closeList(); }
            if (!inList) { inList = true; listType = 'ol'; }
            listItems.push(`<li class="text-gray-800 dark:text-gray-200 leading-relaxed">${escapeHtml(numberedMatch[2])}</li>`);
        } else if (bulletMatch) {
            if (inList && listType !== 'ul') { closeList(); }
            if (!inList) { inList = true; listType = 'ul'; }
            listItems.push(`<li class="text-gray-800 dark:text-gray-200 leading-relaxed">${escapeHtml(bulletMatch[1])}</li>`);
        } else if (letteredMatch && block.length < 200) {
            if (inList && listType !== 'ol') { closeList(); }
            if (!inList) { inList = true; listType = 'ol'; }
            listItems.push(`<li class="text-gray-800 dark:text-gray-200 leading-relaxed"><strong>${letteredMatch[1]}.</strong> ${escapeHtml(letteredMatch[2])}</li>`);
        } else {
            closeList();

            if (isHeading) {
                // Major heading — centered, bold, larger
                html += `<h2 class="text-center font-bold text-base text-gray-900 dark:text-gray-100 my-3 uppercase tracking-wide">${escapeHtml(block)}</h2>`;
            } else if (romanNumeralMatch && block.length < 200) {
                // Roman numeral section heading
                html += `<h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 mt-4 mb-2">${escapeHtml(block)}</h3>`;
            } else if (block.length < 100 && /^(Section|Article|Chapter|Title)\s/i.test(block)) {
                // Section heading
                html += `<h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 mt-4 mb-2">${escapeHtml(block)}</h3>`;
            } else {
                // Regular paragraph — handle single line breaks within block
                const lines = block.split('\n');
                if (lines.length === 1) {
                    html += `<p class="text-gray-800 dark:text-gray-200 leading-relaxed mb-3 text-justify">${escapeHtml(block)}</p>`;
                } else {
                    // Multi-line block: check if it's a sub-list or indented content
                    const isIndented = lines.every(l => /^\s+/.test(l) || !l.trim());
                    if (isIndented && lines.length > 2) {
                        html += `<div class="pl-4 border-l-2 border-gray-200 dark:border-gray-700 my-3 space-y-1">`;
                        for (const line of lines) {
                            if (line.trim()) {
                                html += `<p class="text-gray-700 dark:text-gray-300 leading-relaxed text-[12px]">${escapeHtml(line.trim())}</p>`;
                            }
                        }
                        html += `</div>`;
                    } else {
                        // Join lines with <br> for line-by-line content (e.g., addresses)
                        html += `<p class="text-gray-800 dark:text-gray-200 leading-relaxed mb-3 text-justify">${lines.map(l => escapeHtml(l.trim())).join('<br>')}</p>`;
                    }
                }
            }
        }
    }
    closeList();

    if (!html.trim()) {
        return '<p class="text-gray-400 italic">No usable text could be displayed. The extracted content only contained OCR markers or the document is scanned/image-based and requires Tesseract OCR.</p>';
    }

    return html;
}

function viewDocument(id) {
    const modal = document.getElementById('preview-modal');
    const content = document.getElementById('preview-content');
    const modalContainer = document.getElementById('preview-modal-panel');
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    content.innerHTML = `
        <div class="flex items-center justify-center p-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600"></div>
        </div>
    `;

    // Animation classes
    setTimeout(() => {
        modalContainer.classList.remove('translate-y-full', 'opacity-0');
        modalContainer.classList.add('translate-y-0', 'opacity-100');
    }, 10);

    fetch(App.apiUrl('documents', `get_details.php?id=${id}`))
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const doc = res.document;
                const statusBadge = getStatusBadgeHTML(doc.status);
                
                content.innerHTML = `
                    <div class="p-4 md:p-8">
                        <!-- Top Header Area -->
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 mb-8 pb-6 border-b border-gray-100 dark:border-gray-800">
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-3">
                                    <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white leading-tight">${doc.title}</h2>
                                    <div class="flex">${statusBadge}</div>
                                </div>
                                <div class="flex flex-wrap items-center gap-y-3 text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center bg-gray-50 dark:bg-gray-800/50 px-2 py-1 rounded-lg border border-gray-100 dark:border-gray-700">
                                        <i class="bi bi-hash mr-1.5 text-red-500"></i>
                                        REF: <span class="font-black text-gray-800 dark:text-gray-200 ml-1 uppercase">${doc.reference_number}</span>
                                    </span>
                                    <span class="hidden md:inline mx-3 text-gray-300 dark:text-gray-700">|</span>
                                    <span class="flex items-center">
                                        <i class="bi bi-file-earmark-text mr-1.5 text-blue-500"></i>
                                        Type: <span class="capitalize ml-1 font-bold text-gray-700 dark:text-gray-300">${doc.document_type}</span>
                                    </span>
                                    <span class="mx-3 text-gray-300 dark:text-gray-700">|</span>
                                    <span class="flex items-center">
                                        <i class="bi bi-calendar3 mr-1.5 text-green-500"></i>
                                        Date: <span class="ml-1 font-bold text-gray-700 dark:text-gray-300">${formatDate(doc.document_date)}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-row md:flex-row items-center gap-3">
                                ${currentUserRole !== 'viewer' ? `
                                <a href="${App.apiUrl('documents', `download.php?id=${doc.id}`)}" class="flex-1 sm:flex-none justify-center bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl font-black uppercase tracking-widest text-[11px] flex items-center shadow-lg shadow-red-200 dark:shadow-none transition-all active:scale-95">
                                    <i class="bi bi-download mr-2 text-base"></i> Download
                                </a>
                                ` : ''}
                                ${currentUserRole !== 'viewer' ? `
                                <button type="button" onclick="editDocument(${doc.id})" class="flex-1 sm:flex-none justify-center bg-gray-900 dark:bg-black hover:bg-black text-white px-6 py-3 rounded-xl font-black uppercase tracking-widest text-[11px] flex items-center shadow-lg transition-all active:scale-95">
                                    <i class="bi bi-pencil-square mr-2 text-base"></i> Edit
                                </button>
                                ` : ''}
                            </div>
                        </div>

                        ${currentUserRole === 'viewer' ? `
                        <div class="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 rounded-r-xl p-4 mb-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-info-circle-fill text-amber-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-black text-amber-800 dark:text-amber-200 uppercase tracking-widest mb-1">View-Only Access</h4>
                                    <p class="text-sm text-amber-700 dark:text-amber-300">
                                        Your account has view-only access. You can view document details but cannot download or edit files. Contact an administrator if you need download permissions.
                                    </p>
                                </div>
                            </div>
                        </div>
                        ` : ''}

                        <!-- Main Content Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Left: Primary Information -->
                            <div class="lg:col-span-2 space-y-8">
                                <section class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 md:p-6">
                                    <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 mb-6 flex items-center uppercase tracking-widest">
                                        <span class="w-1 h-5 bg-red-600 rounded-full mr-3"></span>
                                        Document Details
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">File Name</label>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 font-bold break-all">${doc.file_name}</p>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">File Size</label>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 font-bold">${formatSize(doc.file_size)}</p>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Category</label>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 font-bold uppercase">${doc.file_type}</p>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Uploaded By</label>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 font-bold">${doc.uploader_name || 'System Admin'}</p>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Registered On</label>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 font-bold">${formatDateTime(doc.created_at)}</p>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Last Interaction</label>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 font-bold">${formatDateTime(doc.updated_at)}</p>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Status Set By</label>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 font-bold">${doc.status_changed_by_name ? `${doc.status_changed_by_name}` : 'N/A'}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-8 pt-6 border-t border-gray-50 dark:border-gray-800">
                                        <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2.5">Description / Annotations</label>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-medium">${doc.description || 'No additional notes provided for this record.'}</p>
                                    </div>
                                </section>

                                <!-- Document Analysis (OCR) -->
                                ${(() => {
                                    const ocrStatus = doc.ocr_status || 'pending';
                                    const ocrInfo = {
                                        'completed': { badge: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50', icon: 'patch-check-fill', label: 'Digitally Extracted' },
                                        'pending': { badge: 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 border-amber-200 dark:border-amber-800/50', icon: 'hourglass-split', label: 'Extraction Scheduled' },
                                        'processing': { badge: 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 border-blue-200 dark:border-blue-800/50', icon: 'arrow-repeat', label: 'Extracting...' },
                                        'failed': { badge: 'bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400 border-rose-200 dark:border-rose-800/50', icon: 'exclamation-triangle-fill', label: 'Extraction Unavailable' },
                                        'skipped': { badge: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-200 dark:border-slate-700', icon: 'dash-circle-fill', label: 'Extraction Skipped' }
                                    };
                                    const info = ocrInfo[ocrStatus] || ocrInfo['pending'];
                                    const keyPoints = doc.key_points ? doc.key_points.split('\n').filter(p => p.trim()) : [];
                                    const extractedText = doc.extracted_text || '';
                                    const processedDate = doc.ocr_processed_at ? new Date(doc.ocr_processed_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : null;
                                    const wordCount = extractedText ? extractedText.trim().split(/\s+/).length : 0;

                                    return `
                                    <section class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                                        <!-- Header Bar -->
                                        <div class="px-5 md:px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white dark:from-gray-800/80 dark:to-gray-800/50">
                                            <div class="flex items-center">
                                                <span class="w-1 h-5 bg-indigo-600 rounded-full mr-3"></span>
                                                <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest">
                                                    Document Analysis
                                                </h3>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ${info.badge}">
                                                    <i class="bi bi-${info.icon}"></i>${info.label}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="p-5 md:p-6 space-y-6">
                                            ${processedDate ? `
                                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-gray-400 dark:text-gray-500">
                                                    <span class="flex items-center"><i class="bi bi-calendar-check mr-1.5"></i>Extracted on ${processedDate}</span>
                                                    ${extractedText ? `<span class="flex items-center"><i class="bi bi-file-earmark-text mr-1.5"></i>${extractedText.length.toLocaleString()} characters &middot; ${wordCount.toLocaleString()} words</span>` : ''}
                                                </div>
                                            ` : ''}

                                            ${keyPoints.length > 0 ? `
                                                <!-- Summary of Key Points -->
                                                <div>
                                                    <div class="flex items-center mb-3">
                                                        <i class="bi bi-card-text text-indigo-600 dark:text-indigo-400 mr-2"></i>
                                                        <h4 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Summary of Key Points</h4>
                                                    </div>
                                                    <ol class="space-y-2.5 border-l-2 border-indigo-100 dark:border-indigo-900/50 pl-5">
                                                        ${keyPoints.map((p, i) => `
                                                            <li class="relative">
                                                                <span class="absolute -left-[27px] top-0 w-5 h-5 rounded-full bg-indigo-600 text-white text-[10px] font-bold flex items-center justify-center">${i + 1}</span>
                                                                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed pt-0.5">${escapeHtml(p.replace(/^[•·\-\*]\s*/, ''))}</p>
                                                            </li>
                                                        `).join('')}
                                                    </ol>
                                                </div>
                                            ` : ''}

                                            ${extractedText ? `
                                                <!-- Document Preview -->
                                                <div>
                                                    <div class="flex items-center justify-between mb-3">
                                                        <div class="flex items-center">
                                                            <i class="bi bi-file-earmark-richtext text-slate-600 dark:text-slate-400 mr-2"></i>
                                                            <h4 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Document Preview</h4>
                                                        </div>
                                                        <div class="flex items-center gap-3">
                                                            <button type="button" data-preview-id="${doc.id}" data-preview-name="${escapeHtml(doc.file_name || '')}" data-preview-type="${escapeHtml(doc.file_type || '')}" data-compliance="${escapeHtml(doc.compliance_status || 'pending')}" data-rejection-notes="${escapeHtml(doc.rejection_notes || '')}" data-can-run="<?php echo (strtolower(trim($_SESSION['user_role'] ?? 'viewer')) !== 'viewer' ? '1' : '0'); ?>" class="btn-original-preview text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 flex items-center gap-1">
                                                                <i class="bi bi-eye"></i><span>Preview</span>
                                                            </button>
                                                            <button type="button" onclick="toggleDocPreview(this)" class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center gap-1">
                                                                <i class="bi bi-chevron-down"></i><span>Expand</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div id="doc-preview-container" class="max-h-72 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 transition-all duration-300">
                                                        <div class="bg-white dark:bg-gray-900 p-6 md:p-10 doc-preview-page">
                                                            <div class="doc-content text-[13px] text-gray-800 dark:text-gray-200 leading-7">${formatDocumentText(extractedText)}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ` : !keyPoints.length ? `
                                                <div class="text-center py-10 bg-gray-50 dark:bg-gray-800/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                                    <i class="bi bi-file-earmark-x text-3xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
                                                    <p class="text-sm text-gray-400 dark:text-gray-500 font-medium">${ocrStatus === 'pending' ? 'Document content extraction is scheduled and will be available once processing is complete.' : ocrStatus === 'failed' ? 'Content extraction was unsuccessful. The file may be corrupted or in an unsupported format.' : 'No readable text content was found in this document.'}</p>
                                                </div>
                                            ` : ''}
                                        </div>
                                    </section>
                                    `;
                                })()}

                                <section class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 md:p-6">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-6 flex items-center">
                                        <span class="w-1.5 h-6 bg-blue-600 rounded-full mr-3"></span>
                                        Version History
                                    </h3>
                                    ${res.versions && res.versions.length > 0 ? `
                                        <div class="space-y-3">
                                            ${res.versions.map(v => `
                                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/30 rounded-xl border border-gray-100 dark:border-gray-700">
                                                    <div class="flex items-center">
                                                        <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center mr-4">
                                                            <span class="text-blue-600 font-black text-sm">V${v.version_number}</span>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-bold text-gray-800 dark:text-gray-200">${v.file_name}</p>
                                                            <p class="text-[10px] text-gray-500">${formatDateTime(v.created_at)} • ${v.created_by_name}</p>
                                                        </div>
                                                    </div>
                                                    <button type="button" onclick="revertToVersion(${doc.id}, ${v.version_number})" class="text-xs font-black uppercase text-blue-600 hover:text-blue-700">Revert</button>
                                                </div>
                                            `).join('')}
                                        </div>
                                    ` : `
                                        <div class="text-center py-10 bg-gray-50 dark:bg-gray-800/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                            <p class="text-gray-400 italic text-sm">No previous versions available.</p>
                                        </div>
                                    `}
                                </section>

                                <section class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 md:p-6">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-6 flex items-center">
                                        <span class="w-1.5 h-6 bg-emerald-600 rounded-full mr-3"></span>
                                        Compliance
                                    </h3>
                                    <div id="preview-compliance-badge" class="mb-3"></div>
                                    <div id="preview-compliance-content"></div>
                                </section>

                            </div>

                            <!-- Right: Sidebar Information -->
                            <div class="space-y-6">
                                <section class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 p-6">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center">
                                        <i class="bi bi-link-45deg mr-2 text-indigo-600"></i>
                                        Related Documents
                                    </h3>
                                    ${res.related && res.related.length > 0 ? `
                                        <div class="space-y-3">
                                            ${res.related.map(r => `
                                                <a href="javascript:void(0)" onclick="viewDocument(${r.id})" class="block p-3 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl transition-colors border border-transparent hover:border-gray-100 dark:hover:border-gray-700">
                                                    <p class="text-xs font-black text-indigo-600 uppercase mb-1">${r.document_type}</p>
                                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200 leading-tight">${r.title}</p>
                                                    <p class="text-[10px] text-gray-500 mt-1">${r.reference_number}</p>
                                                </a>
                                            `).join('')}
                                        </div>
                                    ` : `
                                        <div class="text-center py-6">
                                            <p class="text-gray-400 italic text-sm">No related documents</p>
                                        </div>
                                    `}
                                </section>

                                <section class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 p-6 shadow-sm">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-5 flex items-center">
                                        <i class="bi bi-lightning-charge mr-2 text-yellow-500"></i>
                                        Quick Actions
                                    </h3>
                                    <div class="grid gap-3">
                                        <button type="button" onclick="shareDocument(${doc.id})" class="flex items-center w-full px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 transition-colors">
                                            <i class="bi bi-share mr-3 text-blue-500"></i> Share Document
                                        </button>
                                        <button type="button" onclick="window.print()" class="flex items-center w-full px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 transition-colors">
                                            <i class="bi bi-printer mr-3 text-gray-500"></i> Print Details
                                        </button>
                                        <button type="button" onclick="viewActivityHistory(${doc.id})" class="flex items-center w-full px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 transition-colors">
                                            <i class="bi bi-clock-history mr-3 text-purple-500"></i> Activity History
                                        </button>
                                        <div class="mt-2 pt-2 border-t border-gray-50 dark:border-gray-800">
                                            ${doc.status !== 'approved' ? `
                                            <button type="button" onclick="deleteDocument(${doc.id})" class="flex items-center w-full px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors">
                                                <i class="bi bi-trash mr-3"></i> Delete Document
                                            </button>
                                            ` : ''}
                                        </div>
                                    </div>
                                </section>

                            </div>
                        </div>
                    </div>
                `;
                loadComplianceForPreviewModal(doc.id, currentUserRole);
            } else {
                content.innerHTML = `<div class="p-12 text-center text-red-600">${res.error}</div>`;
            }
        })
        .catch(e => {
            content.innerHTML = `<div class="p-12 text-center text-red-600">Failed to load document details</div>`;
        });
}

function loadComplianceForPreviewModal(docId, role) {
    const badgeEl = document.getElementById('preview-compliance-badge');
    const contentEl = document.getElementById('preview-compliance-content');
    if (!badgeEl || !contentEl) return;
    contentEl.innerHTML = '<div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"><i class="bi bi-arrow-repeat animate-spin"></i>Loading compliance analysis...</div>';
    fetch(App.apiUrl('documents', 'get-compliance-results.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ document_id: docId })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            badgeEl.innerHTML = getComplianceBadgeHTML(data.compliance_status || 'pending');
            renderPreviewModalCompliance(docId, data, role);
        } else {
            contentEl.innerHTML = '<p class="text-sm text-red-600">Could not load compliance analysis: ' + escapeHtml(data.error || 'Unknown error') + '</p>';
        }
    }).catch(() => {
        contentEl.innerHTML = '<p class="text-sm text-red-600">Could not load compliance analysis.</p>';
    });
}

function renderPreviewModalCompliance(docId, data, role) {
    const contentEl = document.getElementById('preview-compliance-content');
    if (!contentEl) return;
    let html = '<div class="space-y-3">';
    if (data.rejection_notes) {
        html += '<div class="p-3 rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-900/40 text-red-800 dark:text-red-300 text-sm">' +
            '<p class="font-semibold mb-1"><i class="bi bi-exclamation-circle mr-1"></i>Rejection Notes</p>' +
            '<p>' + escapeHtml(data.rejection_notes) + '</p>' +
        '</div>';
    }
    if (data.results && data.results.length > 0) {
        html += '<div class="space-y-2">';
        data.results.forEach(function(r) {
            const passed = r.status === 'compliant';
            const icon = passed ? 'bi-check-circle text-green-600 dark:text-green-400' : 'bi-x-circle text-red-600 dark:text-red-400';
            const barColor = passed ? 'bg-green-500' : 'bg-red-500';
            const code = escapeHtml(r.code || r.title || 'Rule');
            const title = r.code ? escapeHtml(r.title || '') : '';
            const score = parseInt(r.score || 0, 10);
            let aiHtml = '';
            if (r.ai_analysis) {
                try {
                    const ai = JSON.parse(r.ai_analysis);
                    aiHtml = '<p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1"><i class="bi bi-robot mr-1"></i><strong>AI Analysis' + (ai.confidence ? ' (' + ai.confidence + '%)' : '') + ':</strong> ' + escapeHtml(ai.analysis || ai.reason || '') + '</p>';
                } catch (e) {}
            }
            html += '<div class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">' +
                '<div class="flex items-center gap-2 mb-1">' +
                    '<i class="bi ' + icon + '"></i>' +
                    '<span class="font-medium text-sm text-gray-800 dark:text-gray-200">' + code + '</span>' +
                '</div>' +
                (title ? '<p class="text-xs text-gray-500 dark:text-gray-400 mb-1">' + title + '</p>' : '') +
                '<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mb-1">' +
                    '<div class="' + barColor + ' h-1.5 rounded-full" style="width: ' + Math.min(100, Math.max(0, score)) + '%;"></div>' +
                '</div>' +
                '<p class="text-xs text-gray-500 dark:text-gray-400">' + escapeHtml(r.explanation || '') + '</p>' +
                aiHtml +
            '</div>';
        });
        html += '</div>';
    } else {
        html += '<p class="text-sm text-gray-500 dark:text-gray-400">No compliance analysis available yet.</p>';
    }
    html += '</div>';
    if (role !== 'viewer') {
        html += '<button type="button" onclick="runComplianceCheckInPreviewModal(' + docId + ')" class="mt-4 w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg transition"><i class="bi bi-arrow-repeat mr-1"></i> Run Compliance Check</button>';
    }
    if (role !== 'viewer' && data.compliance_status === 'non_compliant') {
        html += '<div class="mt-4 p-4 rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-900/40">' +
            '<label class="block text-sm font-medium text-gray-800 dark:text-gray-200 mb-1">Non-compliance comment</label>' +
            '<textarea id="reject-comment-preview" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-red-500 focus:border-red-500 mb-2" placeholder="Explain why this document is non-compliant..."></textarea>' +
            '<button type="button" onclick="rejectDocumentFromPreview(' + docId + ')" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg transition"><i class="bi bi-x-circle mr-1"></i> Send Back for Revision</button>' +
        '</div>';
    }
    contentEl.innerHTML = html;
}

async function runComplianceCheckInPreviewModal(docId) {
    const contentEl = document.getElementById('preview-compliance-content');
    const badgeEl = document.getElementById('preview-compliance-badge');
    if (contentEl) contentEl.innerHTML = '<div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"><i class="bi bi-arrow-repeat animate-spin"></i>Running compliance check...</div>';
    try {
        const response = await fetch(App.apiUrl('documents', 'check-compliance.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ document_id: docId })
        });
        const data = await response.json();
        if (data.success) {
            showToast('Compliance status: ' + data.compliance_status, data.compliance_status === 'compliant' ? 'success' : 'warning');
            if (badgeEl) badgeEl.innerHTML = getComplianceBadgeHTML(data.compliance_status || 'pending');
            const role = (typeof currentUserRole !== 'undefined' && currentUserRole) ? currentUserRole : 'viewer';
            loadComplianceForPreviewModal(docId, role);
        } else {
            if (contentEl) contentEl.innerHTML = '<p class="text-sm text-red-600">Compliance check failed: ' + escapeHtml(data.error || 'Unknown error') + '</p>';
        }
    } catch (e) {
        if (contentEl) contentEl.innerHTML = '<p class="text-sm text-red-600">Failed to run compliance check.</p>';
    }
}

async function rejectDocumentFromPreview(documentId) {
    const commentEl = document.getElementById('reject-comment-preview');
    const comment = commentEl ? commentEl.value.trim() : '';
    if (!comment) {
        showToast('Please enter a non-compliance comment.', 'warning');
        return;
    }
    try {
        const response = await fetch(App.apiUrl('documents', 'reject-document.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ document_id: documentId, comment: comment })
        });
        const data = await response.json();
        if (data.success) {
            showToast('Document rejected and marked as non-compliant.', 'success');
            viewDocument(documentId);
        } else {
            showToast('Rejection failed: ' + (data.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Failed to reject document.', 'error');
    }
}

/**
 * Handle document sharing
 */
function shareDocument(id) {
    const url = window.location.origin + App.apiUrl('documents', `download.php?id=${id}`);
    
    if (navigator.share) {
        navigator.share({
            title: 'Share Document',
            url: url
        }).catch(err => console.error('Error sharing:', err));
    } else {
        // Fallback: Copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            alert('Document link copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy link:', err);
        });
    }
}

/**
 * View document activity history
 */
async function viewActivityHistory(id) {
    const modal = document.getElementById('activity-modal');
    const content = document.getElementById('activity-content');
    const modalContent = document.getElementById('activity-modal-content');
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        if (modalContent) {
            modalContent.classList.remove('translate-y-full', 'sm:scale-95', 'opacity-0');
            modalContent.classList.add('translate-y-0', 'sm:scale-100', 'opacity-100');
        }
    }, 10);
    content.innerHTML = '<div class="p-8 text-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600 mx-auto"></div></div>';
    
    try {
        const response = await fetch(App.apiUrl('documents', `get_details.php?id=${id}`));
        const res = await response.json();
        
        if (res.success && res.activity) {
            if (res.activity.length === 0) {
                content.innerHTML = '<div class="p-8 text-center text-gray-500 italic">No activity recorded for this document.</div>';
            } else {
                content.innerHTML = `
                    <div class="px-6 py-4">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                ${res.activity.map((a, idx) => `
                                    <li>
                                        <div class="relative pb-8">
                                            ${idx !== res.activity.length - 1 ? '<span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-800"></span>' : ''}
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-900 ${
                                                        a.action.includes('upload') || a.action.includes('create') ? 'bg-green-500' :
                                                        a.action.includes('delete') ? 'bg-red-500' :
                                                        a.action.includes('update') ? 'bg-blue-500' :
                                                        a.action.includes('download') ? 'bg-indigo-500' : 'bg-gray-400'
                                                    }">
                                                        <i class="bi ${
                                                            a.action.includes('upload') || a.action.includes('create') ? 'bi-cloud-upload' :
                                                            a.action.includes('delete') ? 'bi-trash' :
                                                            a.action.includes('update') ? 'bi-pencil' :
                                                            a.action.includes('download') ? 'bi-download' : 'bi-eye'
                                                        } text-white text-xs"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-1 min-w-0 pt-1.5">
                                                    <div class="flex flex-col">
                                                        <p class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-tight">${a.action.replace(/_/g, ' ')}</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">${a.description || 'Action performed on document'}</p>
                                                    </div>
                                                    <div class="mt-2 flex items-center gap-2">
                                                        <span class="text-[10px] font-black text-gray-400 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">${a.user_name}</span>
                                                        <span class="text-[10px] text-gray-400">${formatDateTime(a.created_at)}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    </div>
                `;
            }
        } else {
            content.innerHTML = `<div class="p-8 text-center text-red-600">${res.error || 'Failed to load activity'}</div>`;
        }
    } catch (e) {
        content.innerHTML = '<div class="p-8 text-center text-red-600">Failed to load activity logs</div>';
    }
}

function closeActivityModal() {
    const modal = document.getElementById('activity-modal');
    const modalContent = document.getElementById('activity-modal-content');
    if (modalContent) {
        modalContent.classList.add('translate-y-full', 'sm:scale-95', 'opacity-0');
        modalContent.classList.remove('translate-y-0', 'sm:scale-100', 'opacity-100');
    }
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }, 300);
}

/**
 * Revert to a specific version
 */
async function revertToVersion(docId, version) {
    if (!confirm(`Are you sure you want to revert to Version ${version}? This will create a new version of the current file.`)) return;
    
    try {
        const formData = new FormData();
        formData.append('document_id', docId);
        formData.append('version_number', version);
        formData.append('csrf_token', App.getCsrfToken());

        const response = await fetch(App.apiUrl('documents', 'revert-version.php'), {
            method: 'POST',
            body: formData
        });
        
        const res = await response.json();
        if (res.success) {
            alert(res.message);
            viewDocument(docId); // Refresh the preview
        } else {
            alert(res.error || 'Failed to revert version');
        }
    } catch (e) {
        alert('Failed to process revert request');
    }
}


function closePreviewModal() {
    const modal = document.getElementById('preview-modal');
    const modalContainer = document.getElementById('preview-modal-panel');
    
    modalContainer.classList.add('translate-y-full', 'opacity-0');
    modalContainer.classList.remove('translate-y-0', 'opacity-100');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }, 300);
}

// Helper functions for modal
function getStatusBadgeHTML(status) {
    if (!status) return '';
    const s = status.toLowerCase();
    const badges = {
        'draft': '<span class="badge badge-secondary"><i class="bi bi-pencil mr-1"></i>Draft</span>',
        'pending': '<span class="badge badge-warning"><i class="bi bi-clock mr-1"></i>Pending</span>',
        'approved': '<span class="badge badge-success"><i class="bi bi-check-circle mr-1"></i>Approved</span>',
        'rejected': '<span class="badge badge-danger"><i class="bi bi-x-circle mr-1"></i>Rejected</span>',
        'archived': '<span class="badge bg-gray-500 text-white"><i class="bi bi-archive mr-1"></i>Archived</span>'
    };
    return badges[s] || `<span class="badge badge-info">${status}</span>`;
}

function getComplianceBadgeHTML(status) {
    if (!status) return '';
    const s = status.toLowerCase();
    const badges = {
        'pending': '<span class="badge badge-warning"><i class="bi bi-hourglass-split mr-1"></i>Pending</span>',
        'compliant': '<span class="badge badge-success"><i class="bi bi-shield-check mr-1"></i>Compliant</span>',
        'non_compliant': '<span class="badge badge-danger"><i class="bi bi-shield-exclamation mr-1"></i>Non-Compliant</span>'
    };
    return badges[s] || `<span class="badge badge-warning">${status}</span>`;
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function formatDateTime(dateStr) {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', { 
        year: 'numeric', month: 'long', day: 'numeric', 
        hour: '2-digit', minute: '2-digit' 
    });
}

function formatSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function editDocument(id) {
    // Close the preview modal first if it's open
    const previewModal = document.getElementById('preview-modal');
    if (previewModal && !previewModal.classList.contains('hidden')) {
        closePreviewModal();
    }
    
    const modal = document.getElementById('edit-modal');
    const form = document.getElementById('edit-form-modal');
    const modalContainer = document.getElementById('edit-modal-panel');
    
    // Show modal and start transition
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    setTimeout(() => {
        modalContainer.classList.remove('translate-y-full', 'opacity-0');
        modalContainer.classList.add('translate-y-0', 'opacity-100');
    }, 10);

    // Fetch details to populate form
    fetch(App.apiUrl('documents', `get_details.php?id=${id}`))
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const doc = res.document;
                // Populate fields
                form.querySelector('[name="document_id"]').value = doc.id;
                form.querySelector('[name="title"]').value = doc.title;
                form.querySelector('[name="document_type"]').value = doc.document_type;
                form.querySelector('[name="reference_number"]').value = doc.reference_number;
                form.querySelector('[name="description"]').value = doc.description || '';
                form.querySelector('[name="document_date"]').value = doc.document_date;
                form.querySelector('[name="status"]').value = doc.status;
                form.querySelector('[name="tags"]').value = doc.tags || '';
            } else {
                alert('Error loading document: ' + res.error);
                closeEditModal();
            }
        })
        .catch(err => {
            alert('Failed to connect to API');
            closeEditModal();
        });
}

function closeEditModal() {
    const modal = document.getElementById('edit-modal');
    const modalContainer = document.getElementById('edit-modal-panel');
    
    modalContainer.classList.add('translate-y-full', 'opacity-0');
    modalContainer.classList.remove('translate-y-0', 'opacity-100');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('edit-form-modal').reset();
    }, 300);
}

function deleteDocument(id) {
    console.log('deleteDocument called with id:', id);
    if (!confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        return;
    }
    
    // Close the preview modal if open (so user sees the result)
    const previewModal = document.getElementById('preview-modal');
    if (previewModal && !previewModal.classList.contains('hidden')) {
        closePreviewModal();
    }
    
    fetch(App.apiUrl('documents', 'delete.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => {
        console.log('Delete response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Delete response data:', data);
        if (data.success) {
            showToast('Document deleted successfully', 'success');
            closePreviewModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to delete document', 'error');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('An error occurred while deleting', 'error');
    });
}

function showNotification(message, type) {
    // Simple notification (you can enhance this)
    alert(message);
}

function toggleSelectAll(checkbox) {
    if (window.docManager) {
        window.docManager.selectAll(checkbox.checked);
    }

    const documentCheckboxes = document.querySelectorAll('.document-checkbox');
    const selectAllTop = document.getElementById('select-all-top');
    const selectAllHeader = document.getElementById('select-all-header');
    
    documentCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    
    // Sync both select-all checkboxes
    if (selectAllTop) selectAllTop.checked = checkbox.checked;
    if (selectAllHeader) selectAllHeader.checked = checkbox.checked;
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const selected = document.querySelectorAll('.document-checkbox:checked').length;
    const total = document.querySelectorAll('.document-checkbox').length;
    const countElement = document.getElementById('selected-count');
    
    if (selected > 0) {
        countElement.innerHTML = `<span class="font-semibold text-red-600">${selected} selected</span> of ${total} documents`;
    } else {
        countElement.innerHTML = `${total} documents found`;
    }
}

function getSelectedDocumentIds() {
    const checkboxes = document.querySelectorAll('.document-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function exportSelectedFiles() {
    const selectedIds = getSelectedDocumentIds();
    
    if (selectedIds.length === 0) {
        alert('Please select at least one document to export');
        return;
    }
    
    // Close the dropdown
    document.getElementById('export-menu').classList.add('hidden');
    
    // Create export URL with selected IDs
    const url = App.apiUrl('documents', `export.php?export_type=files&ids=${selectedIds.join(',')}`);
    window.location.href = url;
}

function exportAllFiles() {
    if (!confirm('This will download all visible documents as a ZIP file. Continue?')) {
        return;
    }
    
    // Close the dropdown
    document.getElementById('export-menu').classList.add('hidden');
    
    // Get current filters from URL or form
    const searchParams = new URLSearchParams(window.location.search);
    searchParams.set('export_type', 'files');
    const url = App.apiUrl('documents', `export.php?${searchParams.toString()}`);
    window.location.href = url;
}

function exportList(format) {
    // Close the dropdown
    document.getElementById('export-menu').classList.add('hidden');
    
    // Get current filters from URL or form
    const searchParams = new URLSearchParams(window.location.search);
    searchParams.set('export_type', 'list');
    searchParams.set('format', format);
    const url = App.apiUrl('documents', `export.php?${searchParams.toString()}`);
    window.location.href = url;
}

function toggleExportMenu(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    const menu = document.getElementById('export-menu');
    const button = document.querySelector('#export-dropdown button');
    const rect = button.getBoundingClientRect();

    // Move menu to body if not already there (to escape overflow:hidden containers)
    if (menu.parentElement.id === 'export-dropdown') {
        document.body.appendChild(menu);
    }

    // Position the fixed dropdown below the button, aligned to the right
    menu.style.top = (rect.bottom + 8) + 'px';
    menu.style.left = (rect.right - 224) + 'px'; // 224 is the menu width

    menu.classList.toggle('hidden');
}

// Close export menu when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('export-dropdown');
    const menu = document.getElementById('export-menu');
    
    if (dropdown && menu && !dropdown.contains(event.target) && !menu.contains(event.target)) {
        menu.classList.add('hidden');
    }
});

// Update selected count when individual checkboxes change
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.document-checkbox');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
    
    // Initialize count
    const totalDocs = checkboxes.length;
    if (document.getElementById('total-docs')) {
        document.getElementById('total-docs').textContent = totalDocs;
    }

    // Auto-open upload modal if requested in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('upload') === 'true') {
        openUploadModal();
        // Remove the parameter from URL without reloading
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
});

// Upload Modal Functions
function openUploadModal() {
    const modal = document.getElementById('upload-modal');
    const modalContainer = document.getElementById('upload-modal-panel');
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    setTimeout(() => {
        modalContainer.classList.remove('translate-y-full', 'opacity-0');
        modalContainer.classList.add('translate-y-0', 'opacity-100');
    }, 10);
}

function closeUploadModal() {
    const modal = document.getElementById('upload-modal');
    const modalContainer = document.getElementById('upload-modal-panel');
    
    modalContainer.classList.add('translate-y-full', 'opacity-0');
    modalContainer.classList.remove('translate-y-0', 'opacity-100');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        // Reset form
        document.getElementById('upload-form-modal').reset();
        document.getElementById('file-preview-modal').classList.add('hidden');
        document.getElementById('drop-zone-modal').classList.remove('hidden');
    }, 300);
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUploadModal();
        closePreviewModal();
        closeEditModal();
    }
});
</script>

<!-- Edit Document Modal -->
<div id="edit-modal" class="hidden fixed inset-0 z-[100004] overflow-y-auto">
    <div class="flex items-stretch justify-center min-h-screen sm:items-center sm:p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm pointer-events-none"></div>
        <div id="edit-modal-panel" class="modal-panel-mobile relative z-10 bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-2xl shadow-2xl max-w-4xl w-full sm:h-auto sm:max-h-[90vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800">
        <!-- Mobile Drag Handle -->
        <div class="modal-drag-handle sm:hidden w-full flex justify-center pt-3 pb-1 bg-white dark:bg-gray-900 translate-y-[1px]" data-close-fn="closeEditModal">
            <div class="drag-bar w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        </div>
        
        <!-- Modal Header -->
        <div class="modal-sticky-header flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 z-10">
            <h2 class="text-lg md:text-2xl font-black text-gray-900 dark:text-white flex items-center tracking-tight uppercase">
                <i class="bi bi-pencil-square mr-3 text-red-600"></i>
                Edit Document
            </h2>
            <button type="button" onclick="closeEditModal()" class="w-11 h-11 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-red-600 transition-all">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body-scroll overflow-y-auto flex-1 min-h-0 custom-scrollbar">
            <form id="edit-form-modal" class="p-4 md:p-8 bg-white dark:bg-gray-900">
                <input type="hidden" name="document_id">
                
                <div class="mb-8">
                    <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 mb-6 flex items-center uppercase tracking-[0.2em]">
                        <span class="w-1 h-4 bg-red-600 rounded-full mr-3"></span>
                        Key Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                        <!-- Document Type -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Document Type <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group">
                                <i class="bi bi-tag absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500"></i>
                                <select name="document_type" required class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold text-sm appearance-none">
                                    <option value="ordinance">Ordinance</option>
                                    <option value="resolution">Resolution</option>
                                    <option value="session">Session Minutes</option>
                                    <option value="agenda">Agenda</option>
                                    <option value="committee">Committee Report</option>
                                    <option value="hearing">Public Hearing</option>
                                    <option value="consultation">Public Consultation</option>
                                    <option value="research">Research Document</option>
                                    <option value="other">Other</option>
                                </select>
                                <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                            </div>
                        </div>
                        
                        <!-- Reference Number -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Reference Number <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group">
                                <i class="bi bi-hash absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500"></i>
                                <input type="text" name="reference_number" required class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold text-sm" placeholder="e.g. ORD-2024-001">
                            </div>
                        </div>
                        
                        <!-- Document Title -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Document Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" required class="w-full px-5 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold text-sm" placeholder="Enter the full legislative title">
                        </div>
                        
                        <!-- Description -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Summary / Description
                            </label>
                            <textarea name="description" rows="4" class="w-full px-5 py-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-medium text-sm leading-relaxed" placeholder="Provide a brief overview of the document's content..."></textarea>
                        </div>
                        
                        <!-- Document Date -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Document Date <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group">
                                <i class="bi bi-calendar3 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500"></i>
                                <input type="date" name="document_date" required class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold text-sm">
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Publication Status <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group">
                                <i class="bi bi-shield-check absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500"></i>
                                <select name="status" required class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold text-sm appearance-none">
                                    <option value="draft">Draft (Working Paper)</option>
                                    <option value="pending">For Review</option>
                                    <option value="approved">Approved / Official</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="archived">Archived</option>
                                </select>
                                <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Search Metadata (Tags)
                            </label>
                            <div class="relative group">
                                <i class="bi bi-bookmarks absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500"></i>
                                <input type="text" name="tags" placeholder="e.g. budget, taxation, land-use" class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-medium text-sm">
                            </div>
                            <p class="text-[10px] text-gray-400 pl-1 mt-1 italic">Separate tags with commas to improve search relevance.</p>
                        </div>
                    </div>
                </div>

                <!-- Sticky Footer within Scroll Area for Forms -->
                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800 mt-4">
                    <button type="button" onclick="closeEditModal()" class="w-full sm:w-auto order-2 sm:order-1 px-8 py-3 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-black uppercase tracking-widest text-[11px] transition-all">
                        Discard Changes
                    </button>
                    <button type="submit" class="w-full sm:w-auto order-1 sm:order-2 px-10 py-3 bg-red-600 dark:bg-red-600 hover:bg-red-700 dark:hover:bg-red-500 text-white rounded-xl font-black uppercase tracking-widest text-[11px] shadow-lg shadow-red-200 dark:shadow-none transition-all active:scale-95">
                        Update Record
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div id="preview-modal" class="hidden fixed inset-0 z-[100002] overflow-y-auto">
    <div class="flex items-stretch justify-center min-h-screen sm:items-center sm:p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm pointer-events-none"></div>
<div id="preview-modal-panel" class="modal-panel-mobile relative z-10 bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-3xl shadow-2xl max-w-6xl w-full sm:h-[85vh] sm:max-h-[85vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800">
        <!-- Mobile Drag Handle -->
        <div class="modal-drag-handle sm:hidden w-full flex justify-center pt-3 pb-1 bg-white dark:bg-gray-900 translate-y-[1px]" data-close-fn="closePreviewModal">
            <div class="drag-bar w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        </div>

        <!-- Sticky Modal Header -->
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

        <!-- Scrollable Modal Content -->
        <div id="preview-content" class="modal-body-scroll overflow-y-auto overflow-x-hidden flex-1 min-h-0 bg-white dark:bg-gray-900" style="-webkit-overflow-scrolling: touch; overscroll-behavior: contain;">
            <!-- Content injected by JS -->
        </div>
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
            <div id="activity-content" class="modal-body-scroll overflow-y-auto flex-1 min-h-0 custom-scrollbar">
                <!-- Content injected by JS -->
            </div>
        </div>
    </div>
</div>


<!-- Upload Document Modal -->
<div id="upload-modal" class="hidden fixed inset-0 z-[100004] overflow-y-auto">
    <div class="flex items-stretch justify-center min-h-screen sm:items-center sm:p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm pointer-events-none"></div>
        <div id="upload-modal-panel" class="modal-panel-mobile relative z-10 bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-2xl shadow-2xl max-w-4xl w-full sm:h-auto sm:max-h-[90vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800">
        <!-- Mobile Drag Handle -->
        <div class="modal-drag-handle sm:hidden w-full flex justify-center pt-3 pb-1 bg-white dark:bg-gray-900 translate-y-[1px]" data-close-fn="closeUploadModal">
            <div class="drag-bar w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        </div>

        <!-- Modal Header -->
        <div class="modal-sticky-header flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 z-10">
            <h2 class="text-lg md:text-2xl font-black text-gray-900 dark:text-white flex items-center tracking-tight uppercase">
                <i class="bi bi-cloud-arrow-up mr-3 text-red-600"></i>
                Upload Repository
            </h2>
            <button type="button" onclick="closeUploadModal()" class="w-11 h-11 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-red-600 transition-all transform-none">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body-scroll overflow-y-auto flex-1 min-h-0 custom-scrollbar">
            <form id="upload-form-modal" class="p-4 md:p-8 bg-white dark:bg-gray-900">
                <!-- File Upload Section -->
                <div class="mb-10">
                    <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 mb-6 flex items-center uppercase tracking-[0.2em]">
                        <span class="w-1 h-4 bg-red-600 rounded-full mr-3"></span>
                        Document Binary
                    </h3>
                    
                    <!-- Drag & Drop Area -->
                    <div id="drop-zone-modal" class="bg-gray-50/50 dark:bg-gray-800/30 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-8 md:p-12 text-center hover:border-red-500/50 hover:bg-red-50/30 dark:hover:bg-red-900/5 transition-all duration-300 cursor-pointer group relative overflow-hidden">
                        <div class="relative z-10">
                            <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-2xl shadow-sm flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-500">
                                <i class="bi bi-clipboard2-plus text-4xl text-gray-400 group-hover:text-red-500 transition-colors"></i>
                            </div>
                            <p class="text-lg font-black text-gray-900 dark:text-gray-100 mb-2">Ingest New Document</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-8 max-w-xs mx-auto">Drop your legislative files here or browse for local records.</p>
                            <input type="file" id="file-input-modal" name="document_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" class="hidden" required>
                            <button type="button" onclick="event.stopPropagation(); document.getElementById('file-input-modal').click()" class="no-ripple bg-gray-900 dark:bg-black text-white px-10 py-3.5 rounded-xl font-black uppercase tracking-[0.15em] text-[11px] shadow-xl transition-colors inline-flex items-center justify-center min-w-[170px] h-12 flex-shrink-0 transform-none hover:transform-none active:transform-none">
                                <i class="bi bi-plus-lg mr-2 font-black"></i> Choose File
                            </button>
                            <p class="text-[10px] text-gray-400 mt-8 uppercase tracking-widest font-bold">
                                PDF, DOCX, XLSX (Max 50MB) 
                            </p>
                        </div>
                    </div>
                    
                    <!-- File Preview -->
                    <div id="file-preview-modal" class="hidden mt-6 p-5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm animate-in fade-in slide-in-from-top-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 rounded-xl flex items-center justify-center mr-4">
                                    <i class="bi bi-file-earmark-pdf text-red-600 text-xl"></i>
                                </div>
                                <div class="min-w-0">
                                    <p id="file-name-modal" class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate"></p>
                                    <p id="file-size-modal" class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase"></p>
                                </div>
                            </div>
                            <button type="button" id="remove-file-modal" class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-red-600 bg-gray-50 dark:bg-gray-700/50 rounded-xl transition-colors">
                                <i class="bi bi-trash text-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Document Information -->
                <div class="mb-8">
                    <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 mb-6 flex items-center uppercase tracking-[0.2em]">
                        <span class="w-1 h-4 bg-red-600 rounded-full mr-3"></span>
                        Legislative Metadata
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                        <!-- Document Type -->
                        <div class="space-y-1.5 relative z-30">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Document Type <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group custom-select-container">
                                <i class="bi bi-bookmark-plus absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 z-10"></i>
                                <div id="upload-document-type-trigger" class="w-full pl-11 pr-10 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold text-sm cursor-pointer flex items-center justify-between">
                                    <span id="upload-document-type-value">Select Category</span>
                                    <i class="bi bi-chevron-down text-gray-400 text-xs"></i>
                                </div>
                                <div id="upload-document-type-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-xl z-[100] max-h-64 overflow-y-auto">
                                    <div class="p-2 space-y-1">
                                        <div class="upload-document-type-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="">Select Category</div>
                                        <div class="upload-document-type-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="ordinance">Ordinance</div>
                                        <div class="upload-document-type-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="resolution">Resolution</div>
                                        <div class="upload-document-type-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="session">Session Minutes</div>
                                        <div class="upload-document-type-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="agenda">Agenda</div>
                                        <div class="upload-document-type-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="committee">Committee Report</div>
                                        <div class="upload-document-type-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="hearing">Public Hearing</div>
                                        <div class="upload-document-type-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="consultation">Public Consultation</div>
                                        <div class="upload-document-type-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="research">Research Document</div>
                                    </div>
                                </div>
                                <input type="hidden" name="document_type" id="upload-document-type-input" required>
                            </div>
                        </div>
                        
                        <!-- Reference Number -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Reference Number <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group">
                                <i class="bi bi-hash absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500"></i>
                                <input type="text" name="reference_number" id="upload-reference-number" required placeholder="e.g. ORD-2025-042" class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold text-sm">
                            </div>
                        </div>
                        
                        <!-- Document Title -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Legislative Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" required placeholder="Enter the official title of the record" class="w-full px-5 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold text-sm">
                        </div>
                        
                        <!-- Description -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Summary / Description
                            </label>
                            <textarea name="description" rows="3" placeholder="Briefly describe the purpose or content of this document..." class="w-full px-5 py-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-2xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-medium text-sm"></textarea>
                        </div>
                        
                        <!-- Document Date -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Official Date <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group">
                                <i class="bi bi-calendar3 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500"></i>
                                <input type="date" name="document_date" id="document-date-modal" required class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold text-sm">
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="space-y-1.5 relative z-30">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Initial Status <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group custom-select-container">
                                <i class="bi bi-activity absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 z-10"></i>
                                <div id="status-modal-trigger" class="w-full pl-11 pr-10 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold text-sm cursor-pointer flex items-center justify-between">
                                    <span id="status-modal-value">Draft</span>
                                    <i class="bi bi-chevron-down text-gray-400 text-xs"></i>
                                </div>
                                <div id="status-modal-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-xl z-[100] max-h-64 overflow-y-auto">
                                    <div class="p-2 space-y-1">
                                        <div class="status-modal-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="draft">Draft</div>
                                        <div class="status-modal-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="published">Published</div>
                                    </div>
                                </div>
                                <input type="hidden" name="status" id="status-modal-input" required value="draft">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800 mt-4">
                    <button type="button" onclick="closeUploadModal()" class="w-full sm:w-auto order-2 sm:order-1 px-8 py-3 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-black uppercase tracking-widest text-[11px] transition-all">
                        Cancel Upload
                    </button>
                    <button type="submit" id="upload-submit-btn" class="w-full sm:w-auto order-1 sm:order-2 px-10 py-3 bg-red-600 dark:bg-red-600 hover:bg-red-700 dark:hover:bg-red-500 text-white rounded-xl font-black uppercase tracking-widest text-[11px] shadow-lg shadow-red-200 dark:shadow-none transition-all active:scale-95">
                        Submit Repository
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>
</div>

<script>
// Custom Dropdown Helper Function
function initCustomDropdown(triggerId, dropdownId, valueId, inputId, optionClass, defaultValue, onChangeCallback = null, autoSubmit = true) {
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
            
            // Trigger change event only for non-filter dropdowns (e.g. modal forms)
            if (autoSubmit) {
                hiddenInput.dispatchEvent(new Event('change'));
            }
            
            // Call onChange callback if provided
            if (onChangeCallback && typeof onChangeCallback === 'function') {
                onChangeCallback(value);
            }
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
    initCustomDropdown('type-filter-trigger', 'type-filter-dropdown', 'type-filter-value', 'type-filter-input', '.type-filter-option', 'All Types', null, false);
    initCustomDropdown('status-filter-trigger', 'status-filter-dropdown', 'status-filter-value', 'status-filter-input', '.status-filter-option', 'All Status', null, false);
    initCustomDropdown('upload-document-type-trigger', 'upload-document-type-dropdown', 'upload-document-type-value', 'upload-document-type-input', '.upload-document-type-option', 'Select Category', autoGenerateReference);
    initCustomDropdown('status-modal-trigger', 'status-modal-dropdown', 'status-modal-value', 'status-modal-input', '.status-modal-option', 'Draft');
});

// Modal File Upload Handling
const dropZoneModal = document.getElementById('drop-zone-modal');
const fileInputModal = document.getElementById('file-input-modal');
const filePreviewModal = document.getElementById('file-preview-modal');
const fileNameModal = document.getElementById('file-name-modal');
const fileSizeModal = document.getElementById('file-size-modal');
const removeFileModal = document.getElementById('remove-file-modal');

// Click to upload
dropZoneModal.addEventListener('click', (e) => {
    if (e.target !== fileInputModal) {
        fileInputModal.click();
    }
});

// Prevent default drag behaviors
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZoneModal.addEventListener(eventName, preventDefaultsModal, false);
});

function preventDefaultsModal(e) {
    e.preventDefault();
    e.stopPropagation();
}

// Highlight drop zone when dragging over it
['dragenter', 'dragover'].forEach(eventName => {
    dropZoneModal.addEventListener(eventName, () => {
        dropZoneModal.classList.add('border-red-500', 'bg-red-50');
    });
});

['dragleave', 'drop'].forEach(eventName => {
    dropZoneModal.addEventListener(eventName, () => {
        dropZoneModal.classList.remove('border-red-500', 'bg-red-50');
    });
});

// Handle dropped files
dropZoneModal.addEventListener('drop', (e) => {
    const files = e.dataTransfer.files;
    if (files.length) {
        fileInputModal.files = files;
        displayFileModal(files[0]);
    }
});

// Handle file selection
fileInputModal.addEventListener('change', (e) => {
    if (e.target.files.length) {
        displayFileModal(e.target.files[0]);
    }
});

// Display selected file
function displayFileModal(file) {
    fileNameModal.textContent = file.name;
    fileSizeModal.textContent = formatFileSizeModal(file.size);
    filePreviewModal.classList.remove('hidden');
    dropZoneModal.classList.add('hidden');
}

// Remove file
removeFileModal.addEventListener('click', () => {
    fileInputModal.value = '';
    filePreviewModal.classList.add('hidden');
    dropZoneModal.classList.remove('hidden');
});

// Format file size
function formatFileSizeModal(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Set default date to today
document.getElementById('document-date-modal').valueAsDate = new Date();

// Auto-generate reference number when document type or date changes
const typeSelect = document.getElementById('upload-document-type-input');
const dateInput = document.getElementById('document-date-modal');
const referenceInput = document.getElementById('upload-reference-number');

async function editDocument(id) {
    try {
        const response = await fetch(App.apiUrl('documents', `get_details.php?id=${id}`));
        const res = await response.json();
        
        if (res.success) {
            const doc = res.document;
            const form = document.getElementById('edit-form-modal');
            
            // Fill form fields
            form.querySelector('[name="document_id"]').value = doc.id;
            form.querySelector('[name="title"]').value = doc.title;
            form.querySelector('[name="document_type"]').value = doc.document_type;
            form.querySelector('[name="reference_number"]').value = doc.reference_number;
            form.querySelector('[name="document_date"]').value = doc.document_date;
            form.querySelector('[name="status"]').value = doc.status;
            form.querySelector('[name="description"]').value = doc.description || '';
            form.querySelector('[name="tags"]').value = doc.tags || '';

            // Track compliance status to gate the approve/publish option
            form.dataset.complianceStatus = doc.compliance_status || 'pending';
            updateApprovedOptionState();

            openEditModal();
        } else {
            alert(res.error || 'Failed to load document details');
        }
    } catch (e) {
        alert('Failed to connect to server');
    }
}

function updateApprovedOptionState() {
    const form = document.getElementById('edit-form-modal');
    const statusSelect = form?.querySelector('[name="status"]');
    if (!statusSelect) return;

    const complianceStatus = form.dataset.complianceStatus || 'pending';
    const existingApproved = statusSelect.querySelector('option[value="approved"]');

    if (complianceStatus !== 'compliant') {
        if (existingApproved) existingApproved.remove();
    } else if (!existingApproved) {
        statusSelect.add(new Option('Approved / Official', 'approved'));
    }
}

async function deleteDocument(id) {
    if (!confirm('Are you sure you want to delete this document? This will move it to trash.')) return;
    
    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', App.getCsrfToken());

        const response = await fetch(App.apiUrl('documents', 'delete.php'), {
            method: 'POST',
            body: formData
        });
        
        const res = await response.json();
        if (res.success) {
            alert('Document deleted successfully');
            location.reload();
        } else {
            alert(res.error || 'Failed to delete document');
        }
    } catch (e) {
        alert('Failed to process delete request');
    }
}

function openEditModal() {
    const modal = document.getElementById('edit-modal');
    modal.classList.remove('hidden');
    setTimeout(() => {
        const panel = document.getElementById('edit-modal-panel');
        panel.classList.remove('translate-y-full', 'opacity-0');
        panel.classList.add('translate-y-0', 'opacity-100');
    }, 10);
}

function closeEditModal() {
    const modal = document.getElementById('edit-modal');
    const modalContainer = document.getElementById('edit-modal-panel');
    modalContainer.classList.add('translate-y-full', 'opacity-0');
    modalContainer.classList.remove('translate-y-0', 'opacity-100');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

async function autoGenerateReference() {
    const type = typeSelect.value;
    const date = dateInput.value;
    
    if (!type) return;

    try {
        const year = date ? new Date(date).getFullYear() : new Date().getFullYear();
        const originalPlaceholder = referenceInput.placeholder;
        referenceInput.placeholder = 'Generating...';
        
        const response = await fetch(`<?php echo DOCUMENTS_URL; ?>/api/generate_reference.php?type=${type}&year=${year}`);
        const result = await response.json();
        
        if (result.success) {
            referenceInput.value = result.reference_number;
        }
        
        referenceInput.placeholder = originalPlaceholder;
    } catch (error) {
        console.error('Failed to generate reference number:', error);
    }
}

// Event listeners are now handled by the custom dropdown callback
dateInput.addEventListener('change', autoGenerateReference);

// Handle form submission
document.getElementById('upload-form-modal').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Validate file is selected
    if (!fileInputModal.files.length) {
        alert('Please select a file to upload');
        return;
    }
    
    // Create FormData
    const formData = new FormData(e.target);
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split mr-2 animate-spin"></i>Uploading...';
    
    try {
        const response = await fetch('<?php echo DOCUMENTS_URL; ?>/api/upload.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Document uploaded successfully!');
            closeUploadModal();
            location.reload(); // Reload to show new document
        } else {
            alert('Error: ' + (result.error || 'Upload failed'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        alert('Network error: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Handle Edit form submission
document.getElementById('edit-form-modal').addEventListener('submit', async (e) => {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const status = formData.get('status');
    const complianceStatus = form.dataset.complianceStatus || 'pending';

    if (status === 'approved' && complianceStatus !== 'compliant') {
        alert('This document is not compliant. Run a compliance check before approving/publishing.');
        return;
    }

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-arrow-repeat mr-2 animate-spin"></i>Updating...';

    try {
        const response = await fetch('<?php echo DOCUMENTS_URL; ?>/api/update.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Document updated successfully!');
            closeEditModal();
            location.reload(); 
        } else {
            alert('Error: ' + (result.error || 'Update failed'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        alert('Network error: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>

<!-- Original File Preview Modal -->
<div id="original-file-preview-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-[100003] flex items-stretch sm:items-center justify-center sm:p-4" onclick="if(event.target===this) closeOriginalFilePreviewModal()">
    <div id="original-file-preview-content" class="modal-panel-mobile relative bg-white dark:bg-gray-900 sm:rounded-2xl shadow-2xl max-w-6xl w-full max-h-[100dvh] sm:h-[92vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 sm:scale-95 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800">
        <div class="modal-drag-handle sm:hidden w-full flex justify-center pt-3 pb-1 bg-white dark:bg-gray-900 translate-y-[1px]" data-close-fn="closeOriginalFilePreviewModal"><div class="drag-bar w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div></div>
        <!-- Header -->
        <div class="mobile-preview-header flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex-shrink-0">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex-shrink-0">
                    <i class="bi bi-file-earmark-text text-emerald-600 dark:text-emerald-400 text-xl"></i>
                </div>
                <div class="min-w-0 w-full">
                    <h3 id="original-file-preview-title" class="header-title w-full text-xs sm:text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest truncate">Document Preview</h3>
                    <p id="original-file-preview-type" class="hidden sm:block text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider"></p>
                    <div id="original-file-preview-compliance" class="hidden sm:flex mt-1 items-center"></div>
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
                <button type="button" id="original-file-run-compliance" onclick="runComplianceCheckInModal()" class="inline-flex items-center justify-center gap-1.5 h-11 w-11 sm:w-auto sm:px-4 rounded-lg text-[10px] font-bold uppercase tracking-wider text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                    <i class="bi bi-shield-check text-base"></i>
                    <span class="hidden sm:inline">Compliance</span>
                </button>
                <button type="button" onclick="closeOriginalFilePreviewModal()" class="h-11 w-11 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                    <i class="bi bi-x-lg text-base"></i>
                </button>
            </div>
        </div>
        <!-- Preview + Compliance -->
        <div class="flex flex-col sm:flex-row flex-1 overflow-hidden">
            <div id="original-file-preview-body" class="mobile-preview-body flex-1 overflow-y-auto overflow-x-hidden bg-gray-100 dark:bg-gray-950 min-h-0" style="-webkit-overflow-scrolling: touch; overscroll-behavior: contain;">
            </div>
            <div id="original-file-preview-analysis" class="compliance-sheet w-full sm:w-80 border-t sm:border-t-0 sm:border-l border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 flex flex-col overflow-hidden sm:overflow-y-auto">
                <button type="button" onclick="toggleComplianceSheet()" class="compliance-sheet-handle sm:hidden w-full px-4 py-3 flex items-center justify-between border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Compliance</span>
                    <i class="bi bi-chevron-up compliance-sheet-chevron transition-transform duration-300"></i>
                </button>
                <div id="original-file-analysis-content" class="compliance-sheet-body p-4 text-sm overflow-y-auto"></div>
            </div>
        </div>
    </div>
</div>

<script>
function openOriginalFilePreviewModal(docId, fileName, fileType, complianceStatus = 'pending', rejectionNotes = '', canRun = '0') {
    window.currentPreviewDocId = docId;
    const modal = document.getElementById('original-file-preview-modal');
    const body = document.getElementById('original-file-preview-body');
    const titleEl = document.getElementById('original-file-preview-title');
    const typeEl = document.getElementById('original-file-preview-type');
    const complianceEl = document.getElementById('original-file-preview-compliance');
    const newTabLink = document.getElementById('original-file-preview-newtab');
    const downloadLink = document.getElementById('original-file-preview-download');
    const runComplianceBtn = document.getElementById('original-file-run-compliance');

    const previewUrl = App.apiUrl('documents', `preview.php?id=${docId}`);
    const downloadUrl = App.apiUrl('documents', `download.php?id=${docId}`);

    titleEl.textContent = fileName || 'Document Preview';
    typeEl.textContent = (fileType || '').replace('application/', '').replace('image/', 'img/');
    if (complianceEl) complianceEl.innerHTML = getComplianceBadgeHTML(complianceStatus);
    if (runComplianceBtn) runComplianceBtn.style.display = canRun === '1' ? '' : 'none';

    newTabLink.href = previewUrl;
    downloadLink.href = downloadUrl;

    loadComplianceAnalysis(docId);

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
        body.innerHTML = `<div class="flex items-center justify-center h-full p-4 overflow-auto"><img src="${previewUrl}" alt="${escapeHtml(fileName)}" class="max-w-full max-h-full object-contain rounded-lg shadow-lg"></div>`;
    } else {
        const ext = (fileName || '').split('.').pop().toUpperCase();
        body.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full p-12 text-center">
                <div class="w-20 h-20 rounded-2xl bg-gray-200 dark:bg-gray-800 flex items-center justify-center mb-5">
                    <i class="bi bi-file-earmark-x text-4xl text-gray-400 dark:text-gray-600"></i>
                </div>
                <h4 class="text-base font-bold text-gray-700 dark:text-gray-300 mb-2">Cannot preview ${ext} files in browser</h4>
                <p class="text-sm text-gray-400 dark:text-gray-500 max-w-md mb-6">This file type cannot be displayed directly in the web browser. You can download it to view the full document.</p>
                <a href="${downloadUrl}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold uppercase tracking-widest text-[11px] shadow-lg transition-all active:scale-95">
                    <i class="bi bi-download text-base"></i> Download File
                </a>
            </div>`;
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    const content = document.getElementById('original-file-preview-content');
    setTimeout(() => {
        if (content) {
            content.classList.remove('translate-y-full', 'sm:scale-95', 'opacity-0');
            content.classList.add('translate-y-0', 'sm:scale-100', 'opacity-100');
        }
    }, 10);
}

async function loadComplianceAnalysis(docId) {
    const container = document.getElementById('original-file-analysis-content');
    if (!container) return;
    container.innerHTML = '<div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400"><i class="bi bi-arrow-repeat animate-spin"></i>Loading analysis...</div>';
    try {
        const response = await fetch(App.apiUrl('documents', 'get-compliance-results.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ document_id: docId })
        });
        const data = await response.json();
        if (data.success) {
            renderComplianceAnalysis(data);
        } else {
            container.innerHTML = '<p class="text-xs text-red-500">Could not load analysis: ' + escapeHtml(data.error || 'Unknown error') + '</p>';
        }
    } catch (error) {
        container.innerHTML = '<p class="text-xs text-red-500">Could not load analysis.</p>';
    }
}

function renderComplianceAnalysis(data) {
    const container = document.getElementById('original-file-analysis-content');
    if (!container) return;
    const status = data.compliance_status || 'pending';
    const badge = getComplianceBadgeHTML(status);

    let resultsHtml = '';
    if (data.results && data.results.length > 0) {
        resultsHtml = '<div class="space-y-2">';
        data.results.forEach(function(r) {
            const passed = r.status === 'compliant';
            const icon = passed ? 'bi-check-circle text-green-600 dark:text-green-400' : 'bi-x-circle text-red-600 dark:text-red-400';
            const barColor = passed ? 'bg-green-500' : 'bg-red-500';
            const code = escapeHtml(r.code || r.title || 'Rule');
            const title = r.code ? escapeHtml(r.title || '') : '';
            const score = parseInt(r.score || 0, 10);
            resultsHtml += '<div class="p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">' +
                '<div class="flex items-center gap-2 mb-1">' +
                    '<i class="bi ' + icon + '"></i>' +
                    '<span class="font-medium text-xs text-gray-800 dark:text-gray-200">' + code + '</span>' +
                '</div>' +
                (title ? '<p class="text-[10px] text-gray-500 dark:text-gray-400 mb-1">' + title + '</p>' : '') +
                '<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mb-1">' +
                    '<div class="' + barColor + ' h-1.5 rounded-full" style="width: ' + Math.min(100, Math.max(0, score)) + '%;"></div>' +
                '</div>' +
                '<p class="text-[10px] text-gray-500 dark:text-gray-400">' + escapeHtml(r.explanation || '') + '</p>' +
            '</div>';
        });
        resultsHtml += '</div>';
    } else {
        resultsHtml = '<p class="text-xs text-gray-500 dark:text-gray-400">No analysis available. Click Compliance to run a check.</p>';
    }

    container.innerHTML = '<div class="space-y-4">' +
        '<div>' +
            '<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Compliance Status</p>' +
            '<div>' + badge + '</div>' +
        '</div>' +
        (data.rejection_notes ? '<div class="p-3 rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-900/40 text-red-800 dark:text-red-300 text-xs">' +
            '<p class="font-semibold mb-1"><i class="bi bi-exclamation-circle mr-1"></i>Rejection Notes</p>' +
            '<p>' + escapeHtml(data.rejection_notes) + '</p>' +
        '</div>' : '') +
        '<div>' +
            '<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Rule Analysis</p>' +
            resultsHtml +
        '</div>' +
    '</div>';
}

async function runComplianceCheckInModal() {
    const docId = window.currentPreviewDocId;
    if (!docId) return;
    const btn = document.getElementById('original-file-run-compliance');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin text-base"></i><span class="hidden sm:inline">Checking...</span>';
    }
    try {
        const response = await fetch(App.apiUrl('documents', 'check-compliance.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ document_id: docId })
        });
        const data = await response.json();
        if (data.success) {
            const isCompliant = data.compliance_status === 'compliant';
            showToast('Compliance status: ' + data.compliance_status, isCompliant ? 'success' : 'warning');
            const badgeEl = document.getElementById('original-file-preview-compliance');
            if (badgeEl) badgeEl.innerHTML = getComplianceBadgeHTML(data.compliance_status || 'pending');
            renderComplianceAnalysis(data);
        } else {
            showToast('Compliance check failed: ' + (data.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Failed to run compliance check.', 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-shield-check text-base"></i><span class="hidden sm:inline">Compliance</span>';
        }
    }
}

function closeOriginalFilePreviewModal() {
    const modal = document.getElementById('original-file-preview-modal');
    const body = document.getElementById('original-file-preview-body');
    const content = document.getElementById('original-file-preview-content');
    const analysisContent = document.getElementById('original-file-analysis-content');
    if (content) {
        content.classList.add('translate-y-full', 'sm:scale-95', 'opacity-0');
        content.classList.remove('translate-y-0', 'sm:scale-100', 'opacity-100');
    }
    const analysisPanel = document.getElementById('original-file-preview-analysis');
    if (analysisPanel) analysisPanel.classList.remove('expanded');
    setTimeout(() => {
        modal.classList.add('hidden');
        body.innerHTML = '';
        if (analysisContent) analysisContent.innerHTML = '';
        window.currentPreviewDocId = null;
        document.body.style.overflow = '';
    }, 300);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('original-file-preview-modal');
        if (!modal.classList.contains('hidden')) {
            closeOriginalFilePreviewModal();
        }
    }
});

function toggleComplianceSheet() {
    const sheet = document.getElementById('original-file-preview-analysis');
    if (!sheet) return;
    sheet.classList.toggle('expanded');
}

// Delegate click for original file preview buttons (works for dynamically generated content)
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-original-preview');
    if (!btn) return;
    const docId = btn.getAttribute('data-preview-id');
    const fileName = btn.getAttribute('data-preview-name');
    const fileType = btn.getAttribute('data-preview-type');
    const complianceStatus = btn.getAttribute('data-compliance') || 'pending';
    const rejectionNotes = btn.getAttribute('data-rejection-notes') || '';
    const canRun = btn.getAttribute('data-can-run') || '0';
    if (docId) {
        openOriginalFilePreviewModal(parseInt(docId, 10), fileName, fileType, complianceStatus, rejectionNotes, canRun);
    }
});

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

async function checkCompliance(docId, btn) {
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i>';
    }
    try {
        const response = await fetch(App.apiUrl('documents', 'check-compliance.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ document_id: docId })
        });
        const data = await response.json();
        if (data.success) {
            const isCompliant = data.compliance_status === 'compliant';
            showToast('Compliance status: ' + data.compliance_status, isCompliant ? 'success' : 'warning');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showToast('Compliance check failed: ' + (data.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        showToast('Failed to run compliance check.', 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-shield-check"></i>';
        }
    }
}
</script>

