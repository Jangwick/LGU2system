<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

checkAuth();

// Load controller
require_once __DIR__ . '/../controllers/SearchController.php';
$controller = new SearchController();

// Handle AJAX suggestions
if (isset($_GET['action']) && $_GET['action'] === 'suggestions') {
    $controller->suggestions();
}

// Handle AJAX export
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    $controller->export();
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
        'rejected' => 'badge-danger',
        'archived' => 'badge-gray'
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
                            <button class="px-4 md:px-5 py-2 md:py-2.5 rounded-md md:rounded-lg bg-white !text-red-700 font-black text-[10px] md:text-sm shadow-lg whitespace-nowrap uppercase tracking-tight">Documents</button>
                            <button class="px-4 md:px-5 py-2 md:py-2.5 rounded-md md:rounded-lg text-white hover:bg-white/10 font-black text-[10px] md:text-sm transition-all whitespace-nowrap uppercase tracking-tight">Legislations</button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Filter Toggle -->
                <div class="lg:hidden mb-4">
                    <button onclick="toggleMobileFilters()" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center justify-between shadow-sm active:scale-[0.98] transition-all">
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
                                            $checked = ($filters['type'] ?? '') === $value ? 'checked' : '';
                                            $count = $getFacetCount($value);
                                        ?>
                                        <label class="flex items-center justify-between p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer group transition-all <?= $checked ? 'bg-red-50 dark:bg-red-900/10 ring-1 ring-red-100 dark:ring-red-900/30' : '' ?>">
                                            <div class="flex items-center gap-3">
                                                <input type="radio" name="type" value="<?= $value ?>" <?= $checked ?> class="w-4 h-4 rounded-full border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500/20 bg-white dark:bg-gray-700">
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
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Status</label>
                                    <select name="status" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all appearance-none cursor-pointer">
                                        <option value="">All Statuses</option>
                                        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                                        <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                                    </select>
                                </div>

                                <!-- Date Range -->
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Time Period</label>
                                    <div class="space-y-2">
                                        <div class="relative group">
                                            <i class="bi bi-calendar3 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xs group-focus-within:text-red-500 transition-colors"></i>
                                            <input type="date" name="date_from" value="<?= $filters['date_from'] ?? '' ?>" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl pl-11 pr-4 py-2.5 text-xs text-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-red-500/20 outline-none">
                                        </div>
                                        <div class="relative group">
                                            <i class="bi bi-calendar3 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xs group-focus-within:text-red-500 transition-colors"></i>
                                            <input type="date" name="date_to" value="<?= $filters['date_to'] ?? '' ?>" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl pl-11 pr-4 py-2.5 text-xs text-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-red-500/20 outline-none">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="w-full py-3.5 bg-red-600 hover:bg-red-700 text-white font-black uppercase tracking-widest text-xs rounded-xl shadow-lg shadow-red-600/20 transition-all flex items-center justify-center gap-2 transform active:scale-95">
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
                                    <?= ucfirst($mode) ?> Engine
                                </div>
                                <button onclick="exportResults()" class="flex items-center gap-2 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 px-3 md:px-4 py-1.5 rounded-full border border-gray-200 dark:border-gray-700 text-[9px] md:text-[10px] font-black text-gray-500 dark:text-gray-400 shadow-sm uppercase tracking-widest transition-all">
                                    <i class="bi bi-download text-red-600 dark:text-red-500"></i> Export CSV
                                </button>
                            </div>
                            <div class="flex items-center gap-2 self-end sm:self-auto">
                                <button onclick="setView('grid')" id="view-grid" class="w-8 h-8 md:w-9 md:h-9 flex items-center justify-center transition-all bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:text-red-600 dark:hover:text-red-500 rounded-lg shadow-sm">
                                    <i class="bi bi-grid-fill"></i>
                                </button>
                                <button onclick="setView('list')" id="view-list" class="w-8 h-8 md:w-9 md:h-9 flex items-center justify-center transition-all bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-900/50 text-red-600 dark:text-red-400 rounded-lg shadow-sm">
                                    <i class="bi bi-list-task"></i>
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
                                <div class="group bg-white dark:bg-gray-800 hover:bg-white dark:hover:bg-gray-800/50 border border-gray-200 dark:border-gray-700 hover:border-red-200 dark:hover:border-red-900/50 rounded-2xl p-5 md:p-7 transition-all duration-300 shadow-sm hover:shadow-xl hover:-translate-y-1 animate-fade-in-up <?= $delayClass ?>">
                                    <div class="flex flex-col md:flex-row gap-6">
                                        <!-- Doc Icon -->
                                        <div class="w-16 h-16 shrink-0 rounded-2xl bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-700 flex items-center justify-center text-3xl group-hover:scale-110 group-hover:bg-red-50 dark:group-hover:bg-red-900/20 group-hover:border-red-100 dark:group-hover:border-red-900 transition-all duration-300">
                                            <i class="bi <?= getTypeIcon($doc['document_type']) ?>"></i>
                                        </div>

                                        <!-- Doc Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                                <span class="px-3 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest <?= getStatusBadgeClass($doc['status']) ?>">
                                                    <?= $doc['status'] ?>
                                                </span>
                                                <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest bg-gray-50 dark:bg-gray-950 px-2 py-1 rounded-lg border border-gray-100 dark:border-gray-700">REF: <?= $doc['reference_number'] ?? 'N/A' ?></span>
                                                
                                                <?php if(isset($doc['relevance_score'])): ?>
                                                <div class="relevance-bar ml-auto flex items-center gap-2 bg-red-50/50 dark:bg-red-900/10 px-3 py-1.5 rounded-xl border border-red-100 dark:border-red-900/30">
                                                    <div class="text-[9px] font-black uppercase text-red-600 dark:text-red-400 tracking-tighter">AI Relevance</div>
                                                    <div class="h-1.5 w-14 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                        <div class="h-full bg-red-500 shadow-sm shadow-red-500/50" style="width: <?= $doc['relevance_score'] * 100 ?>%"></div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>

                                            <h3 class="text-xl font-black text-gray-800 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors line-clamp-1 mb-2">
                                                <?= htmlspecialchars($doc['title']) ?>
                                            </h3>

                                            <p class="text-gray-500 dark:text-gray-400 text-sm line-clamp-2 mb-6 leading-relaxed font-medium">
                                                <?= htmlspecialchars($doc['description'] ?? 'No description available for this legislative record.') ?>
                                            </p>

                                            <div class="flex flex-wrap items-center gap-y-4 gap-x-6 border-t border-gray-50 dark:border-gray-700 pt-5">
                                                <div class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500">
                                                    <i class="bi bi-calendar-event text-red-500 dark:text-red-400 text-sm"></i>
                                                    <?= date('M d, Y', strtotime($doc['created_at'])) ?>
                                                </div>
                                                <div class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500">
                                                    <i class="bi bi-person-circle text-gray-300 dark:text-gray-600 text-sm"></i>
                                                    <span class="hover:text-red-500 dark:hover:text-red-400 transition-colors cursor-default"><?= htmlspecialchars($doc['uploaded_by_name'] ?? 'System Admin') ?></span>
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    <?php 
                                                    $tags = explode(',', $doc['tags'] ?? '');
                                                    foreach(array_slice($tags, 0, 3) as $tag): if(empty($tag)) continue; ?>
                                                    <span class="px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-950 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 border border-gray-100 dark:border-gray-700 hover:border-red-200 dark:hover:border-red-900 hover:text-red-600 dark:hover:text-red-400 transition-all cursor-pointer">#<?= trim($tag) ?></span>
                                                    <?php endforeach; ?>
                                                </div>

                                                <div class="card-actions ml-auto flex items-center gap-3">
                                                    <button onclick="previewDocument(<?= $doc['id'] ?>)" class="px-6 py-2.5 rounded-xl bg-gray-800 dark:bg-black hover:bg-gray-900 dark:bg-gray-700 text-white text-[10px] font-black uppercase tracking-widest transition-all transform active:scale-95 shadow-lg shadow-gray-200 dark:shadow-none group/btn">
                                                        <i class="bi bi-eye mr-2 group-hover/btn:scale-125 transition-transform"></i> Preview
                                                    </button>
                                                    <a href="<?php echo DOCUMENTS_URL; ?>/api/download.php?id=<?= $doc['id'] ?>" class="w-10 h-10 rounded-xl bg-red-600 dark:bg-red-700 hover:bg-red-700 dark:hover:bg-red-800 flex items-center justify-center text-white transition-all shadow-lg shadow-red-600/30 transform active:scale-90 group/dl">
                                                        <i class="bi bi-download group-hover/dl:translate-y-0.5 transition-transform"></i>
                                                    </a>
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
                                        <button onclick="changePage(<?= $page - 1 ?>)" class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:border-red-600 dark:hover:border-red-500 hover:text-red-600 dark:hover:text-red-400 transition-all shadow-sm">
                                            <i class="bi bi-chevron-left"></i>
                                        </button>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <button onclick="changePage(<?= $i ?>)" class="w-10 h-10 rounded-xl font-bold text-sm transition-all shadow-sm <?= $i === $page ? 'bg-red-600 text-white border-red-600' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:border-red-600 dark:hover:border-red-500 hover:text-red-600 dark:hover:text-red-400' ?>">
                                            <?= $i ?>
                                        </button>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <button onclick="changePage(<?= $page + 1 ?>)" class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:border-red-600 dark:hover:border-red-500 hover:text-red-600 dark:hover:text-red-400 transition-all shadow-sm">
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
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div id="preview-overlay" class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity opacity-0 pointer-events-none"></div>

                <!-- Modal Content -->
                <div id="preview-content" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-red-600 to-red-800 px-6 py-6 md:px-8 flex items-center justify-between text-white border-b border-white/10">
                        <div class="flex items-center gap-4">
                            <div id="modal-icon-bg" class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-2xl">
                                <i id="modal-icon" class="bi bi-file-earmark-text"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black leading-6" id="modal-title">Document Preview</h3>
                                <p id="modal-subtitle" class="text-red-100 text-xs font-bold uppercase tracking-widest mt-1 opacity-80">Reference ID: ---</p>
                            </div>
                        </div>
                        <button onclick="closePreview()" class="w-10 h-10 rounded-xl hover:bg-white/10 flex items-center justify-center transition-all">
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="bg-white dark:bg-gray-800 px-6 py-8 md:px-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <!-- Left: Details -->
                            <div class="md:col-span-2 space-y-6">
                                <div>
                                    <h4 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Document Title</h4>
                                    <h2 id="preview-title" class="text-2xl font-black text-gray-800 dark:text-white leading-tight">---</h2>
                                </div>

                                <div>
                                    <h4 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Description</h4>
                                    <p id="preview-desc" class="text-gray-600 dark:text-gray-400 leading-relaxed font-medium">---</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-4 border border-gray-100 dark:border-gray-700">
                                        <h4 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Status</h4>
                                        <div id="preview-status" class="inline-flex mt-1">
                                            <span class="px-3 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700">---</span>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-4 border border-gray-100 dark:border-gray-700">
                                        <h4 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Date Created</h4>
                                        <p id="preview-date" class="text-sm font-bold text-gray-800 dark:text-gray-200 mt-1">---</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Metadata & Actions -->
                            <div class="space-y-6">
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-3xl p-6 border border-gray-100 dark:border-gray-700">
                                    <h4 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">File Information</h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 flex items-center justify-center text-red-600 dark:text-red-500 shadow-sm border border-gray-100 dark:border-gray-700">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p id="preview-filename" class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">filename.pdf</p>
                                                <p id="preview-filesize" class="text-[10px] text-gray-400 dark:text-gray-500">0.0 MB</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 flex items-center justify-center text-gray-400 dark:text-gray-500 shadow-sm border border-gray-100 dark:border-gray-700">
                                                <i class="bi bi-person-circle"></i>
                                            </div>
                                            <div>
                                                <p id="preview-uploader" class="text-sm font-bold text-gray-800 dark:text-gray-200">Uploader</p>
                                                <p class="text-[10px] text-gray-400 dark:text-gray-500">Uploaded By</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="preview-tags" class="mt-6 flex flex-wrap gap-2">
                                        <!-- Tags will be injected here -->
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <a id="preview-download-btn" href="#" class="w-full py-3.5 bg-red-600 hover:bg-red-700 text-white font-black uppercase tracking-widest text-[10px] rounded-2xl shadow-lg shadow-red-600/20 transition-all flex items-center justify-center gap-2 transform active:scale-95">
                                        <i class="bi bi-download"></i> Download Document
                                    </a>
                                    <a id="preview-full-view" href="#" class="w-full py-3.5 bg-gray-800 dark:bg-black hover:bg-gray-900 text-white font-black uppercase tracking-widest text-[10px] rounded-2xl shadow-lg shadow-gray-200 dark:shadow-none transition-all flex items-center justify-center gap-2 transform active:scale-95">
                                        <i class="bi bi-fullscreen"></i> Detailed View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .custom-scrollbar::-webkit-scrollbar { width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 20px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(220,38,38,0.2); }
        </style>

        <script>
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
                    resultsList.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'xl:grid-cols-3', 'gap-4');
                    
                    // Update buttons
                    gridBtn.classList.add('bg-red-50', 'border-red-200', 'text-red-600');
                    gridBtn.classList.remove('bg-white', 'border-gray-200', 'text-gray-400');
                    listBtn.classList.remove('bg-red-50', 'border-red-200', 'text-red-600');
                    listBtn.classList.add('bg-white', 'border-gray-200', 'text-gray-400');
                    
                    // Update items
                    resultsList.querySelectorAll('.group > div').forEach(item => {
                        item.classList.remove('md:flex-row');
                        item.classList.add('flex-col');
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
                    resultsList.classList.remove('grid', 'grid-cols-1', 'md:grid-cols-2', 'xl:grid-cols-3', 'gap-4');
                    
                    // Update buttons
                    listBtn.classList.add('bg-red-50', 'border-red-200', 'text-red-600');
                    listBtn.classList.remove('bg-white', 'border-gray-200', 'text-gray-400');
                    gridBtn.classList.remove('bg-red-50', 'border-red-200', 'text-red-600');
                    gridBtn.classList.add('bg-white', 'border-gray-200', 'text-gray-400');
                    
                    // Update items
                    resultsList.querySelectorAll('.group > div').forEach(item => {
                        item.classList.add('md:flex-row');
                        item.classList.remove('flex-col');
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
            function exportResults() {
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
                    content.classList.remove('opacity-0', 'translate-y-4', 'scale-95');
                    content.classList.add('opacity-100', 'translate-y-0', 'scale-100');
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

                        // Buttons
                        document.getElementById('preview-download-btn').href = `<?= BASE_URL ?>/modules/document-management/api/download.php?id=${doc.id}`;
                        document.getElementById('preview-full-view').href = `<?= BASE_URL ?>/modules/document-management/views/view.php?id=${doc.id}`;
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

                overlay.classList.add('opacity-0', 'pointer-events-none');
                overlay.classList.remove('opacity-100', 'pointer-events-auto');
                content.classList.add('opacity-0', 'translate-y-4', 'scale-95');
                content.classList.remove('opacity-100', 'translate-y-0', 'scale-100');

                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
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
                const params = new URLSearchParams(filterData);
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
                        suggestionsContent.innerHTML = suggestions.map(s => `
                            <div class="p-4 hover:bg-red-50 dark:hover:bg-red-900/20 cursor-pointer border-b border-gray-50 dark:border-gray-700 flex items-center justify-between group" onclick="selectSuggestion('${s.title}')">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-gray-400 group-hover:bg-white dark:group-hover:bg-gray-800 group-hover:text-red-600 transition-all shadow-sm">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-white group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors">${s.title}</div>
                                        <div class="text-[10px] text-gray-400 dark:text-gray-500 font-black uppercase tracking-widest">${s.reference_number || s.document_type}</div>
                                    </div>
                                </div>
                                <i class="bi bi-arrow-up-left text-gray-300 group-hover:text-red-400 transition-all opacity-0 group-hover:opacity-100"></i>
                            </div>
                        `).join('');
                        
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
                    filterForm.querySelectorAll('input[type="radio"], input[type="date"], select').forEach(el => {
                        el.addEventListener('change', (e) => {
                            updateResults();
                        });
                    });

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

        <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
    </div>
</div>

