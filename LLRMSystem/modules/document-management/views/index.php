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

function getStatusBadge($status) {
    $badges = [
        'draft' => '<span class="badge badge-secondary"><i class="bi bi-pencil mr-1"></i>Draft</span>',
        'pending' => '<span class="badge badge-warning"><i class="bi bi-clock mr-1"></i>Pending</span>',
        'approved' => '<span class="badge badge-success"><i class="bi bi-check-circle mr-1"></i>Approved</span>',
        'rejected' => '<span class="badge badge-danger"><i class="bi bi-x-circle mr-1"></i>Rejected</span>',
        'archived' => '<span class="badge bg-gray-500 text-white"><i class="bi bi-archive mr-1"></i>Archived</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-info">' . ucfirst($status) . '</span>';
}

function getOcrBadge($ocrStatus) {
    $badges = [
        'completed' => '<span class="badge badge-success text-[10px]" title="OCR Completed"><i class="bi bi-check-circle mr-0.5"></i>OCR</span>',
        'pending' => '<span class="badge badge-warning text-[10px]" title="OCR Pending"><i class="bi bi-hourglass-split mr-0.5"></i>OCR</span>',
        'processing' => '<span class="badge badge-info text-[10px]" title="OCR Processing"><i class="bi bi-arrow-repeat mr-0.5"></i>OCR</span>',
        'failed' => '<span class="badge badge-danger text-[10px]" title="OCR Failed"><i class="bi bi-x-circle mr-0.5"></i>OCR</span>',
        'skipped' => '',
    ];
    return $badges[$ocrStatus] ?? '';
}

function getComplianceBadge($complianceStatus) {
    $status = strtolower(trim($complianceStatus ?? ''));
    if (empty($status)) {
        $status = 'pending';
    }
    $badges = [
        'pending' => '<span class="badge badge-warning compliance-badge text-[10px]" title="Compliance Pending"><i class="bi bi-hourglass-split mr-0.5"></i>Compliance</span>',
        'compliant' => '<span class="badge badge-success compliance-badge text-[10px]" title="Compliant"><i class="bi bi-shield-check mr-0.5"></i>Compliance</span>',
        'non_compliant' => '<span class="badge badge-danger compliance-badge text-[10px]" title="Non-Compliant"><i class="bi bi-shield-exclamation mr-0.5"></i>Non-Compliant</span>',
    ];
    return $badges[$status] ?? '<span class="badge badge-info compliance-badge text-[10px]"><i class="bi bi-shield mr-0.5"></i>' . ucfirst($status) . '</span>';
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
                                    'rejected' => 'Rejected'
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
                    <i class="bi bi-chevron-down ml-2 transition-transform duration-300" id="advanced-filters-chevron" style="transform: rotate(0deg);"></i>
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
                            <option value="compliant" <?php echo (($_GET['compliance_status'] ?? '') === 'compliant') ? 'selected' : ''; ?>>Compliant</option>
                            <option value="non_compliant" <?php echo (($_GET['compliance_status'] ?? '') === 'non_compliant') ? 'selected' : ''; ?>>Non-Compliant</option>
                            <option value="pending" <?php echo (($_GET['compliance_status'] ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
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
            <div class="px-3 py-3.5 md:py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row items-center gap-4">
                    <!-- Source System Tabs -->
                    <div class="grid grid-cols-3 gap-2 w-full md:flex md:items-center md:w-auto pb-1">
                        <?php
                        $sourceSystems = $data['source_systems'] ?? [];
                        $sourceLabels  = $data['source_labels'] ?? [];
                        $sourceCounts  = $data['source_counts'] ?? [];
                        $currentSource = $_GET['source_system'] ?? '';
                        $baseParams = $_GET;
                        unset($baseParams['source_system'], $baseParams['page']);
                        $tabs = ['all' => 'All'] + $sourceLabels;
                        foreach ($tabs as $sourceKey => $label):
                            $sourceValue = $sourceKey === 'all' ? '' : $sourceKey;
                            $linkParams = $baseParams;
                            if ($sourceValue !== '') $linkParams['source_system'] = $sourceValue;
                            $url = '?' . http_build_query($linkParams);
                            $isActive = (string)$currentSource === (string)$sourceValue;
                            $count = $sourceKey === 'all'
                                ? ($sourceCounts['all'] ?? ($data['pagination']['total'] ?? count($data['documents'] ?? [])))
                                : ($sourceCounts[$sourceKey] ?? 0);
                            $activeClass = $isActive
                                ? 'bg-red-600 text-white border-red-600'
                                : 'bg-gray-100 dark:bg-gray-900/80 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700';
                            $badgeClass = $isActive
                                ? 'bg-white text-red-600'
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400';
                        ?>
                        <a href="<?php echo $url; ?>" data-source="<?php echo htmlspecialchars($sourceValue); ?>" class="source-tab min-w-0 w-full md:w-auto inline-flex items-center justify-center px-2 md:px-3 py-2 md:py-1.5 rounded-lg border text-[11px] md:text-xs font-bold transition-colors whitespace-nowrap <?php echo $activeClass; ?>">
                            <?php echo htmlspecialchars($label); ?>
                            <span class="ml-1.5 md:ml-2 inline-flex flex-shrink-0 items-center justify-center px-1.5 py-0.5 text-[9px] md:text-[10px] rounded-full <?php echo $badgeClass; ?>"><?php echo (int)$count; ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <!-- Document Count -->
                    <div class="flex items-center justify-center md:ml-auto gap-2">
                        <div class="inline-flex items-center px-4 py-1.5 bg-gray-100 dark:bg-gray-900/80 text-gray-600 dark:text-gray-400 rounded-full border border-gray-200 dark:border-gray-700/50 text-[11px] font-black uppercase tracking-[0.1em] shadow-inner" id="selected-count">
                            <span id="total-docs" class="text-gray-900 dark:text-white mr-1"><?php echo $data['pagination']['total'] ?? count($data['documents'] ?? []); ?></span> documents found
                        </div>
                        <button type="button" id="bulk-delete-btn" class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-[11px] font-bold uppercase tracking-wider shadow transition-all disabled:opacity-50 disabled:cursor-not-allowed" title="Delete Selected" disabled>
                            <i class="bi bi-trash mr-1.5"></i> Delete Selected
                        </button>
                    </div>
                </div>
            </div>
            <div id="documents-body">
            
            <!-- Table -->
            <!-- Desktop Table View -->
            <div class="hidden md:block drag-scroll overflow-x-auto cursor-grab active:cursor-grabbing select-none">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-3 py-2.5 text-left w-12">
                                <input type="checkbox" id="select-all-top" class="w-4 h-4 text-red-600 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded focus:ring-red-500 cursor-pointer" onchange="toggleSelectAll(this)">
                            </th>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Document
                            </th>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Reference
                            </th>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Size
                            </th>
                            <th class="px-3 py-2.5 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (isset($data['error'])): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="text-red-600">
                                        <i class="bi bi-exclamation-circle text-4xl mb-2"></i>
                                        <p><?php echo htmlspecialchars($data['message']); ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php elseif (empty($data['documents'])): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
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
                                    <td class="px-3 py-3 w-12 text-center">
                                        <input type="checkbox" class="document-checkbox w-4 h-4 text-red-600 dark:text-red-500 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded focus:ring-red-500 cursor-pointer" value="<?php echo $doc['id']; ?>">
                                    </td>
                                    <td class="px-3 py-3">
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
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <span class="badge badge-primary !text-[10px]"><?php echo e(ucfirst($doc['document_type'])); ?></span>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                                        <?php echo htmlspecialchars($doc['reference_number']); ?>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-1">
                                            <?php echo getStatusBadge($doc['status']); ?>
                                            <?php echo getOcrBadge($doc['ocr_status'] ?? ''); ?>
                                            <?php echo getComplianceBadge($doc['compliance_status'] ?? 'pending'); ?>
                                        </div>
                                        <?php if (!empty($doc['status_changed_by_name'])): ?>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5" title="<?php echo !empty($doc['status_changed_at']) ? date('M d, Y H:i', strtotime($doc['status_changed_at'])) : ''; ?>">
                                            by <?php echo htmlspecialchars($doc['status_changed_by_name']); ?>
                                        </p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                                        <?php echo date('M d, Y', strtotime($doc['document_date'])); ?>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                                        <?php echo formatFileSize($doc['file_size']); ?>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-right text-xs font-medium">
                                        <div class="flex justify-end gap-1">
                                            <button type="button" onclick="viewDocument(<?php echo $doc['id']; ?>)" class="no-ripple inline-flex items-center justify-center bg-blue-50 text-blue-600 dark:bg-blue-900/60 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/80 px-2 py-1.5 rounded-md font-semibold text-[10px] md:px-3 md:py-2 md:rounded-lg md:text-xs transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-sm hover:shadow-md dark:shadow-none dark:hover:shadow-none">
                                                <i class="bi bi-eye mr-1"></i> View
                                            </button>
                                            <?php 
                                            $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                                            if ($userRole !== 'viewer'): 
                                            ?>
                                            <a href="<?php echo DOCUMENTS_URL; ?>/api/download.php?id=<?php echo $doc['id']; ?>" class="no-ripple inline-flex items-center justify-center bg-green-50 text-green-600 dark:bg-green-900/60 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/80 px-2 py-1.5 rounded-md font-semibold text-[10px] md:px-3 md:py-2 md:rounded-lg md:text-xs transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-sm hover:shadow-md dark:shadow-none dark:hover:shadow-none">
                                                <i class="bi bi-download mr-1"></i> Download
                                            </a>
                                            <?php endif; ?>
                                            <?php 
                                            $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                                            $isDocOwner = ($doc['uploaded_by'] ?? 0) == ($_SESSION['user_id'] ?? 0);
                                            $isApproved = ($doc['status'] ?? '') === 'approved';
                                            $canEdit = (in_array($userRole, ['super_admin', 'superadmin', 'administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner)) && !$isApproved;
                                            $canDelete = (in_array($userRole, ['super_admin', 'superadmin', 'administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner)) && !$isApproved;
                                            ?>
                                            <?php if ($canEdit): ?>
                                            <button type="button" onclick="editDocument(<?php echo $doc['id']; ?>, <?php echo $canDelete ? 'true' : 'false'; ?>)" class="no-ripple inline-flex items-center justify-center bg-purple-50 text-purple-600 dark:bg-purple-900/60 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/80 px-2 py-1.5 rounded-md font-semibold text-[10px] md:px-3 md:py-2 md:rounded-lg md:text-xs transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-sm hover:shadow-md dark:shadow-none dark:hover:shadow-none">
                                                <i class="bi bi-pencil mr-1"></i> Edit
                                            </button>
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
                            <!-- Top: Checkbox, Type & Date -->
                            <div class="px-4 py-3 bg-gray-50/50 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" class="document-checkbox w-5 h-5 text-red-600 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-red-500 cursor-pointer bg-white dark:bg-gray-800" value="<?php echo $doc['id']; ?>">
                                    <span class="badge badge-primary !text-[10px] !py-0.5">
                                        <?php echo e(ucfirst($doc['document_type'])); ?>
                                    </span>
                                </div>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider">
                                    <i class="bi bi-calendar-event mr-1"></i><?php echo date('M d, Y', strtotime($doc['document_date'])); ?>
                                </span>
                            </div>

                            <!-- Badges Row -->
                            <div class="px-4 pt-3 pb-1 flex flex-wrap items-center gap-1">
                                <?php echo getStatusBadge($doc['status']); ?>
                                <?php echo getOcrBadge($doc['ocr_status'] ?? ''); ?>
                                <?php echo getComplianceBadge($doc['compliance_status'] ?? 'pending'); ?>
                            </div>

                            <!-- Title & Filename -->
                            <div class="px-4 pb-3">
                                <h4 class="text-sm font-black text-gray-900 dark:text-gray-100 mb-0.5 leading-tight line-clamp-2"><?php echo htmlspecialchars($doc['title']); ?></h4>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate font-medium opacity-80"><?php echo htmlspecialchars($doc['file_name']); ?></p>
                            </div>

                            <!-- PDF Icon Banner -->
                            <div class="h-24 <?php echo getFileIconClass($doc['file_type'], $doc['file_name']); ?> flex items-center justify-center mx-4 mb-3 rounded-2xl shadow-sm">
                                <i class="<?php echo getFileIcon($doc['file_type'], $doc['file_name']); ?> text-5xl"></i>
                            </div>

                            <!-- Footer: By User & Actions -->
                            <?php 
                            $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                            $isDocOwner = ($doc['uploaded_by'] ?? 0) == ($_SESSION['user_id'] ?? 0);
                            $isApproved = ($doc['status'] ?? '') === 'approved';
                            $canEdit = (in_array($userRole, ['super_admin', 'superadmin', 'administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner)) && !$isApproved;
                            $canDelete = $canEdit;
                            ?>
                            <div class="px-4 py-3 bg-white dark:bg-gray-800 flex items-center justify-between border-t border-gray-50 dark:border-gray-700/50">
                                <div class="min-w-0 flex-1 pr-3">
                                    <?php if (!empty($doc['status_changed_by_name'])): ?>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate block" title="<?php echo !empty($doc['status_changed_at']) ? date('M d, Y H:i', strtotime($doc['status_changed_at'])) : ''; ?>">
                                        by <?php echo htmlspecialchars($doc['status_changed_by_name']); ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-600">&nbsp;</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <button type="button" class="w-9 h-9 flex items-center justify-center text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-xl transition-all active:scale-90" title="View" onclick="viewDocument(<?php echo $doc['id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <?php if ($userRole !== 'viewer'): ?>
                                    <a href="<?php echo DOCUMENTS_URL; ?>/api/download.php?id=<?php echo $doc['id']; ?>" class="w-9 h-9 flex items-center justify-center text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 rounded-xl transition-all active:scale-90" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($canEdit): ?>
                                    <button type="button" class="w-9 h-9 flex items-center justify-center text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl transition-all active:scale-90" title="Edit" onclick="editDocument(<?php echo $doc['id']; ?>, <?php echo $canDelete ? 'true' : 'false'; ?>)">
                                        <i class="bi bi-pencil"></i>
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
                <div class="px-3 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
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
const currentUserRole = '<?php echo $userRole; ?>';

function filterBySource(source) {
    const params = new URLSearchParams(window.location.search);
    if (source) params.set('source_system', source);
    else params.delete('source_system');
    params.delete('page');
    const url = '?' + params.toString();

    fetch(url)
        .then(r => r.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newBody = doc.getElementById('documents-body');
            const currentBody = document.getElementById('documents-body');
            if (newBody && currentBody) currentBody.innerHTML = newBody.innerHTML;

            document.querySelectorAll('.source-tab').forEach(tab => {
                const isActive = tab.getAttribute('data-source') === source;
                const tabInactive = ['bg-gray-100','dark:bg-gray-900/80','text-gray-700','dark:text-gray-300','border-gray-200','dark:border-gray-700','hover:bg-gray-200','dark:hover:bg-gray-700'];
                const tabActive = ['bg-red-600','text-white','border-red-600'];
                tab.classList.remove(...tabInactive, ...tabActive);
                tab.classList.add(...(isActive ? tabActive : tabInactive));
                const badge = tab.querySelector('span');
                if (badge) {
                    const badgeInactive = ['bg-gray-200','dark:bg-gray-700','text-gray-600','dark:text-gray-400'];
                    const badgeActive = ['bg-white','text-red-600'];
                    badge.classList.remove(...badgeInactive, ...badgeActive);
                    badge.classList.add(...(isActive ? badgeActive : badgeInactive));
                }
            });

            history.pushState({}, '', url);
        })
        .catch(err => console.error('Tab filter failed', err));
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.source-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            filterBySource(this.getAttribute('data-source'));
        });
    });

    // Auto-open document view modal when navigated from notification
    const urlParams = new URLSearchParams(window.location.search);
    const viewDocId = urlParams.get('view_doc');
    if (viewDocId && typeof viewDocument === 'function') {
        viewDocument(parseInt(viewDocId));
    }
});
</script>
<script src="<?php echo asset('js/document-view-modal.js'); ?>?v=<?php echo time(); ?>"></script>

<!-- Edit Document Modal -->
<div id="edit-modal" class="hidden fixed inset-0 z-[100004] overflow-y-auto">
    <div class="flex items-stretch justify-center min-h-screen sm:items-center sm:p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm pointer-events-none"></div>
        <div id="edit-modal-panel" class="relative z-10 bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-2xl shadow-2xl max-w-4xl w-full sm:h-auto sm:max-h-[90vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800">
        <!-- Mobile Drag Handle -->
        <div class="sm:hidden w-full flex justify-center pt-3 pb-1 bg-white dark:bg-gray-900 translate-y-[1px]">
            <div class="w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        </div>
        
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 px-6 py-4 flex items-center justify-between z-10">
            <h2 class="text-xl md:text-2xl font-black text-gray-900 dark:text-white flex items-center tracking-tight uppercase">
                <i class="bi bi-pencil-square mr-3 text-red-600"></i>
                Edit Document
            </h2>
            <button type="button" onclick="closeEditModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-red-600 transition-all">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="overflow-y-auto flex-1 min-h-0 custom-scrollbar">
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
                    <button type="button" id="edit-modal-delete-btn" onclick="deleteDocumentFromEditModal()" class="hidden w-full sm:w-auto order-3 px-8 py-3 border border-red-600 dark:border-red-500 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl font-black uppercase tracking-widest text-[11px] transition-all">
                        Delete
                    </button>
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
        <div id="preview-modal-panel" class="relative z-10 bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-3xl shadow-2xl max-w-6xl w-full sm:h-[85vh] sm:max-h-[85vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800" style="max-height: 100dvh;">
        <!-- Mobile Drag Handle -->
        <div class="sm:hidden w-full flex justify-center pt-3 pb-1 bg-white dark:bg-gray-900 translate-y-[1px]">
            <div class="w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        </div>

        <!-- Sticky Modal Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-6 py-4 flex items-center justify-between z-10">
            <div class="flex items-center">
                <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded-xl mr-3">
                    <i class="bi bi-file-earmark-pdf text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest">Document Preview</h3>
                </div>
            </div>
            <button type="button" onclick="closePreviewModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-all transform-none">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Scrollable Modal Content -->
        <div id="preview-content" class="overflow-y-auto overflow-x-hidden flex-1 min-h-0 bg-white dark:bg-gray-900" style="-webkit-overflow-scrolling: touch; overscroll-behavior: contain;">
            <!-- Content injected by JS -->
        </div>
        </div>
    </div>
</div>

<!-- Activity History Modal -->
<div id="activity-modal" class="hidden fixed inset-0 z-[100003] overflow-y-auto">
    <div class="flex items-stretch justify-center min-h-screen sm:items-center sm:p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm pointer-events-none"></div>
        <div id="activity-modal-content" class="relative z-10 bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-2xl shadow-2xl max-w-lg w-full max-h-[92vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 sm:scale-95 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800">
            <div class="sm:hidden w-full flex justify-center pt-3 pb-1 bg-white dark:bg-gray-900 translate-y-[1px]">
                <div class="w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
            </div>
            <div class="sticky top-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-6 py-4 flex items-center justify-between z-10">
                <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest">Activity History</h3>
                <button type="button" onclick="closeActivityModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-all transform-none">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div id="activity-content" class="overflow-y-auto flex-1 min-h-0 custom-scrollbar">
                <!-- Content injected by JS -->
            </div>
        </div>
    </div>
</div>


<!-- Upload Document Modal -->
<div id="upload-modal" class="hidden fixed inset-0 z-[100004] overflow-y-auto">
    <div class="flex items-stretch justify-center min-h-screen sm:items-center sm:p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm pointer-events-none"></div>
        <div id="upload-modal-panel" class="relative z-10 bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-2xl shadow-2xl max-w-4xl w-full sm:h-auto sm:max-h-[90vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800">
        <!-- Mobile Drag Handle -->
        <div class="sm:hidden w-full flex justify-center pt-3 pb-1 bg-white dark:bg-gray-900 translate-y-[1px]">
            <div class="w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        </div>

        <!-- Modal Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 px-6 py-4 flex items-center justify-between z-10">
            <h2 class="text-xl md:text-2xl font-black text-gray-900 dark:text-white flex items-center tracking-tight uppercase">
                <i class="bi bi-cloud-arrow-up mr-3 text-red-600"></i>
                Upload Repository
            </h2>
            <button type="button" onclick="closeUploadModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-red-600 transition-all transform-none">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="overflow-y-auto flex-1 min-h-0 custom-scrollbar">
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

async function editDocument(id, canDelete = false) {
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
            
            const deleteBtn = document.getElementById('edit-modal-delete-btn');
            if (deleteBtn) deleteBtn.classList.toggle('hidden', !canDelete);
            
            openEditModal();
        } else {
            alert(res.error || 'Failed to load document details');
        }
    } catch (e) {
        alert('Failed to connect to server');
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

function deleteDocumentFromEditModal() {
    const form = document.getElementById('edit-form-modal');
    const id = form ? form.querySelector('[name="document_id"]').value : null;
    if (id) deleteDocument(id);
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
    
    // Create FormData
    const formData = new FormData(e.target);
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
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
    <div id="original-file-preview-content" class="bg-white dark:bg-gray-900 rounded-none sm:rounded-2xl shadow-2xl max-w-6xl w-full max-h-[100dvh] sm:h-[92vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 sm:scale-95 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800">
        <div class="sm:hidden w-full flex justify-center pt-3 pb-1 bg-white dark:bg-gray-900 translate-y-[1px]"><div class="w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div></div>
        <!-- Header -->
        <div class="flex flex-row items-center justify-between gap-3 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex-shrink-0">
            <div class="flex items-center gap-3 min-w-1 flex-1">
                <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex-shrink-0">
                    <i class="bi bi-file-earmark-text text-emerald-600 dark:text-emerald-400 text-xl"></i>
                </div>
                <div class="min-w-1 flex-1">
                    <h3 id="original-file-preview-title" class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest truncate leading-tight">Document Preview</h3>
                    <p id="original-file-preview-type" class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider"></p>
                    <div id="original-file-preview-compliance" class="mt-1 text-[10px] sm:text-xs"></div>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a id="original-file-preview-newtab" href="#" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <i class="bi bi-box-arrow-up-right"></i> New Tab
                </a>
                <?php if ($userRole !== 'viewer'): ?>
                <a id="original-file-preview-download" href="#" class="inline-flex items-center justify-center w-9 h-9 sm:w-auto sm:px-3 sm:py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider text-white bg-red-600 hover:bg-red-700 transition-colors" title="Download">
                    <i class="bi bi-download text-base sm:text-sm"></i>
                    <span class="hidden sm:inline ml-1.5">Download</span>
                </a>
                <?php endif; ?>
                <button type="button" onclick="closeOriginalFilePreviewModal()" class="w-9 h-9 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors flex-shrink-0">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>
        </div>
        <!-- Body -->
        <div id="original-file-preview-body" class="flex-1 flex flex-col lg:flex-row overflow-hidden bg-gray-100 dark:bg-gray-950 min-h-0" style="-webkit-overflow-scrolling: touch; overscroll-behavior: contain;">
            <div id="original-file-preview-iframe" class="flex-1 min-h-0 overflow-auto bg-gray-100 dark:bg-gray-950"></div>
            <!-- Desktop Compliance Sidebar -->
            <div id="original-file-preview-sidebar" class="hidden lg:flex flex-col w-full lg:w-80 border-t lg:border-t-0 lg:border-l border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-y-auto">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex items-center gap-2">
                    <i class="bi bi-shield-check text-red-600 dark:text-red-400"></i>
                    <span class="text-sm font-black text-gray-800 dark:text-gray-200 uppercase tracking-widest">Compliance</span>
                </div>
                <div id="original-file-compliance-badge" class="px-4 pt-3 text-[10px] sm:text-xs"></div>
                <div id="original-file-compliance-content" class="p-4 flex-1 min-h-0 overflow-y-auto"></div>
            </div>
        </div>
        <!-- Mobile Compliance Toggle + Panel -->
        <div class="lg:hidden flex flex-col border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
            <button type="button" id="original-file-compliance-toggle" onclick="toggleOriginalFileCompliance()" class="w-full px-4 py-3 flex items-center justify-between text-sm font-bold text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-900/50">
                <span><i class="bi bi-shield-check text-red-600 dark:text-red-400 mr-2"></i>Compliance</span>
                <i class="bi bi-chevron-down transition-transform duration-300 rotate-180" id="original-file-compliance-chevron"></i>
            </button>
            <div id="original-file-compliance-mobile" class="lg:hidden max-h-0 opacity-0 overflow-hidden overflow-y-auto transition-all duration-300 ease-in-out">
                <div id="original-file-compliance-mobile-badge" class="px-4 pt-3 text-[10px] sm:text-xs"></div>
                <div id="original-file-compliance-mobile-content" class="p-4"></div>
            </div>
        </div>
    </div>
</div>

<script>
function openOriginalFilePreviewModal(docId, fileName, fileType, complianceStatus) {
    const modal = document.getElementById('original-file-preview-modal');
    const body = document.getElementById('original-file-preview-body');
    const iframeContainer = document.getElementById('original-file-preview-iframe');
    const titleEl = document.getElementById('original-file-preview-title');
    const typeEl = document.getElementById('original-file-preview-type');
    const complianceEl = document.getElementById('original-file-preview-compliance');
    const newTabLink = document.getElementById('original-file-preview-newtab');
    const downloadLink = document.getElementById('original-file-preview-download');

    // Reset compliance mobile toggle
    const mobilePanel = document.getElementById('original-file-compliance-mobile');
    const chevron = document.getElementById('original-file-compliance-chevron');
    if (mobilePanel) {
        mobilePanel.classList.add('max-h-0', 'opacity-0');
        mobilePanel.classList.remove('max-h-[80vh]', 'opacity-100');
    }
    if (chevron) chevron.classList.add('rotate-180');

    const previewUrl = App.apiUrl('documents', `preview.php?id=${docId}`);
    const downloadUrl = App.apiUrl('documents', `download.php?id=${docId}`);

    titleEl.textContent = fileName || 'Document Preview';
    typeEl.textContent = (fileType || '').replace('application/', '').replace('image/', 'img/');
    if (complianceEl) complianceEl.innerHTML = typeof getComplianceBadgeHTML === 'function' ? getComplianceBadgeHTML(complianceStatus || 'pending') : '';

    if (newTabLink) newTabLink.href = previewUrl;
    if (downloadLink) downloadLink.href = downloadUrl;

    const ft = (fileType || '').toLowerCase();
    const ext = ((fileName || '').split('.').pop() || '').toLowerCase();
    const isPdf = ft === 'application/pdf' || ft === 'pdf' || ext === 'pdf';
    const isDocx = ft === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || ft === 'word' || ext === 'docx' || ext === 'doc';
    const isImage = ft.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(ft.replace('image/', '')) || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(ext);

    if (iframeContainer) {
        if (isPdf || isDocx) {
            iframeContainer.innerHTML = `<iframe src="${previewUrl}" title="Document Preview" frameborder="0" scrolling="auto" allowfullscreen class="w-full h-full border-0 block" style="min-height: 400px;"></iframe>`;
        } else if (isImage) {
            iframeContainer.innerHTML = `<div class="flex items-center justify-center h-full p-4 overflow-auto"><img src="${previewUrl}" alt="${escapeHtml(fileName)}" class="max-w-full max-h-full object-contain rounded-lg shadow-lg"></div>`;
        } else {
            const fileExt = (fileName || '').split('.').pop().toUpperCase();
            iframeContainer.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full p-12 text-center">
                    <div class="w-20 h-20 rounded-2xl bg-gray-200 dark:bg-gray-800 flex items-center justify-center mb-5">
                        <i class="bi bi-file-earmark-x text-4xl text-gray-400 dark:text-gray-600"></i>
                    </div>
                    <h4 class="text-base font-bold text-gray-700 dark:text-gray-300 mb-2">Cannot preview ${fileExt} files in browser</h4>
                    <p class="text-sm text-gray-400 dark:text-gray-500 max-w-md mb-6">This file type cannot be displayed directly in the web browser. You can download it to view the full document.</p>
                    ${currentUserRole !== 'viewer' ? `<a href="${downloadUrl}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold uppercase tracking-widest text-[11px] shadow-lg transition-all active:scale-95">
                        <i class="bi bi-download text-base"></i> Download File
                    </a>` : '<p class="text-sm text-gray-400 dark:text-gray-500 max-w-md">Contact an administrator if you need a copy of this document.</p>'}
                </div>`;
        }
    }

    if (typeof loadComplianceForPreview === 'function') {
        loadComplianceForPreview(docId, 'original-file-compliance-badge', 'original-file-compliance-content', true);
        loadComplianceForPreview(docId, 'original-file-compliance-mobile-badge', 'original-file-compliance-mobile-content', true);
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

function toggleOriginalFileCompliance() {
    const panel = document.getElementById('original-file-compliance-mobile');
    const chevron = document.getElementById('original-file-compliance-chevron');
    if (!panel) return;
    const isOpen = panel.classList.contains('max-h-[80vh]');
    if (isOpen) {
        panel.classList.remove('max-h-[80vh]', 'opacity-100');
        panel.classList.add('max-h-0', 'opacity-0');
        if (chevron) chevron.classList.add('rotate-180');
    } else {
        panel.classList.remove('max-h-0', 'opacity-0');
        panel.classList.add('max-h-[80vh]', 'opacity-100');
        if (chevron) chevron.classList.remove('rotate-180');
    }
}

function closeOriginalFilePreviewModal() {
    const modal = document.getElementById('original-file-preview-modal');
    const body = document.getElementById('original-file-preview-body');
    const iframeContainer = document.getElementById('original-file-preview-iframe');
    const content = document.getElementById('original-file-preview-content');
    if (content) {
        content.classList.add('translate-y-full', 'sm:scale-95', 'opacity-0');
        content.classList.remove('translate-y-0', 'sm:scale-100', 'opacity-100');
    }
    setTimeout(() => {
        modal.classList.add('hidden');
        if (iframeContainer) iframeContainer.innerHTML = '';
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

// Delegate click for original file preview buttons (works for dynamically generated content)
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-original-preview');
    if (!btn) return;
    const docId = btn.getAttribute('data-preview-id');
    const fileName = btn.getAttribute('data-preview-name');
    const fileType = btn.getAttribute('data-preview-type');
    const compliance = btn.getAttribute('data-compliance') || 'pending';
    if (docId) {
        openOriginalFilePreviewModal(parseInt(docId, 10), fileName, fileType, compliance);
    }
});
</script>

