<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Load controller
require_once __DIR__ . '/../controllers/DocumentController.php';
$controller = new DocumentController();
$data = $controller->index();

$pageTitle = 'Document Management';
$currentPage = 'documents';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => DASHBOARD_INDEX_URL],
    ['label' => 'Documents']
];

// Helper functions
function getFileIcon($mimeType) {
    if (strpos($mimeType, 'pdf') !== false) return 'bi bi-file-pdf text-red-600';
    if (strpos($mimeType, 'word') !== false) return 'bi bi-file-word text-blue-600';
    if (strpos($mimeType, 'excel') !== false || strpos($mimeType, 'spreadsheet') !== false) return 'bi bi-file-excel text-green-600';
    if (strpos($mimeType, 'powerpoint') !== false || strpos($mimeType, 'presentation') !== false) return 'bi bi-file-ppt text-orange-600';
    return 'bi bi-file-earmark text-gray-600';
}

function getFileIconClass($mimeType) {
    if (strpos($mimeType, 'pdf') !== false) return 'bg-red-100';
    if (strpos($mimeType, 'word') !== false) return 'bg-blue-100';
    if (strpos($mimeType, 'excel') !== false || strpos($mimeType, 'spreadsheet') !== false) return 'bg-green-100';
    if (strpos($mimeType, 'powerpoint') !== false || strpos($mimeType, 'presentation') !== false) return 'bg-orange-100';
    return 'bg-gray-100';
}

function getStatusBadge($status) {
    $badges = [
        'draft' => '<span class="badge badge-warning"><i class="bi bi-pencil mr-1"></i>Draft</span>',
        'pending' => '<span class="badge badge-warning"><i class="bi bi-clock mr-1"></i>Pending</span>',
        'approved' => '<span class="badge badge-success"><i class="bi bi-check-circle mr-1"></i>Approved</span>',
        'rejected' => '<span class="badge badge-danger"><i class="bi bi-x-circle mr-1"></i>Rejected</span>',
        'archived' => '<span class="badge bg-gray-100 text-gray-800"><i class="bi bi-archive mr-1"></i>Archived</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-info">' . ucfirst($status) . '</span>';
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

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <!-- Header Section -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Document Management</h1>
                    <p class="text-gray-600">Manage all legislative documents in one place</p>
                </div>
                <div class="flex gap-3">
                    <?php 
                    $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                    if (!in_array($userRole, ['viewer'])): 
                    ?>
                    <a href="create.php" class="btn-primary flex items-center">
                        <i class="bi bi-plus-circle mr-2"></i>
                        Upload Document
                    </a>
                    <?php endif; ?>
                    <div class="relative" id="export-dropdown">
                        <button onclick="toggleExportMenu()" class="btn-outline flex items-center">
                            <i class="bi bi-download mr-2"></i>
                            Export
                            <i class="bi bi-chevron-down ml-2"></i>
                        </button>
                        <div id="export-menu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="py-1">
                                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase">Export List</div>
                                <button onclick="exportList('csv')" 
                                   class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                    <i class="bi bi-filetype-csv mr-2 text-green-600"></i>
                                    Export List as CSV
                                </button>
                                <button onclick="exportList('excel')" 
                                   class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                    <i class="bi bi-file-earmark-excel mr-2 text-green-600"></i>
                                    Export List as Excel
                                </button>
                                <hr class="my-1">
                                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase">Export Files</div>
                                <button onclick="exportSelectedFiles()" 
                                   class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                    <i class="bi bi-file-earmark-zip mr-2 text-blue-600"></i>
                                    Export Selected Files (ZIP)
                                </button>
                                <button onclick="exportAllFiles()" 
                                   class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                    <i class="bi bi-archive mr-2 text-purple-600"></i>
                                    Export All Files (ZIP)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Documents</label>
                    <div class="relative">
                        <input type="text" 
                               placeholder="Search by title, reference, or keywords..." 
                               class="input-field pl-10">
                        <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                
                <!-- Document Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                    <select class="input-field">
                        <option value="">All Types</option>
                        <option value="ordinance">Ordinance</option>
                        <option value="resolution">Resolution</option>
                        <option value="session">Session Minutes</option>
                        <option value="agenda">Agenda</option>
                        <option value="committee">Committee Report</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select class="input-field">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="pending">Pending Review</option>
                        <option value="approved">Approved</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
            
            <!-- Advanced Filters Toggle -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center">
                    <i class="bi bi-funnel mr-2"></i>
                    Advanced Filters
                    <i class="bi bi-chevron-down ml-2"></i>
                </button>
            </div>
        </div>
        
        <!-- Documents Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <!-- Table Header Actions -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="select-all-top" class="w-4 h-4 text-blue-600 border-gray-300 rounded" onchange="toggleSelectAll(this)">
                        <span class="ml-2 text-sm text-gray-700">Select All</span>
                    </label>
                    <span class="text-sm text-gray-600" id="selected-count">
                        <span id="total-docs">0</span> documents found
                    </span>
                </div>
                
                <div class="flex items-center gap-2">
                    <button class="px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="bi bi-download mr-1"></i>Bulk Download
                    </button>
                    <button class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                        <i class="bi bi-trash mr-1"></i>Delete Selected
                    </button>
                </div>
            </div>
            
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" id="select-all-header" class="w-4 h-4 text-blue-600 border-gray-300 rounded" onchange="toggleSelectAll(this)">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Document
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Reference
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Size
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (isset($data['error'])): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="text-red-600">
                                        <i class="bi bi-exclamation-circle text-4xl mb-2"></i>
                                        <p><?php echo htmlspecialchars($data['message']); ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php elseif (empty($data['documents'])): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="text-gray-500">
                                        <i class="bi bi-inbox text-4xl mb-2"></i>
                                        <p>No documents found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['documents'] as $doc): ?>
                                <tr class="hover:bg-gray-50" data-document-id="<?php echo $doc['id']; ?>">
                                    <td class="px-6 py-4">
                                        <input type="checkbox" class="document-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded" value="<?php echo $doc['id']; ?>">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="<?php echo getFileIconClass($doc['file_type']); ?> rounded-lg p-2 mr-3">
                                                <i class="<?php echo getFileIcon($doc['file_type']); ?> text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($doc['title']); ?></p>
                                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($doc['file_name']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="badge badge-primary"><?php echo ucfirst($doc['document_type']); ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo htmlspecialchars($doc['reference_number']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo getStatusBadge($doc['status']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo date('M d, Y', strtotime($doc['document_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo formatFileSize($doc['file_size']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <!-- View button - All roles can view -->
                                        <button class="text-blue-600 hover:text-blue-700 mr-3" title="View" onclick="viewDocument(<?php echo $doc['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        
                                        <!-- Download button - All roles can download -->
                                        <a href="<?php echo DOCUMENTS_URL; ?>/api/download.php?id=<?php echo $doc['id']; ?>" class="text-green-600 hover:text-green-700 mr-3" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        
                                        <?php 
                                        $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
                                        $isDocOwner = ($doc['uploaded_by'] ?? 0) == ($_SESSION['user_id'] ?? 0);
                                        $canEdit = in_array($userRole, ['administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner);
                                        $canDelete = in_array($userRole, ['administrator', 'admin', 'officer']) || ($userRole === 'staff' && $isDocOwner);
                                        ?>
                                        
                                        <!-- Edit button - Staff (own docs), Officer, Admin -->
                                        <?php if ($canEdit): ?>
                                        <button class="text-gray-600 hover:text-gray-700 mr-3" title="Edit" onclick="editDocument(<?php echo $doc['id']; ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php endif; ?>
                                        
                                        <!-- Delete button - Staff (own docs), Officer, Admin -->
                                        <?php if ($canDelete): ?>
                                        <button class="text-red-600 hover:text-red-700" title="Delete" onclick="deleteDocument(<?php echo $doc['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                Nov 19, 2025
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                1.8 MB
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-700 mr-3" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="text-green-600 hover:text-green-700 mr-3" title="Download">
                                    <i class="bi bi-download"></i>
                                </button>
                                <button class="text-gray-600 hover:text-gray-700 mr-3" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-700" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        
                        <!-- More rows would be loaded dynamically -->
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if (isset($data['pagination']) && $data['pagination']['total_pages'] > 1): ?>
                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-medium"><?php echo (($data['pagination']['current_page'] - 1) * $data['pagination']['per_page']) + 1; ?></span> 
                        to <span class="font-medium"><?php echo min($data['pagination']['current_page'] * $data['pagination']['per_page'], $data['pagination']['total']); ?></span> 
                        of <span class="font-medium"><?php echo number_format($data['pagination']['total']); ?></span> results
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if ($data['pagination']['current_page'] > 1): ?>
                            <a href="?page=<?php echo $data['pagination']['current_page'] - 1; ?><?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>" 
                               class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        <?php else: ?>
                            <button class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg opacity-50 cursor-not-allowed" disabled>
                                <i class="bi bi-chevron-left"></i>
                            </button>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $data['pagination']['current_page'] - 2); $i <= min($data['pagination']['total_pages'], $data['pagination']['current_page'] + 2); $i++): ?>
                            <?php if ($i == $data['pagination']['current_page']): ?>
                                <button class="px-3 py-1.5 text-sm text-white bg-blue-600 rounded-lg"><?php echo $i; ?></button>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?><?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>" 
                                   class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($data['pagination']['current_page'] < $data['pagination']['total_pages']): ?>
                            <a href="?page=<?php echo $data['pagination']['current_page'] + 1; ?><?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>" 
                               class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <button class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg opacity-50 cursor-not-allowed" disabled>
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>

<script src="<?php echo asset('js/documents.js'); ?>"></script>
<script>
function viewDocument(id) {
    window.location.href = 'view.php?id=' + id;
}

function editDocument(id) {
    window.location.href = 'edit.php?id=' + id;
}

function deleteDocument(id) {
    if (confirm('Are you sure you want to delete this document?')) {
        fetch(App.apiUrl('documents', 'delete.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Document deleted successfully', 'success');
                location.reload();
            } else {
                showNotification(data.error || 'Failed to delete document', 'error');
            }
        })
        .catch(error => {
            showNotification('An error occurred', 'error');
        });
    }
}

function showNotification(message, type) {
    // Simple notification (you can enhance this)
    alert(message);
}

function toggleSelectAll(checkbox) {
    const documentCheckboxes = document.querySelectorAll('.document-checkbox');
    const selectAllTop = document.getElementById('select-all-top');
    const selectAllHeader = document.getElementById('select-all-header');
    
    documentCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    
    // Sync both select-all checkboxes
    selectAllTop.checked = checkbox.checked;
    selectAllHeader.checked = checkbox.checked;
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const selected = document.querySelectorAll('.document-checkbox:checked').length;
    const total = document.querySelectorAll('.document-checkbox').length;
    const countElement = document.getElementById('selected-count');
    
    if (selected > 0) {
        countElement.innerHTML = `<span class="font-semibold text-blue-600">${selected} selected</span> of ${total} documents`;
    } else {
        countElement.innerHTML = `${total} documents found`;
    }
}

function getSelectedDocumentIds() {
    const checkboxes = document.querySelectorAll('.document-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function exportSelectedFiles() {
    const selectedIds = getSelectedDocumentIds();
    
    if (selectedIds.length === 0) {
        alert('Please select at least one document to export');
        return;
    }
    
    // Close the dropdown
    document.getElementById('export-menu').classList.add('hidden');
    
    // Create export URL with selected IDs
    const url = App.apiUrl('documents', `export.php?export_type=files&ids=${selectedIds.join(',')}`);
    window.location.href = url;
}

function exportAllFiles() {
    if (!confirm('This will download all visible documents as a ZIP file. Continue?')) {
        return;
    }
    
    // Close the dropdown
    document.getElementById('export-menu').classList.add('hidden');
    
    // Get current filters from URL or form
    const searchParams = new URLSearchParams(window.location.search);
    searchParams.set('export_type', 'files');
    const url = App.apiUrl('documents', `export.php?${searchParams.toString()}`);
    window.location.href = url;
}

function exportList(format) {
    // Close the dropdown
    document.getElementById('export-menu').classList.add('hidden');
    
    // Get current filters from URL or form
    const searchParams = new URLSearchParams(window.location.search);
    searchParams.set('export_type', 'list');
    searchParams.set('format', format);
    const url = App.apiUrl('documents', `export.php?${searchParams.toString()}`);
    window.location.href = url;
}

function toggleExportMenu() {
    const menu = document.getElementById('export-menu');
    menu.classList.toggle('hidden');
}

// Close export menu when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('export-dropdown');
    const menu = document.getElementById('export-menu');
    
    if (dropdown && !dropdown.contains(event.target)) {
        menu.classList.add('hidden');
    }
});

// Update selected count when individual checkboxes change
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.document-checkbox');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
    
    // Initialize count
    const totalDocs = checkboxes.length;
    document.getElementById('total-docs').textContent = totalDocs;
});
</script>
