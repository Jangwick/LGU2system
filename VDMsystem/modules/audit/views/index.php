<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../controllers/AuditController.php';

// Check authentication & admin access
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}
if (!isAdmin()) {
    $_SESSION['flash_error'] = 'Access denied. Admin privileges required.';
    redirectToDashboard();
}

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
$breadcrumbs = [
    ['label' => 'Administration', 'url' => '#'],
    ['label' => 'Audit Logs']
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
    <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-[#0f0f0f] p-3 md:p-6 custom-scrollbar">
        <!-- Header Banner -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white transform transition-all duration-500 ease-in-out animate-fade-in relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl transition-opacity duration-500 dark:opacity-5"></div>
            
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight transition-all duration-500">
                        Audit Logs
                    </h1>
                    <p class="text-red-100 text-sm opacity-90 font-medium transition-all duration-500">
                        System activity tracking and security audit trail.
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <a href="?export=csv&<?php echo http_build_query($data['filters']); ?>" 
                       class="!bg-white !text-red-600 hover:!bg-gray-50 px-4 md:px-6 py-3 rounded-xl font-bold shadow-lg transition-all duration-500 transform hover:-translate-y-0.5 flex items-center justify-center group border border-red-600 w-full md:w-auto text-sm">
                        <i class="bi bi-download mr-2 transition-transform group-hover:rotate-12"></i>
                        Export CSV Intelligence
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 animate-fade-in-up">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-red-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Total Logs</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo number_format($stats['total_logs']); ?></p>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-full p-2.5">
                        <i class="bi bi-database-fill text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-green-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Today</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['logs_today']); ?></p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-full p-2.5">
                        <i class="bi bi-calendar-day text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-indigo-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">This Week</p>
                        <p class="text-2xl font-bold text-indigo-600"><?php echo number_format($stats['logs_this_week']); ?></p>
                    </div>
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-full p-2.5">
                        <i class="bi bi-calendar-week text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-amber-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Most Active</p>
                        <p class="text-sm font-bold text-gray-800 dark:text-white truncate" title="<?php echo e($stats['most_active_user']); ?>"><?php echo e($stats['most_active_user']); ?></p>
                    </div>
                    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-full p-2.5">
                        <i class="bi bi-person-fill text-amber-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-xl shadow-md p-6 mb-6 animate-fade-in-up border border-gray-100 dark:border-gray-800" style="animation-delay: 100ms;">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6">
                <div>
                    <label class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase mb-2 block tracking-widest">Target User</label>
                    <select name="user_id" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all text-sm">
                        <option value="">All Identities</option>
                        <?php foreach ($data['users'] as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php echo ($data['filters']['user_id'] ?? '') == $user['id'] ? 'selected' : ''; ?>>
                                <?php echo e($user['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase mb-2 block tracking-widest">System Module</label>
                    <select name="module" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all text-sm">
                        <option value="">All Contexts</option>
                        <?php foreach ($data['modules'] as $mod): ?>
                            <option value="<?php echo e($mod); ?>" <?php echo ($data['filters']['module'] ?? '') == $mod ? 'selected' : ''; ?>>
                                <?php echo ucfirst(e($mod)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase mb-2 block tracking-widest">Event Type</label>
                    <select name="event_type" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all text-sm">
                        <option value="">All Protocols</option>
                        <?php foreach ($data['eventTypes'] as $et): ?>
                            <option value="<?php echo e($et); ?>" <?php echo ($data['filters']['event_type'] ?? '') == $et ? 'selected' : ''; ?>>
                                <?php echo ucwords(str_replace('_', ' ', $et)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase mb-2 block tracking-widest">From</label>
                    <input type="date" name="date_from" value="<?php echo e($data['filters']['date_from'] ?? ''); ?>" 
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all text-sm">
                </div>
                
                <div>
                    <label class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase mb-2 block tracking-widest">To</label>
                    <input type="date" name="date_to" value="<?php echo e($data['filters']['date_to'] ?? ''); ?>" 
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all text-sm">
                </div>
                
                <div class="flex items-end gap-3">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-xl font-black shadow-md transition-all flex items-center justify-center gap-2 text-[11px] uppercase tracking-widest border border-red-600">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                    <a href="index.php" class="bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 p-2.5 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all border border-gray-200 dark:border-gray-700" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
            
            <!-- Search Bar -->
            <form method="GET" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 relative">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="<?php echo e($data['filters']['search'] ?? ''); ?>" 
                               class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all text-sm shadow-sm" 
                               placeholder="Search identities, context, or protocol actions...">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 sm:flex-none bg-gray-900 dark:bg-gray-800 text-white px-8 py-3 rounded-xl font-black text-[11px] uppercase tracking-widest hover:bg-black transition-all border border-gray-900">Search</button>
                        <?php if (!empty($data['filters']['search'])): ?>
                            <a href="index.php" class="px-4 py-3 bg-red-50 dark:bg-red-900/20 text-red-600 rounded-xl hover:bg-red-100 transition-all flex items-center border border-red-100 dark:border-red-900">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-xl shadow-md overflow-hidden animate-fade-in-up border border-gray-100 dark:border-gray-800" style="animation-delay: 200ms;">
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 backdrop-blur-sm">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-3 uppercase tracking-tighter">
                        <i class="bi bi-shield-lock-fill text-red-600"></i>
                        Activity Audit Logs
                        <span class="px-3 py-1 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-[10px] font-black rounded-lg uppercase tracking-widest border border-red-100/50"><?php echo number_format($data['total']); ?> records</span>
                    </h2>
                    <div class="text-[10px] text-gray-500 dark:text-gray-400 font-black uppercase tracking-widest bg-white dark:bg-gray-800 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                        Intelligence Stream: <?php echo max(1, (($data['page'] - 1) * $data['perPage']) + 1); ?> - <?php echo min($data['page'] * $data['perPage'], $data['total']); ?>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/80 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-800 backdrop-blur-md">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Temporal Signature</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Entity / Identity</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Protocol Event</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Module Context</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Operation Details</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Network Origin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800/50">
                        <?php if (empty($data['logs'])): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <i class="bi bi-inbox text-5xl block mb-3 opacity-20"></i>
                                    <p class="text-lg">No audit logs found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['logs'] as $log): ?>
                                <tr class="hover:bg-red-50 dark:hover:bg-red-900/10 transition-all group">
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors">
                                        <i class="bi bi-clock text-gray-400 mr-1"></i>
                                        <?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center text-white text-xs font-bold mr-3 transform group-hover:scale-110 transition-transform shadow-sm">
                                                <?php echo strtoupper(substr($log['full_name'] ?? 'S', 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 dark:text-white text-sm group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors"><?php echo e($log['full_name'] ?? 'System'); ?></div>
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400 font-medium"><?php echo e($log['email'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <?php
                                        $eventStr = $log['event_type'] ?? '';
                                        $eventClass = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                        $eventIcon = 'bi-activity';
                                        
                                        if (str_contains($eventStr, 'create') || str_contains($eventStr, 'created')) {
                                            $eventClass = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
                                            $eventIcon = 'bi-plus-circle';
                                        } elseif (str_contains($eventStr, 'update') || str_contains($eventStr, 'updated') || str_contains($eventStr, 'edit')) {
                                            $eventClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
                                            $eventIcon = 'bi-pencil';
                                        } elseif (str_contains($eventStr, 'delete') || str_contains($eventStr, 'deleted')) {
                                            $eventClass = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                                            $eventIcon = 'bi-trash';
                                        } elseif (str_contains($eventStr, 'login')) {
                                            $eventClass = 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400';
                                            $eventIcon = 'bi-box-arrow-in-right';
                                        } elseif (str_contains($eventStr, 'logout')) {
                                            $eventClass = 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
                                            $eventIcon = 'bi-box-arrow-right';
                                        } elseif (str_contains($eventStr, 'vote')) {
                                            $eventClass = 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
                                            $eventIcon = 'bi-hand-thumbs-up';
                                        } elseif (str_contains($eventStr, 'session')) {
                                            $eventClass = 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
                                            $eventIcon = 'bi-calendar-check';
                                        }
                                        ?>
                                        <span class="px-3 py-1.5 text-[9px] font-black rounded uppercase tracking-widest <?php echo $eventClass; ?> inline-flex items-center gap-1.5 shadow-sm">
                                            <i class="bi <?php echo $eventIcon; ?>"></i>
                                            <?php echo ucwords(str_replace('_', ' ', $eventStr)); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo ucfirst(e($log['module'] ?? '-')); ?>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate" title="<?php echo e($log['action'] ?? ''); ?>">
                                        <?php echo e($log['action'] ?? '-'); ?>
                                        <?php if ($log['entity_id']): ?>
                                            <span class="text-gray-400 text-xs ml-1">(ID: <?php echo $log['entity_id']; ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span class="text-xs font-mono text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 px-2 py-1 rounded">
                                            <?php echo e($log['ip_address'] ?? '-'); ?>
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
                <div class="px-6 py-6 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                    <nav class="flex justify-center">
                        <ul class="flex flex-wrap items-center justify-center gap-3">
                            <li>
                                <a href="?page=<?php echo $data['page'] - 1; ?>&<?php echo http_build_query($data['filters']); ?>" 
                                   class="<?php echo $data['page'] <= 1 ? 'pointer-events-none opacity-50' : 'hover:bg-gray-200 dark:hover:bg-gray-800'; ?> w-10 h-10 rounded-xl bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 transition-all shadow-sm">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            
                            <?php for ($i = max(1, $data['page'] - 2); $i <= min($data['totalPages'], $data['page'] + 2); $i++): ?>
                                <li>
                                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($data['filters']); ?>" 
                                       class="w-10 h-10 rounded-xl flex items-center justify-center text-xs font-black transition-all <?php echo $i == $data['page'] ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-red-600'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li>
                                <a href="?page=<?php echo $data['page'] + 1; ?>&<?php echo http_build_query($data['filters']); ?>" 
                                   class="<?php echo $data['page'] >= $data['totalPages'] ? 'pointer-events-none opacity-50' : 'hover:bg-gray-200 dark:hover:bg-gray-800'; ?> w-10 h-10 rounded-xl bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 transition-all shadow-sm">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>
