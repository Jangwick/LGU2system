<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Check authentication and permissions
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

if (!hasRole(['admin', 'secretary', 'encoder'])) {
    $_SESSION['flash_error'] = "You don't have permission to create documents.";
    header('Location: index.php');
    exit;
}

$errors = [];
$success = false;

// Get committees for dropdown
$committees = dbFetchAll("SELECT id, name FROM committees WHERE is_active = 1 ORDER BY name");

// Document types
$documentTypes = [
    'resolution' => 'Resolution',
    'ordinance' => 'Ordinance',
    'agenda' => 'Agenda',
    'minutes' => 'Minutes',
    'committee_report' => 'Committee Report',
    'other' => 'Other'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $type = $_POST['type'] ?? '';
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $committeeId = $_POST['committee_id'] ?? null;
    $tags = trim($_POST['tags'] ?? '');
    $submitForReview = isset($_POST['submit_for_review']);
    
    // Validation
    if (empty($title)) {
        $errors[] = "Document title is required.";
    }
    if (empty($type) || !array_key_exists($type, $documentTypes)) {
        $errors[] = "Please select a valid document type.";
    }
    
    if (empty($errors)) {
        try {
            // Generate document number
            $year = date('Y');
            $typePrefix = strtoupper(substr($type, 0, 3));
            $count = dbCount('documents', "type = ? AND YEAR(created_at) = ?", [$type, $year]);
            $docNumber = sprintf("%s-%s-%04d", $typePrefix, $year, $count + 1);
            
            // Determine initial status
            $status = $submitForReview ? 'under_review' : 'draft';
            
            // Insert document
            $docId = dbInsert('documents', [
                'doc_number' => $docNumber,
                'title' => $title,
                'type' => $type,
                'summary' => $summary,
                'content' => $content,
                'author_id' => $_SESSION['user_id'],
                'committee_id' => $committeeId ?: null,
                'status' => $status,
                'tags' => $tags
            ]);
            
            if ($docId) {
                // Create initial version
                dbInsert('document_versions', [
                    'document_id' => $docId,
                    'version_number' => 1,
                    'content' => $content,
                    'changes_summary' => 'Initial version',
                    'created_by' => $_SESSION['user_id']
                ]);
                
                // Log audit
                logAudit($_SESSION['user_id'], 'create', 'documents', $docId, null, [
                    'doc_number' => $docNumber,
                    'title' => $title,
                    'type' => $type,
                    'status' => $status
                ]);
                
                $_SESSION['flash_success'] = "Document created successfully!";
                header("Location: view.php?id=$docId");
                exit;
            }
        } catch (Exception $e) {
            $errors[] = "Error creating document: " . $e->getMessage();
        }
    }
}

$pageTitle = 'Create Document';
$currentPage = 'documents';
$breadcrumbs = [
    ['label' => 'Documents', 'url' => 'index.php'],
    ['label' => 'Create New']
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
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Create New Document</h1>
                <p class="text-gray-600 text-sm mt-1">Create a new legislative document</p>
            </div>
            <a href="index.php" class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-x-lg text-xl"></i>
            </a>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
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
        
        <form method="POST" class="space-y-6">
            <!-- Document Information -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="bi bi-file-earmark-text text-red-600 mr-2"></i>
                    Document Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Document Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Enter document title" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Document Type <span class="text-red-500">*</span></label>
                        <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" required>
                            <option value="">-- Select Type --</option>
                            <?php foreach ($documentTypes as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo ($_POST['type'] ?? '') === $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Committee (Optional)</label>
                        <select name="committee_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="">-- No specific committee --</option>
                            <?php foreach ($committees as $committee): ?>
                                <option value="<?php echo $committee['id']; ?>" <?php echo ($_POST['committee_id'] ?? '') == $committee['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($committee['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Summary</label>
                        <textarea name="summary" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Brief summary of the document..."><?php echo htmlspecialchars($_POST['summary'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                        <input type="text" name="tags" value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Comma-separated tags (e.g., budget, 2024, infrastructure)">
                        <p class="text-xs text-gray-500 mt-1">Separate multiple tags with commas</p>
                    </div>
                </div>
            </div>
            
            <!-- Document Content -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="bi bi-card-text text-red-600 mr-2"></i>
                    Document Content
                </h2>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea name="content" rows="15" id="content"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 font-mono text-sm"
                              placeholder="Enter document content here..."><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">You can use basic HTML formatting if needed</p>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" name="submit_for_review" id="submit_for_review" 
                           class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <label for="submit_for_review" class="ml-2 text-sm text-gray-700">Submit for review immediately</label>
                </div>
                <div class="flex gap-4">
                    <a href="index.php" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors inline-flex items-center">
                        <i class="bi bi-check-circle mr-2"></i>
                        Create Document
                    </button>
                </div>
            </div>
        </form>
    </main>

    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
