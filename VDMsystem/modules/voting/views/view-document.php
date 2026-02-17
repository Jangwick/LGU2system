<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

$docId = $_GET['id'] ?? null;
if (!$docId) {
    $_SESSION['flash_error'] = "No document ID provided.";
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?: '../../dashboard/views/index.php');
    exit;
}

// Fetch document details
$doc = dbFetchOne(
    "SELECT d.*, u.full_name as author_name, c.name as committee_name
     FROM documents d
     LEFT JOIN users u ON d.author_id = u.id
     LEFT JOIN committees c ON d.committee_id = c.id
     WHERE d.id = ?",
    [$docId]
);

if (!$doc) {
    $_SESSION['flash_error'] = "Document not found.";
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?: '../../dashboard/views/index.php');
    exit;
}

$pageTitle = 'View Document - ' . ($doc['doc_number'] ?? 'N/A');
$currentPage = 'documents';
$breadcrumbs = [
    ['label' => 'Documents', 'url' => '../../documents/views/index.php'],
    ['label' => 'View Document']
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
    <main class="flex-1 overflow-y-auto bg-gray-100 p-3 md:p-6 custom-scrollbar">
        <div class="max-w-5xl mx-auto pb-12">
            <!-- Back Button -->
            <button onclick="history.back()" class="flex items-center text-gray-600 hover:text-red-600 font-bold mb-6 transition-colors">
                <i class="bi bi-arrow-left mr-2"></i> Back to Previous Page
            </button>

            <!-- Document Header -->
            <div class="bg-white rounded-[2.5rem] shadow-xl overflow-hidden mb-8 border border-white">
                <div class="bg-gradient-to-r from-red-600 to-red-800 p-8 md:p-12 text-white">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                        <div>
                            <span class="inline-block px-4 py-1.5 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-widest mb-4">
                                <?php echo strtoupper($doc['type'] ?? 'DOCUMENT'); ?>
                            </span>
                            <h1 class="text-3xl md:text-5xl font-black leading-tight tracking-tight mb-2">
                                <?php echo htmlspecialchars($doc['title']); ?>
                            </h1>
                            <p class="text-red-100 text-sm font-bold opacity-80">
                                #<?php echo htmlspecialchars($doc['doc_number'] ?? 'N/A'); ?> &bull; Created on <?php echo formatDate($doc['created_at']); ?>
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-5 py-2 bg-white text-red-600 rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg">
                                <?php echo strtoupper($doc['status'] ?? 'PENDING'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x border-b border-gray-100">
                    <div class="p-8">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Author / Proponent</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center font-black">
                                <?php echo strtoupper(substr($doc['author_name'] ?? 'A', 0, 1)); ?>
                            </div>
                            <p class="font-black text-gray-800 tracking-tight"><?php echo htmlspecialchars($doc['author_name'] ?? 'Admin User'); ?></p>
                        </div>
                    </div>
                    <div class="p-8">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Committee</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                                <i class="bi bi-building"></i>
                            </div>
                            <p class="font-black text-gray-800 tracking-tight"><?php echo htmlspecialchars($doc['committee_name'] ?? 'Unassigned'); ?></p>
                        </div>
                    </div>
                    <div class="p-8">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Filing Date</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <p class="font-black text-gray-800 tracking-tight"><?php echo formatDate($doc['created_at']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Document Summary -->
                <div class="p-8 md:p-12">
                    <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-3">
                        <i class="bi bi-text-left text-red-600"></i> Narrative Summary
                    </h3>
                    <div class="prose max-w-none text-gray-600 leading-relaxed font-medium">
                        <?php echo nl2br(htmlspecialchars($doc['summary'] ?? 'No summary available for this document.')); ?>
                    </div>
                </div>
            </div>

            <!-- Full Document Content (Mock/Placeholder) -->
            <div class="bg-white rounded-[2.5rem] shadow-xl p-8 md:p-12 border border-white">
                <div class="flex items-center justify-between mb-8 pb-8 border-b border-gray-100">
                    <h3 class="text-xl font-black text-gray-900 flex items-center gap-3">
                        <i class="bi bi-file-earmark-pdf text-red-600 text-2xl"></i> Full Document Body
                    </h3>
                    <div class="flex gap-2">
                        <button class="bg-slate-100 hover:bg-slate-200 text-gray-700 px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all transition-colors flex items-center gap-2">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <button class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-red-500/20 transition-all flex items-center gap-2">
                            <i class="bi bi-download"></i> PDF
                        </button>
                    </div>
                </div>

                <!-- Document Body Content -->
                <div class="bg-slate-50 rounded-3xl p-8 md:p-16 text-center border-2 border-dashed border-slate-200">
                    <div class="max-w-md mx-auto">
                        <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center mx-auto mb-6 text-2xl text-slate-300">
                            <i class="bi bi-file-earmark-lock2"></i>
                        </div>
                        <h4 class="text-xl font-black text-gray-800 mb-2">Electronic Copy secured</h4>
                        <p class="text-gray-500 font-medium">Full textual body is stored in the legislative cloud repository. You can download the official PDF copy for review.</p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                            <a href="#" class="px-8 py-3 bg-white border border-gray-200 rounded-2xl font-black text-sm text-gray-800 hover:bg-gray-50 transition-all shadow-sm">Preview Text</a>
                            <a href="#" class="px-8 py-3 bg-red-600 rounded-2xl font-black text-sm text-white shadow-lg shadow-red-500/20 hover:bg-red-700 transition-all">Download .PDF</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
