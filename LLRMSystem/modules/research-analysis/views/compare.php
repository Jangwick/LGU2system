<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../controllers/ResearchController.php';

$controller = new ResearchController();
$ids = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];
$documents = $controller->getDocumentsForComparison($ids);

$pageTitle = 'Law Comparison Tool';
$currentPage = 'research-analysis';

require_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php require_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php require_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-900 p-6 transition-colors duration-300">
        <div class="max-w-7xl mx-auto">
            <div class="mb-4">
                <a href="index.php" class="inline-flex items-center text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                    <i class="bi bi-arrow-left mr-2"></i> Back to Analysis Dashboard
                </a>
            </div>
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 line-tight">Law Comparison Tool</h1>
                    <p class="text-gray-600 dark:text-gray-400">Analyze differences between ordinances and resolutions side-by-side.</p>
                </div>
                <button type="button" onclick="openSelectModal()" class="bg-red-600 dark:bg-red-700 text-white px-4 py-2 rounded-lg hover:bg-red-700 dark:hover:bg-red-600 transition flex items-center shadow-md">
                    <i class="bi bi-plus-lg mr-2"></i> Add Document to Compare
                </button>
            </div>

            <?php if (empty($documents)): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-16 text-center animate-fade-in">
                    <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-3xl flex items-center justify-center mx-auto mb-6 transform rotate-12 transition-all hover:rotate-0">
                        <i class="bi bi-layout-split text-4xl text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">No documents selected</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-sm mx-auto text-lg leading-relaxed">Select two or more documents to begin side-by-side analysis.</p>
                    <button type="button" onclick="openSelectModal()" class="bg-red-600 dark:bg-red-700 text-white px-10 py-3 rounded-xl font-bold hover:bg-red-700 dark:hover:bg-red-600 transition-all shadow-lg hover:shadow-xl active:scale-95">Select Documents</button>
                </div>
            <?php else: ?>
                <div class="mb-8 flex justify-center">
                    <button type="button" onclick="generateSmartComparison()" id="smart-compare-btn" 
                            class="bg-red-800 dark:bg-red-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg hover:bg-red-700 dark:hover:bg-red-600 transition-all flex items-center group">
                        <i class="bi bi-cpu mr-3 group-hover:animate-spin"></i> Generate Smart Analysis
                    </button>
                </div>

                <div id="analysis-container" class="hidden mb-12 animate-fade-in-up">
                    <div class="bg-white rounded-2xl shadow-xl border-t-8 border-red-800 p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h3 id="analysis-title" class="text-xl font-bold text-gray-900 flex items-center">
                                <i class="bi bi-graph-up-arrow mr-3 text-red-800"></i> Smart Legislative Analysis
                            </h3>
                            <button type="button" onclick="document.getElementById('analysis-container').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div id="analysis-content" class="prose max-w-none text-gray-800 leading-relaxed">
                            <!-- Content will be injected here -->
                        </div>
                    </div>
                </div>

                <div class="flex gap-6 overflow-x-auto pb-6 scrollbar-thin">
                    <?php foreach ($documents as $doc): ?>
                        <div class="min-w-[400px] flex-1">
                            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden flex flex-col h-full">
                                <div class="bg-gray-50 p-4 border-b flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold text-gray-900 line-clamp-2"><?= htmlspecialchars($doc['title']) ?></h4>
                                        <p class="text-xs text-gray-500 font-mono mt-1"><?= e($doc['reference_number']) ?></p>
                                    </div>
                                    <button type="button" onclick="removeDocument(<?= $doc['id'] ?>)" class="text-gray-400 hover:text-red-500">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <div class="p-6 space-y-6 flex-1">
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="block text-gray-500 mb-1 font-medium italic">Type</span>
                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-md font-semibold"><?= e(ucfirst($doc['document_type'])) ?></span>
                                        </div>
                                        <div>
                                            <span class="block text-gray-500 mb-1 font-medium italic">Date</span>
                                            <span class="text-gray-700 font-semibold"><?= date('M d, Y', strtotime($doc['document_date'])) ?></span>
                                        </div>
                                    </div>

                                    <div>
                                        <span class="block text-sm font-medium text-gray-700 mb-2">Description</span>
                                        <div class="text-sm text-gray-600 bg-gray-50 rounded-lg p-4 border leading-relaxed h-48 overflow-y-auto">
                                            <?= nl2br(htmlspecialchars($doc['description'] ?: 'No description provided.')) ?>
                                        </div>
                                    </div>

                                    <div>
                                        <span class="block text-sm font-medium text-gray-700 mb-2">Metadata</span>
                                        <div class="space-y-2">
                                            <div class="flex justify-between text-xs py-1 border-b">
                                                <span class="text-gray-500">Uploaded By</span>
                                                <span class="text-gray-900 font-medium"><?= htmlspecialchars($doc['author']) ?></span>
                                            </div>
                                            <div class="flex justify-between text-xs py-1 border-b">
                                                <span class="text-gray-500">File Format</span>
                                                <span class="text-gray-900 font-medium">
                                                    <?php 
                                                    $type = $doc['file_type'];
                                                    if (strpos($type, 'wordprocessingml') !== false) echo 'DOCX';
                                                    elseif (strpos($type, 'pdf') !== false) echo 'PDF';
                                                    elseif (strpos($type, 'msword') !== false) echo 'DOC';
                                                    else echo strtoupper(explode('/', $type)[1] ?? 'UNKNOWN');
                                                    ?>
                                                </span>
                                            </div>
                                            <div class="flex justify-between text-xs py-1">
                                                <span class="text-gray-500">Status</span>
                                                <span class="text-green-600 font-bold"><?= e(strtoupper($doc['status'])) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 bg-gray-50 border-t mt-auto">
                                    <a href="<?= DOCUMENTS_URL ?>/views/view.php?id=<?= $doc['id'] ?>" target="_blank" class="w-full btn-secondary text-sm flex items-center justify-center">
                                        <i class="bi bi-eye mr-2"></i> View Full Original
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php require_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<!-- Simple Select Modal -->
<div id="selectModal" class="hidden fixed inset-0 bg-black/50 z-50 animate-fade-in backdrop-blur-sm">
    <!-- Desktop: centered card. Mobile: full-height bottom sheet -->
    <div class="absolute inset-0 flex items-end md:items-center md:justify-center">
        <div class="w-full md:max-w-2xl bg-white dark:bg-gray-900 md:rounded-2xl rounded-t-2xl shadow-2xl border-t md:border border-gray-200 dark:border-gray-700 animate-fade-in-up flex flex-col max-h-[92vh] md:max-h-[80vh]">
            <!-- Header -->
            <div class="flex justify-between items-center px-5 pt-5 pb-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center">
                    <i class="bi bi-file-earmark-plus mr-2 text-red-600"></i> Add to Comparison
                </h3>
                <button type="button" onclick="closeSelectModal()" class="hero-toggle-btn w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-all">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>

            <!-- Search -->
            <div class="px-5 py-4 flex-shrink-0">
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="docSearch" placeholder="Search by title or reference number..." 
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm">
                </div>
            </div>

            <!-- Results (scrollable) -->
            <div id="searchResults" class="flex-1 overflow-y-auto px-5 space-y-2 custom-scrollbar min-h-0">
                <p class="text-center text-gray-500 dark:text-gray-400 py-8 italic font-medium text-sm">Type to search for documents...</p>
            </div>

            <!-- Footer -->
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                <button type="button" onclick="closeSelectModal()" class="w-full md:w-auto md:float-right px-6 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 font-bold transition-all text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedIds = <?= json_encode($ids) ?>;

function openSelectModal() {
    document.getElementById('selectModal').classList.remove('hidden');
    document.getElementById('docSearch').focus();
    searchDocs('');
}

function closeSelectModal() {
    document.getElementById('selectModal').classList.add('hidden');
}

// Close on backdrop click
document.getElementById('selectModal').addEventListener('click', function(e) {
    if (e.target === this || e.target.classList.contains('absolute')) {
        closeSelectModal();
    }
});

function searchDocs(query) {
    const resultsContainer = document.getElementById('searchResults');
    resultsContainer.innerHTML = '<div class="text-center py-8"><i class="bi bi-arrow-repeat animate-spin text-2xl text-red-600"></i></div>';
    
    fetch('../api/search_for_compare.php?q=' + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                resultsContainer.innerHTML = '<p class="text-center text-gray-500 py-8">No matching documents found.</p>';
                return;
            }
            resultsContainer.innerHTML = data.map(doc => `
                <div class="flex justify-between items-center p-3 md:p-4 hover:!bg-red-50 dark:hover:!bg-red-950/30 rounded-xl border-b md:border border-gray-100 md:border-gray-200 dark:border-gray-700 hover:!border-red-500 dark:hover:!border-red-600 transition-all duration-200 cursor-pointer group" onclick="addDocument(${doc.id})">
                    <div class="flex-1 min-w-0">
                        <h5 class="font-bold text-gray-900 dark:text-gray-100 group-hover:!text-red-600 dark:group-hover:!text-red-400 transition-colors text-sm md:text-base truncate">${doc.title}</h5>
                        <p class="text-[11px] md:text-xs text-gray-500 dark:text-gray-400 font-mono truncate">${doc.reference_number} | ${doc.document_type} | ${doc.document_date}</p>
                    </div>
                    <i class="bi bi-plus-circle text-red-600 dark:text-red-500 text-lg md:text-xl ml-3 flex-shrink-0 opacity-50 md:opacity-0 group-hover:opacity-100 transition-all"></i>
                </div>
            `).join('');
        })
        .catch(err => {
            resultsContainer.innerHTML = '<p class="text-center text-red-500 py-8">Error loading documents. Please try again.</p>';
            console.error(err);
        });
}

document.getElementById('docSearch').addEventListener('input', (e) => {
    searchDocs(e.target.value);
});

function addDocument(id) {
    if (!selectedIds.includes(String(id))) {
        selectedIds.push(String(id));
        window.location.search = '?ids=' + selectedIds.join(',');
    } else {
        alert('This document is already in the comparison list.');
    }
}

function removeDocument(id) {
    selectedIds = selectedIds.filter(sid => sid !== String(id));
    if (selectedIds.length === 0) {
        window.location.search = '';
    } else {
        window.location.search = '?ids=' + selectedIds.join(',');
    }
}

async function generateSmartComparison() {
    const btn = document.getElementById('smart-compare-btn');
    const container = document.getElementById('analysis-container');
    const content = document.getElementById('analysis-content');
    const title = document.getElementById('analysis-title');
    
    if (selectedIds.length < 2) {
        alert('Please select at least two documents for comparison.');
        return;
    }

    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin mr-3"></i> Analyzing...';
    
    try {
        console.log("Starting Smart Comparison for IDs:", selectedIds);
        const response = await fetch('../api/compare_docs_smart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: selectedIds })
        });
        
        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        console.log("Data received:", data);
        
        container.classList.remove('hidden');
        container.scrollIntoView({ behavior: 'smooth' });
        
        if (data.success) {
            const analysis = data.analysis;
            let html = `
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Insights -->
                    <div class="lg:col-span-2 space-y-8">
                        <div>
                            <h4 class="font-bold text-red-800 mb-4 flex items-center">
                                <i class="bi bi-tags-fill mr-2"></i> Overlapping Topics & Tags
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                ${analysis.shared_tags.length > 0 
                                    ? analysis.shared_tags.map(t => `<span class="px-3 py-1 bg-red-100 text-red-700 rounded-lg text-sm font-medium border border-red-200">${t}</span>`).join('') 
                                    : '<span class="text-gray-400 italic">No significant tag overlaps found.</span>'}
                            </div>
                        </div>

                        <div>
                            <h4 class="font-bold text-red-800 mb-4 flex items-center">
                                <i class="bi bi-key-fill mr-2"></i> Core Legislative Keywords
                            </h4>
                            <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 shadow-inner">
                                <div class="flex flex-wrap gap-3">
                                    ${analysis.shared_keywords.length > 0 
                                        ? analysis.shared_keywords.map(w => `<div class="flex items-center text-gray-700 bg-white px-4 py-2 rounded-lg shadow-sm text-sm border font-medium uppercase tracking-wide">
                                            <i class="bi bi-check-circle-fill text-red-500 mr-2"></i>${w}
                                          </div>`).join('') 
                                        : '<p class="text-gray-400 italic w-full text-center py-4">No recurring technical terms identified across all selected documents.</p>'}
                                </div>
                            </div>
                        </div>

                        <!-- Facts Comparison Table -->
                        <div class="mt-8">
                            <h4 class="font-bold text-red-800 mb-4 flex items-center">
                                <i class="bi bi-list-columns-reverse mr-2"></i> Structural Comparison
                            </h4>
                            <div class="overflow-x-auto rounded-xl border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Document Title</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Word Count</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        ${analysis.document_stats.map(s => `
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${s.title}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <span class="px-2 py-1 bg-gray-100 rounded-md text-xs font-bold uppercase">${s.type}</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">${s.word_count} words</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Stats -->
                    <div class="space-y-6">
                        <div class="bg-red-50 rounded-xl p-6 border border-red-100">
                            <h4 class="font-bold text-red-900 mb-4 flex items-center">
                                <i class="bi bi-bar-chart-fill mr-2"></i> Comparative Stats
                            </h4>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center bg-white p-3 rounded-lg border border-red-100 shadow-sm">
                                    <span class="text-gray-600 text-sm">Timeline Span</span>
                                    <span class="font-bold text-red-700">${analysis.timeline_span}</span>
                                </div>
                                <div class="flex justify-between items-center bg-white p-3 rounded-lg border border-red-100 shadow-sm">
                                    <span class="text-gray-600 text-sm">Total Docs</span>
                                    <span class="font-bold text-red-700">${selectedIds.length}</span>
                                </div>
                                
                                <div class="mt-4 pt-4 border-t border-red-200">
                                    <span class="text-xs font-bold text-red-800 uppercase tracking-wider mb-2 block">Document Volume</span>
                                    ${analysis.document_stats.map(s => `
                                        <div class="mb-2">
                                            <div class="flex justify-between text-xs mb-1">
                                                <span class="truncate pr-4">${s.title.substring(0, 30)}...</span>
                                                <span class="font-mono">${s.word_count} words</span>
                                            </div>
                                            <div class="w-full bg-red-100 h-1 rounded-full overflow-hidden">
                                                <div class="bg-red-600 h-full" style="width: ${Math.min(100, (s.word_count / 1000) * 100)}%"></div>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 p-4 bg-gray-50 rounded-xl border border-gray-200 flex items-start gap-3">
                    <i class="bi bi-shield-check text-green-600 mt-1"></i>
                    <p class="text-xs text-gray-600">
                        <strong>Data-Driven Analysis:</strong> This report is generated strictly from local system metadata and content processing. It identifies statistical patterns and overlaps without utilizing external AI models.
                    </p>
                </div>
            `;
            content.innerHTML = html;
        } else {
            content.innerHTML = `<div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
                <i class="bi bi-exclamation-triangle-fill mr-2"></i> <strong>Error:</strong> ${data.error}
            </div>`;
        }
    } catch (error) {
        console.error("Analysis Error:", error);
        content.innerHTML = `<div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            <i class="bi bi-exclamation-triangle-fill mr-2"></i> <strong>Service Unavailable:</strong> The analysis service could not be reached.
        </div>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}
</script>

<style>
.scrollbar-thin::-webkit-scrollbar { height: 6px; }
.scrollbar-thin::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* Force Red Theme in Dark Mode */
.dark #smart-compare-btn,
.dark button[onclick="openSelectModal()"] {
    background-color: #b91c1c !important;
    color: white !important;
}

.dark #smart-compare-btn:hover,
.dark button[onclick="openSelectModal()"]:hover {
    background-color: #991b1b !important;
}

/* Dark Mode text readability for analysis results */
.dark #analysis-container {
    background-color: #1e1e1e !important;
    border-color: #404040 !important;
}

.dark #analysis-title, 
.dark .font-bold.text-red-800 {
    color: #f87171 !important; /* Lighter red for headings in dark mode */
}

.dark #analysis-content {
    color: #e5e5e5 !important;
}

.dark .bg-red-50 {
    background-color: rgba(153, 27, 27, 0.2) !important;
    border-color: rgba(153, 27, 27, 0.4) !important;
}
</style>
