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

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-6">
            <div>
                <a href="sessions.php" class="text-red-600 hover:text-red-700 text-sm mb-2 inline-flex items-center">
                    <i class="bi bi-arrow-left mr-1"></i> Back to Sessions
                </a>
                <h1 class="text-2xl font-bold text-gray-800"><?php echo e($session['title']); ?></h1>
                <p class="text-gray-500 text-sm mt-1">
                    <?php echo e($session['session_number']); ?> • Created by <?php echo e($session['created_by_name'] ?? 'System'); ?>
                </p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <?php
                $statusColors = [
                    'scheduled' => 'bg-indigo-100 text-indigo-800',
                    'in_progress' => 'bg-green-100 text-green-800',
                    'completed' => 'bg-purple-100 text-purple-800',
                    'cancelled' => 'bg-red-100 text-red-800'
                ];
                $statusClass = $statusColors[$session['status']] ?? 'bg-gray-100 text-gray-800';
                ?>
                <span class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-full <?php echo $statusClass; ?>">
                    <?php if ($session['status'] === 'in_progress'): ?>
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                    <?php endif; ?>
                    <?php echo ucfirst(str_replace('_', ' ', $session['status'])); ?>
                </span>
                
                <?php if (hasRole(['admin', 'secretary'])): ?>
                    <?php if ($session['status'] === 'scheduled'): ?>
                        <form method="POST" class="inline" onsubmit="return confirm('Start this voting session? Councilors will be able to cast votes.');">
                            <input type="hidden" name="action" value="start">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-full text-sm font-medium transition-colors inline-flex items-center">
                                <i class="bi bi-play-fill mr-1"></i> Start Session
                            </button>
                        </form>
                        <a href="edit-session.php?id=<?php echo $sessionId; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-1.5 rounded-full text-sm font-medium transition-colors inline-flex items-center">
                            <i class="bi bi-pencil mr-1"></i> Edit
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($session['status'] === 'in_progress'): ?>
                        <form method="POST" class="inline" onsubmit="return confirm('End this voting session? Results will be calculated and finalized.');">
                            <input type="hidden" name="action" value="end">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-1.5 rounded-full text-sm font-medium transition-colors inline-flex items-center">
                                <i class="bi bi-stop-fill mr-1"></i> End Session
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <?php if (in_array($session['status'], ['scheduled', 'in_progress'])): ?>
                        <form method="POST" class="inline" onsubmit="return confirm('Cancel this session? This action cannot be undone.');">
                            <input type="hidden" name="action" value="cancel">
                            <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-1.5 rounded-full text-sm font-medium transition-colors inline-flex items-center">
                                <i class="bi bi-x-circle mr-1"></i> Cancel
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-md p-4 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">Documents</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $totalDocs; ?></p>
                    </div>
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="bi bi-file-earmark-text text-red-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">Present</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $totalPresent; ?>/<?php echo count($attendees); ?></p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="bi bi-people text-green-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">Total Votes</p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo $totalVotes; ?></p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="bi bi-hand-thumbs-up text-purple-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">Passed</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $passedDocs; ?></p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="bi bi-check-circle text-green-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium">Failed</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo $failedDocs; ?></p>
                    </div>
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="bi bi-x-circle text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Session Details & Attendees Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Session Information -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6" >
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="bi bi-info-circle text-red-600 mr-2"></i>
                    Session Information
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium mb-1">Date</p>
                        <p class="text-gray-800 font-medium">
                            <i class="bi bi-calendar text-gray-400 mr-1"></i>
                            <?php echo formatDate($session['session_date'], 'F d, Y'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium mb-1">Time</p>
                        <p class="text-gray-800 font-medium">
                            <i class="bi bi-clock text-gray-400 mr-1"></i>
                            <?php echo date('h:i A', strtotime($session['start_time'])); ?>
                            <?php if ($session['end_time']): ?>
                                - <?php echo date('h:i A', strtotime($session['end_time'])); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium mb-1">Location</p>
                        <p class="text-gray-800 font-medium">
                            <i class="bi bi-geo-alt text-gray-400 mr-1"></i>
                            <?php echo e($session['location'] ?? 'Session Hall'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium mb-1">Vote Type</p>
                        <p class="text-gray-800 font-medium">
                            <i class="bi bi-diagram-3 text-gray-400 mr-1"></i>
                            <?php echo ucfirst(str_replace('_', ' ', $session['vote_type'] ?? 'roll_call')); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium mb-1">Quorum Required</p>
                        <p class="text-gray-800 font-medium">
                            <i class="bi bi-people text-gray-400 mr-1"></i>
                            <?php echo $session['quorum_required'] ?? 5; ?> members
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium mb-1">Committee</p>
                        <p class="text-gray-800 font-medium">
                            <i class="bi bi-building text-gray-400 mr-1"></i>
                            <?php echo e($session['committee_name'] ?? 'None / Plenary'); ?>
                        </p>
                    </div>
                </div>
                <?php if (!empty($session['description'])): ?>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-500 uppercase font-medium mb-1">Description</p>
                        <p class="text-gray-700 text-sm"><?php echo nl2br(e($session['description'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Attendees -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="bi bi-people text-red-600 mr-2"></i>
                    Attendees
                    <span class="ml-auto text-sm font-normal text-gray-500"><?php echo $totalPresent; ?>/<?php echo count($attendees); ?></span>
                </h2>
                
                <?php if (empty($attendees)): ?>
                    <div class="text-center py-6 text-gray-400">
                        <i class="bi bi-people text-3xl mb-2"></i>
                        <p class="text-sm">No attendees assigned</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-2 max-h-80 overflow-y-auto">
                        <?php foreach ($attendees as $attendee): ?>
                            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-sm font-semibold mr-3">
                                        <?php echo strtoupper(substr($attendee['full_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800"><?php echo e($attendee['full_name']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo e($attendee['position'] ?? 'Member'); ?></p>
                                    </div>
                                </div>
                                
                                <?php if (hasRole(['admin', 'secretary']) && $session['status'] === 'in_progress'): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="mark_attendance">
                                        <input type="hidden" name="user_id" value="<?php echo $attendee['user_id']; ?>">
                                        <?php if ($attendee['status'] === 'present'): ?>
                                            <input type="hidden" name="status" value="absent">
                                            <button type="submit" class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 hover:bg-green-200 transition-colors" title="Click to mark absent">
                                                <i class="bi bi-check-circle mr-1"></i>Present
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="status" value="present">
                                            <button type="submit" class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors" title="Click to mark present">
                                                <i class="bi bi-circle mr-1"></i>Absent
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $attendee['status'] === 'present' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'; ?>">
                                        <?php echo ucfirst($attendee['status']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Documents for Voting -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-4 md:p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="bi bi-file-earmark-text text-red-600 mr-2"></i>
                    Documents for Voting
                </h2>
                <?php if ($session['status'] === 'in_progress' && hasRole(['councilor', 'admin'])): ?>
                    <a href="cast-vote.php?session=<?php echo $sessionId; ?>" 
                       class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors inline-flex items-center">
                        <i class="bi bi-hand-thumbs-up mr-2"></i> Cast Vote
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if (empty($documents)): ?>
                <div class="p-8 md:p-12 text-center">
                    <i class="bi bi-inbox text-5xl text-gray-300 mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-700 mb-2">No Documents</h3>
                    <p class="text-gray-500">No documents have been assigned to this session.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($documents as $doc): ?>
                        <?php
                        $total = $doc['approve_count'] + $doc['reject_count'] + $doc['abstain_count'];
                        $approvePercent = $total > 0 ? ($doc['approve_count'] / $total) * 100 : 0;
                        $rejectPercent = $total > 0 ? ($doc['reject_count'] / $total) * 100 : 0;
                        $abstainPercent = $total > 0 ? ($doc['abstain_count'] / $total) * 100 : 0;
                        
                        $votingStatusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'passed' => 'bg-green-100 text-green-800',
                            'failed' => 'bg-red-100 text-red-800'
                        ];
                        $votingStatusClass = $votingStatusColors[$doc['voting_status']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <div class="p-4 md:p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex flex-col md:flex-row md:items-center gap-4">
                                <!-- Document Info -->
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm text-gray-500"><?php echo e($doc['doc_number']); ?></span>
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800"><?php echo ucfirst($doc['type']); ?></span>
                                        <span class="px-2 py-0.5 text-xs rounded-full <?php echo $votingStatusClass; ?>">
                                            <?php echo ucfirst($doc['voting_status']); ?>
                                        </span>
                                    </div>
                                    <h3 class="font-semibold text-gray-900"><?php echo e($doc['title']); ?></h3>
                                    <?php if (!empty($doc['summary'])): ?>
                                        <p class="text-gray-500 text-sm mt-1 line-clamp-2"><?php echo e(substr($doc['summary'], 0, 150)); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Vote Counts -->
                                <div class="flex items-center gap-6">
                                    <div class="flex items-center gap-4">
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-green-600"><?php echo $doc['approve_count']; ?></div>
                                            <div class="text-xs text-gray-500">Approve</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-red-600"><?php echo $doc['reject_count']; ?></div>
                                            <div class="text-xs text-gray-500">Reject</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-gray-500"><?php echo $doc['abstain_count']; ?></div>
                                            <div class="text-xs text-gray-500">Abstain</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <?php if ($total > 0): ?>
                                    <div class="w-32 hidden md:block">
                                        <div class="flex h-3 rounded-full overflow-hidden bg-gray-200">
                                            <div class="bg-green-500 transition-all" style="width: <?php echo $approvePercent; ?>%"></div>
                                            <div class="bg-red-500 transition-all" style="width: <?php echo $rejectPercent; ?>%"></div>
                                            <div class="bg-gray-400 transition-all" style="width: <?php echo $abstainPercent; ?>%"></div>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                                            <span><?php echo round($approvePercent); ?>%</span>
                                            <span><?php echo round($rejectPercent); ?>%</span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Individual Votes (admin/secretary only) -->
                            <?php if (hasRole(['admin', 'secretary']) && $total > 0): ?>
                                <details class="mt-4">
                                    <summary class="cursor-pointer text-sm text-red-600 hover:text-red-700 font-medium">
                                        <i class="bi bi-chevron-down mr-1"></i> View Individual Votes (<?php echo $total; ?>)
                                    </summary>
                                    <div class="mt-3 pl-4 border-l-2 border-gray-200">
                                        <?php
                                        $individualVotes = $voting->getDocumentVotes($doc['document_id'], $sessionId);
                                        ?>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                            <?php foreach ($individualVotes as $iv): ?>
                                                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-xs font-semibold mr-2">
                                                            <?php echo strtoupper(substr($iv['voter_name'], 0, 1)); ?>
                                                        </div>
                                                        <span class="text-sm text-gray-700"><?php echo e($iv['voter_name']); ?></span>
                                                    </div>
                                                    <span class="px-2 py-0.5 text-xs rounded-full <?php echo getVoteBadgeClass($iv['vote']); ?>">
                                                        <?php echo ucfirst($iv['vote']); ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </details>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quorum Check -->
        <?php if ($session['status'] === 'in_progress'): ?>
        <div class="mt-6 bg-white rounded-xl shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="bi bi-shield-check text-red-600 mr-2"></i>
                Quorum Status
            </h2>
            <?php
            $quorumRequired = $session['quorum_required'] ?? 5;
            $quorumMet = $totalPresent >= $quorumRequired;
            ?>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">Present: <strong><?php echo $totalPresent; ?></strong> / Required: <strong><?php echo $quorumRequired; ?></strong></span>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $quorumMet ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo $quorumMet ? '✓ Quorum Met' : '✗ No Quorum'; ?>
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <?php $quorumPercent = min(100, ($totalPresent / max(1, $quorumRequired)) * 100); ?>
                        <div class="h-3 rounded-full transition-all duration-500 <?php echo $quorumMet ? 'bg-green-500' : 'bg-red-500'; ?>" 
                             style="width: <?php echo $quorumPercent; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
