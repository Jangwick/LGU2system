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
        'archived' => '<span class="badge badge-gray"><i class="bi bi-archive mr-1"></i>Archived</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-info">' . ucfirst($status) . '</span>';
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
    
    <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-900 p-6">
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
                    <button onclick="openUploadModal()" class="flex items-center px-4 py-2 !bg-white !text-red-600 border border-red-600 rounded-lg font-bold transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95 shadow-sm">
                        <i class="bi bi-plus-circle mr-2"></i>
                        Upload Document
                    </button>
                    <?php endif; ?>
                    <div class="relative" id="export-dropdown">
                        <button onclick="toggleExportMenu()" class="flex items-center px-4 py-2 bg-red-600 dark:bg-red-700 text-white rounded-lg font-bold transform hover:scale-105 hover:shadow-lg transition-all duration-200 active:scale-95 shadow-sm">
                            <i class="bi bi-download mr-2"></i>
                            Export
                            <i class="bi bi-chevron-down ml-2 transition-transform" id="export-chevron"></i>
                        </button>
                        <div id="export-menu" class="hidden bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700" style="position: fixed; width: 224px; z-index: 99999;">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide bg-gray-50 dark:bg-gray-900/50 border-b dark:border-gray-700">Export List</div>
                            <button onclick="exportList('csv')" 
                               class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-400 flex items-center transition-colors duration-150">
                                <i class="bi bi-filetype-csv mr-3 text-green-600 dark:text-green-500 text-lg"></i>
                                Export as CSV
                            </button>
                            <button onclick="exportList('excel')" 
                               class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-400 flex items-center transition-colors duration-150">
                                <i class="bi bi-file-earmark-excel mr-3 text-green-600 dark:text-green-500 text-lg"></i>
                                Export as Excel
                            </button>
                            <div class="border-t border-gray-200 dark:border-gray-700"></div>
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide bg-gray-50 dark:bg-gray-900/50 border-b dark:border-gray-700">Export Files</div>
                            <button onclick="exportSelectedFiles()" 
                               class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-400 flex items-center transition-colors duration-150">
                                <i class="bi bi-file-earmark-zip mr-3 text-blue-600 dark:text-blue-500 text-lg"></i>
                                Selected Files (ZIP)
                            </button>
                            <button onclick="exportAllFiles()" 
                               class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-400 flex items-center transition-colors duration-150">
                                <i class="bi bi-archive mr-3 text-purple-600 dark:text-purple-500 text-lg"></i>
                                All Files (ZIP)
                            </button>
                        </div>
                    </div>
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
        <div id="filters-section" class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 md:p-6 mb-6 animate-fade-in-up animation-delay-100 hidden md:block border border-transparent dark:border-gray-800">
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Document Type</label>
                    <select id="type-filter" class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 hover:border-red-300 dark:hover:border-red-900">
                        <option value="">All Types</option>
                        <?php 
                        $types = ['ordinance', 'resolution', 'session', 'agenda', 'committee', 'other'];
                        $selectedType = $_GET['type'] ?? '';
                        foreach ($types as $t): ?>
                            <option value="<?php echo $t; ?>" <?php echo $selectedType === $t ? 'selected' : ''; ?>>
                                <?php echo ucfirst($t === 'session' ? 'session minutes' : $t); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select id="status-filter" class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200">
                        <option value="">All Status</option>
                        <?php 
                        $statuses = [
                            'draft' => 'Draft',
                            'pending' => 'Pending Review',
                            'approved' => 'Approved'
                        ];
                        $selectedStatus = $_GET['status'] ?? '';
                        foreach ($statuses as $val => $label): ?>
                            <option value="<?php echo $val; ?>" <?php echo $selectedStatus === $val ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
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
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-200 border border-transparent dark:border-gray-700">
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
                        <button class="w-10 h-10 flex items-center justify-center text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl transition-all" title="Download Selected">
                            <i class="bi bi-download text-lg"></i>
                        </button>
                        <?php if (in_array($userRole, ['administrator', 'officer'])): ?>
                        <button class="w-10 h-10 flex items-center justify-center text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 border border-red-100 dark:border-red-900/30 rounded-xl transition-all" onclick="bulkDelete()" title="Delete Selected">
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
            <div class="hidden md:block drag-scroll overflow-x-auto cursor-grab active:cursor-grabbing select-none">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 md:px-6 py-3 text-left w-12">
                                <!-- Redundant checkbox removed -->
                            </th>
                            <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Document
                            </th>
                            <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Reference
                            </th>
                            <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Size
                            </th>
                            <th class="px-4 md:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
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
                                    <td class="px-4 md:px-6 py-4 w-12 text-center">
                                        <input type="checkbox" class="document-checkbox w-4 h-4 text-red-600 dark:text-red-500 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded focus:ring-red-500 cursor-pointer" value="<?php echo $doc['id']; ?>">
                                    </td>
                                    <td class="px-4 md:px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="<?php echo getFileIconClass($doc['file_type'], $doc['file_name']); ?> rounded-lg p-2 mr-3 flex-shrink-0">
                                                <i class="<?php echo getFileIcon($doc['file_type'], $doc['file_name']); ?> text-xl"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?php echo htmlspecialchars($doc['title']); ?></p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?php echo htmlspecialchars($doc['file_name']); ?></p>
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
                                        <?php echo getStatusBadge($doc['status']); ?>
                                    </td>
                                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo date('M d, Y', strtotime($doc['document_date'])); ?>
                                    </td>
                                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo formatFileSize($doc['file_size']); ?>
                                    </td>
                                    <td class="px-4 md:px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex justify-end gap-3">
                                            <button class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors" title="View" onclick="viewDocument(<?php echo $doc['id']; ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="<?php echo DOCUMENTS_URL; ?>/api/download.php?id=<?php echo $doc['id']; ?>" class="text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 transition-colors" title="Download">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <?php 
                                            $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                                            $isDocOwner = ($doc['uploaded_by'] ?? 0) == ($_SESSION['user_id'] ?? 0);
                                            $isApproved = ($doc['status'] ?? '') === 'approved';
                                            $canEdit = (in_array($userRole, ['administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner)) && !$isApproved;
                                            $canDelete = (in_array($userRole, ['administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner)) && !$isApproved;
                                            ?>
                                            <?php if ($canEdit): ?>
                                            <button class="text-gray-600 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors" title="Edit" onclick="editDocument(<?php echo $doc['id']; ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($canDelete): ?>
                                            <button class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors" title="Delete" onclick="deleteDocument(<?php echo $doc['id']; ?>)">
                                                <i class="bi bi-trash"></i>
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
                            <!-- Top: Type & Date -->
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

                            <!-- Middle: Icon & Title -->
                            <div class="p-4 flex gap-4">
                                <div class="w-16 h-16 rounded-2xl <?php echo getFileIconClass($doc['file_type'], $doc['file_name']); ?> flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <i class="<?php echo getFileIcon($doc['file_type'], $doc['file_name']); ?> text-3xl"></i>
                                </div>
                                <div class="flex-1 min-w-0 flex flex-col justify-center">
                                    <h4 class="text-sm font-black text-gray-900 dark:text-gray-100 mb-1 leading-tight line-clamp-2"><?php echo htmlspecialchars($doc['title']); ?></h4>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate font-medium opacity-80"><?php echo htmlspecialchars($doc['file_name']); ?></p>
                                </div>
                            </div>

                            <!-- Bottom: Status & Actions -->
                            <div class="px-4 py-3 bg-white dark:bg-gray-800 flex items-center justify-between border-t border-gray-50 dark:border-gray-700/50">
                                <div class="flex items-center">
                                    <?php echo getStatusBadge($doc['status']); ?>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button class="w-9 h-9 flex items-center justify-center text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-xl transition-all active:scale-90" title="View" onclick="viewDocument(<?php echo $doc['id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="<?php echo DOCUMENTS_URL; ?>/api/download.php?id=<?php echo $doc['id']; ?>" class="w-9 h-9 flex items-center justify-center text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 rounded-xl transition-all active:scale-90" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <?php 
                                    $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                                    $isDocOwner = ($doc['uploaded_by'] ?? 0) == ($_SESSION['user_id'] ?? 0);
                                    $isApproved = ($doc['status'] ?? '') === 'approved';
                                    $canEdit = (in_array($userRole, ['administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner)) && !$isApproved;
                                    $canDelete = $canEdit; // Same policy for mobile
                                    ?>
                                    <?php if ($canEdit): ?>
                                    <button class="w-9 h-9 flex items-center justify-center text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl transition-all active:scale-90" title="Edit" onclick="editDocument(<?php echo $doc['id']; ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if (isset($canDelete) && $canDelete): ?>
                                    <button class="w-9 h-9 flex items-center justify-center text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded-xl transition-all active:scale-90" title="Delete" onclick="deleteDocument(<?php echo $doc['id']; ?>)">
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
                <div class="px-4 md:px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400 order-2 sm:order-1">
                        Showing <span class="font-medium text-gray-900 dark:text-white"><?php echo (($data['pagination']['current_page'] - 1) * $data['pagination']['per_page']) + 1; ?></span> 
                        to <span class="font-medium text-gray-900 dark:text-white"><?php echo min($data['pagination']['current_page'] * $data['pagination']['per_page'], $data['pagination']['total']); ?></span> 
                        of <span class="font-medium text-gray-900 dark:text-white"><?php echo number_format($data['pagination']['total']); ?></span> results
                    </div>
                    <div class="flex items-center gap-1.5 order-1 sm:order-2">
                        <?php if ($data['pagination']['current_page'] > 1): ?>
                            <a href="?page=<?php echo $data['pagination']['current_page'] - 1; ?><?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>" 
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
                                <a href="?page=<?php echo $i; ?><?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>" 
                                   class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($data['pagination']['current_page'] < $data['pagination']['total_pages']): ?>
                            <a href="?page=<?php echo $data['pagination']['current_page'] + 1; ?><?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>" 
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

<script src="<?php echo asset('js/documents.js'); ?>?v=<?php echo time(); ?>"></script>
<script>
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

function viewDocument(id) {
    const modal = document.getElementById('preview-modal');
    const content = document.getElementById('preview-content');
    const modalContainer = modal.querySelector('div');
    
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
                                <a href="${App.apiUrl('documents', `download.php?id=${doc.id}`)}" class="flex-1 sm:flex-none justify-center bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl font-black uppercase tracking-widest text-[11px] flex items-center shadow-lg shadow-red-200 dark:shadow-none transition-all active:scale-95">
                                    <i class="bi bi-download mr-2 text-base"></i> Download
                                </a>
                                <button onclick="editDocument(${doc.id})" class="flex-1 sm:flex-none justify-center bg-gray-900 dark:bg-black hover:bg-black text-white px-6 py-3 rounded-xl font-black uppercase tracking-widest text-[11px] flex items-center shadow-lg transition-all active:scale-95">
                                    <i class="bi bi-pencil-square mr-2 text-base"></i> Edit
                                </button>
                            </div>
                        </div>

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
                                    </div>
                                    
                                    <div class="mt-8 pt-6 border-t border-gray-50 dark:border-gray-800">
                                        <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2.5">Description / Annotations</label>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-medium">${doc.description || 'No additional notes provided for this record.'}</p>
                                    </div>
                                </section>

                                <section class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 md:p-6">
                                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                                        <span class="w-1.5 h-6 bg-blue-600 rounded-full mr-3"></span>
                                        Version History
                                    </h3>
                                    <div class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                        <p class="text-gray-400 italic text-sm">No previous versions available.</p>
                                    </div>
                                </section>
                            </div>

                            <!-- Right: Sidebar Information -->
                            <div class="space-y-6">
                                <section class="bg-white rounded-2xl border border-gray-100 p-6">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                        <i class="bi bi-link-45deg mr-2 text-indigo-600"></i>
                                        Related Documents
                                    </h3>
                                    <div class="text-center py-6">
                                        <p class="text-gray-400 italic text-sm">No related documents</p>
                                    </div>
                                </section>

                                <section class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                                    <h3 class="text-lg font-bold text-gray-800 mb-5 flex items-center">
                                        <i class="bi bi-lightning-charge mr-2 text-yellow-500"></i>
                                        Quick Actions
                                    </h3>
                                    <div class="grid gap-3">
                                        <button class="flex items-center w-full px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 rounded-xl border border-gray-100 transition-colors">
                                            <i class="bi bi-share mr-3 text-blue-500"></i> Share Document
                                        </button>
                                        <button class="flex items-center w-full px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 rounded-xl border border-gray-100 transition-colors">
                                            <i class="bi bi-printer mr-3 text-gray-500"></i> Print Details
                                        </button>
                                        <button class="flex items-center w-full px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 rounded-xl border border-gray-100 transition-colors">
                                            <i class="bi bi-clock-history mr-3 text-purple-500"></i> Activity History
                                        </button>
                                        <div class="mt-2 pt-2 border-t border-gray-50">
                                            ${doc.status !== 'approved' ? `
                                            <button onclick="deleteDocument(${doc.id})" class="flex items-center w-full px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 rounded-xl transition-colors">
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
            } else {
                content.innerHTML = `<div class="p-12 text-center text-red-600">${res.error}</div>`;
            }
        })
        .catch(e => {
            content.innerHTML = `<div class="p-12 text-center text-red-600">Failed to load document details</div>`;
        });
}

function closePreviewModal() {
    const modal = document.getElementById('preview-modal');
    const modalContainer = modal.querySelector('div');
    
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
        'archived': '<span class="badge badge-gray"><i class="bi bi-archive mr-1"></i>Archived</span>'
    };
    return badges[s] || `<span class="badge badge-info">${status}</span>`;
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
    const modal = document.getElementById('edit-modal');
    const form = document.getElementById('edit-form-modal');
    const modalContainer = modal.querySelector('div');
    
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
    const modalContainer = modal.querySelector('div');
    
    modalContainer.classList.add('translate-y-full', 'opacity-0');
    modalContainer.classList.remove('translate-y-0', 'opacity-100');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('edit-form-modal').reset();
    }, 300);
}

function deleteDocument(id) {
    if (confirm('Are you sure you want to delete this document?')) {
        fetch(App.apiUrl('documents', 'delete.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Document deleted successfully', 'success');
                location.reload();
            } else {
                showNotification(data.error || 'Failed to delete document', 'error');
            }
        })
        .catch(error => {
            showNotification('An error occurred', 'error');
        });
    }
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

function toggleExportMenu() {
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
    const modalContainer = modal.querySelector('div');
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    setTimeout(() => {
        modalContainer.classList.remove('translate-y-full', 'opacity-0');
        modalContainer.classList.add('translate-y-0', 'opacity-100');
    }, 10);
}

function closeUploadModal() {
    const modal = document.getElementById('upload-modal');
    const modalContainer = modal.querySelector('div');
    
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
<div id="edit-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center sm:p-4">
    <div class="bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-2xl shadow-2xl max-w-4xl w-full max-h-[92vh] sm:max-h-[90vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800">
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
            <button onclick="closeEditModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-red-600 transition-all">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="overflow-y-auto flex-1 custom-scrollbar">
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

<!-- Document Preview Modal -->
<div id="preview-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-end sm:items-center justify-center sm:p-4">
    <div class="bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-3xl shadow-2xl max-w-6xl w-full max-h-[92vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800">
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
            <button onclick="closePreviewModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-all">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Scrollable Modal Content -->
        <div id="preview-content" class="overflow-y-auto overflow-x-hidden flex-1 bg-white dark:bg-gray-900">
            <!-- Content injected by JS -->
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div id="upload-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center sm:p-4">
    <div class="bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-2xl shadow-2xl max-w-4xl w-full max-h-[92vh] sm:max-h-[90vh] overflow-hidden flex flex-col transform transition-all duration-300 translate-y-full sm:translate-y-0 opacity-0 sm:opacity-100 border border-gray-200 dark:border-gray-800">
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
            <button onclick="closeUploadModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-red-600 transition-all">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="overflow-y-auto flex-1 custom-scrollbar">
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
                            <button type="button" onclick="document.getElementById('file-input-modal').click()" class="bg-gray-900 dark:bg-black text-white px-10 py-3.5 rounded-xl font-black uppercase tracking-[0.15em] text-[11px] shadow-xl transition transform active:scale-95 inline-flex items-center">
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
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Document Type <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group">
                                <i class="bi bi-bookmark-plus absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500"></i>
                                <select name="document_type" required class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold text-sm appearance-none">
                                    <option value="">Select Category</option>
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
                                <input type="text" name="reference_number" required placeholder="e.g. ORD-2025-042" class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold text-sm">
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
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest pl-1">
                                Initial Status <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group">
                                <i class="bi bi-activity absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500"></i>
                                <select name="status" id="status-modal" required class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all font-bold text-sm appearance-none">
                                    <option value="Draft">Draft</option>
                                    <option value="Published">Published</option>
                                    <option value="Archived">Archived</option>
                                </select>
                                <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
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
        </form>
    </div>
</div>

<script>
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

