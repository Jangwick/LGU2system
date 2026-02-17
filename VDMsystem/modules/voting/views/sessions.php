<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../controllers/VotingController.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

$voting = new VotingController();
$filters = [
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

$sessions = $voting->getSessions($filters);
$stats = $voting->getStatistics();

$pageTitle = 'Voting Sessions';
$currentPage = 'sessions';
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
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 animate-fade-in">
                <div class="flex items-center">
                    <i class="bi bi-check-circle text-green-500 mr-2 text-lg"></i>
                    <span class="text-green-700 font-medium"><?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Welcome/Header Banner -->
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white transform transition-all duration-300 animate-fade-in relative overflow-hidden">
            <!-- Subtle decorative background element -->
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl"></div>
            
            <div class="relative flex items-center justify-between gap-4">
                <!-- Left Side: Title & Context -->
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight">
                        Voting Sessions
                    </h1>
                    <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium">
                        Manage, monitor, and conduct legislative voting sessions effectively.
                    </p>
                </div>

                <!-- Right Side: Action Button -->
                <div class="shrink-0">
                    <?php if (hasRole(['admin', 'secretary'])): ?>
                    <a href="create-session.php" class="bg-white text-red-700 hover:bg-gray-50 px-6 py-2.5 rounded-xl font-bold shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center group">
                        <i class="bi bi-plus-lg mr-2 transition-transform group-hover:rotate-90"></i>
                        New Voting Session
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Statistics Quick View -->
        <?php 
        // Ensure stats has default values to prevent calculation errors
        $sTotal = (int)($stats['total_sessions'] ?? 0);
        $sActive = (int)($stats['active_sessions'] ?? 0);
        $sComp = (int)($stats['completed_sessions'] ?? 0);
        $sSched = $sTotal - $sActive - $sComp;
        if ($sSched < 0) $sSched = 0;
        ?>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 animate-fade-in-up">
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-red-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Sessions</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $sTotal; ?></p>
                    </div>
                    <div class="bg-red-50 rounded-full p-2.5">
                        <i class="bi bi-calendar-event text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-green-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">In Progress</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $sActive; ?></p>
                    </div>
                    <div class="bg-green-50 rounded-full p-2.5">
                        <i class="bi bi-play-circle text-green-600 text-xl animate-pulse"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-indigo-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Scheduled</p>
                        <p class="text-2xl font-bold text-indigo-600"><?php echo $sSched; ?></p>
                    </div>
                    <div class="bg-indigo-50 rounded-full p-2.5">
                        <i class="bi bi-clock-history text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-purple-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Completed</p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo $sComp; ?></p>
                    </div>
                    <div class="bg-purple-50 rounded-full p-2.5">
                        <i class="bi bi-check2-all text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 animate-fade-in-up" style="animation-delay: 100ms;">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="relative group">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Search Sessions</label>
                    <div class="relative">
                        <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="<?php echo e($filters['search']); ?>" 
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all outline-none" 
                               placeholder="Title or Session #">
                    </div>
                </div>
                
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition-all">
                        <option value="">All Statuses</option>
                        <option value="scheduled" <?php echo $filters['status'] === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="in_progress" <?php echo $filters['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $filters['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Date Range</label>
                    <div class="flex gap-2">
                        <input type="date" name="date_from" value="<?php echo e($filters['date_from']); ?>" 
                               class="w-full px-2 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500 outline-none">
                        <input type="date" name="date_to" value="<?php echo e($filters['date_to']); ?>" 
                               class="w-full px-2 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500 outline-none">
                    </div>
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-gray-800 text-white px-4 py-2 rounded-lg font-bold hover:bg-gray-700 transition-all flex items-center justify-center">
                        <i class="bi bi-filter mr-2"></i> Apply Filters
                    </button>
                    <a href="sessions.php" class="bg-gray-100 text-gray-600 p-2 rounded-lg hover:bg-gray-200 transition-all" title="Clear Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Sessions Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden animate-fade-in-up" style="animation-delay: 200ms;">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Session Details</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Schedule</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Committee</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Documents</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($sessions)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="bi bi-inbox text-5xl mb-4 block opacity-20"></i>
                                    <p class="text-lg">No voting sessions found matching your criteria.</p>
                                    <a href="sessions.php" class="text-red-600 font-bold hover:underline">Clear all filters</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sessions as $session): ?>
                                <tr class="hover:bg-red-50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="bg-red-100 text-red-700 w-10 h-10 rounded-lg flex items-center justify-center font-bold mr-4 group-hover:bg-red-600 group-hover:text-white transition-all transform group-hover:rotate-3 shadow-sm">
                                                <i class="bi bi-calendar-check"></i>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 group-hover:text-red-700 transition-colors"><?php echo e($session['title']); ?></div>
                                                <div class="text-xs text-gray-500"><?php echo e($session['session_number']); ?> • Added by <?php echo e($session['created_by_name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-700">
                                                <i class="bi bi-calendar3 mr-1 text-red-500"></i>
                                                <?php echo formatDate($session['session_date'], 'M d, Y'); ?>
                                            </span>
                                            <span class="text-xs text-gray-500 mt-1">
                                                <i class="bi bi-clock mr-1 text-gray-400"></i>
                                                <?php echo date('h:i A', strtotime($session['start_time'])); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded border border-gray-200">
                                            <?php echo e($session['committee_name'] ?? 'Plenary / General'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-bold text-gray-800 bg-red-50 text-red-700 w-8 h-8 rounded-full flex items-center justify-center border border-red-100 shadow-sm" title="Documents">
                                                <?php echo $session['document_count']; ?>
                                            </span>
                                            <span class="text-sm font-bold text-blue-800 bg-blue-50 text-blue-700 w-8 h-8 rounded-full flex items-center justify-center border border-blue-100 shadow-sm" title="Attendees Present">
                                                <?php echo $session['attendee_count']; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php
                                        $statusClass = getStatusBadgeClass($session['status']);
                                        ?>
                                        <span class="px-3 py-1 text-xs font-bold rounded-full uppercase tracking-tighter shadow-sm <?php echo $statusClass; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $session['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="session-details.php?id=<?php echo $session['id']; ?>" 
                                               class="bg-white p-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-800 hover:text-white transition-all shadow-sm hover:shadow-md" 
                                               title="View Details">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            
                                            <?php if ($session['status'] === 'in_progress' && hasRole(['councilor', 'admin'])): ?>
                                                <a href="cast-vote.php?session=<?php echo $session['id']; ?>" 
                                                   class="bg-green-600 text-white p-2 rounded-lg hover:bg-green-700 transition-all shadow-sm hover:shadow-md pulse-effect" 
                                                   title="Cast Vote">
                                                    <i class="bi bi-hand-thumbs-up-fill"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($session['status'] === 'completed'): ?>
                                                <a href="results.php?session=<?php echo $session['id']; ?>" 
                                                   class="bg-purple-600 text-white p-2 rounded-lg hover:bg-purple-700 transition-all shadow-sm hover:shadow-md" 
                                                   title="View Results">
                                                    <i class="bi bi-bar-chart-fill"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($session['status'] === 'scheduled' && hasRole(['admin', 'secretary'])): ?>
                                                <a href="edit-session.php?id=<?php echo $session['id']; ?>" 
                                                   class="bg-yellow-500 text-white p-2 rounded-lg hover:bg-yellow-600 transition-all shadow-sm hover:shadow-md" 
                                                   title="Edit Session">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>
<?php ?>
