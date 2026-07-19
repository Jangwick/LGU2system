<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

checkAuth();

// Get user role for access control
$userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));

// Load controller
require_once __DIR__ . '/../controllers/SearchController.php';
$controller = new SearchController();

// Handle AJAX suggestions
if (isset($_GET['action']) && $_GET['action'] === 'suggestions') {
    $controller->suggestions();
}

// Handle AJAX export
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    $export = $controller->export();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $export['filename'] . '"');
    echo $export['csv'];
    exit;
}

$data = $controller->index();

// Extract data for view
$results = $data['results'] ?? [];
$total = $data['total'] ?? 0;
$facets = $data['facets'] ?? [];
$query = $data['query'] ?? '';
$mode = $data['mode'] ?? 'hybrid';
$filters = $data['filters'] ?? [];
$page = $data['page'] ?? 1;
$totalPages = $data['total_pages'] ?? 1;

$pageTitle = 'Advanced Search System';
$currentPage = 'search';

/**
 * Helper: Get status badge styling
 */
function getStatusBadgeClass($status) {
    $badges = [
        'approved' => 'badge-success',
        'pending' => 'badge-warning',
        'draft' => 'badge-secondary',
        'rejected' => 'badge-danger'
    ];
    return 'badge ' . ($badges[strtolower($status)] ?? 'badge-info');
}

/**
 * Helper: Get document type icon
 */
function getTypeIcon($type) {
    $icons = [
        'ordinance' => 'bi-journal-text text-amber-600 dark:text-amber-500',
        'resolution' => 'bi-file-earmark-check text-blue-600 dark:text-blue-500',
        'session' => 'bi-people text-emerald-600 dark:text-emerald-500',
        'agenda' => 'bi-list-ul text-rose-600 dark:text-rose-500',
        'committee' => 'bi-shield-check text-indigo-600 dark:text-indigo-500',
        'research' => 'bi-search text-purple-600 dark:text-purple-500'
    ];
    return $icons[strtolower($type)] ?? 'bi-file-earmark text-gray-600 dark:text-gray-400';
}

function getTypeIconBgClass($type) {
    $bg = [
        'ordinance' => 'bg-amber-100 dark:bg-amber-900/30',
        'resolution' => 'bg-blue-100 dark:bg-blue-900/30',
        'session' => 'bg-emerald-100 dark:bg-emerald-900/30',
        'agenda' => 'bg-rose-100 dark:bg-rose-900/30',
        'committee' => 'bg-indigo-100 dark:bg-indigo-900/30',
        'research' => 'bg-purple-100 dark:bg-purple-900/30'
    ];
    return $bg[strtolower($type)] ?? 'bg-gray-100 dark:bg-gray-800';
}

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<!-- Sidebar -->
<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Top Navbar -->
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-950 p-2 md:p-6 custom-scrollbar">
        <div class="max-w-7xl mx-auto space-y-4 md:space-y-6">
                
                <!-- Search Hero/Header -->
                <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-xl md:rounded-2xl shadow-xl p-5 md:p-10 text-white relative overflow-hidden mb-4 md:mb-6 animate-fade-in">
                    <!-- Background Decor -->
                    <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute -left-10 -top-10 w-48 h-48 bg-red-400/20 rounded-full blur-2xl"></div>

                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4 md:gap-6">
                        <div>
                            <div class="flex items-center gap-2 text-red-100 font-bold tracking-wider text-[10px] md:text-xs uppercase mb-2 md:mb-3">
                                <span class="w-6 md:w-8 h-0.5 bg-red-100/50"></span>
                                AI-Powered Intelligence
                            </div>
                            <h1 class="text-2xl md:text-4xl font-black mb-1 md:mb-2 italic">Advanced Search</h1>
                            <p class="text-red-50 text-xs md:text-base max-w-xl opacity-90 font-medium">Hybrid engine combining keywords with semantic understanding.</p>
                        </div>
                        <div class="flex items-center gap-1.5 bg-black/10 p-1 rounded-lg md:rounded-xl backdrop-blur-md border border-white/10 w-fit">
                            <button class="hero-toggle-btn px-4 md:px-5 py-2 md:py-2.5 rounded-md md:rounded-lg bg-white !text-red-700 font-black text-[10px] md:text-sm shadow-lg whitespace-nowrap uppercase tracking-tight">Documents</button>
                            <button class="hero-toggle-btn px-4 md:px-5 py-2 md:py-2.5 rounded-md md:rounded-lg text-white hover:bg-white/10 font-black text-[10px] md:text-sm transition-all whitespace-nowrap uppercase tracking-tight">Legislations</button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Filter Toggle -->
                <div class="lg:hidden mb-4">
                    <button type="button" onclick="toggleMobileFilters()" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center justify-between shadow-sm active:scale-[0.98] transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-500">
                                <i class="bi bi-sliders2"></i>
                            </div>
                            <span class="font-bold text-gray-700 dark:text-gray-200">Refine Search</span>
                        </div>
                        <i id="filter-chevron" class="bi bi-chevron-down text-gray-400 dark:text-gray-500 transition-transform"></i>
                    </button>
                </div>

                <!-- Main Layout Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 md:gap-6">
                    
                    <!-- Left Sidebar Filters -->
                    <aside id="filters-sidebar" class="hidden lg:block space-y-4 md:space-y-6 lg:sticky lg:top-0 h-fit animate-slide-in-left">
                        <!-- Filters Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-md border border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                                    <i class="bi bi-sliders2 text-red-600 dark:text-red-500"></i> Refine Results
                                </h3>
                                <a href="?" class="text-[10px] text-gray-400 dark:text-gray-500 hover:text-red-600 dark:hover:text-red-400 transition-colors uppercase font-black tracking-widest">Clear All</a>
                            </div>

                            <form id="filter-form" action="" method="GET" class="space-y-6">
                                <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">

                                <!-- Category Filter -->
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Document Type</label>
                                    <div class="space-y-1">
                                        <?php 
                                        $types = [
                                            'ordinance' => 'Ordinance',
                                            'resolution' => 'Resolution',
                                            'session' => 'Session',
                                            'agenda' => 'Agenda',
                                            'committee' => 'Committee',
                                            'research' => 'Research'
                                        ];
                                        
                                        $getFacetCount = function($type) use ($facets) {
                                            if (!isset($facets['by_type'])) return 0;
                                            foreach ($facets['by_type'] as $f) {
                                                if (strtolower($f['document_type']) === $type) return $f['count'];
                                            }
                                            return 0;
                                        };

                                        foreach($types as $value => $label):
                                            $checked = is_array($filters['type'] ?? '') ? (in_array($value, $filters['type']) ? 'checked' : '') : (($filters['type'] ?? '') === $value ? 'checked' : '');
                                            $count = $getFacetCount($value);
                                        ?>
                                        <label class="flex items-center justify-between p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer group transition-all <?= $checked ? 'bg-red-50 dark:bg-red-900/10 ring-1 ring-red-100 dark:ring-red-900/30' : '' ?>">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" name="type[]" value="<?= $value ?>" <?= $checked ?> class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500/20 bg-white dark:bg-gray-700">
                                                <span class="text-sm font-bold <?= $checked ? 'text-red-700 dark:text-red-400' : 'text-gray-600 dark:text-gray-400' ?> group-hover:text-red-600 dark:group-hover:text-red-400"><?= $label ?></span>
                                            </div>
                                            <span class="text-[10px] font-black <?= $checked ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500' ?> px-2 py-0.5 rounded-full transition-all">
                                                <?= number_format($count) ?>
                                            </span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Status Filter -->
                                <div class="relative z-30">
                                    <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Status</label>
                                    <div class="relative custom-select-container">
                                        <div id="status-filter-trigger" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all cursor-pointer flex items-center justify-between" style="min-height: 48px;">
                                            <span id="status-filter-value">All Statuses</span>
                                            <i class="bi bi-chevron-down text-gray-400"></i>
                                        </div>
                                        <div id="status-filter-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl z-[100] max-h-64 overflow-y-auto">
                                            <div class="p-2 space-y-1">
                                                <div class="status-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="">All Statuses</div>
                                                <div class="status-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="approved">Approved</div>
                                                <div class="status-filter-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="draft">Draft</div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="status" id="status-filter-input" value="<?= ($filters['status'] ?? '') ?>">
                                    </div>
                                </div>

                                <!-- Date Range -->
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Time Period</label>
                                    <div class="space-y-2">
                                        <div class="relative group">
                                            <i class="bi bi-calendar3 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xs group-focus-within:text-red-500 transition-colors"></i>
                                            <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl pl-11 pr-4 py-2.5 text-xs text-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-red-500/20 outline-none">
                                        </div>
                                        <div class="relative group">
                                            <i class="bi bi-calendar3 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xs group-focus-within:text-red-500 transition-colors"></i>
                                            <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl pl-11 pr-4 py-2.5 text-xs text-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-red-500/20 outline-none">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="no-ripple w-full h-12 min-h-12 max-h-12 overflow-hidden px-4 bg-red-600 hover:bg-red-700 text-white font-black uppercase tracking-widest text-xs rounded-xl shadow-lg shadow-red-600/20 transition-colors flex items-center justify-center gap-2 transform-none hover:transform-none active:transform-none flex-shrink-0">
                                    <i class="bi bi-funnel-fill"></i> Apply Filters
                                </button>
                            </form>
                        </div>

                        <!-- Quick Stats -->
                        <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl p-5 shadow-sm">
                            <h4 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase mb-4 tracking-widest">System Insights</h4>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-600 dark:text-red-500 shadow-sm">
                                        <i class="bi bi-database"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase">Total Records</div>
                                        <div class="text-sm font-black text-gray-800 dark:text-gray-200"><?= number_format($total) ?></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 dark:text-emerald-500 shadow-sm">
                                        <i class="bi bi-lightning-charge-fill"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase">AI Status</div>
                                        <div class="text-sm font-black text-emerald-600 dark:text-emerald-500"><?= defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY) ? 'Online' : 'Offline' ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- Main Search Results -->
                    <div class="lg:col-span-3 space-y-6">
                        
                        <!-- Top Search Bar -->
                        <div class="relative group" data-aos="fade-up">
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl md:rounded-3xl p-1.5 md:p-2 pl-4 md:pl-6 flex items-center gap-3 md:gap-4 focus-within:ring-4 focus-within:ring-red-500/10 focus-within:border-red-500/40 transition-all shadow-xl shadow-gray-200/50 dark:shadow-none animate-fade-in-up">
                                <i class="bi bi-search text-gray-400 dark:text-gray-500 text-lg md:text-xl shrink-0"></i>
                                <form id="search-main-form" action="" method="GET" class="flex-1 flex items-center gap-2">
                                    <input type="hidden" name="mode" id="search-mode" value="<?= htmlspecialchars($mode) ?>">
                                    <input type="text" name="q" id="search-input" value="<?= htmlspecialchars($query) ?>" 
                                           placeholder="Search documents or intent..." 
                                           class="flex-1 bg-transparent border-none outline-none text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 py-3 md:py-4 text-sm md:text-lg font-bold" autocomplete="off">
                                    
                                    <!-- Search Mode Toggle -->
                                    <div class="hidden md:flex items-center gap-1 bg-gray-100 dark:bg-gray-950 p-1 rounded-xl border border-gray-200 dark:border-gray-700 mr-2">
                                        <button type="button" onclick="setSearchMode('hybrid')" 
                                                class="mode-btn px-3 py-1.5 text-[10px] font-black uppercase transition-all duration-200 rounded-lg <?= $mode === 'hybrid' ? 'text-red-600 dark:text-red-400 bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700' : 'text-gray-400 dark:text-gray-600 hover:text-gray-600 dark:hover:text-gray-400' ?>">
                                            Hybrid
                                        </button>
                                        <button type="button" onclick="setSearchMode('semantic')" 
                                                class="mode-btn px-3 py-1.5 text-[10px] font-black uppercase transition-all duration-200 rounded-lg <?= $mode === 'semantic' ? 'text-red-600 dark:text-red-400 bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700' : 'text-gray-400 dark:text-gray-600 hover:text-gray-600 dark:hover:text-gray-400' ?>">
                                            Semantic
                                        </button>
                                    </div>

                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white w-12 h-12 md:w-14 md:h-14 rounded-xl md:rounded-2xl flex items-center justify-center shadow-lg shadow-red-600/30 transition-all active:scale-95 group shrink-0">
                                        <i class="bi bi-arrow-right text-xl md:text-2xl group-hover:translate-x-0.5 transition-transform"></i>
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Suggestions Dropdown -->
                            <div id="suggestions-box" class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden z-50 hidden transition-all duration-200 opacity-0 transform translate-y-2">
                                <div id="suggestions-content" class="max-h-80 overflow-y-auto p-2">
                                    <!-- Suggestions will be injected here -->
                                </div>
                            </div>
                        </div>

                        <!-- Results Meta -->
                        <div id="search-meta" class="flex flex-col sm:flex-row sm:items-center justify-between px-2 gap-4 animate-fade-in-up animation-delay-100">
                            <div class="flex flex-wrap items-center gap-3 md:gap-4">
                                <span class="text-xs md:text-sm text-gray-500 dark:text-gray-400 font-medium w-full sm:w-auto mb-1 sm:mb-0">
                                    Found <span class="text-gray-900 dark:text-white font-black"><?= number_format($total) ?></span> matches 
                                    <?php if($query): ?> for "<span class="text-red-600 dark:text-red-500 italic font-bold"><?= htmlspecialchars($query) ?></span>"<?php endif; ?>
                                </span>
                                <div class="flex items-center gap-2 bg-white dark:bg-gray-800 px-3 md:px-4 py-1.5 rounded-full border border-gray-200 dark:border-gray-700 text-[9px] md:text-[10px] font-black text-gray-500 dark:text-gray-400 shadow-sm uppercase tracking-widest">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span>
                                    <?= e(ucfirst($mode)) ?> Engine
                                </div>
                                <?php if ($userRole !== 'viewer'): ?>
                                <button type="button" onclick="exportResults(event)" class="no-ripple flex items-center gap-2 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 px-3 md:px-4 py-1.5 rounded-full border border-gray-200 dark:border-gray-700 text-[9px] md:text-[10px] font-black text-gray-500 dark:text-gray-400 shadow-sm uppercase tracking-widest transition-colors">
                                    <i class="bi bi-download text-red-600 dark:text-red-500"></i> Export CSV
                                </button>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-2 self-end sm:self-auto">
                                <button type="button" onclick="setView('grid')" id="view-grid" class="no-ripple w-8 h-8 md:w-9 md:h-9 flex items-center justify-center transition-all bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:text-red-600 dark:hover:text-red-500 rounded-lg shadow-sm">
                                    <i class="bi bi-grid-fill leading-none pointer-events-none"></i>
                                </button>
                                <button type="button" onclick="setView('list')" id="view-list" class="no-ripple w-8 h-8 md:w-9 md:h-9 flex items-center justify-center transition-all bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-900/50 text-red-600 dark:text-red-400 rounded-lg shadow-sm">
                                    <i class="bi bi-list-task leading-none pointer-events-none"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Results List -->
                        <div id="results-list" class="space-y-4">
                            <?php if (empty($results)): ?>
                                <!-- Empty State -->
                                <div class="bg-white dark:bg-gray-800 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-3xl p-16 md:p-24 text-center shadow-sm animate-bounce-in">
                                    <div class="w-24 h-24 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-300 dark:text-gray-600 shadow-inner">
                                        <i class="bi bi-search text-5xl"></i>
                                    </div>
                                    <h3 class="text-2xl font-black text-gray-800 dark:text-white mb-2">No documents found</h3>
                                    <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto font-medium">Try adjusting your filters or use more specific keywords like "Ordinance 2024".</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($results as $index => $doc): 
                                    $delayClass = $index < 10 ? 'animation-delay-' . (($index + 2) * 100) : '';
                                ?>
                                                                <div class="md:hidden">
<div class="result-card group bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700 transition-all active:scale-[0.98] animate-fade-in-up <?= $delayClass ?>" data-document-id="<?= $doc['id'] ?>">
                                    <!-- Top: Type, Date & Status -->
                                    <div class="px-4 py-3 bg-gray-50/50 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-700/50 flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <span class="badge badge-primary !text-[10px] !py-0.5">
                                                <?= e(ucfirst($doc['document_type'])) ?>
                                            </span>
                                        </div>
                                        <div class="flex flex-col items-end gap-1">
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider">
                                                <i class="bi bi-calendar-event mr-1"></i><?= date('M d, Y', strtotime($doc['created_at'])) ?>
                                            </span>
                                            <div class="flex flex-nowrap items-center gap-1 overflow-x-auto">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide whitespace-nowrap <?= getStatusBadgeClass($doc['status']) ?>">
                                                    <?= e($doc['status']) ?>
                                                </span>
                                                <?php if(isset($doc['relevance_score'])): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide whitespace-nowrap bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300" title="AI Relevance">
                                                    <i class="bi bi-stars"></i><?= round($doc['relevance_score'] * 100) ?>%
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Middle: Title, Ref/Description & Icon -->
                                    <div class="p-4 flex flex-col items-center gap-3">
                                        <div class="min-w-0 w-full">
                                            <h4 class="text-sm font-black text-gray-900 dark:text-gray-100 mb-1 leading-tight line-clamp-2">
                                                <?= htmlspecialchars($doc['title']) ?>
                                            </h4>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate font-medium opacity-80">
                                                REF: <?= e($doc['reference_number'] ?? 'N/A') ?>
                                            </p>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-2 mt-1 leading-relaxed">
                                                <?= htmlspecialchars($doc['description'] ?? 'No description available for this legislative record.') ?>
                                            </p>
                                        </div>
                                        <div class="w-full h-16 rounded-2xl <?= getTypeIconBgClass($doc['document_type']) ?> flex items-center justify-center flex-shrink-0 shadow-sm">
                                            <i class="bi <?= getTypeIcon($doc['document_type']) ?> text-3xl"></i>
                                        </div>
                                    </div>

                                    <!-- Bottom: Actions & Metadata -->
                                    <div class="px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-50 dark:border-gray-700/50 flex items-center justify-between gap-3">
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate">
                                            by <?= htmlspecialchars($doc['uploaded_by_name'] ?? 'System Admin') ?>
                                        </span>
                                        <div class="card-actions flex items-center gap-3">
                                            <button type="button" onclick="previewDocument(<?= $doc['id'] ?>)" class="px-4 py-2 md:px-6 md:py-2.5 rounded-xl bg-gray-800 dark:bg-black hover:bg-gray-900 dark:bg-gray-700 text-white text-[10px] font-black uppercase tracking-widest transition-all transform active:scale-95 shadow-lg shadow-gray-200 dark:shadow-none group/btn">
                                                <i class="bi bi-eye mr-2 group-hover/btn:scale-125 transition-transform"></i> Preview
                                            </button>
                                            <?php if ($userRole !== 'viewer'): ?>
                                            <a href="<?php echo DOCUMENTS_URL; ?>/api/download.php?id=<?= $doc['id'] ?>" class="w-10 h-10 rounded-xl bg-red-600 dark:bg-red-700 hover:bg-red-700 dark:hover:bg-red-800 flex items-center justify-center text-white transition-all shadow-lg shadow-red-600/30 transform active:scale-90 group/dl" title="Download">
                                                <i class="bi bi-download group-hover/dl:translate-y-0.5 transition-transform"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                <div class="hidden md:block">
<div class="group bg-white hover:bg-white border border-gray-200 hover:border-red-200 rounded-2xl p-5 md:p-7 transition-all duration-300 shadow-sm hover:shadow-xl hover:-translate-y-1 animate-fade-in-up <?= $delayClass ?>">
                                    <div class="flex flex-col md:flex-row gap-6">
                                        <!-- Doc Icon -->
                                        <div class="w-16 h-16 shrink-0 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center text-3xl group-hover:scale-110 group-hover:bg-red-50 group-hover:border-red-100 transition-all duration-300">
                                            <i class="bi <?= getTypeIcon($doc['document_type']) ?>"></i>
                                        </div>

                                        <!-- Doc Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                                <span class="px-3 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest <?= getStatusBadgeClass($doc['status']) ?>">
                                                    <?= $doc['status'] ?>
                                                </span>
                                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 px-2 py-1 rounded-lg border border-gray-100">REF: <?= $doc['reference_number'] ?? 'N/A' ?></span>
                                                
                                                <?php if(isset($doc['relevance_score'])): ?>
                                                <div class="relevance-bar ml-auto flex items-center gap-2 bg-red-50/50 px-3 py-1.5 rounded-xl border border-red-100">
                                                    <div class="text-[9px] font-black uppercase text-red-600 tracking-tighter">AI Relevance</div>
                                                    <div class="h-1.5 w-14 bg-gray-200 rounded-full overflow-hidden">
                                                        <div class="h-full bg-red-500 shadow-sm shadow-red-500/50" style="width: <?= $doc['relevance_score'] * 100 ?>%"></div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>

                                            <h3 class="text-xl font-black text-gray-800 group-hover:text-red-600 transition-colors line-clamp-1 mb-2">
                                                <?= htmlspecialchars($doc['title']) ?>
                                            </h3>

                                            <p class="text-gray-500 text-sm line-clamp-2 mb-6 leading-relaxed font-medium">
                                                <?= htmlspecialchars($doc['description'] ?? 'No description available for this legislative record.') ?>
                                            </p>

                                            <div class="flex flex-wrap items-center gap-y-4 gap-x-6 border-t border-gray-50 pt-5">
                                                <div class="flex items-center gap-2 text-xs font-bold text-gray-400">
                                                    <i class="bi bi-calendar-event text-red-500 text-sm"></i>
                                                    <?= date('M d, Y', strtotime($doc['created_at'])) ?>
                                                </div>
                                                <div class="flex items-center gap-2 text-xs font-bold text-gray-400">
                                                    <i class="bi bi-person-circle text-gray-300 text-sm"></i>
                                                    <span class="hover:text-red-500 transition-colors cursor-default"><?= htmlspecialchars($doc['uploaded_by_name'] ?? 'System Admin') ?></span>
                                                </div>
                                </div>
                                                                </div>
                                </div>
                                </div>
                                </div>
<?php endforeach; ?>

                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                <div id="pagination" class="flex items-center justify-center gap-2 pt-8">
                                    <?php if ($page > 1): ?>
                                        <button type="button" onclick="changePage(<?= $page - 1 ?>)" class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:border-red-600 dark:hover:border-red-500 hover:text-red-600 dark:hover:text-red-400 transition-all shadow-sm">
                                            <i class="bi bi-chevron-left"></i>
                                        </button>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <button type="button" onclick="changePage(<?= $i ?>)" class="w-10 h-10 rounded-xl font-bold text-sm transition-all shadow-sm <?= $i === $page ? 'bg-red-600 text-white border-red-600' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:border-red-600 dark:hover:border-red-500 hover:text-red-600 dark:hover:text-red-400' ?>">
                                            <?= $i ?>
                                        </button>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <button type="button" onclick="changePage(<?= $page + 1 ?>)" class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:border-red-600 dark:hover:border-red-500 hover:text-red-600 dark:hover:text-red-400 transition-all shadow-sm">
                                            <i class="bi bi-chevron-right"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Document Preview Modal -->
        <div id="preview-modal" class="fixed inset-0 z-[60] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-stretch justify-center min-h-screen sm:items-center sm:p-4">
                <!-- Overlay -->
                <div id="preview-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity opacity-0 pointer-events-none"></div>

                <!-- Modal Content -->
                <div id="preview-content" class="relative bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full sm:max-w-4xl sm:h-[85vh] sm:max-h-[85vh] opacity-0 translate-y-full sm:translate-y-0 sm:scale-95 flex flex-col duration-300 border border-gray-200 dark:border-gray-800" style="max-height: 100dvh;">
                    <!-- Mobile Drag Handle -->
                    <div class="sm:hidden w-full flex justify-center pt-3 pb-1 bg-white dark:bg-gray-900 translate-y-[1px]">
                        <div class="w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                    </div>

                    <!-- Modal Header -->
                    <div class="sticky top-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-6 py-4 flex items-center justify-between z-10">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded-xl">
                                <i id="modal-icon" class="bi bi-file-earmark-text text-red-600 text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest" id="modal-title">Document Preview</h3>
                                <p id="modal-subtitle" class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider truncate">Reference ID: ---</p>
                            </div>
                        </div>
                        <button type="button" onclick="closePreview()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-all transform-none">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 md:px-8 md:py-8 overflow-y-auto overflow-x-hidden flex-1 min-h-0" style="-webkit-overflow-scrolling: touch; overscroll-behavior: contain;">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 md:gap-8">
                            <!-- Left: Details -->
                            <div class="md:col-span-2 space-y-6">
                                <div>
                                    <h4 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Document Title</h4>
                                    <h2 id="preview-title" class="text-xl md:text-2xl font-black text-gray-800 dark:text-white leading-tight">---</h2>
                                </div>

                                <div>
                                    <h4 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Description</h4>
                                    <p id="preview-desc" class="text-sm md:text-base text-gray-600 dark:text-gray-400 leading-relaxed font-medium">---</p>
                                </div>

                                <div class="grid grid-cols-2 gap-3 md:gap-4">
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-xl md:rounded-2xl p-3 md:p-4 border border-gray-100 dark:border-gray-700">
                                        <h4 class="text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Status</h4>
                                        <div id="preview-status" class="inline-flex mt-1">
                                            <span class="px-2.5 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700">---</span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-xl md:rounded-2xl p-3 md:p-4 border border-gray-100 dark:border-gray-700">
                                        <h4 class="text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Date Created</h4>
                                        <p id="preview-date" class="text-xs md:text-sm font-bold text-gray-800 dark:text-gray-200 mt-1">---</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Metadata & Actions -->
                            <div class="space-y-6">
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl md:rounded-3xl p-4 md:p-6 border border-gray-100 dark:border-gray-700">
                                    <h4 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3 md:mb-4">File Information</h4>
                                    <div class="space-y-3 md:space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-red-600 dark:text-red-500 shadow-sm border border-gray-100 dark:border-gray-700 flex-shrink-0">
                                                <i class="bi bi-file-earmark-pdf text-lg md:text-xl"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p id="preview-filename" class="text-[11px] font-bold text-gray-800 dark:text-gray-200 truncate">filename.pdf</p>
                                                <p id="preview-filesize" class="text-[9px] text-gray-400 dark:text-gray-500 font-bold">0.0 MB</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-gray-400 dark:text-gray-500 shadow-sm border border-gray-100 dark:border-gray-700 flex-shrink-0">
                                                <i class="bi bi-person-circle text-base md:text-lg"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p id="preview-uploader" class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">Uploader</p>
                                                <p class="text-[9px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider">Uploaded By</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="preview-tags" class="mt-4 md:mt-6 flex flex-wrap gap-2">
                                        <!-- Tags will be injected here -->
                                    </div>
                                </div>

                                <div class="hidden md:flex flex-col space-y-3 pb-6 md:pb-0">
                                    <?php if ($userRole !== 'viewer'): ?>
                                    <a id="preview-download-btn" href="#" class="w-full py-4 bg-red-600 hover:bg-red-700 text-white font-black uppercase tracking-widest text-[11px] rounded-2xl shadow-xl shadow-red-600/20 transition-all flex items-center justify-center gap-2 transform active:scale-95">
                                        <i class="bi bi-download text-base"></i> Download Document
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Document Analysis Section -->
                        <div id="preview-analysis" class="mt-6"></div>
                    </div>
                    <!-- Mobile Sticky Footer: Download -->
                    <div class="md:hidden flex items-center gap-3 px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex-shrink-0 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                        <?php if ($userRole !== 'viewer'): ?>
                        <a id="preview-download-btn-mobile" href="#" class="flex-1 py-3 bg-red-600 hover:bg-red-700 text-white font-black uppercase tracking-widest text-[11px] rounded-xl flex items-center justify-center gap-2 active:scale-95 transition-all">
                            <i class="bi bi-download text-base"></i> Download
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .custom-scrollbar::-webkit-scrollbar { width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 20px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(220,38,38,0.2); }

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

        <script>
// Custom Dropdown Helper Function
function initCustomDropdown(triggerId, dropdownId, valueId, inputId, optionClass, defaultValue) {
    const trigger = document.getElementById(triggerId);
    const dropdown = document.getElementById(dropdownId);
    const valueDisplay = document.getElementById(valueId);
    const hiddenInput = document.getElementById(inputId);
    const options = document.querySelectorAll(optionClass);
    
    if (!trigger || !dropdown || !valueDisplay || !hiddenInput) return;
    
    const selectedValue = hiddenInput.value;
    if (selectedValue) {
        const selectedOption = document.querySelector(`${optionClass}[data-value="${selectedValue}"]`);
        if (selectedOption) {
            valueDisplay.textContent = selectedOption.textContent;
        }
    }
    
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });
    
    options.forEach(option => {
        option.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            const text = this.textContent;
            valueDisplay.textContent = text;
            hiddenInput.value = value;
            dropdown.classList.add('hidden');
        });
    });
    
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initCustomDropdown('status-filter-trigger', 'status-filter-dropdown', 'status-filter-value', 'status-filter-input', '.status-filter-option', 'All Statuses');
});

            /**
             * Interface Controls
             */
            const searchInput = document.getElementById('search-input');
            const searchModeInput = document.getElementById('search-mode');
            const suggestionsBox = document.getElementById('suggestions-box');
            const suggestionsContent = document.getElementById('suggestions-content');
            const filterForm = document.getElementById('filter-form');
            const mainForm = document.getElementById('search-main-form');
            const resultsList = document.getElementById('results-list');
            const searchMeta = document.getElementById('search-meta');
            
            let queryTimer;
            let currentView = localStorage.getItem('searchView') || 'list';
            
            function toggleMobileFilters() {
                const aside = document.getElementById('filters-sidebar');
                const chevron = document.getElementById('filter-chevron');
                if (aside.classList.contains('hidden')) {
                    aside.classList.remove('hidden');
                    aside.classList.add('block', 'animate-fade-in');
                    chevron.style.transform = 'rotate(180deg)';
                } else {
                    aside.classList.add('hidden');
                    aside.classList.remove('block', 'animate-fade-in');
                    chevron.style.transform = 'rotate(0deg)';
                }
            }

            // Set View (Grid/List)
            function setView(view) {
                currentView = view;
                localStorage.setItem('searchView', view);
                
                const resultsList = document.getElementById('results-list');
                const gridBtn = document.getElementById('view-grid');
                const listBtn = document.getElementById('view-list');
                
                if (view === 'grid') {
                    resultsList.classList.remove('space-y-4');
                    resultsList.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'xl:grid-cols-3', 'gap-4', 'items-stretch');
                    
                    // Update buttons
                    gridBtn.classList.add('bg-red-50', 'border-red-200', 'text-red-600');
                    gridBtn.classList.remove('bg-white', 'border-gray-200', 'text-gray-400');
                    listBtn.classList.remove('bg-red-50', 'border-red-200', 'text-red-600');
                    listBtn.classList.add('bg-white', 'border-gray-200', 'text-gray-400');
                    
                    resultsList.querySelectorAll('.result-card').forEach(card => {
                        card.classList.add('h-full');
                    });

                    resultsList.querySelectorAll('.result-card-inner').forEach(item => {
                        item.classList.remove('md:flex-row');
                        item.classList.add('flex-col', 'h-full');
                    });

                    resultsList.querySelectorAll('.result-card-body').forEach(body => {
                        body.classList.add('flex', 'flex-col', 'h-full');
                    });

                    resultsList.querySelectorAll('.result-card-footer').forEach(footer => {
                        footer.classList.add('mt-auto');
                    });
                    
                    // Adjust uploader/date section for grid
                    resultsList.querySelectorAll('.card-actions').forEach(actions => {
                        actions.classList.remove('ml-auto');
                        actions.classList.add('w-full', 'justify-between', 'pt-2');
                    });
                    
                    resultsList.querySelectorAll('.relevance-bar').forEach(bar => {
                        bar.classList.remove('ml-auto');
                        bar.classList.add('mb-2');
                    });
                } else {
                    resultsList.classList.add('space-y-4');
                    resultsList.classList.remove('grid', 'grid-cols-1', 'md:grid-cols-2', 'xl:grid-cols-3', 'gap-4', 'items-stretch');
                    
                    // Update buttons
                    listBtn.classList.add('bg-red-50', 'border-red-200', 'text-red-600');
                    listBtn.classList.remove('bg-white', 'border-gray-200', 'text-gray-400');
                    gridBtn.classList.remove('bg-red-50', 'border-red-200', 'text-red-600');
                    gridBtn.classList.add('bg-white', 'border-gray-200', 'text-gray-400');
                    
                    resultsList.querySelectorAll('.result-card').forEach(card => {
                        card.classList.remove('h-full');
                    });

                    resultsList.querySelectorAll('.result-card-inner').forEach(item => {
                        item.classList.add('md:flex-row');
                        item.classList.remove('flex-col', 'h-full');
                    });

                    resultsList.querySelectorAll('.result-card-body').forEach(body => {
                        body.classList.remove('flex', 'flex-col', 'h-full');
                    });

                    resultsList.querySelectorAll('.result-card-footer').forEach(footer => {
                        footer.classList.remove('mt-auto');
                    });

                    // Restore classes
                    resultsList.querySelectorAll('.card-actions').forEach(actions => {
                        actions.classList.add('ml-auto');
                        actions.classList.remove('w-full', 'justify-between', 'pt-2');
                    });
                    
                    resultsList.querySelectorAll('.relevance-bar').forEach(bar => {
                        bar.classList.add('ml-auto');
                        bar.classList.remove('mb-2');
                    });
                }
            }

            // Set Search Mode
            function setSearchMode(mode) {
                searchModeInput.value = mode;
                
                // Update UI buttons
                document.querySelectorAll('.mode-btn').forEach(btn => {
                    if (btn.textContent.trim().toLowerCase() === mode) {
                        btn.classList.add('text-red-600', 'bg-white', 'shadow-sm', 'border', 'border-gray-200');
                        btn.classList.remove('text-gray-400', 'hover:text-gray-600');
                    } else {
                        btn.classList.remove('text-red-600', 'bg-white', 'shadow-sm', 'border', 'border-gray-200');
                        btn.classList.add('text-gray-400', 'hover:text-gray-600');
                    }
                });
                
                updateResults();
            }

            // Export Results
            function exportResults(event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                const formData = new FormData(filterForm);
                const mainData = new FormData(mainForm);
                const params = new URLSearchParams(formData);
                for (const [key, value] of mainData.entries()) {
                    params.append(key, value);
                }
                params.append('action', 'export');
                window.location.href = `?${params.toString()}`;
            }

            // Change Page
            function changePage(page) {
                const url = new URL(window.location.href);
                url.searchParams.set('page', page);
                window.history.pushState({}, '', url);
                updateResults(page);
                
                // Scroll to results
                window.scrollTo({
                    top: resultsList.offsetTop - 100,
                    behavior: 'smooth'
                });
            }

            // Preview Document In Modal
            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            function toggleDocPreview(btn) {
                const container = document.getElementById('doc-preview-container');
                if (!container) return;
                const icon = btn.querySelector('i');
                const label = btn.querySelector('span');
                if (container.classList.contains('max-h-72')) {
                    container.classList.remove('max-h-72');
                    container.classList.add('max-h-[2000px]');
                    if (icon) { icon.classList.remove('bi-chevron-down'); icon.classList.add('bi-chevron-up'); }
                    if (label) label.textContent = 'Collapse';
                } else {
                    container.classList.remove('max-h-[2000px]');
                    container.classList.add('max-h-72');
                    if (icon) { icon.classList.remove('bi-chevron-up'); icon.classList.add('bi-chevron-down'); }
                    if (label) label.textContent = 'Expand';
                }
            }

            function formatDocumentText(rawText) {
                if (!rawText) return '<p class="text-gray-400 italic">No content available.</p>';
                let text = rawText.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
                text = text.replace(/^\[OCR\]\s*\n?/i, '');
                text = text.replace(/\[Page OCR failed:.*?\]/g, '');
                text = text.replace(/--- Page Break ---/g, '\n\n');
                const blocks = text.split(/\n{2,}/);
                let html = '';
                let inList = false, listType = '', listItems = [];
                const closeList = () => {
                    if (inList) {
                        const tag = listType === 'ol' ? 'ol' : 'ul';
                        const cls = listType === 'ol' ? 'list-decimal list-inside space-y-1.5 my-3 pl-2' : 'list-disc list-inside space-y-1.5 my-3 pl-2';
                        html += `<${tag} class="${cls}">${listItems.join('')}</${tag}>`;
                        inList = false; listType = ''; listItems = [];
                    }
                };
                for (let block of blocks) {
                    block = block.trim();
                    if (!block) continue;
                    const numberedMatch = block.match(/^(\d+)\.\s*(.+)/);
                    const bulletMatch = block.match(/^[•·\-\*]\s*(.+)/);
                    const romanNumeralMatch = block.match(/^([IVXLCDM]+)\.\s*(.+)/i);
                    const isShortAllCaps = block.length < 80 && block === block.toUpperCase() && /[A-Z]/.test(block) && !block.endsWith('.') && !numberedMatch;
                    const letteredMatch = block.match(/^([A-Z])\.\s*(.+)/);
                    const isHeading = isShortAllCaps || (block.length < 100 && block === block.toUpperCase() && /[A-Z]/.test(block)) || /^(DETAILED\s|AN\s|ORDINANCE|RESOLUTION|REPUBLIC\s|CITY\s|MUNICIPAL|PROVINCIAL|BARANGAY|OFFICE\s|DEPARTMENT|COLLEGE|UNIVERSITY|SCHOOL|SECTION|ARTICLE|CHAPTER)/i.test(block) && block.length < 120;
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
                            html += `<h2 class="text-center font-bold text-base text-gray-900 dark:text-gray-100 my-3 uppercase tracking-wide">${escapeHtml(block)}</h2>`;
                        } else if (romanNumeralMatch && block.length < 200) {
                            html += `<h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 mt-4 mb-2">${escapeHtml(block)}</h3>`;
                        } else if (block.length < 100 && /^(Section|Article|Chapter|Title)\s/i.test(block)) {
                            html += `<h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 mt-4 mb-2">${escapeHtml(block)}</h3>`;
                        } else {
                            const lines = block.split('\n');
                            if (lines.length === 1) {
                                html += `<p class="text-gray-800 dark:text-gray-200 leading-relaxed mb-3 text-justify">${escapeHtml(block)}</p>`;
                            } else {
                                const isIndented = lines.every(l => /^\s+/.test(l) || !l.trim());
                                if (isIndented && lines.length > 2) {
                                    html += `<div class="pl-4 border-l-2 border-gray-200 dark:border-gray-700 my-3 space-y-1">`;
                                    for (const line of lines) {
                                        if (line.trim()) html += `<p class="text-gray-700 dark:text-gray-300 leading-relaxed text-[12px]">${escapeHtml(line.trim())}</p>`;
                                    }
                                    html += `</div>`;
                                } else {
                                    html += `<p class="text-gray-800 dark:text-gray-200 leading-relaxed mb-3 text-justify">${lines.map(l => escapeHtml(l.trim())).join('<br>')}</p>`;
                                }
                            }
                        }
                    }
                }
                closeList();
                return html;
            }

            async function previewDocument(id) {
                const modal = document.getElementById('preview-modal');
                const overlay = document.getElementById('preview-overlay');
                const content = document.getElementById('preview-content');

                // Show modal & initial loader state
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                setTimeout(() => {
                    overlay.classList.remove('opacity-0', 'pointer-events-none');
                    overlay.classList.add('opacity-100', 'pointer-events-auto');
                    content.classList.remove('translate-y-full', 'sm:scale-95', 'opacity-0');
                    content.classList.add('translate-y-0', 'sm:scale-100', 'opacity-100');
                }, 10);

                try {
                    const response = await fetch(`<?= BASE_URL ?>/modules/document-management/api/get_details.php?id=${id}`);
                    const result = await response.json();

                    if (result.success) {
                        const doc = result.document;
                        
                        // Update Modal Content
                        document.getElementById('modal-title').textContent = "Document Preview";
                        document.getElementById('modal-subtitle').textContent = `REF: ${doc.reference_number || 'N/A'}`;
                        document.getElementById('preview-title').textContent = doc.title;
                        document.getElementById('preview-desc').textContent = doc.description || "No description available for this legislative record.";
                        document.getElementById('preview-date').textContent = new Date(doc.created_at).toLocaleDateString();
                        document.getElementById('preview-filename').textContent = doc.file_name;
                        document.getElementById('preview-filesize').textContent = formatFileSize(doc.file_size || 0);
                        document.getElementById('preview-uploader').textContent = doc.full_name || "System Admin";
                        
                        // Status Badge
                        const statusClasses = {
                            'approved': 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'pending': 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'draft': 'bg-gray-100 text-gray-700 border-gray-200',
                            'rejected': 'bg-red-100 text-red-700 border-red-200'
                        };
                        const statusClass = statusClasses[doc.status.toLowerCase()] || 'bg-gray-100 text-gray-700 border-gray-200';
                        document.getElementById('preview-status').innerHTML = `<span class="px-3 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest ${statusClass}">${doc.status}</span>`;

                        // Icon
                        const iconClasses = {
                            'ordinance': 'bi-journal-text',
                            'resolution': 'bi-file-earmark-check',
                            'session': 'bi-people',
                            'agenda': 'bi-list-ul',
                            'committee': 'bi-shield-check',
                            'research': 'bi-search'
                        };
                        document.getElementById('modal-icon').className = `bi ${iconClasses[doc.document_type.toLowerCase()] || 'bi-file-earmark-text'}`;

                        // Tags
                        const tagsContainer = document.getElementById('preview-tags');
                        tagsContainer.innerHTML = '';
                        if (doc.tags) {
                            doc.tags.split(',').forEach(tag => {
                                if (tag.trim()) {
                                    const span = document.createElement('span');
                                    span.className = 'px-2 py-1 rounded-lg bg-white text-[9px] font-black uppercase tracking-widest text-gray-400 border border-gray-100';
                                    span.textContent = `#${tag.trim()}`;
                                    tagsContainer.appendChild(span);
                                }
                            });
                        }

                        // Document Analysis Section
                        const analysisContainer = document.getElementById('preview-analysis');
                        if (analysisContainer) {
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

                            analysisContainer.innerHTML = `
                                <section class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                                    <div class="px-4 md:px-6 py-3 md:py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white dark:from-gray-800/80 dark:to-gray-800/50">
                                        <div class="flex items-center min-w-0">
                                            <span class="w-1 h-5 bg-indigo-600 rounded-full mr-3 shrink-0"></span>
                                            <h3 class="text-xs md:text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest truncate">Document Analysis</h3>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ${info.badge}">
                                                <i class="bi bi-${info.icon}"></i><span class="hidden sm:inline">${info.label}</span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="p-4 md:p-6 space-y-5 md:space-y-6">
                                        ${processedDate ? `
                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-gray-400 dark:text-gray-500">
                                                <span class="flex items-center"><i class="bi bi-calendar-check mr-1.5"></i>Extracted on ${processedDate}</span>
                                                ${extractedText ? `<span class="flex items-center"><i class="bi bi-file-earmark-text mr-1.5"></i>${extractedText.length.toLocaleString()} characters &middot; ${wordCount.toLocaleString()} words</span>` : ''}
                                            </div>
                                        ` : ''}
                                        ${keyPoints.length > 0 ? `
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
                                            <div>
                                                <div class="flex items-center justify-between mb-3">
                                                    <div class="flex items-center">
                                                        <i class="bi bi-file-earmark-richtext text-slate-600 dark:text-slate-400 mr-2"></i>
                                                        <h4 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Document Preview</h4>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <button type="button" data-preview-id="${doc.id}" data-preview-name="${escapeHtml(doc.file_name || '')}" data-preview-type="${escapeHtml(doc.file_type || '')}" class="btn-original-preview text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 flex items-center gap-1">
                                                            <i class="bi bi-eye"></i><span>Preview</span>
                                                        </button>
                                                        <button type="button" onclick="toggleDocPreview(this)" class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center gap-1">
                                                            <i class="bi bi-chevron-down"></i><span>Expand</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div id="doc-preview-container" class="max-h-72 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 transition-all duration-300">
                                                    <div class="bg-white dark:bg-gray-900 p-4 md:p-10 doc-preview-page">
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
                        }

                        // Buttons
                        const downloadBtn = document.getElementById('preview-download-btn');
                        if (downloadBtn) {
                            downloadBtn.href = `<?= BASE_URL ?>/modules/document-management/api/download.php?id=${doc.id}`;
                        }
                        const downloadBtnMobile = document.getElementById('preview-download-btn-mobile');
                        if (downloadBtnMobile) {
                            downloadBtnMobile.href = `<?= BASE_URL ?>/modules/document-management/api/download.php?id=${doc.id}`;
                        }
                        // Detailed view button removed - now closes preview instead
                    } else {
                        showToast(result.error || "Failed to load document details", "error");
                        closePreview();
                    }
                } catch (error) {
                    console.error('Preview error:', error);
                    showToast("An unexpected error occurred", "error");
                    closePreview();
                }
            }

            function closePreview() {
                const modal = document.getElementById('preview-modal');
                const overlay = document.getElementById('preview-overlay');
                const content = document.getElementById('preview-content');
                const analysis = document.getElementById('preview-analysis');

                overlay.classList.add('opacity-0', 'pointer-events-none');
                overlay.classList.remove('opacity-100', 'pointer-events-auto');
                content.classList.add('translate-y-full', 'sm:scale-95', 'opacity-0');
                content.classList.remove('translate-y-0', 'sm:scale-100', 'opacity-100');

                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                    if (analysis) analysis.innerHTML = '';
                }, 300);
            }

            // Close on escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closePreview();
            });
            
            // Close on overlay click
            document.getElementById('preview-overlay')?.addEventListener('click', closePreview);

            /**
             * AJAX Functions
             */
            const updateResults = async (page = 1) => {
                // Show loading state
                resultsList.classList.add('opacity-50', 'pointer-events-none');
                
                const filterData = new FormData(filterForm);
                const mainData = new FormData(mainForm);
                const params = new URLSearchParams();
                
                // Handle checkbox arrays manually
                const checkboxes = filterForm.querySelectorAll('input[type="checkbox"][name="type[]"]:checked');
                const typeValues = Array.from(checkboxes).map(cb => cb.value);
                typeValues.forEach(val => params.append('type[]', val));
                
                // Handle other form fields
                for (const [key, value] of filterData.entries()) {
                    if (key !== 'type[]') {
                        params.set(key, value);
                    }
                }
                
                // Merge main form data
                for (const [key, value] of mainData.entries()) {
                    params.set(key, value);
                }
                
                params.set('page', page);
                
                const url = `${window.location.pathname}?${params.toString()}`;
                
                try {
                    const response = await fetch(url);
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Update results and meta
                    const newResults = doc.getElementById('results-list');
                    const newMeta = doc.getElementById('search-meta');
                    
                    if (newResults) resultsList.innerHTML = newResults.innerHTML;
                    if (newMeta) searchMeta.innerHTML = newMeta.innerHTML;
                    
                    // Re-apply view style
                    setView(currentView);
                    
                    // Update URL without refreshing
                    window.history.pushState({}, '', url);
                    
                    // Re-init AOS for new elements
                    if (typeof AOS !== 'undefined') {
                        AOS.refresh();
                    }
                } catch (error) {
                    console.error('Search failed:', error);
                } finally {
                    resultsList.classList.remove('opacity-50', 'pointer-events-none');
                }
            };

            // Handle Suggestions
            const showSuggestions = async (q) => {
                if (q.length < 2) {
                    hideSuggestions();
                    return;
                }

                try {
                    const response = await fetch(`?action=suggestions&q=${encodeURIComponent(q)}`);
                    const suggestions = await response.json();
                    
                    if (suggestions.length > 0) {
                        // Inline encoder for DB-sourced values injected into innerHTML.
                        // escapeHtml() from footer.php is available at runtime but may
                        // not be defined yet if called during initial parse.
                        const _esc = v => String(v ?? '')
                            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                            .replace(/>/g,'&gt;').replace(/"/g,'&quot;')
                            .replace(/'/g,'&#039;');

                        suggestionsContent.innerHTML = suggestions.map(s => {
                            const safeTitle = _esc(s.title);
                            const safeSub   = _esc(s.reference_number || s.document_type);
                            return `
                            <div class="p-4 hover:bg-red-50 dark:hover:bg-red-900/20 cursor-pointer border-b border-gray-50 dark:border-gray-700 flex items-center justify-between group" onclick="selectSuggestion(${JSON.stringify(s.title)})">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-gray-400 group-hover:bg-white dark:group-hover:bg-gray-800 group-hover:text-red-600 transition-all shadow-sm">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-white group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors">${safeTitle}</div>
                                        <div class="text-[10px] text-gray-400 dark:text-gray-500 font-black uppercase tracking-widest">${safeSub}</div>
                                    </div>
                                </div>
                                <i class="bi bi-arrow-up-left text-gray-300 group-hover:text-red-400 transition-all opacity-0 group-hover:opacity-100"></i>
                            </div>
                        `;
                        }).join('');
                        
                        suggestionsBox.classList.remove('hidden');
                        setTimeout(() => {
                            suggestionsBox.classList.remove('opacity-0', 'translate-y-2');
                            suggestionsBox.classList.add('opacity-100', 'translate-y-0');
                        }, 10);
                    } else {
                        hideSuggestions();
                    }
                } catch (error) {
                    console.error('Suggestions failed:', error);
                }
            };

            const hideSuggestions = () => {
                suggestionsBox.classList.add('opacity-0', 'translate-y-2');
                suggestionsBox.classList.remove('opacity-100', 'translate-y-0');
                setTimeout(() => {
                    suggestionsBox.classList.add('hidden');
                }, 200);
            };

            window.selectSuggestion = (title) => {
                searchInput.value = title;
                hideSuggestions();
                updateResults();
            };

            /**
             * Event Listeners
             */
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize view
                setView(currentView);

                if (typeof AOS !== 'undefined') {
                    AOS.init({
                        duration: 800,
                        once: true,
                        easing: 'ease-out-quad'
                    });
                }

                // Search Input Suggestions
                searchInput?.addEventListener('input', (e) => {
                    clearTimeout(queryTimer);
                    queryTimer = setTimeout(() => showSuggestions(e.target.value), 300);
                });

                // Listen for clicks outside to hide suggestions
                document.addEventListener('click', (e) => {
                    if (!suggestionsBox?.contains(e.target) && e.target !== searchInput) {
                        hideSuggestions();
                    }
                });

                if (filterForm) {
                    // Only Apply Filters button triggers refresh; filter inputs no longer auto-submit
                    filterForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        updateResults();
                    });
                }

                if (mainForm) {
                    mainForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        updateResults();
                    });
                }
            });
        </script>

<!-- Original File Preview Modal -->
<div id="original-file-preview-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-[100003] flex items-stretch sm:items-center justify-center sm:p-4" onclick="if(event.target===this) closeOriginalFilePreviewModal()">
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
        <div id="original-file-preview-body" class="mobile-preview-body flex-1 overflow-y-auto overflow-x-hidden bg-gray-100 dark:bg-gray-950 min-h-0" style="-webkit-overflow-scrolling: touch; overscroll-behavior: contain;">
        </div>
    </div>
</div>

<script>
function openOriginalFilePreviewModal(docId, fileName, fileType) {
    const modal = document.getElementById('original-file-preview-modal');
    const body = document.getElementById('original-file-preview-body');
    const titleEl = document.getElementById('original-file-preview-title');
    const typeEl = document.getElementById('original-file-preview-type');
    const newTabLink = document.getElementById('original-file-preview-newtab');
    const downloadLink = document.getElementById('original-file-preview-download');

    const baseUrl = '<?= BASE_URL ?>';
    const previewUrl = `${baseUrl}/modules/document-management/api/preview.php?id=${docId}`;
    const downloadUrl = `${baseUrl}/modules/document-management/api/download.php?id=${docId}`;

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

function closeOriginalFilePreviewModal() {
    const modal = document.getElementById('original-file-preview-modal');
    const body = document.getElementById('original-file-preview-body');
    const content = document.getElementById('original-file-preview-content');
    if (content) {
        content.classList.add('translate-y-full', 'sm:scale-95', 'opacity-0');
        content.classList.remove('translate-y-0', 'sm:scale-100', 'opacity-100');
    }
    setTimeout(() => {
        modal.classList.add('hidden');
        body.innerHTML = '';
        document.body.style.overflow = '';
    }, 300);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('original-file-preview-modal');
        if (modal && !modal.classList.contains('hidden')) {
            closeOriginalFilePreviewModal();
        }
    }
});

document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-original-preview');
    if (!btn) return;
    const docId = btn.getAttribute('data-preview-id');
    const fileName = btn.getAttribute('data-preview-name');
    const fileType = btn.getAttribute('data-preview-type');
    if (docId) {
        openOriginalFilePreviewModal(parseInt(docId, 10), fileName, fileType);
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
</script>

        <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
    </div>
</div>

