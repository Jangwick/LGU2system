<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';
require_once __DIR__ . '/../controllers/VotingController.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

$voting = new VotingController();
$sessionId = $_GET['id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$sessionId) {
    $_SESSION['flash_error'] = "No session ID provided.";
    header('Location: sessions.php');
    exit;
}

$session = $voting->getSession($sessionId);
if (!$session) {
    $_SESSION['flash_error'] = "Session not found.";
    header('Location: sessions.php');
    exit;
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasRole(['admin', 'secretary'])) {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'start':
                $voting->startSession($sessionId, $userId);
                $_SESSION['flash_success'] = "Session started successfully! Voting is now open.";
                break;
            case 'end':
                $voting->endSession($sessionId, $userId);
                $_SESSION['flash_success'] = "Session ended. Results have been calculated.";
                break;
            case 'cancel':
                $voting->cancelSession($sessionId, $userId);
                $_SESSION['flash_success'] = "Session has been cancelled.";
                break;
            case 'mark_attendance':
                $attendeeId = $_POST['user_id'] ?? null;
                $isPresent = ($_POST['status'] ?? '') === 'present';
                if ($attendeeId) {
                    $voting->markAttendance($sessionId, $attendeeId, $isPresent);
                    $_SESSION['flash_success'] = "Attendance updated.";
                }
                break;
        }
    } catch (Exception $e) {
        $_SESSION['flash_error'] = $e->getMessage();
    }
    
    header("Location: session-details.php?id=$sessionId");
    exit;
}

// Refresh session data after potential updates
$session = $voting->getSession($sessionId);
$documents = $voting->getSessionDocuments($sessionId);
$attendees = $voting->getSessionAttendees($sessionId);

// Calculate summary
$totalDocs = count($documents);
$passedDocs = count(array_filter($documents, fn($d) => $d['voting_status'] === 'passed'));
$failedDocs = count(array_filter($documents, fn($d) => $d['voting_status'] === 'failed'));
$pendingDocs = count(array_filter($documents, fn($d) => $d['voting_status'] === 'pending'));
$totalPresent = count(array_filter($attendees, fn($a) => $a['status'] === 'present'));
$totalVotes = array_sum(array_column($documents, 'vote_count'));

$pageTitle = $session['title'] . ' - Details';
$currentPage = 'voting-sessions';
$breadcrumbs = [
    ['label' => 'Voting', 'url' => '#'],
    ['label' => 'Sessions', 'url' => 'sessions.php'],
    ['label' => $session['session_number']]
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
        
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4 animate-fade-in">
                <div class="flex items-center">
                    <i class="bi bi-exclamation-triangle text-red-500 mr-2 text-lg"></i>
                    <span class="text-red-700 font-medium"><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Breadcrumbs & Navigation -->
        <div class="mb-4">
            <nav class="text-xs md:text-sm font-medium mb-3" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-gray-400">
                    <li><a href="#" class="hover:text-red-600 transition-colors">Voting</a></li>
                    <li><i class="bi bi-chevron-right text-[10px]"></i></li>
                    <li><a href="sessions.php" class="hover:text-red-600 transition-colors">Sessions</a></li>
                    <li><i class="bi bi-chevron-right text-[10px]"></i></li>
                    <li class="text-gray-800 font-bold"><?php echo e($session['session_number']); ?></li>
                </ol>
            </nav>
            <a href="sessions.php" class="text-red-600 hover:text-red-700 text-sm font-bold flex items-center group transition-all">
                <i class="bi bi-arrow-left mr-2 transition-transform group-hover:-translate-x-1"></i> Back to Sessions
            </a>
        </div>

        <!-- Session Header Title Section -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl md:text-4xl font-black text-gray-900 tracking-tight leading-tight">
                    <?php echo e($session['title']); ?>
                </h1>
                <p class="text-gray-500 text-sm md:text-base font-medium mt-1">
                    <span class="font-bold text-gray-700"><?php echo e($session['session_number']); ?></span> • Created by <span class="text-red-600"><?php echo e($session['created_by_name'] ?? 'Admin User'); ?></span>
                </p>
                
                <!-- Action Buttons / Status Badge -->
                <div class="flex flex-wrap items-center gap-3 mt-4">
                    <?php
                    $statusConfig = [
                        'scheduled' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'label' => 'Scheduled'],
                        'in_progress' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'In Progress'],
                        'completed' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'label' => 'Completed'],
                        'cancelled' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => 'Cancelled']
                    ];
                    $cfg = $statusConfig[$session['status']] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => 'Unknown'];
                    ?>
                    <span class="<?php echo $cfg['bg'] . ' ' . $cfg['text']; ?> px-4 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm">
                        <?php echo $cfg['label']; ?>
                    </span>

                    <?php if (hasRole(['admin', 'secretary'])): ?>
                        <?php if ($session['status'] === 'scheduled'): ?>
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="start">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-lg flex items-center">
                                <i class="bi bi-play-fill mr-2"></i> Start Session
                            </button>
                        </form>
                        <a href="edit-session.php?id=<?php echo $sessionId; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-2 rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-lg flex items-center">
                            <i class="bi bi-pencil-fill mr-2"></i> Edit
                        </a>
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="cancel">
                            <button type="submit" class="bg-slate-600 hover:bg-slate-700 text-white px-5 py-2 rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-lg flex items-center">
                                <i class="bi bi-x-circle-fill mr-2"></i> Cancel
                            </button>
                        </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Premium Statistics Cards Row -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <!-- Documents Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between group hover:shadow-md transition-all">
                <div>
                    <p class="text-[10px] md:text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Documents</p>
                    <p class="text-2xl md:text-3xl font-black text-gray-900"><?php echo $totalDocs; ?></p>
                </div>
                <div class="bg-red-50 text-red-500 w-12 h-12 rounded-full flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
            </div>
            <!-- Present Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between group hover:shadow-md transition-all">
                <div>
                    <p class="text-[10px] md:text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Present</p>
                    <p class="text-2xl md:text-3xl font-black text-green-600"><?php echo $totalPresent . '/' . count($attendees); ?></p>
                </div>
                <div class="bg-green-50 text-green-500 w-12 h-12 rounded-full flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <!-- Total Votes Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between group hover:shadow-md transition-all">
                <div>
                    <p class="text-[10px] md:text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Votes</p>
                    <p class="text-2xl md:text-3xl font-black text-purple-600"><?php echo $totalVotes; ?></p>
                </div>
                <div class="bg-purple-50 text-purple-500 w-12 h-12 rounded-full flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="bi bi-hand-thumbs-up"></i>
                </div>
            </div>
            <!-- Passed Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between group hover:shadow-md transition-all">
                <div>
                    <p class="text-[10px] md:text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Passed</p>
                    <p class="text-2xl md:text-3xl font-black text-teal-600"><?php echo $passedDocs; ?></p>
                </div>
                <div class="bg-teal-50 text-teal-500 w-12 h-12 rounded-full flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
            <!-- Failed Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between group hover:shadow-md transition-all">
                <div>
                    <p class="text-[10px] md:text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Failed</p>
                    <p class="text-2xl md:text-3xl font-black text-orange-600"><?php echo $failedDocs; ?></p>
                </div>
                <div class="bg-orange-50 text-orange-500 w-12 h-12 rounded-full flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
        
        <!-- Info Split View -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Session Information -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-md p-6 md:p-8" >
                <h2 class="text-lg md:text-xl font-black text-gray-800 mb-6 flex items-center">
                    <i class="bi bi-info-circle text-red-600 mr-3"></i>
                    Session Information
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-8 gap-x-12">
                    <div class="flex items-start">
                        <i class="bi bi-calendar2-check text-gray-400 text-lg mr-4 mt-0.5"></i>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-1">Date</p>
                            <p class="text-gray-900 font-bold"><?php echo formatDate($session['session_date'], 'F d, Y'); ?></p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="bi bi-clock text-gray-400 text-lg mr-4 mt-0.5"></i>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-1">Time</p>
                            <p class="text-gray-900 font-bold">
                                <?php echo date('h:i A', strtotime($session['start_time'])); ?>
                                <?php if ($session['end_time']): ?>
                                    - <?php echo date('h:i A', strtotime($session['end_time'])); ?>
                                <?php else: ?>
                                    - 05:00 PM
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="bi bi-geo-alt text-gray-400 text-lg mr-4 mt-0.5"></i>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-1">Location</p>
                            <p class="text-gray-900 font-bold"><?php echo e($session['location'] ?? 'Session Hall'); ?></p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="bi bi-diagram-3 text-gray-400 text-lg mr-4 mt-0.5"></i>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-1">Vote Type</p>
                            <p class="text-gray-900 font-bold"><?php echo ucfirst(str_replace('_', ' ', $session['vote_type'] ?? 'Roll Call')); ?></p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="bi bi-people text-gray-400 text-lg mr-4 mt-0.5"></i>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-1">Quorum Required</p>
                            <p class="text-gray-900 font-bold"><?php echo $session['quorum_required'] ?? 7; ?> members</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="bi bi-building text-gray-400 text-lg mr-4 mt-0.5"></i>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-1">Committee</p>
                            <p class="text-gray-900 font-bold"><?php echo e($session['committee_name'] ?? 'Finance and Budget'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Attendees -->
            <div class="bg-white rounded-2xl shadow-md p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-black text-gray-800 flex items-center">
                        <i class="bi bi-people text-red-600 mr-3"></i>
                        Attendees
                    </h2>
                    <span class="text-[10px] font-bold text-gray-400 tracking-tighter"><?php echo $totalPresent; ?>/<?php echo count($attendees); ?></span>
                </div>
                
                <div class="space-y-4 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                    <?php if (empty($attendees)): ?>
                        <div class="text-center py-10 text-gray-400">
                            <i class="bi bi-person-x text-4xl mb-3"></i>
                            <p class="text-sm">No attendees listed</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($attendees as $attendee): ?>
                            <div class="flex items-center justify-between group">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-600 font-bold text-sm mr-3">
                                        <?php echo strtoupper(substr($attendee['full_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900"><?php echo e($attendee['full_name']); ?></p>
                                        <p class="text-[10px] text-gray-500 font-medium"><?php echo e($attendee['position'] ?? 'Administrator'); ?></p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 text-[10px] font-black rounded-lg <?php echo $attendee['status'] === 'present' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'; ?> uppercase">
                                    <?php echo e($attendee['status'] ?? 'Present'); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Documents Section -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden animate-fade-in-up">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-black text-gray-800 flex items-center">
                    <i class="bi bi-file-earmark-text text-red-600 mr-3"></i>
                    Documents for Voting
                </h2>
            </div>
            
            <div class="p-12 text-center text-gray-400">
                <div class="bg-gray-50 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-inbox text-4xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">No Documents</h3>
                <p class="text-sm font-medium">No documents have been assigned to this session.</p>
            </div>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>
