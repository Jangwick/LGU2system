<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Load controller
require_once __DIR__ . '/../controllers/SearchController.php';
$controller = new SearchController();
$data = $controller->index();

$pageTitle = 'Advanced Search';
$currentPage = 'search';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => DASHBOARD_INDEX_URL],
    ['label' => 'Search']
];

// Helper functions
function getStatusBadge($status) {
    $badges = [
        'draft' => 'bg-gray-100 text-gray-800',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'approved' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
        'archived' => 'bg-blue-100 text-blue-800',
        'superseded' => 'bg-purple-100 text-purple-800'
    ];
    return $badges[$status] ?? 'bg-gray-100 text-gray-800';
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
    
    <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <!-- Search Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-6 text-white">
            <h1 class="text-3xl font-bold mb-3">Advanced Document Search</h1>
            <p class="text-red-100">Search through thousands of legislative documents with powerful filters</p>
        </div>
        
        <!-- Main Search Box -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex gap-3">
                <div class="flex-1 relative">
                    <input type="text" 
                           id="main-search" 
                           placeholder="Search documents by title, reference number, keywords, content..."
                           class="w-full pl-12 pr-4 py-4 text-lg border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                </div>
                <button class="btn-primary px-8 text-lg">
                    <i class="bi bi-search mr-2"></i>
                    Search
                </button>
            </div>
            
            <!-- Quick Filters -->
            <div class="flex flex-wrap gap-2 mt-4">
                <span class="text-sm text-gray-600 mr-2">Quick filters:</span>
                <button class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200">
                    <i class="bi bi-clock mr-1"></i>This Month
                </button>
                <button class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded-full hover:bg-green-200">
                    <i class="bi bi-check-circle mr-1"></i>Approved
                </button>
                <button class="px-3 py-1 text-sm bg-purple-100 text-purple-700 rounded-full hover:bg-purple-200">
                    <i class="bi bi-journal-text mr-1"></i>Ordinances
                </button>
                <button class="px-3 py-1 text-sm bg-yellow-100 text-yellow-700 rounded-full hover:bg-yellow-200">
                    <i class="bi bi-star mr-1"></i>High Priority
                </button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Advanced Filters Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md p-6 sticky top-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="bi bi-funnel mr-2 text-blue-600"></i>
                        Filters
                    </h2>
                    
                    <!-- Document Type -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Document Type</h3>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Ordinances</span>
                                <span class="ml-auto text-xs text-gray-500">450</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Sessions</span>
                                <span class="ml-auto text-xs text-gray-500">280</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Agendas</span>
                                <span class="ml-auto text-xs text-gray-500">185</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Committees</span>
                                <span class="ml-auto text-xs text-gray-500">120</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Other</span>
                                <span class="ml-auto text-xs text-gray-500">213</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Status</h3>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Draft</span>
                                <span class="ml-auto text-xs text-gray-500">45</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Pending</span>
                                <span class="ml-auto text-xs text-gray-500">23</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Approved</span>
                                <span class="ml-auto text-xs text-gray-500">892</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Archived</span>
                                <span class="ml-auto text-xs text-gray-500">288</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Date Range -->
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Date Range</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs text-gray-600">From</label>
                                <input type="date" class="input-field text-sm py-2">
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">To</label>
                                <input type="date" class="input-field text-sm py-2">
                            </div>
                        </div>
                    </div>
                    
                    <!-- File Type -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">File Type</h3>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">PDF</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Word</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700">Excel</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <button class="flex-1 btn-primary text-sm py-2">
                            Apply Filters
                        </button>
                        <button class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Search Results -->
            <div class="lg:col-span-3">
                <!-- Results Header -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <p class="text-gray-600">Found <span class="font-bold text-gray-800">1,248 documents</span></p>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="text-sm text-gray-600">Sort by:</label>
                            <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option>Most Relevant</option>
                                <option>Newest First</option>
                                <option>Oldest First</option>
                                <option>Title A-Z</option>
                                <option>Title Z-A</option>
                            </select>
                            
                            <div class="flex border border-gray-300 rounded-lg overflow-hidden">
                                <button class="px-3 py-2 bg-blue-600 text-white">
                                    <i class="bi bi-list-ul"></i>
                                </button>
                                <button class="px-3 py-2 hover:bg-gray-100">
                                    <i class="bi bi-grid"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Results List -->
                <div class="space-y-4" id="search-results">
                    <!-- Result Item 1 -->
                    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition animate-fade-in">
                        <div class="flex items-start gap-4">
                            <div class="bg-red-100 rounded-lg p-3">
                                <i class="bi bi-file-pdf text-red-600 text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-800 mb-1">
                                            Ordinance No. 2025-042: Revenue Code Amendment
                                        </h3>
                                        <div class="flex items-center gap-3 text-sm text-gray-600">
                                            <span><i class="bi bi-hash mr-1"></i>ORD-2025-042</span>
                                            <span><i class="bi bi-calendar3 mr-1"></i>Nov 20, 2025</span>
                                            <span><i class="bi bi-hdd mr-1"></i>2.4 MB</span>
                                        </div>
                                    </div>
                                    <span class="badge badge-success">Approved</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">
                                    An ordinance amending the local revenue code to update tax rates and introduce new revenue-generating measures...
                                </p>
                                <div class="flex items-center justify-between">
                                    <div class="flex gap-2">
                                        <span class="badge badge-primary">Ordinance</span>
                                        <span class="badge badge-info">Finance</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button class="px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-lg">
                                            <i class="bi bi-eye mr-1"></i>View
                                        </button>
                                        <button class="px-3 py-1.5 text-sm text-green-600 hover:bg-green-50 rounded-lg">
                                            <i class="bi bi-download mr-1"></i>Download
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Result Item 2 -->
                    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition animate-fade-in" style="animation-delay: 0.1s">
                        <div class="flex items-start gap-4">
                            <div class="bg-blue-100 rounded-lg p-3">
                                <i class="bi bi-file-word text-blue-600 text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-800 mb-1">
                                            Regular Session Minutes - November 2025
                                        </h3>
                                        <div class="flex items-center gap-3 text-sm text-gray-600">
                                            <span><i class="bi bi-hash mr-1"></i>SS-2025-11</span>
                                            <span><i class="bi bi-calendar3 mr-1"></i>Nov 19, 2025</span>
                                            <span><i class="bi bi-hdd mr-1"></i>1.8 MB</span>
                                        </div>
                                    </div>
                                    <span class="badge badge-warning">Pending Review</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">
                                    Official transcript of the regular legislative session held on November 15, 2025, covering budget discussions...
                                </p>
                                <div class="flex items-center justify-between">
                                    <div class="flex gap-2">
                                        <span class="badge badge-info">Session</span>
                                        <span class="badge badge-primary">Legislative</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button class="px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-lg">
                                            <i class="bi bi-eye mr-1"></i>View
                                        </button>
                                        <button class="px-3 py-1.5 text-sm text-green-600 hover:bg-green-50 rounded-lg">
                                            <i class="bi bi-download mr-1"></i>Download
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- More results... -->
                </div>
                
                <!-- Pagination -->
                <div class="mt-6 bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Showing 1-10 of 1,248 results
                        </div>
                        <div class="flex gap-2">
                            <button class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50" disabled>
                                Previous
                            </button>
                            <button class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg">1</button>
                            <button class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">2</button>
                            <button class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">3</button>
                            <span class="px-2">...</span>
                            <button class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">125</button>
                            <button class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>

<script src="/public/assets/js/search.js"></script>
