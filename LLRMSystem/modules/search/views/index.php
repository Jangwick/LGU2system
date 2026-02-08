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

// Extract data for view
$results = $data['results'] ?? [];
$total = $data['total'] ?? 0;
$facets = $data['facets'] ?? [];
$query = $data['query'] ?? '';
$filters = $data['filters'] ?? [];

$pageTitle = 'Advanced Search System';
$currentPage = 'search';

/**
 * Helper: Get status badge styling
 */
function getStatusBadgeClass($status) {
    $badges = [
        'approved' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
        'draft' => 'bg-gray-100 text-gray-700 border-gray-200',
        'rejected' => 'bg-red-100 text-red-700 border-red-200',
        'archived' => 'bg-blue-100 text-blue-700 border-blue-200'
    ];
    return $badges[strtolower($status)] ?? 'bg-gray-100 text-gray-700 border-gray-200';
}

/**
 * Helper: Get document type icon
 */
function getTypeIcon($type) {
    $icons = [
        'ordinance' => 'bi-journal-text text-amber-600',
        'resolution' => 'bi-file-earmark-check text-blue-600',
        'session' => 'bi-people text-emerald-600',
        'agenda' => 'bi-list-ul text-rose-600',
        'committee' => 'bi-shield-check text-indigo-600',
        'research' => 'bi-search text-purple-600'
    ];
    return $icons[strtolower($type)] ?? 'bi-file-earmark text-gray-600';
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
    <main class="flex-1 overflow-y-auto bg-gray-100 p-3 md:p-6 custom-scrollbar">
        <div class="max-w-7xl mx-auto space-y-6">
                
                <!-- Search Hero/Header -->
                <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-6 md:p-10 text-white relative overflow-hidden mb-6 animate-fade-in">
                    <!-- Background Decor -->
                    <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute -left-10 -top-10 w-48 h-48 bg-red-400/20 rounded-full blur-2xl"></div>

                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <div class="flex items-center gap-2 text-red-100 font-bold tracking-wider text-xs uppercase mb-3">
                                <span class="w-8 h-0.5 bg-red-100/50"></span>
                                AI-Powered Intelligence
                            </div>
                            <h1 class="text-3xl md:text-4xl font-black mb-2">Advanced Search</h1>
                            <p class="text-red-50 text-sm md:text-base max-w-xl opacity-90">Intelligent hybrid engine combining traditional keyword matching with semantic AI understanding.</p>
                        </div>
                        <div class="flex items-center gap-2 bg-black/10 p-1.5 rounded-xl backdrop-blur-md border border-white/10">
                            <button class="px-5 py-2.5 rounded-lg bg-white text-red-700 font-bold text-sm shadow-lg whitespace-nowrap">Documents</button>
                            <button class="px-5 py-2.5 rounded-lg text-white hover:bg-white/10 font-bold text-sm transition-all whitespace-nowrap">Legislations</button>
                        </div>
                    </div>
                </div>

                <!-- Main Layout Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    
                    <!-- Left Sidebar Filters (Static to prevent flicker on reload) -->
                    <aside class="space-y-6 lg:sticky lg:top-0 h-fit animate-slide-in-left">
                        <!-- Filters Card -->
                        <div class="bg-white rounded-2xl p-5 shadow-md border border-gray-100">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                    <i class="bi bi-sliders2 text-red-600"></i> Refine Results
                                </h3>
                                <a href="?" class="text-[10px] text-gray-400 hover:text-red-600 transition-colors uppercase font-black tracking-widest">Clear All</a>
                            </div>

                            <form id="filter-form" action="" method="GET" class="space-y-6">
                                <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">

                                <!-- Category Filter -->
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Document Type</label>
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
                                        <label class="flex items-center justify-between p-2.5 rounded-xl hover:bg-gray-50 cursor-pointer group transition-all <?= $checked ? 'bg-red-50 ring-1 ring-red-100' : '' ?>">
                                            <div class="flex items-center gap-3">
                                                <input type="radio" name="type" value="<?= $value ?>" <?= $checked ?> class="w-4 h-4 rounded-full border-gray-300 text-red-600 focus:ring-red-500/20">
                                                <span class="text-sm font-bold <?= $checked ? 'text-red-700' : 'text-gray-600' ?> group-hover:text-red-600"><?= $label ?></span>
                                            </div>
                                            <span class="text-[10px] font-black <?= $checked ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-400' ?> px-2 py-0.5 rounded-full transition-all">
                                                <?= number_format($count) ?>
                                            </span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Status</label>
                                    <select name="status" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all appearance-none cursor-pointer">
                                        <option value="">All Statuses</option>
                                        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                                        <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                                    </select>
                                </div>

                                <!-- Date Range -->
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Time Period</label>
                                    <div class="space-y-2">
                                        <div class="relative group">
                                            <i class="bi bi-calendar3 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs group-focus-within:text-red-500 transition-colors"></i>
                                            <input type="date" name="date_from" value="<?= $filters['date_from'] ?? '' ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-2.5 text-xs text-gray-600 focus:ring-2 focus:ring-red-500/20 outline-none">
                                        </div>
                                        <div class="relative group">
                                            <i class="bi bi-calendar3 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs group-focus-within:text-red-500 transition-colors"></i>
                                            <input type="date" name="date_to" value="<?= $filters['date_to'] ?? '' ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-2.5 text-xs text-gray-600 focus:ring-2 focus:ring-red-500/20 outline-none">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="w-full py-3.5 bg-red-600 hover:bg-red-700 text-white font-black uppercase tracking-widest text-xs rounded-xl shadow-lg shadow-red-600/20 transition-all flex items-center justify-center gap-2 transform active:scale-95">
                                    <i class="bi bi-funnel-fill"></i> Apply Filters
                                </button>
                            </form>
                        </div>

                        <!-- Quick Stats -->
                        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase mb-4 tracking-widest">System Insights</h4>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center text-red-600 shadow-sm">
                                        <i class="bi bi-database"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase">Total Records</div>
                                        <div class="text-sm font-black text-gray-800"><?= number_format($total) ?></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm">
                                        <i class="bi bi-lightning-charge-fill"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase">AI Status</div>
                                        <div class="text-sm font-black text-emerald-600"><?= defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY) ? 'Online' : 'Offline' ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- Main Search Results -->
                    <div class="lg:col-span-3 space-y-6">
                        
                        <!-- Top Search Bar -->
                        <div class="bg-white border border-gray-200 rounded-2xl p-2 pl-6 flex items-center gap-4 focus-within:ring-4 focus-within:ring-red-500/10 focus-within:border-red-500/40 transition-all shadow-xl shadow-gray-200/50 animate-fade-in-up">
                            <i class="bi bi-search text-gray-300 text-xl"></i>
                            <form action="" method="GET" class="flex-1 flex items-center gap-2">
                                <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Search by keywords, reference numbers, or intent..." class="flex-1 bg-transparent border-none outline-none text-gray-800 placeholder-gray-400 py-4 text-base md:text-lg font-medium" autocomplete="off">
                                
                                <!-- Search Mode Toggle -->
                                <div class="hidden md:flex items-center gap-1 bg-gray-100 p-1 rounded-xl border border-gray-200 mr-2">
                                    <span class="px-3 py-1.5 text-[10px] font-black uppercase text-red-600 bg-white rounded-lg shadow-sm border border-gray-200">Hybrid</span>
                                    <span class="px-3 py-1.5 text-[10px] font-black uppercase text-gray-400 hover:text-gray-600 cursor-pointer transition-colors">Semantic Only</span>
                                </div>

                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white w-14 h-14 rounded-xl flex items-center justify-center shadow-lg shadow-red-600/30 transition-all active:scale-95 group">
                                    <i class="bi bi-arrow-right text-2xl group-hover:translate-x-0.5 transition-transform"></i>
                                </button>
                            </form>
                        </div>

                        <!-- Results Meta -->
                        <div id="search-meta" class="flex items-center justify-between px-2 animate-fade-in-up animation-delay-100">
                            <div class="flex items-center gap-4">
                                <span class="text-sm text-gray-500 font-medium">
                                    Found <span class="text-gray-900 font-black"><?= number_format($total) ?></span> matches 
                                    <?php if($query): ?> for "<span class="text-red-600 italic font-bold"><?= htmlspecialchars($query) ?></span>"<?php endif; ?>
                                </span>
                                <div class="flex items-center gap-2 bg-white px-4 py-1.5 rounded-full border border-gray-200 text-[10px] font-black text-gray-500 shadow-sm uppercase tracking-widest">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span>
                                    Relevance Results
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="w-9 h-9 flex items-center justify-center text-gray-400 hover:text-red-600 bg-white rounded-lg border border-gray-200 shadow-sm transition-all"><i class="bi bi-grid-fill"></i></button>
                                <button class="w-9 h-9 flex items-center justify-center text-red-600 bg-red-50 rounded-lg border border-red-200 shadow-sm transition-all"><i class="bi bi-list-task"></i></button>
                            </div>
                        </div>

                        <!-- Results List -->
                        <div id="results-list" class="space-y-4">
                            <?php if (empty($results)): ?>
                                <!-- Empty State -->
                                <div class="bg-white border-2 border-dashed border-gray-200 rounded-3xl p-16 md:p-24 text-center shadow-sm animate-bounce-in">
                                    <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-300 shadow-inner">
                                        <i class="bi bi-search text-5xl"></i>
                                    </div>
                                    <h3 class="text-2xl font-black text-gray-800 mb-2">No documents found</h3>
                                    <p class="text-gray-500 max-w-sm mx-auto font-medium">Try adjusting your filters or use more specific keywords like "Ordinance 2024".</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($results as $index => $doc): 
                                    $delayClass = $index < 10 ? 'animation-delay-' . (($index + 2) * 100) : '';
                                ?>
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
                                                <div class="ml-auto flex items-center gap-2 bg-red-50/50 px-3 py-1.5 rounded-xl border border-red-100">
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
                                                <div class="flex items-center gap-1.5">
                                                    <?php 
                                                    $tags = explode(',', $doc['tags'] ?? '');
                                                    foreach(array_slice($tags, 0, 3) as $tag): if(empty($tag)) continue; ?>
                                                    <span class="px-2.5 py-1 rounded-lg bg-gray-50 text-[10px] font-black uppercase tracking-widest text-gray-400 border border-gray-100 hover:border-red-200 hover:text-red-600 transition-all cursor-pointer">#<?= trim($tag) ?></span>
                                                    <?php endforeach; ?>
                                                </div>

                                                <div class="ml-auto flex items-center gap-3">
                                                    <button class="px-6 py-2.5 rounded-xl bg-gray-800 hover:bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest transition-all transform active:scale-95 shadow-lg shadow-gray-200 group/btn">
                                                        <i class="bi bi-eye mr-2 group-hover/btn:scale-125 transition-transform"></i> Preview
                                                    </button>
                                                    <button class="w-10 h-10 rounded-xl bg-red-600 hover:bg-red-700 flex items-center justify-center text-white transition-all shadow-lg shadow-red-600/30 transform active:scale-90 group/dl">
                                                        <i class="bi bi-download group-hover/dl:translate-y-0.5 transition-transform"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <style>
            .custom-scrollbar::-webkit-scrollbar { width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 20px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(220,38,38,0.2); }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof AOS !== 'undefined') {
                    AOS.init({
                        duration: 800,
                        once: true,
                        easing: 'ease-out-quad'
                    });
                }

                const filterForm = document.getElementById('filter-form');
                const resultsList = document.getElementById('results-list');
                const searchMeta = document.getElementById('search-meta');

                if (filterForm) {
                    // Function to handle filter updates via AJAX
                    const updateResults = async () => {
                        // Show loading state
                        resultsList.classList.add('opacity-50', 'pointer-events-none');
                        
                        const formData = new FormData(filterForm);
                        const params = new URLSearchParams(formData);
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
                            
                            // Update URL without refreshing
                            window.history.pushState({}, '', url);
                            
                            // Re-init AOS for new elements
                            if (typeof AOS !== 'undefined') {
                                AOS.refresh();
                            }
                        } catch (error) {
                            console.error('Search failed:', error);
                            // Fallback to traditional submit if AJAX fails
                            filterForm.submit();
                        } finally {
                            resultsList.classList.remove('opacity-50', 'pointer-events-none');
                        }
                    };

                    filterForm.querySelectorAll('input[type="radio"], select').forEach(el => {
                        el.addEventListener('change', (e) => {
                            e.preventDefault();
                            updateResults();
                        });
                    });

                    // Prevent tradition submit on the form
                    filterForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        updateResults();
                    });
                }
            });
        </script>

        <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
    </div>
</div>

