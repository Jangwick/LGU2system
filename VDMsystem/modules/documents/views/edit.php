<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';

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
$doc = dbFetchOne("SELECT * FROM documents WHERE id = ?", [$id]);
if (!$doc) {
    $_SESSION['flash_error'] = "Document not found.";
    header('Location: index.php');
    exit;
}

// Check permissions
if (!hasRole(['admin', 'secretary', 'encoder'])) {
    $_SESSION['flash_error'] = "You don't have permission to edit this document.";
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $type = $_POST['type'] ?? '';
    $summary = trim($_POST['summary'] ?? '');
    $status = $_POST['status'] ?? $doc['status'];
    $docNumber = trim($_POST['doc_number'] ?? '');
    
    $errors = [];
    if (empty($title)) $errors[] = "Title is required.";
    
    if (empty($errors)) {
        // Check for duplicate doc_number (excluding current document)
        if (dbCount('documents', "doc_number = ? AND id != ?", [$docNumber, $id]) > 0) {
            $errors[] = "Reference number '$docNumber' is already in use by another document.";
        }
    }

    if (empty($errors)) {
        $data = [
            'title' => $title,
            'type' => $type,
            'description' => $summary,
            'status' => $status,
            'doc_number' => $docNumber,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        dbUpdate('documents', $data, "id = ?", [$id]);
        logAudit('document_updated', $_SESSION['user_id'], 'documents', 'documents', $id, "Updated document: $title");
        
        $_SESSION['flash_success'] = "Document updated successfully!";
        header("Location: view.php?id=$id");
        exit;
    }
}

$documentTypes = ['resolution', 'ordinance', 'motion', 'bill', 'report', 'other'];
$statusList = ['draft', 'under_review', 'committee_review', 'pending_vote', 'approved', 'rejected', 'archived'];

$pageTitle = 'Edit Document: ' . $doc['doc_number'];
$currentPage = 'documents';
$breadcrumbs = [
    ['label' => 'Documents', 'url' => 'index.php'],
    ['label' => $doc['doc_number'], 'url' => 'view.php?id='.$id],
    ['label' => 'Edit']
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
    <main class="flex-1 overflow-y-auto bg-gray-100 p-3 md:p-6">
        <!-- Page Header -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="relative flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight italic">Update Document Metadata</h1>
                    <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium">Refine legislative document information and status.</p>
                </div>
                <div class="shrink-0">
                    <a href="view.php?id=<?php echo $id; ?>" class="!bg-white/10 !text-white hover:!bg-white/20 px-4 py-2 rounded-xl font-bold transition-all flex items-center border border-white/20">
                        <i class="bi bi-x-circle mr-2"></i> Cancel
                    </a>
                </div>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 max-w-4xl mx-auto">
                <div class="flex items-start">
                    <i class="bi bi-exclamation-triangle text-red-500 text-xl mr-3"></i>
                    <div>
                        <h4 class="text-red-800 font-medium">Please fix the following errors:</h4>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="max-w-4xl mx-auto space-y-6 pb-12">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Reference -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest pl-1">Reference Number</label>
                        <input type="text" name="doc_number" value="<?php echo htmlspecialchars($doc['doc_number']); ?>" 
                               class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-xl focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-bold text-sm transition-all">
                    </div>

                    <!-- Type -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest pl-1">Document Type</label>
                        <select name="type" class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-xl focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-bold text-sm transition-all">
                            <?php foreach ($documentTypes as $type): ?>
                                <option value="<?php echo $type; ?>" <?php echo $doc['type'] === $type ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Title -->
                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest pl-1">Legislative Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($doc['title']); ?>" required
                               class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-xl focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-bold text-sm transition-all">
                    </div>

                    <!-- Summary -->
                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest pl-1">Executive Summary</label>
                        <textarea name="summary" rows="6"
                                  class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-xl focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-medium text-sm transition-all resize-none leading-relaxed"><?php echo htmlspecialchars($doc['description'] ?? ''); ?></textarea>
                    </div>

                    <!-- Status -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest pl-1">Lifecycle Status</label>
                        <select name="status" class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-xl focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-bold text-sm transition-all">
                            <?php foreach ($statusList as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo $doc['status'] === $status ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-4 rounded-xl font-black uppercase tracking-widest text-[11px] shadow-xl shadow-red-200 transition-all active:scale-95">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </main>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
