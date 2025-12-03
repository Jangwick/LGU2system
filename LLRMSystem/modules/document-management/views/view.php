<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../models/Document.php';
require_once __DIR__ . '/../models/DocumentVersion.php';
require_once __DIR__ . '/../models/DocumentLink.php';
require_once __DIR__ . '/../models/DocumentTag.php';

$db = getDatabase();
$documentModel = new Document($db);
$versionModel = new DocumentVersion($db);
$linkModel = new DocumentLink($db);
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

// Check if viewer can access this document (approved/archived/rejected)
$userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
if ($userRole === 'viewer' && !in_array($document['status'], ['approved', 'archived', 'rejected'])) {
    require_once __DIR__ . '/../../core/config/config.php';
    $_SESSION['error_message'] = 'Access denied. Viewers can only view approved, archived, and rejected documents.';
    redirect(DOCUMENTS_INDEX_URL);
}

$versions = $versionModel->getByDocumentId($documentId);
$links = $linkModel->getByDocumentId($documentId);
$incomingLinks = $linkModel->getIncomingLinks($documentId);
$tags = $tagModel->getByDocumentId($documentId);

$pageTitle = $document['title'];
$currentPage = 'documents';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => DASHBOARD_INDEX_URL],
    ['label' => 'Documents', 'url' => DOCUMENTS_INDEX_URL],
    ['label' => $document['reference_number']]
];

// Helper functions
function getStatusBadge($status) {
    $badges = [
        'draft' => 'bg-gray-100 text-gray-800',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'approved' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
        'archived' => 'bg-blue-100 text-blue-800',
        'superseded' => 'bg-purple-100 text-purple-800'
    ];
    return $badges[$status] ?? 'bg-gray-100 text-gray-800';
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

function getLinkTypeLabel($type) {
    $labels = [
        'related' => 'Related To',
        'supersedes' => 'Supersedes',
        'superseded_by' => 'Superseded By',
        'amends' => 'Amends',
        'amended_by' => 'Amended By',
        'reference' => 'References'
    ];
    return $labels[$type] ?? ucfirst($type);
}

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <!-- Header Section -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 hover:shadow-xl transition-all duration-300 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div class="flex-1 animate-slide-in-left">
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($document['title']) ?></h1>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= getStatusBadge($document['status']) ?>">
                            <?= ucfirst($document['status']) ?>
                        </span>
                    </div>
                    <p class="text-gray-600 mb-1">Reference: <span class="font-mono font-semibold"><?= $document['reference_number'] ?></span></p>
                    <p class="text-sm text-gray-500">
                        Type: <?= ucfirst(str_replace('_', ' ', $document['document_type'])) ?> • 
                        Date: <?= date('F d, Y', strtotime($document['document_date'])) ?>
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="<?php echo DOCUMENTS_URL; ?>/api/download.php?id=<?= $document['id'] ?>" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="bi bi-download mr-2"></i>Download
                    </a>
                    <?php 
                    $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                    $isDocOwner = ($document['uploaded_by'] ?? 0) == ($_SESSION['user_id'] ?? 0);
                    $canEdit = in_array($userRole, ['administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner);
                    if ($canEdit): 
                    ?>
                    <a href="<?php echo DOCUMENTS_URL; ?>/views/edit.php?id=<?= $document['id'] ?>" 
                       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                        <i class="bi bi-pencil mr-2"></i>Edit
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Document Details -->
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Document Information</h2>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">File Name</label>
                            <p class="text-gray-800"><?= htmlspecialchars($document['file_name']) ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">File Size</label>
                            <p class="text-gray-800"><?= formatFileSize($document['file_size']) ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">File Type</label>
                            <p class="text-gray-800"><?= htmlspecialchars($document['file_type']) ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Uploaded By</label>
                            <p class="text-gray-800"><?= htmlspecialchars($document['uploaded_by_name']) ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Created At</label>
                            <p class="text-gray-800"><?= date('F d, Y g:i A', strtotime($document['created_at'])) ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Last Updated</label>
                            <p class="text-gray-800"><?= date('F d, Y g:i A', strtotime($document['updated_at'])) ?></p>
                        </div>
                    </div>

                    <?php if ($document['description']): ?>
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500">Description</label>
                        <p class="text-gray-800 mt-1"><?= nl2br(htmlspecialchars($document['description'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($tags)): ?>
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500 mb-2 block">Tags</label>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($tags as $tag): ?>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                <i class="bi bi-tag mr-1"></i><?= htmlspecialchars($tag['name']) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Version History -->
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-200">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Version History</h2>
                    
                    <?php if (empty($versions)): ?>
                    <p class="text-gray-500 text-center py-8">No previous versions</p>
                    <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($versions as $version): ?>
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-bold">v<?= $version['version_number'] ?></span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($version['file_name']) ?></p>
                                    <p class="text-sm text-gray-500">
                                        <?= formatFileSize($version['file_size']) ?> • 
                                        <?= date('M d, Y g:i A', strtotime($version['created_at'])) ?> • 
                                        by <?= htmlspecialchars($version['created_by_name']) ?>
                                    </p>
                                    <?php if ($version['change_description']): ?>
                                    <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($version['change_description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="downloadVersion(<?= $version['id'] ?>)" 
                                        class="px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 rounded">
                                    <i class="bi bi-download"></i>
                                </button>
                                <?php if (in_array($userRole, ['administrator', 'admin', 'officer'])): ?>
                                <button onclick="revertVersion(<?= $document['id'] ?>, <?= $version['version_number'] ?>)" 
                                        class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-100 rounded">
                                    <i class="bi bi-arrow-counterclockwise"></i> Revert
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Related Documents -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Related Documents</h2>
                    
                    <?php if (empty($links) && empty($incomingLinks)): ?>
                    <p class="text-gray-500 text-center py-4 text-sm">No related documents</p>
                    <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($links as $link): ?>
                        <a href="<?php echo DOCUMENTS_URL; ?>/views/view.php?id=<?= $link['linked_document_id'] ?>" 
                           class="block p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <span class="text-xs text-gray-500"><?= getLinkTypeLabel($link['link_type']) ?></span>
                                    <p class="text-sm font-medium text-gray-800 mt-1"><?= htmlspecialchars($link['linked_title']) ?></p>
                                    <p class="text-xs text-gray-500 mt-1"><?= $link['linked_reference'] ?></p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded <?= getStatusBadge($link['linked_status']) ?>">
                                    <?= ucfirst($link['linked_status']) ?>
                                </span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                        
                        <?php foreach ($incomingLinks as $link): ?>
                        <a href="<?php echo DOCUMENTS_URL; ?>/views/view.php?id=<?= $link['document_id'] ?>" 
                           class="block p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <span class="text-xs text-gray-500"><?= getLinkTypeLabel($link['link_type']) ?> (incoming)</span>
                                    <p class="text-sm font-medium text-gray-800 mt-1"><?= htmlspecialchars($link['title']) ?></p>
                                    <p class="text-xs text-gray-500 mt-1"><?= $link['reference_number'] ?></p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded <?= getStatusBadge($link['status']) ?>">
                                    <?= ucfirst($link['status']) ?>
                                </span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h2>
                    
                    <div class="space-y-2">
                        <button onclick="shareDocument()" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 rounded-lg transition">
                            <i class="bi bi-share mr-2 text-gray-600"></i>Share Document
                        </button>
                        <button onclick="printDocument()" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 rounded-lg transition">
                            <i class="bi bi-printer mr-2 text-gray-600"></i>Print
                        </button>
                        <button onclick="viewHistory()" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 rounded-lg transition">
                            <i class="bi bi-clock-history mr-2 text-gray-600"></i>Activity History
                        </button>
                        <?php 
                        $canDelete = in_array($userRole, ['administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner);
                        if ($canDelete): 
                        ?>
                        <hr class="my-2">
                        <button onclick="deleteDocument(<?= $document['id'] ?>)" 
                                class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 rounded-lg transition">
                            <i class="bi bi-trash mr-2"></i>Delete Document
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<script>
function downloadVersion(versionId) {
    window.location.href = App.apiUrl('documents', `download-version.php?id=${versionId}`);
}

function revertVersion(documentId, versionNumber) {
    if (confirm(`Are you sure you want to revert to version ${versionNumber}? This will create a new version with the content from version ${versionNumber}.`)) {
        fetch(App.apiUrl('documents', 'revert-version.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ document_id: documentId, version_number: versionNumber })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Document reverted successfully');
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}

function shareDocument() {
    const url = window.location.href;
    navigator.clipboard.writeText(url);
    alert('Document link copied to clipboard');
}

function printDocument() {
    window.print();
}

function viewHistory() {
    alert('Activity history feature coming soon');
}

function deleteDocument(id) {
    if (confirm('Are you sure you want to delete this document? It will be moved to trash.')) {
        fetch(App.apiUrl('documents', 'delete.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = App.config.urls.documents + '/views/index.php';
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}
</script>
