<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../../core/config/config.php';
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
    
    <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Edit Document</h1>
                <p class="text-gray-600">Update document information and metadata</p>
            </div>

            <form id="editDocumentForm" class="space-y-6">
                <input type="hidden" name="document_id" value="<?= $document['id'] ?>">
                
                <!-- Basic Information -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Basic Information</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($document['title']) ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Document Type *</label>
                                <select name="document_type"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                <select name="status"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Document Date *</label>
                            <input type="date" name="document_date" value="<?= $document['document_date'] ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Enter document description..."><?= htmlspecialchars($document['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Tags -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Tags</h2>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Tags</label>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php foreach ($documentTags as $tag): ?>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm flex items-center gap-2">
                                <?= htmlspecialchars($tag['name']) ?>
                                <button type="button" onclick="removeTag(<?= $tag['id'] ?>)" class="text-blue-600 hover:text-blue-800">
                                    <i class="bi bi-x"></i>
                                </button>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        
                        <select id="tagSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">-- Select a tag --</option>
                            <?php foreach ($allTags as $tag): ?>
                            <option value="<?= $tag['id'] ?>"><?= htmlspecialchars($tag['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Or create new tag</label>
                        <div class="flex gap-2">
                            <input type="text" id="newTagInput" placeholder="Enter tag name..."
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
                            <button type="button" onclick="createTag()" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Add Tag
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Replace File -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Replace File (Optional)</h2>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">
                            Current file: <span class="font-medium"><?= htmlspecialchars($document['file_name']) ?></span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload New Version</label>
                        <input type="file" id="replacementFile" name="replacement_file"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <p class="text-sm text-gray-500 mt-2">Accepted formats: PDF, Word, Excel, PowerPoint (Max 50MB)</p>
                    </div>

                    <div class="mt-4" id="changeDescriptionDiv" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Change Description</label>
                        <textarea id="changeDescription" name="change_description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                  placeholder="Describe what changed in this version..."></textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-4">
                    <a href="<?php echo DOCUMENTS_URL; ?>/views/view.php?id=<?= $document['id'] ?>"
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="bi bi-check-lg mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </main>
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

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
