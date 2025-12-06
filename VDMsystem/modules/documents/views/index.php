<?php
session_start();
require_once __DIR__ . '/../../../core/config/config.php';
require_once __DIR__ . '/../../../core/config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Get filters
$typeFilter = $_GET['type'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';

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
    $where .= " AND (d.title LIKE ? OR d.doc_number LIKE ? OR d.summary LIKE ?)";
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

// Get document types for filter
$documentTypes = ['resolution', 'ordinance', 'agenda', 'minutes', 'committee_report', 'other'];
$statusList = ['draft', 'under_review', 'committee_review', 'pending_vote', 'approved', 'rejected', 'archived'];

$pageTitle = 'Documents';
$currentPage = 'documents';
$breadcrumbs = [
    ['label' => 'Documents']
];

include_once __DIR__ . '/../../../core/layouts/header.php';
?>

<!-- Sidebar -->
<?php include_once __DIR__ . '/../../../core/layouts/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Top Navbar -->
    <?php include_once __DIR__ . '/../../../core/layouts/navbar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto bg-gray-100 p-3 md:p-6">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Document Management</h1>
                <p class="text-gray-600 text-sm mt-1">Manage legislative documents, resolutions, and ordinances</p>
            </div>
            <?php if (hasRole(['admin', 'secretary', 'encoder'])): ?>
            <a href="create.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                <i class="bi bi-plus-circle mr-2"></i>
                New Document
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-md p-4 mb-6">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" 
                               placeholder="Search documents..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                <div>
                    <select name="type" class="w-full md:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Types</option>
                        <?php foreach ($documentTypes as $type): ?>
                            <option value="<?php echo $type; ?>" <?php echo $typeFilter === $type ? 'selected' : ''; ?>>
                                <?php echo ucfirst(str_replace('_', ' ', $type)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <select name="status" class="w-full md:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <?php foreach ($statusList as $status): ?>
                            <option value="<?php echo $status; ?>" <?php echo $statusFilter === $status ? 'selected' : ''; ?>>
                                <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="bi bi-filter mr-1"></i> Filter
                </button>
                <?php if ($typeFilter || $statusFilter || $searchQuery): ?>
                <a href="index.php" class="text-gray-500 hover:text-gray-700 px-4 py-2">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <div class="text-2xl font-bold text-gray-800"><?php echo count($documents); ?></div>
                <div class="text-xs text-gray-500">Total</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <div class="text-2xl font-bold text-gray-500"><?php echo count(array_filter($documents, fn($d) => $d['status'] === 'draft')); ?></div>
                <div class="text-xs text-gray-500">Draft</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <div class="text-2xl font-bold text-yellow-600"><?php echo count(array_filter($documents, fn($d) => $d['status'] === 'pending_vote')); ?></div>
                <div class="text-xs text-gray-500">Pending Vote</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <div class="text-2xl font-bold text-green-600"><?php echo count(array_filter($documents, fn($d) => $d['status'] === 'approved')); ?></div>
                <div class="text-xs text-gray-500">Approved</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <div class="text-2xl font-bold text-red-600"><?php echo count(array_filter($documents, fn($d) => $d['status'] === 'rejected')); ?></div>
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
                    <a href="create.php" class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="bi bi-plus-circle mr-2"></i> Create Document
                    </a>
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
                                            <div class="bg-blue-100 rounded-lg p-2 mr-3">
                                                <i class="bi bi-file-earmark-text text-blue-600 text-xl"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($doc['title']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($doc['doc_number']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
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
                                               class="text-blue-600 hover:text-blue-800 p-1.5" title="View">
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
</div>

<script>
function deleteDocument(id) {
    if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        // Implement delete functionality
        window.location.href = 'delete.php?id=' + id;
    }
}
</script>

<?php include_once __DIR__ . '/../../../core/layouts/footer.php'; ?>
