<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../controllers/IntegrationController.php';

if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}
if (!isAdmin() && ($_SESSION['user_role'] ?? '') !== 'secretary') {
    $_SESSION['flash_error'] = 'Access denied.';
    redirectToDashboard();
}

$controller = new IntegrationController();
$connected = $controller->isConnected();

$filters = [
    'page' => $_GET['page'] ?? 1,
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? '',
    'year' => $_GET['year'] ?? '',
];

$data = $controller->getDocuments('resolution', $filters);
$stats = $controller->getStatistics('resolution');
$years = $controller->getAvailableYears('resolution');

$pageTitle = 'Resolutions';
$currentPage = 'resolutions';
$breadcrumbs = [
    ['label' => 'Integration', 'url' => '#'],
    ['label' => 'Resolutions']
];

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-gray-950 p-3 md:p-6 custom-scrollbar">
        <!-- Header -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white animate-fade-in relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl dark:opacity-5"></div>
            <div class="relative flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight">Resolutions</h1>
                    <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium">Legislative resolutions from the LRMS integration.</p>
                </div>
                <div class="shrink-0 flex items-center gap-3">
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold <?php echo $connected ? 'bg-green-500/20 text-green-200 border border-green-400/30' : 'bg-red-500/20 text-red-200 border border-red-400/30'; ?>">
                        <i class="bi <?php echo $connected ? 'bi-link-45deg' : 'bi-link-break'; ?> mr-1"></i>
                        <?php echo $connected ? 'Connected' : 'Disconnected'; ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if (!$connected): ?>
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-8 text-center animate-fade-in-up">
                <i class="bi bi-database-x text-5xl text-amber-500 block mb-4"></i>
                <h3 class="text-xl font-bold text-amber-800 dark:text-amber-300 mb-2">LRMS Database Unavailable</h3>
                <p class="text-amber-600 dark:text-amber-400 text-sm max-w-md mx-auto">Could not connect to the LRMS database. Ensure the LLRM System database is running.</p>
            </div>
        <?php else: ?>
            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 animate-fade-in-up">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-purple-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Total</p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo number_format($stats['total']); ?></p>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-full p-2.5"><i class="bi bi-file-earmark-check text-purple-600 text-xl"></i></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-green-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Approved</p>
                            <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['approved']); ?></p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-full p-2.5"><i class="bi bi-check-circle text-green-600 text-xl"></i></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-amber-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Pending</p>
                            <p class="text-2xl font-bold text-amber-600"><?php echo number_format($stats['pending']); ?></p>
                        </div>
                        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-full p-2.5"><i class="bi bi-hourglass-split text-amber-600 text-xl"></i></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-indigo-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Drafts</p>
                            <p class="text-2xl font-bold text-indigo-600"><?php echo number_format($stats['draft']); ?></p>
                        </div>
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-full p-2.5"><i class="bi bi-file-earmark-text text-indigo-600 text-xl"></i></div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 mb-6 animate-fade-in-up" style="animation-delay: 100ms;">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1 block">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 outline-none text-sm">
                            <option value="">All</option>
                            <?php foreach (['draft','pending','approved','rejected','archived','superseded'] as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo ($filters['status'] ?? '') == $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1 block">Year</label>
                        <select name="year" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 outline-none text-sm">
                            <option value="">All Years</option>
                            <?php foreach ($years as $yr): ?>
                                <option value="<?php echo $yr; ?>" <?php echo ($filters['year'] ?? '') == $yr ? 'selected' : ''; ?>><?php echo $yr; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1 block">Search</label>
                        <input type="text" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 outline-none text-sm" placeholder="Search resolutions...">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-red-700 dark:bg-red-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-red-800 transition-all flex items-center justify-center shadow-sm">
                            <i class="bi bi-funnel mr-1"></i> Filter
                        </button>
                        <a href="resolutions.php" class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-all border border-gray-200 dark:border-gray-700"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md overflow-hidden animate-fade-in-up" style="animation-delay: 200ms;">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/80">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-file-earmark-check text-purple-600"></i> Resolutions
                        <span class="px-3 py-1 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 text-xs font-bold rounded-full"><?php echo number_format($data['total']); ?></span>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Reference No.</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Title</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Uploaded By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php if (empty($data['documents'])): ?>
                                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <i class="bi bi-inbox text-5xl block mb-3 opacity-20"></i><p>No resolutions found</p>
                                </td></tr>
                            <?php else: foreach ($data['documents'] as $doc): ?>
                                <tr class="hover:bg-purple-50 dark:hover:bg-purple-900/10 transition-all group">
                                    <td class="px-6 py-3"><span class="font-mono text-sm font-bold text-purple-600 dark:text-purple-400"><?php echo e($doc['reference_number']); ?></span></td>
                                    <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300 max-w-sm truncate" title="<?php echo e($doc['title']); ?>"><?php echo e($doc['title']); ?></td>
                                    <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap"><?php echo $doc['document_date'] ? date('M d, Y', strtotime($doc['document_date'])) : '-'; ?></td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <?php
                                        $sc = ['approved'=>'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400','pending'=>'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400','draft'=>'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300','rejected'=>'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400','archived'=>'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400','superseded'=>'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300'];
                                        ?>
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full <?php echo $sc[$doc['status']] ?? 'bg-gray-100 text-gray-800'; ?>"><?php echo ucfirst($doc['status']); ?></span>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400"><?php echo e($doc['uploaded_by_name'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($data['totalPages'] > 1): ?>
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/80">
                        <nav class="flex justify-center">
                            <ul class="flex items-center space-x-2">
                                <li><a href="?page=<?php echo $data['page'] - 1; ?>&<?php echo http_build_query(array_diff_key($filters, ['page'=>''])); ?>" class="<?php echo $data['page'] <= 1 ? 'pointer-events-none opacity-50' : 'hover:bg-gray-200 dark:hover:bg-gray-700'; ?> w-10 h-10 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300"><i class="bi bi-chevron-left"></i></a></li>
                                <?php for ($i = max(1, $data['page'] - 2); $i <= min($data['totalPages'], $data['page'] + 2); $i++): ?>
                                    <li><a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_diff_key($filters, ['page'=>''])); ?>" class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold transition-all <?php echo $i == $data['page'] ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-red-600'; ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <li><a href="?page=<?php echo $data['page'] + 1; ?>&<?php echo http_build_query(array_diff_key($filters, ['page'=>''])); ?>" class="<?php echo $data['page'] >= $data['totalPages'] ? 'pointer-events-none opacity-50' : 'hover:bg-gray-200 dark:hover:bg-gray-700'; ?> w-10 h-10 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300"><i class="bi bi-chevron-right"></i></a></li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>
