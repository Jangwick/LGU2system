<?php
session_start();
require_once __DIR__ . '/../controllers/AuditController.php';

$controller = new AuditController();

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $controller->exportCSV();
    exit;
}

$data = $controller->index();
$stats = $controller->getStatistics();

$pageTitle = 'Audit Logs';
$currentPage = 'audit';
require_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php require_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php require_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-6 text-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold mb-2">Audit Logs</h1>
                    <p class="text-red-100">System activity and security audit trail</p>
                </div>
                <a href="?export=csv&<?php echo http_build_query($data['filters']); ?>" 
                   class="bg-white text-red-600 px-6 py-3 rounded-lg font-semibold hover:bg-red-50 transition shadow-md flex items-center">
                    <i class="bi bi-download mr-2"></i> Export CSV
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-database-fill text-red-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600">Total Logs</div>
                        <div class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total_logs']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-calendar-day text-green-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600">Today</div>
                        <div class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['logs_today']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-calendar-week text-indigo-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600">This Week</div>
                        <div class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['logs_this_week']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-person-fill text-amber-600 text-4xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-600">Most Active</div>
                        <div class="font-semibold text-gray-900 truncate" title="<?php echo htmlspecialchars($stats['most_active_user']); ?>">
                            <?php echo htmlspecialchars($stats['most_active_user']); ?>
                        </div>
                        <div class="text-sm text-gray-500"><?php echo number_format($stats['most_active_count']); ?> actions</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                    <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Users</option>
                        <?php foreach ($data['users'] as $user): ?>
                            <option value="<?php echo $user['id']; ?>" 
                                    <?php echo $data['filters']['user_id'] == $user['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                    <select name="action" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Actions</option>
                        <?php foreach ($data['actions'] as $action): ?>
                            <option value="<?php echo $action; ?>" 
                                    <?php echo $data['filters']['action'] == $action ? 'selected' : ''; ?>>
                                <?php echo ucfirst($action ?? ''); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Table</label>
                    <select name="table_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Tables</option>
                        <?php foreach ($data['tables'] as $table): ?>
                            <option value="<?php echo $table; ?>" 
                                    <?php echo $data['filters']['table_name'] == $table ? 'selected' : ''; ?>>
                                <?php echo str_replace('_', ' ', ucfirst($table ?? '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           value="<?php echo htmlspecialchars($data['filters']['date_from'] ?? ''); ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           value="<?php echo htmlspecialchars($data['filters']['date_to'] ?? ''); ?>">
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                        <i class="bi bi-funnel mr-1"></i> Filter
                    </button>
                </div>
            </form>
            
            <!-- Search -->
            <form method="GET" class="mt-4">
                <div class="flex gap-2">
                    <input type="text" name="search" 
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="Search by description, user..." 
                           value="<?php echo htmlspecialchars($data['filters']['search'] ?? ''); ?>">
                    <button type="submit" 
                            class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <?php if (!empty($data['filters']['search'])): ?>
                        <a href="?" 
                           class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                            <i class="bi bi-x"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Activity Logs 
                        <span class="ml-2 px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded-full"><?php echo number_format($data['total']); ?> records</span>
                    </h2>
                    <div class="text-sm text-gray-600">
                        Showing <?php echo (($data['page'] - 1) * $data['perPage']) + 1; ?> 
                        to <?php echo min($data['page'] * $data['perPage'], $data['total']); ?>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($data['logs'])): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <i class="bi bi-inbox text-gray-400 text-5xl block mb-3"></i>
                                    <p class="text-gray-500">No audit logs found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['logs'] as $log): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($log['full_name'] ?? 'Unknown'); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($log['username'] ?? ''); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $actionClass = match($log['action']) {
                                            'create' => 'bg-green-100 text-green-800',
                                            'update' => 'bg-blue-100 text-blue-800',
                                            'delete' => 'bg-red-100 text-red-800',
                                            'login' => 'bg-indigo-100 text-indigo-800',
                                            'logout' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $actionClass; ?>">
                                            <?php echo ucfirst($log['action'] ?? ''); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo str_replace('_', ' ', ucfirst($log['table_name'] ?? '')); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($log['description']); ?>
                                        <?php if ($log['record_id']): ?>
                                            <span class="text-gray-500">(ID: <?php echo $log['record_id']; ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">
                                        <?php echo htmlspecialchars($log['ip_address']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($data['totalPages'] > 1): ?>
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <nav class="flex justify-center">
                        <ul class="flex items-center space-x-2">
                            <li>
                                <a href="?page=<?php echo $data['page'] - 1; ?>&<?php echo http_build_query($data['filters']); ?>" 
                                   class="<?php echo $data['page'] <= 1 ? 'pointer-events-none opacity-50' : 'hover:bg-gray-200'; ?> px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 transition-colors">
                                    Previous
                                </a>
                            </li>
                            
                            <?php for ($i = max(1, $data['page'] - 2); $i <= min($data['totalPages'], $data['page'] + 2); $i++): ?>
                                <li>
                                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($data['filters']); ?>" 
                                       class="<?php echo $i == $data['page'] ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-200'; ?> px-4 py-2 rounded-lg border border-gray-300 transition-colors">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li>
                                <a href="?page=<?php echo $data['page'] + 1; ?>&<?php echo http_build_query($data['filters']); ?>" 
                                   class="<?php echo $data['page'] >= $data['totalPages'] ? 'pointer-events-none opacity-50' : 'hover:bg-gray-200'; ?> px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 transition-colors">
                                    Next
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

</div>

<?php require_once __DIR__ . '/../../core/layouts/footer.php'; ?>

<script>
// Handle export CSV
document.querySelector('a[href*="export=csv"]')?.addEventListener('click', function(e) {
    // Let the link work normally for download
});
</script>
