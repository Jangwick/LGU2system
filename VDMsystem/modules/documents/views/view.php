<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Get document
$doc = dbFetchOne(
    "SELECT d.*, u.full_name as author_name, c.name as committee_name, cb.full_name as creator_name
     FROM documents d
     LEFT JOIN users u ON d.author_id = u.id
     LEFT JOIN committees c ON d.committee_id = c.id
     LEFT JOIN users cb ON d.created_by = cb.id
     WHERE d.id = ?",
    [$id]
);

if (!$doc) {
    $_SESSION['flash_error'] = "Document not found.";
    header('Location: index.php');
    exit;
}

$pageTitle = 'View Document: ' . $doc['title'];
$currentPage = 'documents';
$breadcrumbs = [
    ['label' => 'Documents', 'url' => 'index.php'],
    ['label' => $doc['doc_number']]
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
    <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-slate-950 p-3 md:p-6">
        <!-- Page Header -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="relative flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="bg-white/20 p-3 rounded-xl backdrop-blur-md">
                        <i class="bi bi-file-earmark-text text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight"><?php echo htmlspecialchars($doc['doc_number']); ?></h1>
                        <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium"><?php echo htmlspecialchars($doc['title']); ?></p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="index.php" class="!bg-white/10 !text-white hover:!bg-white/20 px-4 py-2 rounded-xl font-bold transition-all flex items-center border border-white/20">
                        <i class="bi bi-arrow-left mr-2"></i> Back
                    </a>
                    <?php if (hasRole(['admin', 'secretary', 'encoder'])): ?>
                    <a href="edit.php?id=<?php echo $doc['id']; ?>" class="!bg-white !text-red-600 hover:!bg-gray-50 px-6 py-2.5 rounded-xl font-bold shadow-lg transition-all flex items-center border border-red-600">
                        <i class="bi bi-pencil mr-2"></i> Edit
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Content/Summary Card -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 dark:text-white flex items-center">
                            <i class="bi bi-card-text text-red-600 mr-2"></i> Executive Summary
                        </h3>
                        <span class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-xs font-bold uppercase tracking-wider">
                            <?php echo ucfirst($doc['type']); ?>
                        </span>
                    </div>
                    <div class="p-8 prose max-w-none text-gray-700 dark:text-slate-300 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($doc['description'] ?? 'No summary provided.')); ?>
                    </div>
                </div>

                <!-- Attachment Card -->
                <?php if ($doc['file_path']): ?>
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-800/50">
                        <h3 class="font-bold text-gray-800 dark:text-white flex items-center">
                            <i class="bi bi-paperclip text-red-600 mr-2"></i> Attached Document
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center p-4 bg-gray-50 dark:bg-slate-800 rounded-xl border border-dashed border-gray-200 dark:border-slate-700 group hover:border-red-300 dark:hover:border-red-500 hover:bg-red-50/30 dark:hover:bg-red-900/10 transition-all cursor-pointer" onclick="window.open('<?php echo BASE_URL . '/' . $doc['file_path']; ?>', '_blank')">
                            <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center text-red-600 dark:text-red-400 text-2xl group-hover:scale-110 transition-transform">
                                <i class="bi bi-file-earmark-pdf-fill"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-900 dark:text-white truncate"><?php echo htmlspecialchars($doc['file_name'] ?? 'View Attachment'); ?></h4>
                                <p class="text-xs text-gray-500 dark:text-slate-400 uppercase tracking-widest mt-1">
                                    <?php echo formatFileSize($doc['file_size']); ?> • <?php echo strtoupper(pathinfo($doc['file_path'], PATHINFO_EXTENSION)); ?>
                                </p>
                            </div>
                            <div class="text-red-600 dark:text-red-400 font-bold flex items-center gap-2">
                                <span class="hidden md:inline">Download</span>
                                <i class="bi bi-download"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 p-6">
                    <h3 class="text-xs font-black text-gray-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-4">Current Status</h3>
                    <div class="flex items-center justify-center p-4 rounded-2xl <?php echo getStatusBadgeClass($doc['status']); ?> bg-opacity-20 mb-4 border border-current border-opacity-10 shadow-sm">
                        <span class="text-lg font-black uppercase tracking-widest"><?php echo str_replace('_', ' ', $doc['status']); ?></span>
                    </div>
                </div>

                <!-- metadata Card -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 p-6 space-y-4">
                    <h3 class="text-xs font-black text-gray-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-2">Legislative Info</h3>
                    
                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-slate-800/50 rounded-xl border border-transparent dark:border-slate-800">
                        <div class="w-10 h-10 bg-white dark:bg-slate-900 rounded-lg shadow-sm flex items-center justify-center text-gray-400 shrink-0">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Author/Sponsor</p>
                            <p class="text-sm font-bold text-gray-800 dark:text-slate-200"><?php echo htmlspecialchars($doc['author_name'] ?? 'N/A'); ?></p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-slate-800/50 rounded-xl border border-transparent dark:border-slate-800">
                        <div class="w-10 h-10 bg-white dark:bg-slate-900 rounded-lg shadow-sm flex items-center justify-center text-gray-400 shrink-0">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Committee</p>
                            <p class="text-sm font-bold text-gray-800 dark:text-slate-200"><?php echo htmlspecialchars($doc['committee_name'] ?? 'General Legislative'); ?></p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-slate-800/50 rounded-xl border border-transparent dark:border-slate-800">
                        <div class="w-10 h-10 bg-white dark:bg-slate-900 rounded-lg shadow-sm flex items-center justify-center text-gray-400 shrink-0">
                            <i class="bi bi-calendar-event-fill"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Created Date</p>
                            <p class="text-sm font-bold text-gray-800 dark:text-slate-200"><?php echo formatDate($doc['created_at']); ?></p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-slate-800/50 rounded-xl border border-transparent dark:border-slate-800">
                        <div class="w-10 h-10 bg-white dark:bg-slate-900 rounded-lg shadow-sm flex items-center justify-center text-gray-400 shrink-0">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Encoded By</p>
                            <p class="text-sm font-bold text-gray-800 dark:text-slate-200"><?php echo htmlspecialchars($doc['creator_name'] ?? 'System'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
