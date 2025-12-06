<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Get sessions list
$statusFilter = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';

$where = "1=1";
$params = [];

if ($statusFilter) {
    $where .= " AND vs.status = ?";
    $params[] = $statusFilter;
}

if ($searchQuery) {
    $where .= " AND (vs.title LIKE ? OR vs.session_number LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$sessions = dbFetchAll(
    "SELECT vs.*, u.full_name as created_by_name,
            (SELECT COUNT(*) FROM session_documents WHERE session_id = vs.id) as document_count,
            (SELECT COUNT(*) FROM session_attendees WHERE session_id = vs.id AND status = 'present') as attendee_count
     FROM voting_sessions vs
     LEFT JOIN users u ON vs.created_by = u.id
     WHERE $where
     ORDER BY vs.session_date DESC, vs.start_time DESC",
    $params
);

$pageTitle = 'Voting Sessions';
$currentPage = 'voting-sessions';
$breadcrumbs = [
    ['label' => 'Voting', 'url' => '#'],
    ['label' => 'Sessions']
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
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Voting Sessions</h1>
                <p class="text-gray-600 text-sm mt-1">Manage and monitor all legislative voting sessions</p>
            </div>
            <?php if (hasRole(['admin', 'secretary'])): ?>
            <a href="create-session.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                <i class="bi bi-plus-circle mr-2"></i>
                Create New Session
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-md p-4 mb-6">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" 
                               placeholder="Search by title or session number..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                <div>
                    <select name="status" class="w-full md:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">All Statuses</option>
                        <option value="scheduled" <?php echo $statusFilter === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="in_progress" <?php echo $statusFilter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="bi bi-filter mr-1"></i> Filter
                </button>
                <?php if ($statusFilter || $searchQuery): ?>
                <a href="sessions.php" class="text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="bi bi-x-circle mr-1"></i> Clear
                </a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-3xl font-bold text-red-600"><?php echo count(array_filter($sessions, fn($s) => $s['status'] === 'scheduled')); ?></div>
                <div class="text-gray-500 text-sm">Scheduled</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-3xl font-bold text-green-600"><?php echo count(array_filter($sessions, fn($s) => $s['status'] === 'in_progress')); ?></div>
                <div class="text-gray-500 text-sm">In Progress</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-3xl font-bold text-purple-600"><?php echo count(array_filter($sessions, fn($s) => $s['status'] === 'completed')); ?></div>
                <div class="text-gray-500 text-sm">Completed</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-3xl font-bold text-gray-500"><?php echo count($sessions); ?></div>
                <div class="text-gray-500 text-sm">Total Sessions</div>
            </div>
        </div>
        
        <!-- Sessions List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <?php if (empty($sessions)): ?>
                <div class="p-12 text-center">
                    <i class="bi bi-calendar-x text-5xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700 mb-2">No Sessions Found</h3>
                    <p class="text-gray-500 mb-4">There are no voting sessions matching your criteria.</p>
                    <?php if (hasRole(['admin', 'secretary'])): ?>
                    <a href="create-session.php" class="inline-flex items-center bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                        <i class="bi bi-plus-circle mr-2"></i> Create Your First Session
                    </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Documents</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Attendees</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($sessions as $session): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="bg-red-100 rounded-lg p-2 mr-3">
                                                <i class="bi bi-calendar-event text-red-600 text-xl"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($session['title']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($session['session_number']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo formatDate($session['session_date'], 'M d, Y'); ?></div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('h:i A', strtotime($session['start_time'])); ?>
                                            <?php if ($session['end_time']): ?>
                                             - <?php echo date('h:i A', strtotime($session['end_time'])); ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center min-w-[32px] px-2 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">
                                            <?php echo $session['document_count']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center min-w-[32px] px-2 py-1 text-sm font-medium bg-purple-100 text-purple-800 rounded-full">
                                            <?php echo $session['attendee_count']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php
                                        $statusColors = [
                                            'scheduled' => 'bg-red-100 text-red-800',
                                            'in_progress' => 'bg-green-100 text-green-800',
                                            'completed' => 'bg-purple-100 text-purple-800',
                                            'cancelled' => 'bg-red-100 text-red-800'
                                        ];
                                        $statusClass = $statusColors[$session['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full <?php echo $statusClass; ?>">
                                            <?php if ($session['status'] === 'in_progress'): ?>
                                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                            <?php endif; ?>
                                            <?php echo ucfirst(str_replace('_', ' ', $session['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="session-details.php?id=<?php echo $session['id']; ?>" 
                                               class="text-red-600 hover:text-red-800 p-1.5" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($session['status'] === 'in_progress' && hasRole(['councilor', 'admin'])): ?>
                                            <a href="cast-vote.php?session=<?php echo $session['id']; ?>" 
                                               class="text-green-600 hover:text-green-800 p-1.5" title="Cast Vote">
                                                <i class="bi bi-hand-thumbs-up"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (hasRole(['admin', 'secretary'])): ?>
                                            <a href="edit-session.php?id=<?php echo $session['id']; ?>" 
                                               class="text-yellow-600 hover:text-yellow-800 p-1.5" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($session['status'] === 'completed'): ?>
                                            <a href="results.php?session=<?php echo $session['id']; ?>" 
                                               class="text-purple-600 hover:text-purple-800 p-1.5" title="View Results">
                                                <i class="bi bi-bar-chart"></i>
                                            </a>
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
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
