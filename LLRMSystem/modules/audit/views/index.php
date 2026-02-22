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
    
    <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-4 md:p-6 custom-scrollbar">
    <div class="max-w-7xl mx-auto px-2 md:px-6 lg:px-8 py-4 md:py-8">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-6 md:p-8 mb-6 text-white transform hover:scale-[1.01] transition-all duration-300 animate-fade-in">
            <div class="flex flex-col items-center text-center md:flex-row md:items-center md:justify-between md:text-left gap-6">
                <div class="animate-slide-in-left">
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">Audit Logs</h1>
                    <p class="text-red-100 font-medium opacity-90">System activity and security audit trail</p>
                </div>
                <div class="animate-slide-in-right w-full md:w-auto">
                    <a href="?export=csv&<?php echo http_build_query($data['filters']); ?>" 
                       style="background-color: #ffffff !important; color: #dc2626 !important;"
                       class="inline-flex items-center justify-center gap-2 px-8 py-3.5 font-bold rounded-xl shadow-lg hover:opacity-95 transition-all transform hover:-translate-y-1 active:scale-95 w-full md:w-auto">
                        <i class="bi bi-download text-xl !text-red-600"></i>
                        <span class="!text-red-600">Export CSV</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-600">
                        <i class="bi bi-database-fill text-xl md:text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Total Logs</div>
                        <div class="text-lg md:text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($stats['total_logs']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up md:animation-delay-100">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600">
                        <i class="bi bi-calendar-day text-xl md:text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Today</div>
                        <div class="text-lg md:text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($stats['logs_today']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up md:animation-delay-200">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600">
                        <i class="bi bi-calendar-week text-xl md:text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">This Week</div>
                        <div class="text-lg md:text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($stats['logs_this_week']); ?></div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up md:animation-delay-300">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600 text-xl md:text-2xl">
                        <i class="bi bi-person-fill text-xl md:text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Most Active</div>
                        <div class="text-sm md:text-base font-bold text-gray-900 dark:text-white truncate" title="<?php echo htmlspecialchars($stats['most_active_user']); ?>">
                            <?php echo htmlspecialchars($stats['most_active_user']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 md:p-6 mb-6 animate-fade-in-up">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">User</label>
                    <select name="user_id" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Action</label>
                    <select name="action" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
                        <option value="">All Actions</option>
                        <?php foreach ($data['actions'] as $action): ?>
                            <option value="<?php echo e($action); ?>" 
                                    <?php echo $data['filters']['action'] == $action ? 'selected' : ''; ?>>
                                <?php echo e(ucfirst($action ?? '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Table</label>
                    <select name="table_name" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
                        <option value="">All Tables</option>
                        <?php foreach ($data['tables'] as $table): ?>
                            <option value="<?php echo e($table); ?>" 
                                    <?php echo $data['filters']['table_name'] == $table ? 'selected' : ''; ?>>
                                <?php echo e(str_replace('_', ' ', ucfirst($table ?? ''))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date From</label>
                    <input type="date" name="date_from" 
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all" 
                           value="<?php echo htmlspecialchars($data['filters']['date_from'] ?? ''); ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date To</label>
                    <input type="date" name="date_to" 
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all" 
                           value="<?php echo htmlspecialchars($data['filters']['date_to'] ?? ''); ?>">
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-4 py-3.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition-all flex items-center justify-center gap-2 transform active:scale-95">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </form>
            
            <!-- Search -->
            <form method="GET" class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                        <input type="text" name="search" 
                               class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all" 
                               placeholder="Search by description, reference, or user..." 
                               value="<?php echo htmlspecialchars($data['filters']['search'] ?? ''); ?>">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" 
                                class="flex-1 sm:flex-none px-6 py-3 bg-gray-800 dark:bg-gray-700 hover:bg-black dark:hover:bg-gray-600 text-white font-semibold rounded-lg shadow-md transition-all flex items-center justify-center gap-2 transform active:scale-95">
                            Search
                        </button>
                        <?php if (!empty($data['filters']['search'])): ?>
                            <a href="?" 
                               class="px-5 py-3 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 rounded-xl transition-all flex items-center justify-center transform active:scale-95">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 animate-fade-in-up">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                        <i class="bi bi-list-ul text-red-600"></i>
                        <span>Activity Logs</span>
                        <span class="px-3 py-1 bg-red-50 dark:bg-red-900/40 text-red-600 dark:text-red-400 text-xs font-semibold rounded-full border border-red-100 dark:border-red-900/50"><?php echo number_format($data['total']); ?> records</span>
                    </h2>
                    <div class="text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700/50 px-3 py-1.5 rounded-lg border border-gray-100 dark:border-gray-600">
                        Showing <?php echo (($data['page'] - 1) * $data['perPage']) + 1; ?> to <?php echo min($data['page'] * $data['perPage'], $data['total']); ?>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date/Time</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User Details</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                        <?php if (empty($data['logs'])): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <i class="bi bi-inbox text-gray-400 text-5xl block mb-3 animate-bounce"></i>
                                    <p class="text-gray-500 animate-fade-in">No audit logs found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['logs'] as $log): ?>
                                <tr class="hover:bg-red-50 dark:hover:bg-red-900/10 transition-all duration-200 group">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200 group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors">
                                        <i class="bi bi-clock text-gray-400 mr-1 group-hover:text-red-500 transition-colors"></i>
                                        <?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center text-white text-xs font-bold mr-3 transform group-hover:scale-110 transition-transform duration-200 shadow-sm">
                                                <?php echo e(strtoupper(substr($log['full_name'] ?? 'U', 0, 1))); ?>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-gray-200 group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors"><?php echo htmlspecialchars($log['full_name'] ?? 'Unknown'); ?></div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($log['email'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $actionClass = match($log['action']) {
                                            'create' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 hover:bg-green-200',
                                            'update' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 hover:bg-blue-200',
                                            'delete' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 hover:bg-red-200',
                                            'login' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400 hover:bg-indigo-200',
                                            'logout' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200'
                                        };
                                        $actionIcon = match($log['action']) {
                                            'create' => 'bi-plus-circle',
                                            'update' => 'bi-pencil',
                                            'delete' => 'bi-trash',
                                            'login' => 'bi-box-arrow-in-right',
                                            'logout' => 'bi-box-arrow-right',
                                            default => 'bi-activity'
                                        };
                                        ?>
                                        <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full <?php echo $actionClass; ?> transform hover:scale-105 transition-all duration-200 cursor-default">
                                            <i class="bi <?php echo $actionIcon; ?> mr-1"></i>
                                            <?php echo ucfirst($log['action'] ?? ''); ?>
                                        </span>
                                    </td>
                                    <?php
                                    // Clean up description - remove JSON details if present
                                    $cleanDescription = $log['description'] ?? '';
                                    if (strpos($cleanDescription, ' | Details:') !== false) {
                                        $cleanDescription = explode(' | Details:', $cleanDescription)[0];
                                    }
                                    if (strpos($cleanDescription, ' | Changes:') !== false) {
                                        $cleanDescription = explode(' | Changes:', $cleanDescription)[0];
                                    }
                                    ?>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200 max-w-xs group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors" title="<?php echo htmlspecialchars($cleanDescription); ?>">
                                        <span class="block truncate"><?php echo htmlspecialchars($cleanDescription); ?></span>
                                        <?php if ($log['record_id']): ?>
                                            <span class="text-gray-400 text-xs ml-1">(ID: <?php echo $log['record_id']; ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400 font-mono group-hover:text-gray-900 dark:group-hover:text-gray-200 transition-colors">
                                        <span class="px-2 py-1 bg-gray-50 dark:bg-gray-700 rounded text-xs group-hover:bg-red-50 dark:group-hover:bg-red-900/20 transition-colors">
                                            <?php echo htmlspecialchars($log['ip_address'] ?? ''); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($data['totalPages'] > 1): ?>
                <div class="px-6 py-6 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                    <div class="flex flex-col md:flex-row justify-center items-center gap-6">
                        <div class="flex items-center gap-2">
                            <a href="?page=<?php echo $data['page'] - 1; ?>&<?php echo http_build_query($data['filters']); ?>" 
                               class="w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:border-red-600 hover:text-red-600 transition-all <?php echo $data['page'] <= 1 ? 'pointer-events-none opacity-40' : ''; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                            
                            <?php for ($i = max(1, $data['page'] - 2); $i <= min($data['totalPages'], $data['page'] + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($data['filters']); ?>" 
                                   class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-black transition-all <?php echo $i == $data['page'] ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-red-600'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <a href="?page=<?php echo $data['page'] + 1; ?>&<?php echo http_build_query($data['filters']); ?>" 
                               class="w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:border-red-600 hover:text-red-600 transition-all <?php echo $data['page'] >= $data['totalPages'] ? 'pointer-events-none opacity-40' : ''; ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<script>
// Handle export CSV
document.querySelector('a[href*="export=csv"]')?.addEventListener('click', function(e) {
    // Let the link work normally for download
});
</script>
