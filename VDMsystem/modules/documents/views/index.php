<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Handle search and filters
$typeFilter = $_GET['type'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Handle upload POST behavior
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasRole(['admin', 'secretary', 'encoder'])) {
    $title = trim($_POST['title'] ?? '');
    $type = $_POST['type'] ?? '';
    $summary = trim($_POST['summary'] ?? '');
    $docNumber = trim($_POST['doc_number'] ?? '');
    
    $errors = [];
    
    if (empty($title)) $errors[] = "Document title is required.";
    if (empty($type)) $errors[] = "Document type is required.";
    
    // File upload handling
    $fileName = null;
    $filePath = null;
    $fileSize = 0;
    $fileType = null;
    
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = STORAGE_PATH . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $tmpName = $_FILES['document_file']['tmp_name'];
        $originalName = basename($_FILES['document_file']['name']);
        $fileSize = $_FILES['document_file']['size'];
        $fileType = $_FILES['document_file']['type'];
        
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $fileName = uniqid('doc_', true) . '.' . $extension;
        $filePath = 'storage/documents/' . $fileName;
        
        if (!move_uploaded_file($tmpName, $uploadDir . $fileName)) {
            $errors[] = "Failed to move uploaded file.";
        }
    }
    
    if (empty($errors)) {
        if (empty($docNumber)) {
            $year = date('Y');
            $prefix = strtoupper(substr($type, 0, 3));
            $count = dbCount('documents', "type = ? AND YEAR(created_at) = ?", [$type, $year]);
            $docNumber = sprintf("%s-%s-%04d", $prefix, $year, $count + 1);
        } else {
            // Check for duplicate doc_number
            if (dbCount('documents', "doc_number = ?", [$docNumber]) > 0) {
                $errors[] = "Reference number '$docNumber' already exists in the repository. Please use a unique ID.";
            }
        }
    }
    
    if (empty($errors)) {
        $docData = [
            'doc_number' => $docNumber,
            'title' => $title,
            'description' => $summary,
            'type' => $type,
            'author_id' => $_SESSION['user_id'],
            'status' => 'draft',
            'file_name' => $originalName ?? null,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'file_type' => $fileType,
            'created_by' => $_SESSION['user_id']
        ];
        
        $docId = dbInsert('documents', $docData);
        if ($docId) {
            logAudit('document_uploaded', $_SESSION['user_id'], 'documents', 'documents', $docId, "Uploaded document: $title");
            $_SESSION['flash_success'] = "Document uploaded successfully!";
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Database error while saving document.";
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode(' ', $errors);
    }
}

$where = "1=1";
$params = [];

if ($typeFilter) {
    $where .= " AND d.type = ?";
    $params[] = $typeFilter;
}

if ($statusFilter) {
    $where .= " AND d.status = ?";
    $params[] = $statusFilter;
}

if ($searchQuery) {
    $where .= " AND (d.title LIKE ? OR d.doc_number LIKE ? OR d.description LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

// Get documents
$documents = dbFetchAll(
    "SELECT d.*, u.full_name as author_name, c.name as committee_name
     FROM documents d
     LEFT JOIN users u ON d.author_id = u.id
     LEFT JOIN committees c ON d.committee_id = c.id
     WHERE $where
     ORDER BY d.created_at DESC",
    $params
);

// Get global stats for cards
$stats = [
    'total' => dbCount('documents'),
    'draft' => dbCount('documents', "status = 'draft'"),
    'pending_vote' => dbCount('documents', "status = 'pending_vote'"),
    'approved' => dbCount('documents', "status = 'approved'"),
    'rejected' => dbCount('documents', "status = 'rejected'")
];

// Get document types for filter
$documentTypes = ['resolution', 'ordinance', 'motion', 'bill', 'report', 'other'];
$statusList = ['draft', 'under_review', 'committee_review', 'pending_vote', 'approved', 'rejected', 'archived'];

$pageTitle = 'Documents';
$currentPage = 'documents';
$breadcrumbs = [
    ['label' => 'Documents']
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
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6 animate-fade-in" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['flash_success']; ?></span>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6 animate-fade-in" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['flash_error']; ?></span>
                <?php unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="relative flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight">Document Management</h1>
                    <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium">Manage legislative documents, resolutions, and ordinances.</p>
                </div>
                <div class="shrink-0">
                    <?php if (hasRole(['admin', 'secretary', 'encoder'])): ?>
                    <button onclick="openUploadModal()" class="!bg-white !text-red-600 hover:!bg-gray-50 px-6 py-2.5 rounded-xl font-bold shadow-lg transition-all duration-500 transform hover:-translate-y-0.5 flex items-center group border border-red-600">
                        <i class="bi bi-cloud-arrow-up mr-2"></i>
                        Upload Repository
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-[2.5rem] shadow-md border border-slate-200/60 p-8 mb-8">
            <form method="GET" class="space-y-6">
                <!-- Search -->
                <div class="relative group">
                    <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-red-600 transition-colors">
                        <i class="bi bi-search font-bold text-lg"></i>
                    </div>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" 
                           placeholder="Search documents..." 
                           class="w-full pl-16 pr-6 py-5 bg-slate-50/50 border border-slate-200 rounded-[1.5rem] focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-bold text-sm transition-all shadow-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Type -->
                    <div class="relative group">
                        <select name="type" class="w-full pl-6 pr-12 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-bold text-sm transition-all appearance-none cursor-pointer">
                            <option value="">All Types</option>
                            <?php foreach ($documentTypes as $type): ?>
                                <option value="<?php echo $type; ?>" <?php echo $typeFilter === $type ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(str_replace('_', ' ', $type)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="bi bi-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none transition-transform group-focus-within:rotate-180"></i>
                    </div>
                    
                    <!-- Status -->
                    <div class="relative group">
                        <select name="status" class="w-full pl-6 pr-12 py-4 bg-slate-50/50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-bold text-sm transition-all appearance-none cursor-pointer">
                            <option value="">All Status</option>
                            <?php foreach ($statusList as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo $statusFilter === $status ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="bi bi-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none transition-transform group-focus-within:rotate-180"></i>
                    </div>
                </div>

                <!-- Filter Action Bar -->
                <div class="relative">
                    <button type="submit" class="w-full py-3.5 bg-slate-900 text-white rounded-xl font-black uppercase tracking-[0.3em] text-[10px] flex items-center justify-center gap-3 transition-all hover:bg-slate-800 hover:shadow-lg active:scale-[0.995]">
                        <i class="bi bi-funnel-fill text-xs opacity-60"></i> Filter
                    </button>
                </div>

                <?php if ($typeFilter || $statusFilter || $searchQuery): ?>
                <div class="flex justify-start">
                    <a href="index.php" class="inline-flex items-center text-xs font-black text-slate-400 hover:text-red-600 uppercase tracking-[0.2em] transition-colors group">
                        <i class="bi bi-x-circle-fill mr-2 text-sm opacity-60 group-hover:opacity-100"></i> Clear Filters
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-3 text-center border-t-4 border-slate-500">
                <div class="text-2xl font-bold text-gray-800"><?php echo $stats['total']; ?></div>
                <div class="text-xs text-gray-500">Total</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center border-t-4 border-gray-400">
                <div class="text-2xl font-bold text-gray-500"><?php echo $stats['draft']; ?></div>
                <div class="text-xs text-gray-500">Draft</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center border-t-4 border-purple-500">
                <div class="text-2xl font-bold text-purple-600"><?php echo $stats['pending_vote']; ?></div>
                <div class="text-xs text-gray-500">Pending Vote</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center border-t-4 border-green-500">
                <div class="text-2xl font-bold text-green-600"><?php echo $stats['approved']; ?></div>
                <div class="text-xs text-gray-500">Approved</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center border-t-4 border-red-500">
                <div class="text-2xl font-bold text-red-600"><?php echo $stats['rejected']; ?></div>
                <div class="text-xs text-gray-500">Rejected</div>
            </div>
        </div>
        
        <!-- Documents List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <?php if (empty($documents)): ?>
                <div class="p-12 text-center">
                    <i class="bi bi-file-earmark-x text-5xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700 mb-2">No Documents Found</h3>
                    <p class="text-gray-500 mb-4">There are no documents matching your criteria.</p>
                    <?php if (hasRole(['admin', 'secretary', 'encoder'])): ?>
                    <button onclick="openUploadModal()" class="inline-flex items-center bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                        <i class="bi bi-plus-circle mr-2"></i> Create Document
                    </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($documents as $doc): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="bg-red-100 rounded-lg p-2 mr-3">
                                                <i class="bi bi-file-earmark-text text-red-600 text-xl"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($doc['title']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($doc['doc_number']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                            <?php echo ucfirst(str_replace('_', ' ', $doc['type'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($doc['author_name'] ?? 'Unknown'); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full <?php echo getStatusBadgeClass($doc['status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $doc['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo formatDate($doc['created_at'], 'M d, Y'); ?>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="view.php?id=<?php echo $doc['id']; ?>" 
                                               class="text-red-600 hover:text-red-800 p-1.5" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (hasRole(['admin', 'secretary', 'encoder']) && in_array($doc['status'], ['draft', 'under_review'])): ?>
                                            <a href="edit.php?id=<?php echo $doc['id']; ?>" 
                                               class="text-yellow-600 hover:text-yellow-800 p-1.5" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (hasRole(['admin']) && $doc['status'] === 'draft'): ?>
                                            <button onclick="deleteDocument(<?php echo $doc['id']; ?>)" 
                                                    class="text-red-600 hover:text-red-800 p-1.5" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
</main>

<!-- Upload Repository Modal -->
<div id="upload-modal" class="fixed inset-0 z-[100] hidden overflow-hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300" onclick="closeUploadModal()"></div>
    
    <div class="flex items-end sm:items-center justify-center min-h-screen p-0 sm:p-4">
        <div class="relative bg-white dark:bg-slate-950 w-full max-w-4xl rounded-t-[2rem] sm:rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all translate-y-full opacity-0 sm:opacity-100 sm:translate-y-0 duration-300 border border-transparent dark:border-slate-800 flex flex-col max-h-[92vh]">
            
            <!-- Mobile Drag Handle -->
            <div class="sm:hidden w-full flex justify-center pt-4 pb-2 bg-white dark:bg-slate-950">
                <div class="w-12 h-1.5 bg-slate-200 dark:bg-slate-800 rounded-full"></div>
            </div>

            <!-- Premium Modal Header -->
            <div class="bg-white dark:bg-slate-950 px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 relative z-10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-2xl flex items-center justify-center text-xl shadow-sm border border-red-100 dark:border-red-900/10">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight uppercase">Upload Repository</h2>
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-widest opacity-70">Ingest New Legislative Record</p>
                    </div>
                </div>
                <button onclick="closeUploadModal()" class="w-11 h-11 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-900 text-slate-400 hover:text-red-600 transition-all border border-slate-200 dark:border-slate-800">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="flex-1 overflow-y-auto custom-scrollbar p-6 md:p-10 bg-white dark:bg-slate-950">
                <form id="upload-form-modal" method="POST" enctype="multipart/form-data" class="space-y-10">
                    <!-- File Selection Area -->
                    <div>
                        <h3 class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-6 flex items-center">
                            <span class="w-1.5 h-4 bg-red-600 rounded-full mr-3 shadow-[0_0_10px_rgba(220,38,38,0.3)]"></span>
                            Document Binary
                        </h3>
                        
                        <!-- Drag & Drop Zone -->
                        <div id="drop-zone-modal" class="relative group cursor-pointer">
                            <div class="absolute inset-0 bg-red-600/5 dark:bg-red-600/5 rounded-[2rem] blur-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="relative bg-white dark:bg-slate-900/30 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-[2rem] p-10 md:p-14 text-center hover:border-red-500/50 hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-all duration-500 overflow-hidden">
                                <div class="relative z-10">
                                    <div class="w-24 h-24 bg-white dark:bg-slate-900 rounded-3xl shadow-sm flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-500 border border-slate-100 dark:border-slate-800">
                                        <i class="bi bi-file-earmark-plus-fill text-4xl text-slate-300 group-hover:text-red-500 transition-colors"></i>
                                    </div>
                                    <h4 class="text-xl font-black text-slate-900 dark:text-slate-100 mb-2">Ingest New Document</h4>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-8 max-w-xs mx-auto font-medium lead-relaxed">Drop your legislative files here or browse for local records.</p>
                                    
                                    <input type="file" id="file-input-modal" name="document_file" accept=".pdf,.doc,.docx" class="hidden">
                                    <button type="button" onclick="document.getElementById('file-input-modal').click()" class="bg-slate-900 dark:bg-white dark:text-slate-900 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-[0.15em] text-[11px] shadow-xl hover:shadow-slate-200 dark:hover:shadow-none transition-all active:scale-95 inline-flex items-center gap-2">
                                        <i class="bi bi-plus-lg"></i> Choose File
                                    </button>
                                    
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-8 uppercase tracking-[0.2em] font-black opacity-60">
                                        PDF, DOCX, XLS (MAX 50MB) 
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- File Preview Card (Hidden initially) -->
                        <div id="file-preview-modal" class="hidden mt-6 p-6 bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl animate-in fade-in slide-in-from-top-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-5">
                                    <div class="w-14 h-14 bg-red-100 dark:bg-red-900/30 rounded-2xl flex items-center justify-center text-red-600 dark:text-red-400 text-2xl shadow-inner">
                                        <i class="bi bi-file-earmark-pdf-fill"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p id="file-name-modal" class="text-base font-black text-slate-900 dark:text-slate-100 truncate"></p>
                                        <p id="file-size-modal" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-0.5"></p>
                                    </div>
                                </div>
                                <button type="button" id="remove-file-modal" class="w-12 h-12 flex items-center justify-center text-slate-400 hover:text-red-600 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 transition-all active:scale-90">
                                    <i class="bi bi-trash-fill text-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Metadata Grid -->
                    <div>
                        <h3 class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-6 flex items-center">
                            <span class="w-1.5 h-4 bg-red-600 rounded-full mr-3 shadow-[0_0_10px_rgba(220,38,38,0.3)]"></span>
                            Legislative Metadata
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-2">
                            <!-- Type -->
                            <div class="space-y-3">
                                <label class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em] pl-1">Document Type <span class="text-red-600 ml-0.5">*</span></label>
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-red-600 transition-colors">
                                        <i class="bi bi-collection-fill"></i>
                                    </div>
                                    <select name="type" required class="w-full pl-14 pr-10 py-4 bg-white dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 text-slate-700 dark:text-slate-200 rounded-[1.25rem] focus:bg-white dark:focus:bg-slate-900 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none appearance-none font-bold text-sm transition-all cursor-pointer">
                                        <option value="">Select Category</option>
                                        <option value="ordinance">Ordinance</option>
                                        <option value="resolution">Resolution</option>
                                        <option value="motion">Motion</option>
                                        <option value="bill">Bill</option>
                                        <option value="report">Committee Report</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <i class="bi bi-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none transition-transform group-focus-within:rotate-180"></i>
                                </div>
                            </div>
                            
                            <!-- Reference -->
                            <div class="space-y-3">
                                <label class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em] pl-1">Reference Number</label>
                                <div class="relative group">
                                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-red-600 transition-colors">
                                        <i class="bi bi-hash"></i>
                                    </div>
                                    <input type="text" name="doc_number" placeholder="Leave blank for auto-gen" class="w-full pl-14 pr-5 py-4 bg-white dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 text-slate-700 dark:text-slate-200 rounded-[1.25rem] focus:bg-white dark:focus:bg-slate-900 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-bold text-sm transition-all">
                                </div>
                            </div>
                            
                            <!-- Title -->
                            <div class="md:col-span-2 space-y-3">
                                <label class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em] pl-1">Legislative Title <span class="text-red-600 ml-0.5">*</span></label>
                                <input type="text" name="title" required placeholder="Enter the official title of the record..." class="w-full px-6 py-4 bg-white dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 text-slate-700 dark:text-slate-200 rounded-[1.25rem] focus:bg-white dark:focus:bg-slate-900 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-bold text-sm transition-all sm:text-base">
                            </div>
                            
                            <!-- Summary -->
                            <div class="md:col-span-2 space-y-3">
                                <label class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em] pl-1">Executive Summary</label>
                                <textarea name="summary" rows="4" placeholder="Briefly describe the purpose or content of this document..." class="w-full px-6 py-5 bg-white dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 text-slate-700 dark:text-slate-200 rounded-[1.5rem] focus:bg-white dark:focus:bg-slate-900 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-medium text-sm transition-all resize-none lead-relaxed"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row items-center justify-end gap-5 pt-8 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" onclick="closeUploadModal()" class="w-full sm:w-auto order-2 sm:order-1 px-10 py-4 text-xs font-black text-slate-400 dark:text-slate-500 hover:text-red-600 transition-all uppercase tracking-[0.2em] bg-transparent">
                            Cancel Upload
                        </button>
                        <button type="submit" class="w-full sm:w-auto order-1 sm:order-2 px-12 py-4.5 bg-red-600 hover:bg-red-700 text-white rounded-[1.5rem] font-black uppercase tracking-[0.15em] text-[11px] shadow-2xl shadow-red-200 dark:shadow-none transition-all active:scale-95 group flex items-center justify-center gap-3">
                            Submit Repository <i class="bi bi-chevron-right group-hover:translate-x-1 transition-transform font-black"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Modal Toggle Logic
function openUploadModal() {
    const modal = document.getElementById('upload-modal');
    const box = modal.querySelector('.relative.bg-white');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Animate in
    setTimeout(() => {
        box.classList.remove('translate-y-full', 'opacity-0');
        box.classList.add('translate-y-0', 'opacity-100');
    }, 50);
}

function closeUploadModal() {
    const modal = document.getElementById('upload-modal');
    const box = modal.querySelector('.relative.bg-white');
    
    // Animate out
    box.classList.add('translate-y-full', 'opacity-0');
    box.classList.remove('translate-y-0', 'opacity-100');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }, 300);
}

// File Interaction Logic
const dropZone = document.getElementById('drop-zone-modal');
const fileInput = document.getElementById('file-input-modal');
const filePreview = document.getElementById('file-preview-modal');
const fileNameLabel = document.getElementById('file-name-modal');
const fileSizeLabel = document.getElementById('file-size-modal');
const removeBtn = document.getElementById('remove-file-modal');

// Preventing defaults
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eName => {
    dropZone.addEventListener(eName, (e) => {
        e.preventDefault();
        e.stopPropagation();
    }, false);
});

// Drag effects
['dragenter', 'dragover'].forEach(eName => {
    dropZone.addEventListener(eName, () => {
        dropZone.querySelector('.relative').classList.add('scale-95');
        dropZone.querySelector('.border-dashed').classList.add('border-red-500', 'bg-red-50/50', 'dark:bg-red-900/10');
    }, false);
});

['dragleave', 'drop'].forEach(eName => {
    dropZone.addEventListener(eName, () => {
        dropZone.querySelector('.relative').classList.remove('scale-95');
        dropZone.querySelector('.border-dashed').classList.remove('border-red-500', 'bg-red-50/50', 'dark:bg-red-900/10');
    }, false);
});

// Handle drop
dropZone.addEventListener('drop', (e) => {
    const files = e.dataTransfer.files;
    if (files.length) {
        fileInput.files = files;
        updatePreview(files[0]);
    }
});

// Handle select
fileInput.addEventListener('change', (e) => {
    if (e.target.files.length) {
        updatePreview(e.target.files[0]);
    }
});

function updatePreview(file) {
    fileNameLabel.textContent = file.name;
    fileSizeLabel.textContent = formatSize(file.size);
    
    dropZone.classList.add('hidden');
    filePreview.classList.remove('hidden');
}

removeBtn.addEventListener('click', () => {
    fileInput.value = '';
    filePreview.classList.add('hidden');
    dropZone.classList.remove('hidden');
});

function formatSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Form Submission
document.getElementById('upload-form-modal').onclick = (e) => {
    if (e.target.closest('button[type="submit"]')) {
        // Validation could go here
    }
};

function deleteDocument(id) {
    if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        window.location.href = 'delete.php?id=' + id;
    }
}
</script>
</main>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
