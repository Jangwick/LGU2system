<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../models/DocumentTag.php';

$db = getDatabase();
$documentModel = new Document($db);
$tagModel = new DocumentTag($db);

$documentId = $_GET['id'] ?? null;

if (!$documentId) {
    require_once __DIR__ . '/../../core/config/config.php';
    redirect(DOCUMENTS_INDEX_URL);
}

$document = $documentModel->getById($documentId);

if (!$document) {
    require_once __DIR__ . '/../../core/config/config.php';
    redirect(DOCUMENTS_INDEX_URL);
}

// Check permissions
$userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));

// Viewers cannot edit any documents
if ($userRole === 'viewer') {
    require_once __DIR__ . '/../../core/config/config.php';
    $_SESSION['error_message'] = 'Access denied. Viewers cannot edit documents.';
    redirect(DOCUMENTS_URL . '/views/view.php?id=' . $documentId);
}

// Staff can only edit their own documents
if ($userRole === 'staff' && $document['uploaded_by'] != $_SESSION['user_id']) {
    require_once __DIR__ . '/../../core/config/config.php';
    $_SESSION['error_message'] = 'Access denied. You can only edit your own documents.';
    redirect(DOCUMENTS_URL . '/views/view.php?id=' . $documentId);
}

$documentTags = $tagModel->getByDocumentId($documentId);
$allTags = $tagModel->getAll();

$pageTitle = 'Edit Document';
$currentPage = 'documents';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => DASHBOARD_INDEX_URL],
    ['label' => 'Documents', 'url' => DOCUMENTS_INDEX_URL],
    ['label' => $document['reference_number'], 'url' => DOCUMENTS_URL . '/views/view.php?id=' . $documentId],
    ['label' => 'Edit']
];

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-900 p-3 sm:p-4 md:p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Back Button -->
            <div class="mb-4 flex animate-fade-in">
                <a href="<?= DOCUMENTS_URL ?>/views/view.php?id=<?= $document['id'] ?>" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:border-red-100 dark:hover:border-red-900/30 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all shadow-sm group">
                    <i class="bi bi-arrow-left mr-2 group-hover:-translate-x-1 transition-transform text-red-600 dark:text-red-500"></i>
                    Back to Document
                </a>
            </div>

            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-4 sm:p-5 md:p-6 mb-4 md:mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-1 sm:mb-2">Edit Document</h1>
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Update document information and metadata</p>
            </div>

            <form id="editDocumentForm" class="space-y-6">
                <input type="hidden" name="document_id" value="<?= $document['id'] ?>">
                
                <!-- Basic Information -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-4 sm:p-5 md:p-6">
                    <h2 class="text-base sm:text-lg font-bold text-gray-800 dark:text-white mb-3 sm:mb-4">Basic Information</h2>
                    
                    <div class="space-y-3 sm:space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 sm:mb-2">Title *</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($document['title']) ?>"
                                   class="w-full px-3 sm:px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base bg-white dark:bg-gray-700 dark:text-white"
                                   required>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Document Type *</label>
                                <select name="document_type"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:text-white"
                                        required>
                                    <option value="ordinance" <?= $document['document_type'] === 'ordinance' ? 'selected' : '' ?>>Ordinance</option>
                                    <option value="resolution" <?= $document['document_type'] === 'resolution' ? 'selected' : '' ?>>Resolution</option>
                                    <option value="session" <?= $document['document_type'] === 'session' ? 'selected' : '' ?>>Session Minutes</option>
                                    <option value="agenda" <?= $document['document_type'] === 'agenda' ? 'selected' : '' ?>>Agenda</option>
                                    <option value="committee" <?= $document['document_type'] === 'committee' ? 'selected' : '' ?>>Committee Report</option>
                                    <option value="voting" <?= $document['document_type'] === 'voting' ? 'selected' : '' ?>>Voting Record</option>
                                    <option value="hearing" <?= $document['document_type'] === 'hearing' ? 'selected' : '' ?>>Public Hearing</option>
                                    <option value="archive" <?= $document['document_type'] === 'archive' ? 'selected' : '' ?>>Archive</option>
                                    <option value="consultation" <?= $document['document_type'] === 'consultation' ? 'selected' : '' ?>>Consultation</option>
                                    <option value="research" <?= $document['document_type'] === 'research' ? 'selected' : '' ?>>Research Paper</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status *</label>
                                <select name="status"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:text-white"
                                        required>
                                    <option value="draft" <?= $document['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="pending" <?= $document['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="approved" <?= $document['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                    <option value="rejected" <?= $document['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                    <option value="archived" <?= $document['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                                    <option value="superseded" <?= $document['status'] === 'superseded' ? 'selected' : '' ?>>Superseded</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Document Date *</label>
                            <input type="date" name="document_date" value="<?= e($document['document_date']) ?>"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:text-white"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                            <textarea name="description" rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 dark:text-white"
                                      placeholder="Enter document description..."><?= htmlspecialchars($document['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Tags -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-4 sm:p-5 md:p-6">
                    <h2 class="text-base sm:text-lg font-bold text-gray-800 dark:text-white mb-3 sm:mb-4">Tags</h2>
                    
                    <div class="mb-3 sm:mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 sm:mb-2">Select Tags</label>
                        <div class="flex flex-wrap gap-2 mb-3 sm:mb-4">
                            <?php foreach ($documentTags as $tag): ?>
                            <span class="px-2 sm:px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-full text-xs sm:text-sm flex items-center gap-1 sm:gap-2">
                                <?= htmlspecialchars($tag['name']) ?>
                                <button type="button" onclick="removeTag(<?= $tag['id'] ?>)" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 p-0.5">
                                    <i class="bi bi-x"></i>
                                </button>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        
                        <select id="tagSelect" class="w-full px-3 sm:px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm sm:text-base bg-white dark:bg-gray-700 dark:text-white">
                            <option value="">-- Select a tag --</option>
                            <?php foreach ($allTags as $tag): ?>
                            <option value="<?= $tag['id'] ?>"><?= htmlspecialchars($tag['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 sm:mb-2">Or create new tag</label>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input type="text" id="newTagInput" placeholder="Enter tag name..."
                                   class="flex-1 px-3 sm:px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm sm:text-base bg-white dark:bg-gray-700 dark:text-white">
                            <button type="button" onclick="createTag()" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-500 text-sm sm:text-base whitespace-nowrap shadow-md transition-colors">
                                <i class="bi bi-plus-circle mr-1 sm:hidden"></i>Add Tag
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Replace File -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-4 sm:p-5 md:p-6">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Replace File (Optional)</h2>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            Current file: <span class="font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($document['file_name']) ?></span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Upload New Version</label>
                        <input type="file" id="replacementFile" name="replacement_file"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 dark:text-white">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Accepted formats: PDF, Word, Excel, PowerPoint (Max 50MB)</p>
                    </div>

                    <div class="mt-4" id="changeDescriptionDiv" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Change Description</label>
                        <textarea id="changeDescription" name="change_description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 dark:text-white"
                                  placeholder="Describe what changed in this version..."></textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-4 pb-10">
                    <a href="<?php echo DOCUMENTS_URL; ?>/views/view.php?id=<?= $document['id'] ?>"
                       class="px-4 sm:px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-center text-sm sm:text-base transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-4 sm:px-6 py-2 bg-blue-600 dark:bg-blue-600 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-500 text-sm sm:text-base shadow-md transition-colors font-bold">
                        <i class="bi bi-check-lg mr-1 sm:mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </main>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<script>
let selectedTags = <?= json_encode(array_column($documentTags, 'id')) ?>;

document.getElementById('replacementFile').addEventListener('change', function() {
    const changeDescDiv = document.getElementById('changeDescriptionDiv');
    changeDescDiv.style.display = this.files.length > 0 ? 'block' : 'none';
});

document.getElementById('tagSelect').addEventListener('change', function() {
    const tagId = parseInt(this.value);
    if (tagId && !selectedTags.includes(tagId)) {
        assignTag(tagId);
    }
    this.value = '';
});

function assignTag(tagId) {
    fetch(App.apiUrl('documents', 'assign-tag.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ document_id: <?= $document['id'] ?>, tag_id: tagId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function removeTag(tagId) {
    fetch(App.apiUrl('documents', 'remove-tag.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ document_id: <?= $document['id'] ?>, tag_id: tagId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function createTag() {
    const tagName = document.getElementById('newTagInput').value.trim();
    if (!tagName) {
        alert('Please enter a tag name');
        return;
    }

    fetch(App.apiUrl('documents', 'create-tag.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: tagName, document_id: <?= $document['id'] ?> })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

document.getElementById('editDocumentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const hasFile = document.getElementById('replacementFile').files.length > 0;
    
    const url = hasFile 
        ? App.apiUrl('documents', 'update-with-file.php')
        : App.apiUrl('documents', 'update.php');
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Document updated successfully');
            window.location.href = App.config.urls.documents + '/views/view.php?id=' + <?= $document['id'] ?>;
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the document');
    });
});
</script>
