<?php
session_start();
require_once __DIR__ . '/../../../core/config/config.php';
require_once __DIR__ . '/../../../core/config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Check role - only councilors and admins can cast votes
if (!hasRole(['councilor', 'admin'])) {
    $_SESSION['flash_error'] = "You don't have permission to cast votes.";
    header('Location: sessions.php');
    exit;
}

$sessionId = $_GET['session'] ?? null;
$userId = $_SESSION['user_id'];
$errors = [];
$success = false;

// Get active sessions for voting
if ($sessionId) {
    $session = dbFetchOne(
        "SELECT vs.*, 
                (SELECT COUNT(*) FROM session_attendees WHERE session_id = vs.id AND user_id = ? AND is_present = 1) as is_attending
         FROM voting_sessions vs 
         WHERE vs.id = ? AND vs.status = 'in_progress'",
        [$userId, $sessionId]
    );
    
    if (!$session) {
        $_SESSION['flash_error'] = "Session not found or not currently active.";
        header('Location: sessions.php');
        exit;
    }
    
    // Get documents pending vote in this session
    $documents = dbFetchAll(
        "SELECT sd.*, d.id as doc_id, d.doc_number, d.title, d.type, d.summary,
                (SELECT vote FROM votes WHERE document_id = d.id AND session_id = sd.session_id AND councilor_id = ?) as my_vote
         FROM session_documents sd
         JOIN documents d ON sd.document_id = d.id
         WHERE sd.session_id = ? AND sd.voting_status = 'pending'
         ORDER BY sd.voting_order, d.created_at",
        [$userId, $sessionId]
    );
} else {
    // Get all active sessions
    $activeSessions = dbFetchAll(
        "SELECT vs.*, 
                (SELECT COUNT(*) FROM session_documents WHERE session_id = vs.id AND voting_status = 'pending') as pending_docs
         FROM voting_sessions vs 
         WHERE vs.status = 'in_progress'
         ORDER BY vs.session_date, vs.start_time"
    );
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sessionId) {
    $documentId = $_POST['document_id'] ?? null;
    $vote = $_POST['vote'] ?? null;
    $remarks = trim($_POST['remarks'] ?? '');
    
    if (!$documentId || !in_array($vote, ['approve', 'reject', 'abstain'])) {
        $errors[] = "Invalid vote submission.";
    } else {
        // Check if already voted
        $existingVote = dbFetchOne(
            "SELECT id FROM votes WHERE document_id = ? AND session_id = ? AND councilor_id = ?",
            [$documentId, $sessionId, $userId]
        );
        
        if ($existingVote) {
            $errors[] = "You have already cast your vote for this document.";
        } else {
            try {
                // Record vote
                $voteId = dbInsert('votes', [
                    'session_id' => $sessionId,
                    'document_id' => $documentId,
                    'councilor_id' => $userId,
                    'vote' => $vote,
                    'remarks' => $remarks,
                    'cast_at' => date('Y-m-d H:i:s'),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                if ($voteId) {
                    // Log audit
                    logAudit($userId, 'vote_cast', 'votes', $voteId, null, [
                        'session_id' => $sessionId,
                        'document_id' => $documentId,
                        'vote' => $vote
                    ]);
                    
                    $_SESSION['flash_success'] = "Your vote has been recorded successfully!";
                    header("Location: cast-vote.php?session=$sessionId");
                    exit;
                }
            } catch (Exception $e) {
                $errors[] = "Error recording vote: " . $e->getMessage();
            }
        }
    }
}

$pageTitle = $sessionId ? 'Cast Vote' : 'Active Voting Sessions';
$currentPage = 'cast-vote';
$breadcrumbs = [
    ['label' => 'Voting', 'url' => '#'],
    ['label' => 'Cast Vote']
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
        <?php if (!$sessionId): ?>
            <!-- Session Selection View -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Cast Your Vote</h1>
                    <p class="text-gray-600 text-sm mt-1">Select an active session to participate in voting</p>
                </div>
            </div>
            
            <?php if (empty($activeSessions)): ?>
                <div class="bg-white rounded-xl shadow-md p-12 text-center">
                    <i class="bi bi-calendar-x text-6xl text-gray-300 mb-4"></i>
                    <h2 class="text-xl font-semibold text-gray-700 mb-2">No Active Sessions</h2>
                    <p class="text-gray-500 mb-4">There are no voting sessions currently in progress.</p>
                    <a href="sessions.php" class="inline-flex items-center text-blue-600 hover:text-blue-700">
                        <i class="bi bi-arrow-left mr-2"></i> View All Sessions
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($activeSessions as $activeSession): ?>
                        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <div class="bg-gradient-to-r from-green-500 to-green-600 p-4 text-white">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm opacity-90"><?php echo htmlspecialchars($activeSession['session_number']); ?></span>
                                    <span class="flex items-center bg-white/20 px-2 py-1 rounded text-xs">
                                        <span class="w-2 h-2 bg-white rounded-full mr-2 animate-pulse"></span>
                                        LIVE
                                    </span>
                                </div>
                                <h3 class="text-lg font-bold mt-2"><?php echo htmlspecialchars($activeSession['title']); ?></h3>
                            </div>
                            <div class="p-4">
                                <div class="flex items-center text-gray-500 text-sm mb-2">
                                    <i class="bi bi-calendar mr-2"></i>
                                    <?php echo formatDate($activeSession['session_date'], 'M d, Y'); ?>
                                </div>
                                <div class="flex items-center text-gray-500 text-sm mb-4">
                                    <i class="bi bi-clock mr-2"></i>
                                    <?php echo date('h:i A', strtotime($activeSession['start_time'])); ?>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">
                                        <span class="font-semibold text-blue-600"><?php echo $activeSession['pending_docs']; ?></span> items pending
                                    </span>
                                    <a href="cast-vote.php?session=<?php echo $activeSession['id']; ?>" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        Enter Session
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Voting Interface -->
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Session Info Sidebar -->
                <div class="lg:w-80 space-y-4">
                    <div class="bg-white rounded-xl shadow-md p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-gray-800">Session Info</h3>
                            <span class="flex items-center bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                LIVE
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($session['session_number']); ?></p>
                        <h2 class="font-bold text-gray-900 mb-3"><?php echo htmlspecialchars($session['title']); ?></h2>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center text-gray-500">
                                <i class="bi bi-calendar w-5"></i>
                                <?php echo formatDate($session['session_date'], 'M d, Y'); ?>
                            </div>
                            <div class="flex items-center text-gray-500">
                                <i class="bi bi-clock w-5"></i>
                                <?php echo date('h:i A', strtotime($session['start_time'])); ?>
                            </div>
                            <div class="flex items-center text-gray-500">
                                <i class="bi bi-geo-alt w-5"></i>
                                <?php echo htmlspecialchars($session['location'] ?? 'Session Hall'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-md p-4">
                        <h3 class="font-semibold text-gray-800 mb-3">Voting Progress</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Total Items</span>
                                <span class="font-semibold"><?php echo count($documents); ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Your Votes Cast</span>
                                <span class="font-semibold text-green-600"><?php echo count(array_filter($documents, fn($d) => $d['my_vote'])); ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Remaining</span>
                                <span class="font-semibold text-yellow-600"><?php echo count(array_filter($documents, fn($d) => !$d['my_vote'])); ?></span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <?php
                            $votedCount = count(array_filter($documents, fn($d) => $d['my_vote']));
                            $totalCount = count($documents);
                            $progress = $totalCount > 0 ? ($votedCount / $totalCount) * 100 : 0;
                            ?>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 text-center"><?php echo round($progress); ?>% Complete</p>
                        </div>
                    </div>
                    
                    <a href="sessions.php" class="block text-center text-gray-500 hover:text-gray-700 text-sm py-2">
                        <i class="bi bi-arrow-left mr-1"></i> Back to Sessions
                    </a>
                </div>
                
                <!-- Documents for Voting -->
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-4">
                        <h1 class="text-xl font-bold text-gray-800">Documents for Voting</h1>
                    </div>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <i class="bi bi-exclamation-triangle text-red-500 mr-2"></i>
                                <span class="text-red-700"><?php echo implode(', ', $errors); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['flash_success'])): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <i class="bi bi-check-circle text-green-500 mr-2"></i>
                                <span class="text-green-700"><?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($documents)): ?>
                        <div class="bg-white rounded-xl shadow-md p-12 text-center">
                            <i class="bi bi-check-circle text-6xl text-green-400 mb-4"></i>
                            <h2 class="text-xl font-semibold text-gray-700 mb-2">All Votes Cast!</h2>
                            <p class="text-gray-500">There are no more pending documents in this session.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($documents as $index => $doc): ?>
                                <div class="bg-white rounded-xl shadow-md overflow-hidden <?php echo $doc['my_vote'] ? 'opacity-60' : ''; ?>">
                                    <div class="p-6">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="text-sm text-gray-500"><?php echo htmlspecialchars($doc['doc_number']); ?></span>
                                                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800"><?php echo ucfirst($doc['type']); ?></span>
                                                </div>
                                                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($doc['title']); ?></h3>
                                                <?php if ($doc['summary']): ?>
                                                    <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars(substr($doc['summary'], 0, 200)); ?>...</p>
                                                <?php endif; ?>
                                            </div>
                                            <a href="#" class="text-blue-600 hover:text-blue-700 text-sm ml-4">
                                                <i class="bi bi-eye mr-1"></i> View
                                            </a>
                                        </div>
                                        
                                        <?php if ($doc['my_vote']): ?>
                                            <div class="bg-gray-50 rounded-lg p-4 mt-4">
                                                <div class="flex items-center">
                                                    <i class="bi bi-check-circle text-green-500 mr-2"></i>
                                                    <span class="text-gray-700">You voted: </span>
                                                    <span class="ml-2 px-3 py-1 rounded-full text-sm font-medium <?php echo getVoteBadgeClass($doc['my_vote']); ?>">
                                                        <?php echo ucfirst($doc['my_vote']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <form method="POST" class="mt-4 border-t pt-4">
                                                <input type="hidden" name="document_id" value="<?php echo $doc['doc_id']; ?>">
                                                
                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Cast Your Vote</label>
                                                    <div class="flex gap-3">
                                                        <label class="flex-1 cursor-pointer">
                                                            <input type="radio" name="vote" value="approve" class="hidden peer" required>
                                                            <div class="peer-checked:bg-green-500 peer-checked:text-white peer-checked:border-green-500 bg-white border-2 border-gray-200 rounded-lg p-3 text-center transition-all hover:border-green-300">
                                                                <i class="bi bi-hand-thumbs-up text-2xl"></i>
                                                                <p class="font-medium mt-1">Approve</p>
                                                            </div>
                                                        </label>
                                                        <label class="flex-1 cursor-pointer">
                                                            <input type="radio" name="vote" value="reject" class="hidden peer">
                                                            <div class="peer-checked:bg-red-500 peer-checked:text-white peer-checked:border-red-500 bg-white border-2 border-gray-200 rounded-lg p-3 text-center transition-all hover:border-red-300">
                                                                <i class="bi bi-hand-thumbs-down text-2xl"></i>
                                                                <p class="font-medium mt-1">Reject</p>
                                                            </div>
                                                        </label>
                                                        <label class="flex-1 cursor-pointer">
                                                            <input type="radio" name="vote" value="abstain" class="hidden peer">
                                                            <div class="peer-checked:bg-gray-500 peer-checked:text-white peer-checked:border-gray-500 bg-white border-2 border-gray-200 rounded-lg p-3 text-center transition-all hover:border-gray-400">
                                                                <i class="bi bi-dash-circle text-2xl"></i>
                                                                <p class="font-medium mt-1">Abstain</p>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Remarks (Optional)</label>
                                                    <textarea name="remarks" rows="2" 
                                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                              placeholder="Add any comments about your vote..."></textarea>
                                                </div>
                                                
                                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium transition-colors">
                                                    <i class="bi bi-check2-circle mr-2"></i> Submit Vote
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include_once __DIR__ . '/../../../core/layouts/footer.php'; ?>
