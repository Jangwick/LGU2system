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

// Get confidentiality level (default to 'public' if not set)
$confidentialityLevel = $document['confidentiality_level'] ?? 'public';
$isConfidential = in_array($confidentialityLevel, ['confidential', 'restricted']);
$isEncrypted = $document['is_encrypted'] ?? false;


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
        'draft' => 'badge-secondary',
        'pending' => 'badge-warning',
        'approved' => 'badge-success',
        'rejected' => 'badge-danger',
        'archived' => 'badge-gray',
        'superseded' => 'badge-purple'
    ];
    return $badges[$status] ?? 'badge-secondary';
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
    
    <main class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-900 p-3 sm:p-4 md:p-6">
        <!-- Back Button -->
        <div class="mb-4 flex animate-fade-in">
            <a href="<?= DOCUMENTS_INDEX_URL ?>" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:border-red-100 dark:hover:border-red-900/40 hover:bg-red-50 dark:hover:bg-red-900/10 transition-all shadow-sm group">
                <i class="bi bi-arrow-left mr-2 group-hover:-translate-x-1 transition-transform text-red-600 dark:text-red-500"></i>
                Back to List
            </a>
        </div>

        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md dark:shadow-none p-4 sm:p-5 md:p-6 mb-4 md:mb-6 hover:shadow-xl dark:hover:shadow-blue-900/10 transition-all duration-300 animate-fade-in">
            <div class="flex flex-col gap-4">
                <div class="flex-1 animate-slide-in-left">
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2">
                        <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($document['title']) ?></h1>
                        <span class="badge <?= getStatusBadge($document['status']) ?>">
                            <?= e(ucfirst($document['status'])) ?>
                        </span>
                        <?php if ($confidentialityLevel !== 'public'): ?>
                        <span class="badge <?= $isConfidential ? 'badge-danger' : 'badge-warning' ?>">
                            <i class="bi bi-shield-lock mr-1"></i><?= e(ucfirst($confidentialityLevel)) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 mb-1">Reference: <span class="font-mono font-semibold"><?= e($document['reference_number']) ?></span></p>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                        Type: <?= e(ucfirst(str_replace('_', ' ', $document['document_type']))) ?> • 
                        Date: <?= date('F d, Y', strtotime($document['document_date'])) ?>
                    </p>
                </div>
                <div class="flex flex-wrap gap-2 sm:gap-3">
                    <?php if ($isConfidential || $isEncrypted): ?>
                    <button onclick="promptPasswordForAccess('view')" 
                       class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-amber-600 dark:bg-amber-600 text-white rounded-lg hover:bg-amber-700 dark:hover:bg-amber-500 transition text-center text-sm sm:text-base shadow-md">
                        <i class="bi bi-shield-lock mr-1 sm:mr-2"></i><span class="hidden xs:inline">Unlock to View</span><span class="xs:hidden">Unlock</span>
                    </button>
                    <button onclick="promptPasswordForAccess('download')" 
                       class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-blue-600 dark:bg-blue-600 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-500 transition text-center text-sm sm:text-base shadow-md">
                        <i class="bi bi-download mr-1 sm:mr-2"></i><span class="hidden xs:inline">Unlock & Download</span><span class="xs:hidden">DL</span>
                    </button>
                    <?php else: ?>
                    <a href="<?php echo DOCUMENTS_URL; ?>/api/download.php?id=<?= $document['id'] ?>" 
                       class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-blue-600 dark:bg-blue-600 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-500 transition text-center text-sm sm:text-base shadow-md">
                        <i class="bi bi-download mr-1 sm:mr-2"></i><span class="hidden xs:inline">Download</span><span class="xs:hidden">DL</span>
                    </a>
                    <?php endif; ?>
                    <?php 
                    $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                    $isDocOwner = ($document['uploaded_by'] ?? 0) == ($_SESSION['user_id'] ?? 0);
                    $isApproved = ($document['status'] ?? '') === 'approved';
                    $canEdit = (in_array($userRole, ['administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner)) && !$isApproved;
                    if ($canEdit): 
                    ?>
                    <a href="<?php echo DOCUMENTS_URL; ?>/views/edit.php?id=<?= $document['id'] ?>" 
                       class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-gray-600 dark:bg-gray-700 text-white rounded-lg hover:bg-gray-700 dark:hover:bg-gray-600 transition text-center text-sm sm:text-base shadow-md">
                        <i class="bi bi-pencil mr-1 sm:mr-2"></i><span class="hidden xs:inline">Edit</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-4 md:space-y-6">
                <!-- Document Details -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md dark:shadow-none p-4 sm:p-5 md:p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-100 <?= $isEncrypted ? 'blur-sm opacity-75' : '' ?>" <?= $isEncrypted ? 'title="This document is encrypted"' : '' ?>>
                    <h2 class="text-base sm:text-lg font-bold text-gray-800 dark:text-white mb-3 sm:mb-4">Document Information</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">File Name</label>
                            <p class="text-gray-800 dark:text-gray-300"><?= htmlspecialchars($document['file_name']) ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">File Size</label>
                            <p class="text-gray-800 dark:text-gray-300"><?= formatFileSize($document['file_size']) ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">File Type</label>
                            <p class="text-gray-800 dark:text-gray-300"><?= htmlspecialchars($document['file_type']) ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Uploaded By</label>
                            <p class="text-gray-800 dark:text-gray-300"><?= htmlspecialchars($document['uploaded_by_name']) ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</label>
                            <p class="text-gray-800 dark:text-gray-300"><?= date('F d, Y g:i A', strtotime($document['created_at'])) ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</label>
                            <p class="text-gray-800 dark:text-gray-300"><?= date('F d, Y g:i A', strtotime($document['updated_at'])) ?></p>
                        </div>
                    </div>

                    <?php if ($document['description']): ?>
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</label>
                        <p class="text-gray-800 dark:text-gray-300 mt-1"><?= nl2br(htmlspecialchars($document['description'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($tags)): ?>
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2 block">Tags</label>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($tags as $tag): ?>
                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 rounded-full text-sm">
                                <i class="bi bi-tag mr-1"></i><?= htmlspecialchars($tag['name']) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Version History -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md dark:shadow-none p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-200">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Version History</h2>
                    
                    <?php if (empty($versions)): ?>
                    <p class="text-gray-500 text-center py-8">No previous versions</p>
                    <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($versions as $version): ?>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 sm:p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 gap-3">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 dark:bg-blue-900/40 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-blue-600 dark:text-blue-400 font-bold text-sm sm:text-base">v<?= $version['version_number'] ?></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-gray-800 dark:text-gray-200 text-sm sm:text-base truncate"><?= htmlspecialchars($version['file_name']) ?></p>
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                                        <?= formatFileSize($version['file_size']) ?> • 
                                        <?= date('M d, Y', strtotime($version['created_at'])) ?>
                                        <span class="hidden sm:inline">• by <?= htmlspecialchars($version['created_by_name']) ?></span>
                                    </p>
                                    <?php if ($version['change_description']): ?>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1"><?= htmlspecialchars($version['change_description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="downloadVersion(<?= $version['id'] ?>)" 
                                        class="px-3 py-1 text-sm text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded">
                                    <i class="bi bi-download"></i>
                                </button>
                                <?php if (in_array($userRole, ['administrator', 'admin', 'officer'])): ?>
                                <button onclick="revertVersion(<?= $document['id'] ?>, <?= $version['version_number'] ?>)" 
                                        class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
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
            <div class="space-y-4 md:space-y-6">
                <!-- Related Documents -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md dark:shadow-none p-4 sm:p-5 md:p-6">
                    <h2 class="text-base sm:text-lg font-bold text-gray-800 dark:text-white mb-3 sm:mb-4">Related Documents</h2>
                    
                    <?php if (empty($links) && empty($incomingLinks)): ?>
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4 text-sm">No related documents</p>
                    <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($links as $link): ?>
                        <a href="<?php echo DOCUMENTS_URL; ?>/views/view.php?id=<?= $link['linked_document_id'] ?>" 
                           class="block p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <span class="text-xs text-gray-500 dark:text-gray-400"><?= e(getLinkTypeLabel($link['link_type'])) ?></span>
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mt-1"><?= htmlspecialchars($link['linked_title']) ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?= e($link['linked_reference']) ?></p>
                                </div>
                                <span class="badge <?= getStatusBadge($link['linked_status']) ?>">
                                    <?= e(ucfirst($link['linked_status'])) ?>
                                </span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                        
                        <?php foreach ($incomingLinks as $link): ?>
                        <a href="<?php echo DOCUMENTS_URL; ?>/views/view.php?id=<?= $link['document_id'] ?>" 
                           class="block p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <span class="text-xs text-gray-500 dark:text-gray-400"><?= e(getLinkTypeLabel($link['link_type'])) ?> (incoming)</span>
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mt-1"><?= htmlspecialchars($link['title']) ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?= e($link['reference_number']) ?></p>
                                </div>
                                <span class="badge <?= getStatusBadge($link['status']) ?>">
                                    <?= e(ucfirst($link['status'])) ?>
                                </span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md dark:shadow-none p-4 sm:p-5 md:p-6">
                    <h2 class="text-base sm:text-lg font-bold text-gray-800 dark:text-white mb-3 sm:mb-4">Quick Actions</h2>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-1 gap-2">
                        <button onclick="shareDocument()" class="px-3 sm:px-4 py-2 text-left text-xs sm:text-sm hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition flex items-center text-gray-700 dark:text-gray-200">
                            <i class="bi bi-share mr-2 text-gray-600 dark:text-gray-400"></i><span class="hidden xs:inline">Share </span>Document
                        </button>
                        <button onclick="printDocument()" class="px-3 sm:px-4 py-2 text-left text-xs sm:text-sm hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition flex items-center text-gray-700 dark:text-gray-200">
                            <i class="bi bi-printer mr-2 text-gray-600 dark:text-gray-400"></i><span class="hidden xs:inline">Print </span>Document
                        </button>
                        <button onclick="viewHistory()" class="px-3 sm:px-4 py-2 text-left text-xs sm:text-sm hover:bg-gray-100 rounded-lg transition flex items-center">
                            <i class="bi bi-clock-history mr-2 text-gray-600"></i><span class="hidden xs:inline">Activity </span>History
                        </button>
                        <?php 
                        $canDelete = (in_array($userRole, ['administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner)) && !$isApproved;
                        if ($canDelete): 
                        ?>
                        <hr class="my-2 col-span-2 sm:col-span-1">
                        <button onclick="deleteDocument(<?= $document['id'] ?>)" 
                                class="col-span-2 sm:col-span-1 px-3 sm:px-4 py-2 text-left text-xs sm:text-sm text-red-600 hover:bg-red-50 rounded-lg transition flex items-center">
                            <i class="bi bi-trash mr-2"></i>Delete Document
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- AI Research Tools -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-4 sm:p-5 md:p-6 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 sm:mb-4 flex items-center">
                        <i class="bi bi-cpu-fill mr-2 text-red-600"></i> AI Research Tools
                    </h2>
                    
                    <div class="space-y-3">
                        <button onclick="generateAISummary(<?= $document['id'] ?>)" 
                                id="summarize-btn"
                                class="w-full px-4 py-3 bg-red-600 dark:bg-red-700 text-white dark:text-white rounded-xl hover:bg-red-700 dark:hover:bg-red-600 transition shadow-sm flex items-center justify-center font-bold group">
                            <i class="bi bi-magic mr-2 group-hover:animate-pulse text-white dark:text-white"></i> 
                            <span class="text-white dark:text-white">Summarize with AI</span>
                        </button>
                        
                        <button onclick="findSimilarDocs(<?= $document['id'] ?>)" 
                                id="similar-btn"
                                class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition shadow-sm flex items-center justify-center font-bold">
                            <i class="bi bi-diagram-3 mr-2"></i> Find Similar Documents
                        </button>
                    </div>
                    
                    <div id="ai-results" class="mt-4 hidden overflow-hidden transition-all duration-300">
                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 shadow-inner">
                            <div id="ai-content" class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<script>
function downloadVersion(versionId) {
    window.location.href = `../../document-management/api/download-version.php?id=${versionId}`;
}

async function generateAISummary(id) {
    const btn = document.getElementById('summarize-btn');
    const results = document.getElementById('ai-results');
    const content = document.getElementById('ai-content');
    
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin mr-2"></i> Analyzing...';
    
    try {
        const response = await fetch(`../../research-analysis/api/summarize.php?id=${id}`);
        const data = await response.json();
        
        results.classList.remove('hidden');
        if (data.success) {
            content.innerHTML = `
                <div class="font-bold text-red-800 mb-2 flex items-center">
                    <i class="bi bi-journal-text mr-2"></i> Executive Summary
                </div>
                <div class="prose prose-sm max-w-none">
                    ${data.summary.replace(/\*/g, '').replace(/\n/g, '<br>')}
                </div>
            `;
        } else {
            content.innerHTML = `<p class="text-red-500"><i class="bi bi-exclamation-triangle mr-1"></i> ${data.error}</p>`;
        }
    } catch (error) {
        content.innerHTML = '<p class="text-red-500">Failed to connect to AI service.</p>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function findSimilarDocs(id) {
    const btn = document.getElementById('similar-btn');
    const results = document.getElementById('ai-results');
    const content = document.getElementById('ai-content');
    
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin mr-2"></i> Searching...';
    
    try {
        const response = await fetch(`../../research-analysis/api/similar.php?id=${id}`);
        const data = await response.json();
        
        results.classList.remove('hidden');
        if (data.success && data.results.length > 0) {
            let html = `
                <div class="font-bold text-red-800 mb-3 flex items-center">
                    <i class="bi bi-layers-half mr-2"></i> Similar Documents
                </div>
                <div class="space-y-2">
            `;
            
            data.results.forEach(doc => {
                html += `
                    <a href="view.php?id=${doc.id}" class="block p-2 rounded hover:bg-red-50 border border-transparent hover:border-red-100 transition-all">
                        <div class="flex justify-between items-start">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-gray-900 truncate">${doc.title}</p>
                                <p class="text-[10px] text-gray-500 font-mono">${doc.reference_number}</p>
                            </div>
                            <span class="text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded-full font-bold ml-2">
                                ${doc.score}%
                            </span>
                        </div>
                    </a>
                `;
            });
            
            html += '</div>';
            content.innerHTML = html;
        } else {
            content.innerHTML = '<p class="text-gray-500 italic py-4 text-center">No similar documents found.</p>';
        }
    } catch (error) {
        content.innerHTML = '<p class="text-red-500">Search service unavailable.</p>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
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

function promptPasswordForAccess(action) {
    const password = prompt('This document is <?= $isConfidential ? 'confidential' : 'encrypted' ?>. Please enter your password to ' + action + ':');
    if (password) {
        // Send password to server for verification
        fetch('<?php echo DOCUMENTS_URL; ?>/api/verify-document-access.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                document_id: <?= $document['id'] ?>,
                password: password,
                action: action
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Password verified, proceed with action
                if (action === 'download') {
                    window.location.href = '<?php echo DOCUMENTS_URL; ?>/api/download.php?id=<?= $document['id'] ?>&token=' + data.token;
                } else if (action === 'view') {
                    // Remove blur effect and show document details
                    document.querySelector('.blur-sm')?.classList.remove('blur-sm', 'opacity-75');
                    alert('Document unlocked. You can now view the details.');
                }
            } else {
                alert('Incorrect password. Access denied.');
            }
        })
        .catch(error => {
            alert('Error verifying password. Please try again.');
        });
    }
}

function promptPasswordForDownload() {
    promptPasswordForAccess('download');
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
