<?php
/**
 * Public Document Portal - Search & Preview
 */
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../search/services/SearchService.php';
require_once __DIR__ . '/../../search/services/EmbeddingService.php';

// ── Security Response Headers ────────────────────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .
    "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://static.cloudflareinsights.com; " .
    "script-src-elem 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://static.cloudflareinsights.com; " .
    "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com; " .
    "font-src 'self' data: https://cdn.jsdelivr.net https://fonts.gstatic.com; " .
    "img-src 'self' data: blob: https://lacs.spvalenzuela.com https://images.unsplash.com https://valenzuela.gov.ph; " .
    "connect-src 'self' https://cdn.jsdelivr.net https://static.cloudflareinsights.com; " .
    "frame-ancestors 'self'; " .
    "base-uri 'self'; " .
    "form-action 'self';"
);
// ─────────────────────────────────────────────────────────────────────────────

// Handle AJAX suggestions
if (isset($_GET['action']) && $_GET['action'] === 'suggestions') {
    header('Content-Type: application/json');
    $db = getDatabase();
    $searchService = new SearchService($db);
    $q = $_GET['q'] ?? '';
    if (strlen($q) >= 2) {
        $suggestions = $searchService->getSuggestions($q, 10);
        // Filter to only approved
        $filtered = [];
        foreach ($suggestions as $s) {
            // getSuggestions doesn't return status, so we return all - the actual search filters by status
            $filtered[] = $s;
        }
        echo json_encode($filtered);
    } else {
        echo json_encode([]);
    }
    exit;
}

$db = getDatabase();
$embeddingService = new EmbeddingService();
$searchService = new SearchService($db, $embeddingService);

$query = $_GET['q'] ?? '';
$mode = $_GET['mode'] ?? 'hybrid';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$typeFilter = $_GET['type'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$filters = [
    'type' => $typeFilter, 'status' => '', 'date_from' => $dateFrom,
    'date_to' => $dateTo, 'tags' => '', 'limit' => $perPage, 'offset' => ($page - 1) * $perPage
];

$results = []; $total = 0;
try {
    if (!empty($query)) {
        if ($mode === 'semantic') {
            $allResults = $searchService->semanticSearch($query, $filters);
        } else {
            $pf = $filters; $pf['limit'] = 100; $pf['offset'] = 0;
            $allResults = $searchService->hybridSearch($query, $pf);
        }
        $allResults = array_values(array_filter($allResults, fn($d) => in_array(strtolower($d['status'] ?? ''), ['approved'])));
        $total = count($allResults);
        $results = array_slice($allResults, ($page - 1) * $perPage, $perPage);
    } else {
        $f1 = $filters; $f1['status'] = 'approved';
        $r1 = $searchService->search('', $f1); $t1 = $searchService->getCount('', $f1);
        $all = $r1;
        usort($all, fn($a,$b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        $total = $t1;
        $results = array_slice($all, ($page - 1) * $perPage, $perPage);
    }
} catch (Exception $e) { $results = []; $total = 0; }

$totalPages = ceil($total / max($perPage, 1));
$facets = $searchService->getFacets($query);

function getPublicTypeIcon($t) {
    $i = ['ordinance'=>'bi-journal-text text-amber-600 dark:text-amber-500','resolution'=>'bi-file-earmark-check text-blue-600 dark:text-blue-500','session'=>'bi-people text-emerald-600 dark:text-emerald-500','agenda'=>'bi-list-ul text-rose-600 dark:text-rose-500','committee'=>'bi-shield-check text-indigo-600 dark:text-indigo-500','research'=>'bi-search text-purple-600 dark:text-purple-500'];
    return $i[strtolower($t)] ?? 'bi-file-earmark text-gray-600 dark:text-gray-400';
}
function getPublicStatusBadge($s) {
    $b = ['approved'=>'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800'];
    return $b[strtolower($s)] ?? 'bg-gray-100 text-gray-600 border-gray-200';
}
function getFacetCount($facets, $type) {
    if (!isset($facets['by_type'])) return 0;
    foreach ($facets['by_type'] as $f) { if (strtolower($f['document_type']) === $type) return $f['count']; }
    return 0;
}
function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<script>if(localStorage.getItem('theme')==='dark')document.documentElement.classList.add('dark');</script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Document Portal - <?= APP_NAME ?></title>
    <meta name="description" content="Search and preview approved legislative records from the City Government of Valenzuela.">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- Tailwind v4: Use .dark class instead of prefers-color-scheme -->
    <style type="text/tailwindcss">
        @custom-variant dark (&:where(.dark, .dark *));
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Inter',sans-serif}
        html.dark{color-scheme:dark}
        .glass-nav{background:rgba(255,255,255,.85);backdrop-filter:blur(16px)}
        html.dark .glass-nav{background:rgba(3,7,18,.92)}
        .custom-scrollbar::-webkit-scrollbar{width:6px}.custom-scrollbar::-webkit-scrollbar-track{background:transparent}.custom-scrollbar::-webkit-scrollbar-thumb{background:rgba(0,0,0,.1);border-radius:20px}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
        .animate-fade-in-up{animation:fadeInUp .5s ease-out forwards}
        .animation-delay-100{animation-delay:.1s;opacity:0}.animation-delay-200{animation-delay:.2s;opacity:0}.animation-delay-300{animation-delay:.3s;opacity:0}.animation-delay-400{animation-delay:.4s;opacity:0}.animation-delay-500{animation-delay:.5s;opacity:0}.animation-delay-600{animation-delay:.6s;opacity:0}.animation-delay-700{animation-delay:.7s;opacity:0}.animation-delay-800{animation-delay:.8s;opacity:0}
        .doc-preview-page{font-family:'Georgia','Times New Roman',serif;line-height:1.8;color:#1a1a1a;max-width:100%;margin:0 auto}
        .doc-preview-page .doc-content h2{font-family:'Georgia','Times New Roman',serif;font-size:15px;font-weight:700;letter-spacing:.05em}
        .doc-preview-page .doc-content h3{font-family:'Georgia','Times New Roman',serif;font-size:13px;font-weight:700}
        .doc-preview-page .doc-content p{text-align:justify;hyphens:auto}
        .doc-preview-page .doc-content ol,.doc-preview-page .doc-content ul{margin-left:0;padding-left:1.5rem}
        .doc-preview-page .doc-content li{margin-bottom:.4rem}
        .doc-preview-page .doc-content ol li::marker{font-weight:600}
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen custom-scrollbar">

<!-- Navigation -->
<nav class="fixed top-0 w-full z-50 glass-nav border-b border-gray-200/50 dark:border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-2">
                <a href="<?= BASE_URL ?>" class="flex items-center group flex-shrink-0">
                <img src="<?= BASE_URL ?>/public/assets/images/logo.png" alt="Logo" class="h-8 w-8 mr-2 rounded-full shadow-sm" onerror="this.src='<?= BASE_URL ?>/public/assets/images/valenzuela-logo.webp'">
                <span class="text-lg font-black text-[#002d72] dark:text-blue-400 tracking-tighter">VALENZUELA<span class="text-red-600">LRMS</span></span>
            </a>
            </div>
            <!-- Desktop top-right actions -->
            <div class="hidden sm:flex items-center gap-3">
                <a href="<?= BASE_URL ?>" class="inline-flex items-center gap-2 border border-gray-200 dark:border-gray-700 hover:border-red-300 hover:text-red-600 text-gray-600 dark:text-gray-300 font-bold px-4 py-2 rounded-full text-xs transition-all">
                    <i class="bi bi-arrow-left"></i> Back to Home
                </a>
                <a href="<?= LOGIN_URL ?>" class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-black px-4 py-2 rounded-full text-xs shadow-lg shadow-red-200/50 dark:shadow-none transition-all">Sign In</a>
            </div>
            <!-- Mobile top-right actions -->
            <div class="flex sm:hidden items-center gap-2 absolute right-2 top-2">
                <button type="button" onclick="toggleDarkMode()" class="dark-toggle w-8 h-8 md:w-10 md:h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:text-red-600 hover:border-red-200" title="Toggle Dark Mode" aria-label="Toggle Dark Mode">
                    <i id="darkModeIcon" class="bi bi-moon-fill text-sm"></i>
                </button>
                <button type="button" id="mobile-nav-toggle" class="flex items-center justify-center w-10 h-10 rounded-xl text-gray-700 hover:text-red-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all" aria-label="Open menu">
                    <i class="bi bi-list text-2xl"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<?php require_once MODULES_PATH . '/core/layouts/mobile_nav.php'; ?>

<main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-950 pt-20 px-2 pb-2 md:pt-22 md:px-6 md:pb-6">
    <div class="max-w-7xl mx-auto space-y-4 md:space-y-6">

    <!-- Hero Banner (rounded card like admin) -->
    <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-xl md:rounded-2xl shadow-xl p-5 md:p-10 text-white relative overflow-hidden animate-fade-in-up">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -left-10 -top-10 w-48 h-48 bg-red-400/20 rounded-full blur-2xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4 md:gap-6">
            <div>
                <div class="flex items-center gap-2 text-red-100 font-bold tracking-wider text-[10px] md:text-xs uppercase mb-2 md:mb-3"><span class="w-6 md:w-8 h-0.5 bg-red-100/50"></span>Open Access &middot; AI-Powered Intelligence</div>
                <h1 class="text-2xl md:text-4xl font-black mb-1 md:mb-2 italic">Public Document Portal</h1>
                <p class="text-red-50 text-xs md:text-base max-w-xl opacity-90 font-medium">Search and preview approved legislative records. Hybrid engine combining keywords with semantic understanding.</p>
                <div class="mt-3 md:mt-4 flex flex-wrap items-center gap-2"></div>
            </div>
            <div class="flex items-center gap-1.5 bg-black/10 p-1 rounded-lg md:rounded-xl backdrop-blur-md border border-white/10 w-fit">
                <button class="hero-toggle-btn px-4 md:px-5 py-2 md:py-2.5 rounded-md md:rounded-lg bg-white !text-red-700 font-black text-[10px] md:text-sm shadow-lg whitespace-nowrap uppercase tracking-tight">Documents</button>
                <button class="hero-toggle-btn px-4 md:px-5 py-2 md:py-2.5 rounded-md md:rounded-lg text-white hover:bg-white/10 font-black text-[10px] md:text-sm transition-all whitespace-nowrap uppercase tracking-tight">Legislations</button>
            </div>
        </div>
    </div>

    <!-- Mobile Filter Toggle -->
    <div class="lg:hidden">
        <button type="button" onclick="toggleMobileFilters()" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center justify-between shadow-sm active:scale-[0.98] transition-all">
            <div class="flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/30 flex items-center justify-center text-red-600"><i class="bi bi-sliders2"></i></div><span class="font-bold text-gray-700 dark:text-gray-200">Refine Search</span></div>
            <i id="filter-chevron" class="bi bi-chevron-down text-gray-400 transition-transform"></i>
        </button>
    </div>

    <!-- Main Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 md:gap-6">

            <!-- Left Sidebar Filters -->
            <aside id="filters-sidebar" class="hidden lg:block space-y-4 md:space-y-6 lg:sticky lg:top-20 h-fit animate-fade-in-up">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-md border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2"><i class="bi bi-sliders2 text-red-600"></i> Refine Results</h3>
                        <a href="?" class="text-[10px] text-gray-400 hover:text-red-600 transition-colors uppercase font-black tracking-widest">Clear All</a>
                    </div>
                    <form id="filter-form" action="" method="GET" class="space-y-6">
                        <input type="hidden" name="q" value="<?= e($query) ?>">
                        <input type="hidden" name="mode" value="<?= e($mode) ?>">
                        <!-- Document Type -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Document Type</label>
                            <div class="space-y-1">
                                <?php
                                $types = ['ordinance'=>'Ordinance','resolution'=>'Resolution','session'=>'Session','agenda'=>'Agenda','committee'=>'Committee','research'=>'Research'];
                                foreach($types as $val => $lbl):
                                    $checked = $typeFilter === $val ? 'checked' : '';
                                    $count = getFacetCount($facets, $val);
                                ?>
                                <label class="flex items-center justify-between p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer group transition-all <?= $checked ? 'bg-red-50 dark:bg-red-900/10 ring-1 ring-red-100 dark:ring-red-900/30' : '' ?>">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="type" value="<?= $val ?>" <?= $checked ?> class="w-4 h-4 rounded-full border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500/20 bg-white dark:bg-gray-700">
                                        <span class="text-sm font-bold <?= $checked ? 'text-red-700 dark:text-red-400' : 'text-gray-600 dark:text-gray-400' ?> group-hover:text-red-600 dark:group-hover:text-red-400"><?= $lbl ?></span>
                                    </div>
                                    <span class="text-[10px] font-black <?= $checked ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500' ?> px-2 py-0.5 rounded-full"><?= number_format($count) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- Date Range -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Time Period</label>
                            <div class="space-y-2">
                                <div class="relative group"><i class="bi bi-calendar3 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs group-focus-within:text-red-500 transition-colors"></i><input type="date" name="date_from" value="<?= e($dateFrom) ?>" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl pl-11 pr-4 py-2.5 text-xs text-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-red-500/20 outline-none"></div>
                                <div class="relative group"><i class="bi bi-calendar3 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs group-focus-within:text-red-500 transition-colors"></i><input type="date" name="date_to" value="<?= e($dateTo) ?>" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl pl-11 pr-4 py-2.5 text-xs text-gray-600 dark:text-gray-300 focus:ring-2 focus:ring-red-500/20 outline-none"></div>
                            </div>
                        </div>
                        <button type="submit" class="w-full py-3.5 bg-red-600 hover:bg-red-700 text-white font-black uppercase tracking-widest text-xs rounded-xl shadow-lg shadow-red-600/20 transition-all flex items-center justify-center gap-2 active:scale-95"><i class="bi bi-funnel-fill"></i> Apply Filters</button>
                    </form>
                </div>
                <!-- Quick Stats -->
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl p-5 shadow-sm">
                    <h4 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase mb-4 tracking-widest">Public Records</h4>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3"><div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-600 shadow-sm"><i class="bi bi-database"></i></div><div><div class="text-[10px] text-gray-400 font-bold uppercase">Total Records</div><div class="text-sm font-black text-gray-800 dark:text-gray-200"><?= number_format($total) ?></div></div></div>
                        <div class="flex items-center gap-3"><div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 shadow-sm"><i class="bi bi-lightning-charge-fill"></i></div><div><div class="text-[10px] text-gray-400 font-bold uppercase">AI Status</div><div class="text-sm font-black text-emerald-600"><?= defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY) ? 'Online' : 'Offline' ?></div></div></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl p-5 shadow-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed"><i class="bi bi-info-circle text-red-500 mr-1"></i>This portal shows only <strong>approved</strong> records. For full access, please <a href="<?= LOGIN_URL ?>" class="text-red-600 font-bold hover:underline">sign in</a>.</p>
                </div>
            </aside>

            <!-- Main Results -->
            <div class="lg:col-span-3 space-y-4 md:space-y-6">
                <!-- Search Bar -->
                <div class="relative group">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl md:rounded-3xl p-1.5 md:p-2 pl-4 md:pl-6 flex items-center gap-3 md:gap-4 focus-within:ring-4 focus-within:ring-red-500/10 focus-within:border-red-500/40 transition-all shadow-xl shadow-gray-200/50 dark:shadow-none animate-fade-in-up">
                        <i class="bi bi-search text-gray-400 dark:text-gray-500 text-lg md:text-xl shrink-0"></i>
                        <form id="search-main-form" action="" method="GET" class="flex-1 flex items-center gap-2">
                            <input type="hidden" name="mode" id="search-mode" value="<?= e($mode) ?>">
                            <input type="text" name="q" id="search-input" value="<?= e($query) ?>" placeholder="Search documents or intent..." aria-label="Search documents or intent" class="flex-1 bg-transparent border-none outline-none text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 py-3 md:py-4 text-sm md:text-lg font-bold" autocomplete="off">
                            <div class="hidden md:flex items-center gap-1 bg-gray-100 dark:bg-gray-950 p-1 rounded-xl border border-gray-200 dark:border-gray-700 mr-2">
                                <button type="button" onclick="setSearchMode('hybrid')" class="mode-btn px-3 py-1.5 text-[10px] font-black uppercase transition-all duration-200 rounded-lg <?= $mode === 'hybrid' ? 'text-red-600 dark:text-red-400 bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700' : 'text-gray-400 dark:text-gray-600 hover:text-gray-600' ?>">Hybrid</button>
                                <button type="button" onclick="setSearchMode('semantic')" class="mode-btn px-3 py-1.5 text-[10px] font-black uppercase transition-all duration-200 rounded-lg <?= $mode === 'semantic' ? 'text-red-600 dark:text-red-400 bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700' : 'text-gray-400 dark:text-gray-600 hover:text-gray-600' ?>">Semantic</button>
                            </div>
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white w-12 h-12 md:w-14 md:h-14 rounded-xl md:rounded-2xl flex items-center justify-center shadow-lg shadow-red-600/30 transition-all active:scale-95 shrink-0"><i class="bi bi-arrow-right text-xl md:text-2xl"></i></button>
                        </form>
                    </div>
                    <!-- Suggestions Dropdown -->
                    <div id="suggestions-box" class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden z-50 hidden transition-all duration-200 opacity-0 transform translate-y-2">
                        <div id="suggestions-content" class="max-h-80 overflow-y-auto p-2"></div>
                    </div>
                </div>

                <!-- Results Meta -->
                <div id="search-meta" class="flex flex-col sm:flex-row sm:items-center justify-between px-2 gap-4 animate-fade-in-up animation-delay-100">
                    <div class="flex flex-wrap items-center gap-3 md:gap-4">
                        <span class="text-xs md:text-sm text-gray-500 dark:text-gray-400 font-medium">Found <span class="text-gray-900 dark:text-white font-black"><?= number_format($total) ?></span> matches <?php if($query): ?> for "<span class="text-red-600 italic font-bold"><?= e($query) ?></span>"<?php endif; ?></span>
                        <div class="flex items-center gap-2 bg-white dark:bg-gray-800 px-3 md:px-4 py-1.5 rounded-full border border-gray-200 dark:border-gray-700 text-[9px] md:text-[10px] font-black text-gray-500 dark:text-gray-400 shadow-sm uppercase tracking-widest"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span><?= ucfirst($mode) ?> Engine</div>
                    </div>
                    <div class="flex items-center gap-2 self-end sm:self-auto">
                        <button type="button" onclick="setView('grid')" id="view-grid" class="w-8 h-8 md:w-9 md:h-9 flex items-center justify-center transition-all bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:text-red-600 rounded-lg shadow-sm"><i class="bi bi-grid-fill"></i></button>
                        <button type="button" onclick="setView('list')" id="view-list" class="w-8 h-8 md:w-9 md:h-9 flex items-center justify-center transition-all bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-900/50 text-red-600 dark:text-red-400 rounded-lg shadow-sm"><i class="bi bi-list-task"></i></button>
                    </div>
                </div>

                <!-- Results List -->
                <div id="results-list" class="space-y-4">
                    <?php if (empty($results)): ?>
                    <div class="bg-white dark:bg-gray-800 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-3xl p-16 md:p-24 text-center shadow-sm animate-fade-in-up animation-delay-200">
                        <div class="w-24 h-24 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-300 dark:text-gray-600 shadow-inner"><i class="bi bi-search text-5xl"></i></div>
                        <h3 class="text-2xl font-black text-gray-800 dark:text-white mb-2">No documents found</h3>
                        <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto font-medium">Try adjusting your filters or use more specific keywords like "Ordinance 2024".</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($results as $i => $doc): $dc = $i < 10 ? 'animation-delay-'.(($i+2)*100) : ''; ?>
                    <div class="result-card group bg-white dark:bg-gray-800 hover:bg-white dark:hover:bg-gray-800/50 border border-gray-200 dark:border-gray-700 hover:border-red-200 dark:hover:border-red-900/50 rounded-2xl p-5 md:p-7 transition-all duration-300 shadow-sm hover:shadow-xl hover:-translate-y-1 animate-fade-in-up <?= $dc ?>">
                        <div class="result-card-inner flex flex-col md:flex-row gap-6">
                            <div class="w-16 h-16 shrink-0 rounded-2xl bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-700 flex items-center justify-center text-3xl group-hover:scale-110 group-hover:bg-red-50 dark:group-hover:bg-red-900/20 group-hover:border-red-100 dark:group-hover:border-red-900 transition-all duration-300"><i class="bi <?= getPublicTypeIcon($doc['document_type']) ?>"></i></div>
                            <div class="result-card-body flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-3">
                                    <span class="px-3 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest <?= getPublicStatusBadge($doc['status']) ?>"><?= e($doc['status']) ?></span>
                                    <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest bg-gray-50 dark:bg-gray-950 px-2 py-1 rounded-lg border border-gray-100 dark:border-gray-700">REF: <?= e($doc['reference_number'] ?? 'N/A') ?></span>
                                    <?php if(isset($doc['relevance_score'])): ?>
                                    <div class="relevance-bar ml-auto flex items-center gap-2 bg-red-50/50 dark:bg-red-900/10 px-3 py-1.5 rounded-xl border border-red-100 dark:border-red-900/30"><div class="text-[9px] font-black uppercase text-red-600 dark:text-red-400 tracking-tighter">AI Relevance</div><div class="h-1.5 w-14 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-red-500 shadow-sm shadow-red-500/50" style="width:<?= $doc['relevance_score'] * 100 ?>%"></div></div></div>
                                    <?php endif; ?>
                                </div>
                                <h3 class="text-xl font-black text-gray-800 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors line-clamp-1 mb-2"><?= e($doc['title']) ?></h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm line-clamp-2 mb-6 leading-relaxed font-medium"><?= e($doc['description'] ?? 'No description available.') ?></p>
                                <div class="result-card-footer flex flex-wrap items-center gap-y-4 gap-x-6 border-t border-gray-50 dark:border-gray-700 pt-5">
                                    <div class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500"><i class="bi bi-calendar-event text-red-500 text-sm"></i><?= date('M d, Y', strtotime($doc['created_at'])) ?></div>
                                    <div class="flex items-center gap-2 text-xs font-bold text-gray-400 dark:text-gray-500"><i class="bi bi-person-circle text-gray-300 dark:text-gray-600 text-sm"></i><?= e($doc['uploaded_by_name'] ?? 'System Admin') ?></div>
                                    <div class="flex items-center gap-1.5"><?php $tags = explode(',', $doc['tags'] ?? ''); foreach(array_slice($tags,0,3) as $tag): if(empty(trim($tag))) continue; ?><span class="px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-950 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 border border-gray-100 dark:border-gray-700 hover:border-red-200 hover:text-red-600 transition-all cursor-pointer">#<?= e(trim($tag)) ?></span><?php endforeach; ?></div>
                                    <div class="card-actions ml-auto flex items-center gap-3">
                                        <button type="button" onclick="previewDocument(<?= $doc['id'] ?>)" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-900 text-white text-[9px] font-black uppercase tracking-widest transition-all transform active:scale-95 shadow-lg shadow-gray-200 group/btn" title="Preview"><i class="bi bi-eye mr-1 group-hover/btn:scale-125 transition-transform"></i> Preview</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div id="pagination" class="flex items-center justify-center gap-2 pt-8">
                        <?php if ($page > 1): ?><button type="button" onclick="changePage(<?= $page-1 ?>)" class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:border-red-600 hover:text-red-600 transition-all shadow-sm"><i class="bi bi-chevron-left"></i></button><?php endif; ?>
                        <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?><button type="button" onclick="changePage(<?= $i ?>)" class="w-10 h-10 rounded-xl font-bold text-sm transition-all shadow-sm <?= $i===$page?'bg-red-600 text-white border-red-600':'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:border-red-600 hover:text-red-600' ?>"><?= $i ?></button><?php endfor; ?>
                        <?php if ($page < $totalPages): ?><button type="button" onclick="changePage(<?= $page+1 ?>)" class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:border-red-600 hover:text-red-600 transition-all shadow-sm"><i class="bi bi-chevron-right"></i></button><?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- end max-w-7xl -->

    <!-- Footer -->
    <footer class="border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center">
            <p class="text-xs text-gray-400">&copy; <?= date('Y') ?> City Government of Valenzuela &mdash; Legislative Records Management System</p>
            <p class="text-xs text-gray-400 mt-2">Need full access? <a href="<?= LOGIN_URL ?>" class="text-red-600 font-bold hover:underline">Sign in</a> or contact the administrator.</p>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="no-ripple fixed bottom-24 right-6 md:bottom-24 md:right-6 z-[40] w-12 h-12 md:w-[46px] md:h-[46px] bg-red-600 text-white rounded-full border-3 border-white cursor-pointer shadow-lg shadow-red-600/50 flex items-center justify-center transition-all duration-300 hover:bg-red-700 hover:scale-110 active:scale-95 hidden"
            title="Back to top"
            aria-label="Scroll to top">
        <i class="bi bi-arrow-up text-xl md:text-base leading-none pointer-events-none"></i>
    </button>
</main>

<!-- Preview Modal (identical to admin but without Download/Detailed View buttons) -->
<div id="preview-modal" class="fixed inset-0 z-[60] hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-stretch justify-center min-h-screen sm:items-center sm:p-4">
        <div id="preview-overlay" class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity opacity-0 pointer-events-none"></div>
        <div id="preview-content" class="relative bg-white dark:bg-gray-800 rounded-t-3xl sm:rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full sm:max-w-4xl sm:h-[85vh] sm:max-h-[85vh] opacity-0 translate-y-full sm:translate-y-0 sm:scale-95 flex flex-col duration-300 border border-gray-200 dark:border-gray-800" style="max-height: 100dvh;">
            <div class="sm:hidden w-full flex justify-center pt-2.5 pb-1 bg-gradient-to-r from-red-600 to-red-700"><div class="w-10 h-1.5 bg-white/30 rounded-full"></div></div>
            <div class="bg-gradient-to-r from-red-600 to-red-800 px-4 py-4 md:px-8 md:py-6 flex items-center justify-between text-white border-b border-white/10">
                <div class="flex items-center gap-3 md:gap-4 min-w-0"><div class="w-10 h-10 md:w-12 md:h-12 rounded-xl md:rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-xl md:text-2xl shrink-0"><i id="modal-icon" class="bi bi-file-earmark-text"></i></div><div class="min-w-0"><h3 class="text-lg md:text-xl font-black leading-none mb-1">Document Preview</h3><p id="modal-subtitle" class="text-red-100 text-[9px] md:text-[10px] font-bold uppercase tracking-widest opacity-80 truncate">REF: ---</p></div></div>
                <button type="button" onclick="closePreview()" class="w-9 h-9 md:w-10 md:h-10 rounded-full bg-black/20 hover:bg-black/30 flex items-center justify-center transition-all shrink-0"><i class="bi bi-x-lg text-base md:text-lg text-white"></i></button>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 py-5 md:px-8 md:py-8 overflow-y-auto overflow-x-hidden flex-1 min-h-0" style="-webkit-overflow-scrolling: touch; overscroll-behavior: contain;">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 md:gap-8">
                    <div class="md:col-span-2 space-y-6">
                        <div><h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Document Title</h4><h2 id="preview-title" class="text-xl md:text-2xl font-black text-gray-800 dark:text-white leading-tight">---</h2></div>
                        <div><h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Description</h4><p id="preview-desc" class="text-sm md:text-base text-gray-600 dark:text-gray-400 leading-relaxed font-medium">---</p></div>
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl md:rounded-2xl p-3 md:p-4 border border-gray-100 dark:border-gray-700"><h4 class="text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status</h4><div id="preview-status" class="inline-flex mt-1"><span class="px-2.5 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest bg-gray-100 text-gray-600 border-gray-200">---</span></div></div>
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl md:rounded-2xl p-3 md:p-4 border border-gray-100 dark:border-gray-700"><h4 class="text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Date Created</h4><p id="preview-date" class="text-xs md:text-sm font-bold text-gray-800 dark:text-gray-200 mt-1">---</p></div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl md:rounded-3xl p-4 md:p-6 border border-gray-100 dark:border-gray-700">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 md:mb-4">File Information</h4>
                            <div class="space-y-3 md:space-y-4">
                                <div class="flex items-center gap-3"><div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-red-600 shadow-sm border border-gray-100 dark:border-gray-700 shrink-0"><i class="bi bi-file-earmark-pdf text-lg md:text-xl"></i></div><div class="min-w-0"><p id="preview-filename" class="text-[11px] font-bold text-gray-800 dark:text-gray-200 truncate">---</p><p id="preview-filesize" class="text-[9px] text-gray-400 font-bold">---</p></div></div>
                                <div class="flex items-center gap-3"><div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-gray-400 shadow-sm border border-gray-100 dark:border-gray-700 shrink-0"><i class="bi bi-person-circle text-base md:text-lg"></i></div><div class="min-w-0"><p id="preview-uploader" class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">---</p><p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Uploaded By</p></div></div>
                            </div>
                            <div id="preview-tags" class="mt-4 md:mt-6 flex flex-wrap gap-2"></div>
                        </div>
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-2xl p-4 md:p-5 pb-6 md:pb-5">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium leading-relaxed"><i class="bi bi-info-circle mr-1.5"></i>Document downloads require a registered account. Please <a href="<?= LOGIN_URL ?>" class="font-bold underline">sign in</a> or contact the administrator for full access.</p>
                        </div>
                    </div>
                </div>
                <!-- Document Analysis Section -->
                <div id="preview-analysis" class="mt-6"></div>
            </div>
            <!-- Mobile Sticky Footer: Close -->
            <div class="sm:hidden flex items-center gap-3 px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex-shrink-0 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                <button type="button" onclick="closePreview()" class="flex-1 py-3 bg-gray-900 dark:bg-black text-white font-black uppercase tracking-widest text-[11px] rounded-xl flex items-center justify-center gap-2 active:scale-95 transition-all">
                    <i class="bi bi-x-lg text-base"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE = '<?= BASE_URL ?>';
const searchInput = document.getElementById('search-input');
const searchModeInput = document.getElementById('search-mode');
const suggestionsBox = document.getElementById('suggestions-box');
const suggestionsContent = document.getElementById('suggestions-content');
const filterForm = document.getElementById('filter-form');
const mainForm = document.getElementById('search-main-form');
const resultsList = document.getElementById('results-list');
const searchMeta = document.getElementById('search-meta');
let queryTimer, currentView = localStorage.getItem('publicSearchView') || 'list';

function toggleDarkMode(){document.documentElement.classList.toggle('dark');const d=document.documentElement.classList.contains('dark');localStorage.setItem('theme',d?'dark':'light');const ic=document.getElementById('darkModeIcon');if(ic)ic.className=d?'bi bi-sun-fill text-sm':'bi bi-moon-fill text-sm';}
(function(){const d=localStorage.getItem('theme')==='dark';const ic=document.getElementById('darkModeIcon');if(ic)ic.className=d?'bi bi-sun-fill text-sm':'bi bi-moon-fill text-sm';})();

function toggleMobileFilters(){const a=document.getElementById('filters-sidebar'),c=document.getElementById('filter-chevron');if(a.classList.contains('hidden')){a.classList.remove('hidden');a.classList.add('block','animate-fade-in-up');c.style.transform='rotate(180deg)';}else{a.classList.add('hidden');a.classList.remove('block','animate-fade-in-up');c.style.transform='rotate(0deg)';}}

function togglePortalMobileMenu(){const m=document.getElementById('portal-mobile-menu'),ic=document.getElementById('portal-menu-icon');if(!m)return;if(m.classList.contains('hidden')){m.classList.remove('hidden');ic.className='bi bi-x-lg text-2xl';}else{m.classList.add('hidden');ic.className='bi bi-list text-2xl';}}

function setView(v){currentView=v;localStorage.setItem('publicSearchView',v);const g=document.getElementById('view-grid'),l=document.getElementById('view-list');if(v==='grid'){resultsList.classList.remove('space-y-4');resultsList.classList.add('grid','grid-cols-1','md:grid-cols-2','xl:grid-cols-3','gap-4','items-stretch');g.classList.add('bg-red-50','border-red-200','text-red-600');g.classList.remove('bg-white','border-gray-200','text-gray-400');l.classList.remove('bg-red-50','border-red-200','text-red-600');l.classList.add('bg-white','border-gray-200','text-gray-400');resultsList.querySelectorAll('.result-card').forEach(card=>{card.classList.add('h-full');});resultsList.querySelectorAll('.result-card-inner').forEach(i=>{i.classList.remove('md:flex-row');i.classList.add('flex-col','h-full');});resultsList.querySelectorAll('.result-card-body').forEach(body=>{body.classList.add('flex','flex-col','h-full');});resultsList.querySelectorAll('.result-card-footer').forEach(footer=>{footer.classList.add('mt-auto');});resultsList.querySelectorAll('.card-actions').forEach(a=>{a.classList.remove('ml-auto');a.classList.add('w-full','justify-between','pt-2');});resultsList.querySelectorAll('.relevance-bar').forEach(b=>{b.classList.remove('ml-auto');b.classList.add('mb-2');});}else{resultsList.classList.add('space-y-4');resultsList.classList.remove('grid','grid-cols-1','md:grid-cols-2','xl:grid-cols-3','gap-4','items-stretch');l.classList.add('bg-red-50','border-red-200','text-red-600');l.classList.remove('bg-white','border-gray-200','text-gray-400');g.classList.remove('bg-red-50','border-red-200','text-red-600');g.classList.add('bg-white','border-gray-200','text-gray-400');resultsList.querySelectorAll('.result-card').forEach(card=>{card.classList.remove('h-full');});resultsList.querySelectorAll('.result-card-inner').forEach(i=>{i.classList.add('md:flex-row');i.classList.remove('flex-col','h-full');});resultsList.querySelectorAll('.result-card-body').forEach(body=>{body.classList.remove('flex','flex-col','h-full');});resultsList.querySelectorAll('.result-card-footer').forEach(footer=>{footer.classList.remove('mt-auto');});resultsList.querySelectorAll('.card-actions').forEach(a=>{a.classList.add('ml-auto');a.classList.remove('w-full','justify-between','pt-2');});resultsList.querySelectorAll('.relevance-bar').forEach(b=>{b.classList.add('ml-auto');b.classList.remove('mb-2');});}}

function setSearchMode(m){searchModeInput.value=m;document.querySelectorAll('.mode-btn').forEach(b=>{if(b.textContent.trim().toLowerCase()===m){b.classList.add('text-red-600','bg-white','shadow-sm','border','border-gray-200');b.classList.remove('text-gray-400','hover:text-gray-600');}else{b.classList.remove('text-red-600','bg-white','shadow-sm','border','border-gray-200');b.classList.add('text-gray-400','hover:text-gray-600');}});updateResults();}

function changePage(p){const u=new URL(window.location.href);u.searchParams.set('page',p);window.history.pushState({},'',u);updateResults(p);window.scrollTo({top:resultsList.offsetTop-100,behavior:'smooth'});}

const updateResults=async(page=1)=>{resultsList.classList.add('opacity-50','pointer-events-none');const fd=new FormData(filterForm),md=new FormData(mainForm),p=new URLSearchParams(fd);for(const[k,v]of md.entries())p.set(k,v);p.set('page',page);const url=`${window.location.pathname}?${p.toString()}`;try{const r=await fetch(url);const html=await r.text();const parser=new DOMParser();const doc=parser.parseFromString(html,'text/html');const nr=doc.getElementById('results-list'),nm=doc.getElementById('search-meta');if(nr)resultsList.innerHTML=nr.innerHTML;if(nm)searchMeta.innerHTML=nm.innerHTML;setView(currentView);window.history.pushState({},'',url);}catch(e){console.error('Search failed:',e);}finally{resultsList.classList.remove('opacity-50','pointer-events-none');}};

function formatFileSize(b){if(!b||b===0)return'0 B';const k=1024,s=['B','KB','MB','GB'];const i=Math.floor(Math.log(b)/Math.log(k));return parseFloat((b/Math.pow(k,i)).toFixed(1))+' '+s[i];}

const _esc=v=>String(v??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');

const showSuggestions=async(q)=>{if(q.length<2){hideSuggestions();return;}try{const r=await fetch(`?action=suggestions&q=${encodeURIComponent(q)}`);const suggestions=await r.json();if(suggestions.length>0){suggestionsContent.innerHTML=suggestions.map(s=>{const t=_esc(s.title),sub=_esc(s.reference_number||s.document_type);return`<div class="p-4 hover:bg-red-50 dark:hover:bg-red-900/20 cursor-pointer border-b border-gray-50 dark:border-gray-700 flex items-center justify-between group" onclick="selectSuggestion(${JSON.stringify(s.title)})"><div class="flex items-center gap-4"><div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-gray-400 group-hover:bg-white dark:group-hover:bg-gray-800 group-hover:text-red-600 transition-all shadow-sm"><i class="bi bi-clock-history"></i></div><div><div class="text-sm font-bold text-gray-800 dark:text-white group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors">${t}</div><div class="text-[10px] text-gray-400 dark:text-gray-500 font-black uppercase tracking-widest">${sub}</div></div></div><i class="bi bi-arrow-up-left text-gray-300 group-hover:text-red-400 transition-all opacity-0 group-hover:opacity-100"></i></div>`;}).join('');suggestionsBox.classList.remove('hidden');setTimeout(()=>{suggestionsBox.classList.remove('opacity-0','translate-y-2');suggestionsBox.classList.add('opacity-100','translate-y-0');},10);}else{hideSuggestions();}}catch(e){console.error('Suggestions failed:',e);}};

const hideSuggestions=()=>{suggestionsBox.classList.add('opacity-0','translate-y-2');suggestionsBox.classList.remove('opacity-100','translate-y-0');setTimeout(()=>{suggestionsBox.classList.add('hidden');},200);};

window.selectSuggestion=(title)=>{searchInput.value=title;hideSuggestions();updateResults();};

function escapeHtml(str){const div=document.createElement('div');div.textContent=str;return div.innerHTML;}

function toggleDocPreview(btn){
    const container=document.getElementById('doc-preview-container');
    if(!container)return;
    const icon=btn.querySelector('i'),label=btn.querySelector('span');
    if(container.classList.contains('max-h-72')){
        container.classList.remove('max-h-72');container.classList.add('max-h-[2000px]');
        if(icon){icon.classList.remove('bi-chevron-down');icon.classList.add('bi-chevron-up');}
        if(label)label.textContent='Collapse';
    }else{
        container.classList.remove('max-h-[2000px]');container.classList.add('max-h-72');
        if(icon){icon.classList.remove('bi-chevron-up');icon.classList.add('bi-chevron-down');}
        if(label)label.textContent='Expand';
    }
}

function formatDocumentText(rawText){
    if(!rawText)return '<p class="text-gray-400 italic">No content available.</p>';
    let text=rawText.replace(/\r\n/g,'\n').replace(/\r/g,'\n');
    text=text.replace(/^\[OCR\]\s*\n?/i,'');
    text=text.replace(/\[Page OCR failed:.*?\]/g,'');
    text=text.replace(/--- Page Break ---/g,'\n\n');
    const blocks=text.split(/\n{2,}/);
    let html='',inList=false,listType='',listItems=[];
    const closeList=()=>{if(inList){const tag=listType==='ol'?'ol':'ul';const cls=listType==='ol'?'list-decimal list-inside space-y-1.5 my-3 pl-2':'list-disc list-inside space-y-1.5 my-3 pl-2';html+=`<${tag} class="${cls}">${listItems.join('')}</${tag}>`;inList=false;listType='';listItems=[];}};
    for(let block of blocks){
        block=block.trim();if(!block)continue;
        const numberedMatch=block.match(/^(\d+)\.\s*(.+)/);
        const bulletMatch=block.match(/^[•·\-\*]\s*(.+)/);
        const romanNumeralMatch=block.match(/^([IVXLCDM]+)\.\s*(.+)/i);
        const isShortAllCaps=block.length<80&&block===block.toUpperCase()&&/[A-Z]/.test(block)&&!block.endsWith('.')&&!numberedMatch;
        const letteredMatch=block.match(/^([A-Z])\.\s*(.+)/);
        const isHeading=isShortAllCaps||(block.length<100&&block===block.toUpperCase()&&/[A-Z]/.test(block))||/^(DETAILED\s|AN\s|ORDINANCE|RESOLUTION|REPUBLIC\s|CITY\s|MUNICIPAL|PROVINCIAL|BARANGAY|OFFICE\s|DEPARTMENT|COLLEGE|UNIVERSITY|SCHOOL|SECTION|ARTICLE|CHAPTER)/i.test(block)&&block.length<120;
        if(numberedMatch){
            if(inList&&listType!=='ol')closeList();
            if(!inList){inList=true;listType='ol';}
            listItems.push(`<li class="text-gray-800 dark:text-gray-200 leading-relaxed">${escapeHtml(numberedMatch[2])}</li>`);
        }else if(bulletMatch){
            if(inList&&listType!=='ul')closeList();
            if(!inList){inList=true;listType='ul';}
            listItems.push(`<li class="text-gray-800 dark:text-gray-200 leading-relaxed">${escapeHtml(bulletMatch[1])}</li>`);
        }else if(letteredMatch&&block.length<200){
            if(inList&&listType!=='ol')closeList();
            if(!inList){inList=true;listType='ol';}
            listItems.push(`<li class="text-gray-800 dark:text-gray-200 leading-relaxed"><strong>${letteredMatch[1]}.</strong> ${escapeHtml(letteredMatch[2])}</li>`);
        }else{
            closeList();
            if(isHeading){
                html+=`<h2 class="text-center font-bold text-base text-gray-900 dark:text-gray-100 my-3 uppercase tracking-wide">${escapeHtml(block)}</h2>`;
            }else if(romanNumeralMatch&&block.length<200){
                html+=`<h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 mt-4 mb-2">${escapeHtml(block)}</h3>`;
            }else if(block.length<100&&/^(Section|Article|Chapter|Title)\s/i.test(block)){
                html+=`<h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 mt-4 mb-2">${escapeHtml(block)}</h3>`;
            }else{
                const lines=block.split('\n');
                if(lines.length===1){
                    html+=`<p class="text-gray-800 dark:text-gray-200 leading-relaxed mb-3 text-justify">${escapeHtml(block)}</p>`;
                }else{
                    const isIndented=lines.every(l=>/^\s+/.test(l)||!l.trim());
                    if(isIndented&&lines.length>2){
                        html+=`<div class="pl-4 border-l-2 border-gray-200 dark:border-gray-700 my-3 space-y-1">`;
                        for(const line of lines){if(line.trim())html+=`<p class="text-gray-700 dark:text-gray-300 leading-relaxed text-[12px]">${escapeHtml(line.trim())}</p>`;}
                        html+=`</div>`;
                    }else{
                        html+=`<p class="text-gray-800 dark:text-gray-200 leading-relaxed mb-3 text-justify">${lines.map(l=>escapeHtml(l.trim())).join('<br>')}</p>`;
                    }
                }
            }
        }
    }
    closeList();
    return html;
}

function showSignInPrompt(){
    const modal=document.getElementById('signin-prompt-modal');
    const content=document.getElementById('signin-prompt-content');
    if(modal){
        modal.classList.remove('hidden');
        document.body.style.overflow='hidden';
        const btt=document.getElementById('back-to-top');if(btt)btt.classList.add('hidden');
        setTimeout(()=>{if(content){content.classList.remove('translate-y-full','sm:scale-95','opacity-0');content.classList.add('translate-y-0','sm:scale-100','opacity-100');}},10);
    }
}

function closeSignInPrompt(){
    const modal=document.getElementById('signin-prompt-modal');
    const content=document.getElementById('signin-prompt-content');
    if(content){content.classList.add('translate-y-full','sm:scale-95','opacity-0');content.classList.remove('translate-y-0','sm:scale-100','opacity-100');}
    if(modal){
        setTimeout(()=>{modal.classList.add('hidden');document.body.style.overflow='';const btt=document.getElementById('back-to-top');if(btt)btt.classList.remove('hidden');},300);
    }
}

async function previewDocument(id){
    const modal=document.getElementById('preview-modal'),overlay=document.getElementById('preview-overlay'),content=document.getElementById('preview-content');
    modal.classList.remove('hidden');document.body.style.overflow='hidden';
    const btt0=document.getElementById('back-to-top');if(btt0)btt0.classList.add('hidden');
    setTimeout(()=>{overlay.classList.remove('opacity-0','pointer-events-none');overlay.classList.add('opacity-100','pointer-events-auto');content.classList.remove('translate-y-full','sm:scale-95','opacity-0');content.classList.add('translate-y-0','sm:scale-100','opacity-100');},10);
    try{
        const r=await fetch(`${BASE}/modules/public-portal/api/public_document.php?id=${id}`);
        const d=await r.json();
        if(d.success){
            const doc=d.document;
            document.getElementById('modal-subtitle').textContent=`REF: ${doc.reference_number||'N/A'}`;
            document.getElementById('preview-title').textContent=doc.title;
            document.getElementById('preview-desc').textContent=doc.description||'No description available.';
            document.getElementById('preview-date').textContent=new Date(doc.created_at).toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});
            document.getElementById('preview-filename').textContent=doc.file_name;
            document.getElementById('preview-filesize').textContent=formatFileSize(doc.file_size||0);
            document.getElementById('preview-uploader').textContent=doc.full_name||'System Admin';
            const sc={'approved':'bg-emerald-100 text-emerald-700 border-emerald-200','archived':'bg-gray-100 text-gray-700 border-gray-200'};
            document.getElementById('preview-status').innerHTML=`<span class="px-3 py-1 rounded-lg border text-[10px] font-black uppercase tracking-widest ${sc[doc.status.toLowerCase()]||'bg-gray-100 text-gray-700 border-gray-200'}">${_esc(doc.status)}</span>`;
            const ic={'ordinance':'bi-journal-text','resolution':'bi-file-earmark-check','session':'bi-people','agenda':'bi-list-ul','committee':'bi-shield-check','research':'bi-search'};
            document.getElementById('modal-icon').className='bi '+(ic[(doc.document_type||'').toLowerCase()]||'bi-file-earmark-text');
            const tc=document.getElementById('preview-tags');
            tc.innerHTML='';
            if(doc.tags){doc.tags.split(',').forEach(t=>{t=t.trim();if(t){const s=document.createElement('span');s.className='px-2 py-1 rounded-lg bg-white dark:bg-gray-800 text-[9px] font-black uppercase tracking-widest text-gray-400 border border-gray-100 dark:border-gray-700';s.textContent='#'+t;tc.appendChild(s);}});}

            // Document Analysis Section
            const analysisContainer=document.getElementById('preview-analysis');
            if(analysisContainer){
                const ocrStatus=doc.ocr_status||'pending';
                const ocrInfo={
                    'completed':{badge:'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50',icon:'patch-check-fill',label:'Digitally Extracted'},
                    'pending':{badge:'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 border-amber-200 dark:border-amber-800/50',icon:'hourglass-split',label:'Extraction Scheduled'},
                    'processing':{badge:'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 border-blue-200 dark:border-blue-800/50',icon:'arrow-repeat',label:'Extracting...'},
                    'failed':{badge:'bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400 border-rose-200 dark:border-rose-800/50',icon:'exclamation-triangle-fill',label:'Extraction Unavailable'},
                    'skipped':{badge:'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-200 dark:border-slate-700',icon:'dash-circle-fill',label:'Extraction Skipped'}
                };
                const info=ocrInfo[ocrStatus]||ocrInfo['pending'];
                const keyPoints=doc.key_points?doc.key_points.split('\n').filter(p=>p.trim()):[];
                const extractedText=doc.extracted_text||'';
                const processedDate=doc.ocr_processed_at?new Date(doc.ocr_processed_at).toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'}):null;
                const wordCount=extractedText?extractedText.trim().split(/\s+/).length:0;

                analysisContainer.innerHTML=`
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
                            ${processedDate?`
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-gray-400 dark:text-gray-500">
                                    <span class="flex items-center"><i class="bi bi-calendar-check mr-1.5"></i>Extracted on ${processedDate}</span>
                                    ${extractedText?`<span class="flex items-center"><i class="bi bi-file-earmark-text mr-1.5"></i>${extractedText.length.toLocaleString()} characters &middot; ${wordCount.toLocaleString()} words</span>`:''}
                                </div>
                            `:''}
                            ${keyPoints.length>0?`
                                <div>
                                    <div class="flex items-center mb-3">
                                        <i class="bi bi-card-text text-indigo-600 dark:text-indigo-400 mr-2"></i>
                                        <h4 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Summary of Key Points</h4>
                                    </div>
                                    <ol class="space-y-2.5 border-l-2 border-indigo-100 dark:border-indigo-900/50 pl-5">
                                        ${keyPoints.map((p,i)=>`
                                            <li class="relative">
                                                <span class="absolute -left-[27px] top-0 w-5 h-5 rounded-full bg-indigo-600 text-white text-[10px] font-bold flex items-center justify-center">${i+1}</span>
                                                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed pt-0.5">${escapeHtml(p.replace(/^[•·\-\*]\s*/,''))}</p>
                                            </li>
                                        `).join('')}
                                    </ol>
                                </div>
                            `:''}
                            ${extractedText?`
                                <div class="flex items-center justify-between pt-2">
                                    <div class="flex items-center">
                                        <i class="bi bi-file-earmark-richtext text-slate-600 dark:text-slate-400 mr-2"></i>
                                        <h4 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Original File</h4>
                                    </div>
                                    <button type="button" onclick="showSignInPrompt()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold uppercase tracking-wider flex items-center gap-1.5 transition-all active:scale-95">
                                        <i class="bi bi-eye"></i><span>Preview File</span>
                                    </button>
                                </div>
                            `:!keyPoints.length?`
                                <div class="text-center py-10 bg-gray-50 dark:bg-gray-800/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                    <i class="bi bi-file-earmark-x text-3xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
                                    <p class="text-sm text-gray-400 dark:text-gray-500 font-medium">${ocrStatus==='pending'?'Document content extraction is scheduled and will be available once processing is complete.':ocrStatus==='failed'?'Content extraction was unsuccessful. The file may be corrupted or in an unsupported format.':'No readable text content was found in this document.'}</p>
                                </div>
                            `:''}
                        </div>
                    </section>
                `;
            }
        }else{closePreview();}
    }catch(e){console.error(e);closePreview();}
}

function closePreview(){const o=document.getElementById('preview-overlay'),c=document.getElementById('preview-content');o.classList.add('opacity-0','pointer-events-none');o.classList.remove('opacity-100','pointer-events-auto');c.classList.add('translate-y-full','sm:scale-95','opacity-0');c.classList.remove('translate-y-0','sm:scale-100','opacity-100');setTimeout(()=>{document.getElementById('preview-modal').classList.add('hidden');document.body.style.overflow='';const ac=document.getElementById('preview-analysis');if(ac)ac.innerHTML='';const btt=document.getElementById('back-to-top');if(btt)btt.classList.remove('hidden');},300);}

document.addEventListener('keydown',e=>{if(e.key==='Escape'){closePreview();const sp=document.getElementById('signin-prompt-modal');if(sp&&!sp.classList.contains('hidden'))closeSignInPrompt();}});
document.getElementById('preview-overlay')?.addEventListener('click',closePreview);

document.addEventListener('DOMContentLoaded',function(){setView(currentView);searchInput?.addEventListener('input',e=>{clearTimeout(queryTimer);queryTimer=setTimeout(()=>showSuggestions(e.target.value),300);});document.addEventListener('click',e=>{if(!suggestionsBox?.contains(e.target)&&e.target!==searchInput)hideSuggestions();});if(filterForm){filterForm.querySelectorAll('input[type="radio"],input[type="date"],select').forEach(el=>{el.addEventListener('change',()=>updateResults());});filterForm.addEventListener('submit',e=>{e.preventDefault();updateResults();});}if(mainForm){mainForm.addEventListener('submit',e=>{e.preventDefault();updateResults();});}

// Back to Top Button
(function(){const btn=document.getElementById('back-to-top');if(!btn)return;function checkScroll(){if(document.body.style.overflow==='hidden'){btn.classList.add('hidden');btn.classList.remove('flex');return;}let scrolled=false;if(window.pageYOffset>200||document.documentElement.scrollTop>200)scrolled=true;const main=document.querySelector('main');if(main&&main.scrollTop>200)scrolled=true;document.querySelectorAll('.overflow-y-auto').forEach(el=>{if(el.scrollTop>200)scrolled=true;});if(scrolled){btn.classList.remove('hidden');btn.classList.add('flex');}else{btn.classList.add('hidden');btn.classList.remove('flex');}}function scrollToTop(){window.scrollTo({top:0,behavior:'smooth'});const main=document.querySelector('main');if(main)main.scrollTo({top:0,behavior:'smooth'});document.querySelectorAll('.overflow-y-auto').forEach(el=>el.scrollTo({top:0,behavior:'smooth'}));}btn.onclick=scrollToTop;window.addEventListener('scroll',checkScroll,{passive:true});const main=document.querySelector('main');if(main)main.addEventListener('scroll',checkScroll,{passive:true});document.querySelectorAll('.overflow-y-auto').forEach(el=>el.addEventListener('scroll',checkScroll,{passive:true}));checkScroll();var bodyObs=new MutationObserver(function(muts){muts.forEach(function(m){if(m.attributeName==='style'){if(document.body.style.overflow==='hidden'){btn.classList.add('hidden');btn.classList.remove('flex');}else{checkScroll();}}});});bodyObs.observe(document.body,{attributes:true,attributeFilter:['style']});var modalObs=new MutationObserver(function(muts){muts.forEach(function(m){if(m.attributeName==='class'){var el=m.target;if(!el.classList.contains('hidden')){btn.classList.add('hidden');btn.classList.remove('flex');}else{checkScroll();}}});});document.querySelectorAll('[id*="modal"]').forEach(function(el){modalObs.observe(el,{attributes:true,attributeFilter:['class']});});document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('[id*="modal"]').forEach(function(el){modalObs.observe(el,{attributes:true,attributeFilter:['class']});});});})();
});
</script>

<!-- Sign-In Prompt Modal -->
<div id="signin-prompt-modal" class="hidden fixed inset-0 z-[100003] bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center sm:p-4" onclick="if(event.target===this) closeSignInPrompt()">
    <div id="signin-prompt-content" class="bg-white dark:bg-gray-800 rounded-t-3xl sm:rounded-3xl shadow-2xl max-w-md w-full max-h-[92vh] overflow-hidden transform transition-all duration-300 translate-y-full sm:translate-y-0 sm:scale-95 opacity-0 sm:opacity-100 flex flex-col">
        <div class="sm:hidden w-full flex justify-center pt-3 pb-1 bg-white dark:bg-gray-800"><div class="w-12 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div></div>
        <div class="bg-gradient-to-r from-red-600 to-red-800 px-6 py-5 text-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center">
                    <i class="bi bi-lock-fill text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black">Account Required</h3>
                    <p class="text-red-100 text-[10px] font-bold uppercase tracking-widest">Sign in to preview files</p>
                </div>
            </div>
        </div>
        <div class="p-6 md:p-8 text-center">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center mx-auto mb-5">
                <i class="bi bi-file-earmark-play text-3xl text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <h4 class="text-base font-black text-gray-800 dark:text-white mb-2">Preview requires an account</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-6">To preview the original document file, you need to sign in with a registered account. A <strong class="text-gray-700 dark:text-gray-300">viewer</strong> account is sufficient for full read access.</p>
            <div class="flex flex-col gap-3">
                <a href="<?= LOGIN_URL ?>" class="w-full py-3.5 bg-red-600 hover:bg-red-700 text-white font-black uppercase tracking-widest text-[11px] rounded-2xl shadow-lg shadow-red-600/20 transition-all flex items-center justify-center gap-2 active:scale-95">
                    <i class="bi bi-box-arrow-in-right text-base"></i> Sign In
                </a>
                <button type="button" onclick="closeSignInPrompt()" class="w-full py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-widest text-[10px] rounded-2xl transition-all">
                    Maybe Later
                </button>
            </div>
        </div>
    </div>
</div>

</body>
</html>
