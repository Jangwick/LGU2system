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
$sessionId = $_GET['session'] ?? null;
$userId = $_SESSION['user_id'];

// Check permissions - only councilors and admins can vote
if (!hasRole(['councilor', 'admin'])) {
    $_SESSION['flash_error'] = "Access denied. Only councilors and administrators can cast votes.";
    header('Location: sessions.php');
    exit;
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cast_vote'])) {
    try {
        $voting->castVote([
            'session_id' => $_POST['session_id'],
            'document_id' => $_POST['document_id'],
            'councilor_id' => $userId,
            'vote' => $_POST['vote'],
            'remarks' => $_POST['remarks'] ?? '',
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ]);
        
        $_SESSION['flash_success'] = "Your vote has been recorded successfully.";
        header("Location: cast-vote.php?session=" . $_POST['session_id']);
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get appropriate data based on view mode
if (!$sessionId) {
    // List active sessions for selection
    $activeSessions = $voting->getSessions(['status' => 'in_progress']);
} else {
    // Load session details and documents
    $session = $voting->getSession($sessionId);
    if (!$session || $session['status'] !== 'in_progress') {
        $_SESSION['flash_error'] = "The selected voting session is not currently active.";
        header('Location: cast-vote.php');
        exit;
    }
    
    // Mark as present if not already
    $voting->markAttendance($sessionId, $userId, true);
    
    // Get documents for this session
    $documents = $voting->getSessionDocuments($sessionId);
    
    // Check which documents the user has already voted on
    foreach ($documents as &$doc) {
        $vote = dbFetchOne(
            "SELECT vote FROM votes WHERE document_id = ? AND session_id = ? AND councilor_id = ?",
            [$doc['document_id'], $sessionId, $userId]
        );
        $doc['my_vote'] = $vote['vote'] ?? null;
    }
    
    $pendingDocs = array_filter($documents, fn($d) => $d['my_vote'] === null);
}

$pageTitle = 'Cast Vote';
$currentPage = 'cast-vote';
$breadcrumbs = [
    ['label' => 'Voting', 'url' => '#'],
    ['label' => 'Cast Vote']
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
        
        <?php if (isset($error)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4 animate-fade-in">
                <div class="flex items-center">
                    <i class="bi bi-exclamation-triangle text-red-500 mr-2 text-lg"></i>
                    <span class="text-red-700 font-medium"><?php echo $error; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$sessionId): ?>
            <!-- Session Selection View -->
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-8 animate-fade-in">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Active Voting Sessions</h1>
                    <p class="text-gray-600">Select an ongoing session to view documents and cast your vote.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in-up">
                    <?php if (empty($activeSessions)): ?>
                        <div class="md:col-span-2 bg-white rounded-2xl shadow-md p-12 text-center">
                            <i class="bi bi-clock-history text-6xl text-gray-200 mb-4 block"></i>
                            <h2 class="text-xl font-bold text-gray-700">No Active Sessions</h2>
                            <p class="text-gray-500 mb-6">There are currently no sessions in progress. Check back later or contact the secretary.</p>
                            <a href="sessions.php" class="text-red-600 hover:underline font-bold">View all sessions</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activeSessions as $s): ?>
                            <a href="cast-vote.php?session=<?php echo $s['id']; ?>" class="group">
                                <div class="bg-white rounded-2xl shadow-md p-6 border-2 border-transparent hover:border-red-500 transition-all transform hover:-translate-y-1 relative overflow-hidden h-full">
                                    <div class="absolute top-0 right-0 w-24 h-24 bg-red-50 rounded-full -mr-12 -mt-12 transition-all group-hover:bg-red-500 opacity-20 group-hover:opacity-10"></div>
                                    
                                    <div class="flex justify-between items-start mb-4">
                                        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full animate-pulse uppercase">
                                            <i class="bi bi-record-fill mr-1"></i> Live Session
                                        </span>
                                        <span class="text-xs text-gray-400 font-medium"><?php echo $s['session_number']; ?></span>
                                    </div>
                                    
                                    <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-red-700 transition-colors"><?php echo e($s['title']); ?></h3>
                                    <p class="text-gray-500 text-sm mb-4 line-clamp-2"><?php echo e($s['description'] ?: 'No description provided.'); ?></p>
                                    
                                    <div class="flex items-center text-sm text-gray-600 space-x-4 mb-4">
                                        <span><i class="bi bi-file-earmark-text mr-1 text-red-500"></i> <?php echo $s['document_count']; ?> Documents</span>
                                        <span><i class="bi bi-people mr-1 text-blue-500"></i> <?php echo $s['attendee_count']; ?> Present</span>
                                    </div>
                                    
                                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                                        <span class="text-xs text-gray-400 font-medium">Started: <?php echo date('h:i A', strtotime($s['actual_start_time'] ?: $s['start_time'])); ?></span>
                                        <div class="text-red-600 font-bold text-sm flex items-center group-hover:translate-x-1 transition-transform">
                                            Enter Session <i class="bi bi-arrow-right ml-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Main Voting Terminal Interface -->
            <div class="flex flex-col lg:flex-row gap-6 animate-fade-in">
                <!-- Left Column: Current Voting Dashboard -->
                <div class="flex-1">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6">
                        <!-- Session Status Bar -->
                        <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4 text-white flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <a href="cast-vote.php" class="text-gray-400 hover:text-white transition-colors" title="Exit Session">
                                    <i class="bi bi-x-circle text-xl"></i>
                                </a>
                                <div>
                                    <h2 class="font-bold"><?php echo e($session['title']); ?></h2>
                                    <p class="text-xs text-gray-400 uppercase tracking-widest font-bold"><?php echo e($session['session_number']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="hidden md:block text-right">
                                    <p class="text-xs text-gray-500 uppercase font-bold">Attendee</p>
                                    <p class="text-sm font-bold"><?php echo e($_SESSION['user_name']); ?></p>
                                </div>
                                <div class="bg-red-600 px-4 py-1.5 rounded-full text-xs font-bold uppercase animate-pulse tracking-wide shadow-lg">
                                    Live Terminal
                                </div>
                            </div>
                        </div>

                        <?php if (empty($pendingDocs)): ?>
                            <div class="p-16 text-center">
                                <div class="w-24 h-24 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                                    <i class="bi bi-check2-all text-5xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Voting Complete!</h3>
                                <p class="text-gray-500 mb-8 max-w-md mx-auto">You have successfully cast your votes for all documents in this session. You can now wait for new items or return to session list.</p>
                                <div class="flex justify-center gap-4">
                                    <a href="results.php?session=<?php echo $sessionId; ?>" class="bg-gray-800 text-white px-6 py-3 rounded-xl font-bold hover:bg-gray-700 transition-all shadow-lg">View Preliminary Results</a>
                                    <a href="cast-vote.php" class="bg-gray-100 text-gray-600 px-6 py-3 rounded-xl font-bold hover:bg-gray-200 transition-all">Exit Session</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Current Document to Vote -->
                            <?php $currentDoc = reset($pendingDocs); ?>
                            <div class="p-6 md:p-8">
                                <div class="mb-8">
                                    <div class="flex items-center gap-2 mb-4">
                                        <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full uppercase"><?php echo ucfirst($currentDoc['type']); ?></span>
                                        <span class="text-gray-400">•</span>
                                        <span class="text-sm text-gray-500 font-bold"><?php echo e($currentDoc['doc_number']); ?></span>
                                    </div>
                                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-3 leading-tight"><?php echo e($currentDoc['title']); ?></h1>
                                    <p class="text-gray-600 leading-relaxed text-lg bg-gray-50 p-6 rounded-xl border border-gray-100 italic">
                                        <?php echo e($currentDoc['summary'] ?: 'No summary available for this document.'); ?>
                                    </p>
                                </div>

                                <!-- Voting Interface -->
                                <form method="POST" class="animate-fade-in-up" onsubmit="return confirm('Ensure your choice is final before submitting. Cast this vote?');">
                                    <input type="hidden" name="session_id" value="<?php echo $sessionId; ?>">
                                    <input type="hidden" name="document_id" value="<?php echo $currentDoc['document_id']; ?>">
                                    <input type="hidden" name="cast_vote" value="1">
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                                        <!-- Approve -->
                                        <label class="relative group cursor-pointer">
                                            <input type="radio" name="vote" value="approve" class="peer hidden" required>
                                            <div class="h-full p-6 bg-white border-2 border-gray-100 rounded-2xl flex flex-col items-center justify-center transition-all peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-300 hover:shadow-lg transform active:scale-95">
                                                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4 transition-all peer-checked:bg-green-600 peer-checked:text-white group-hover:scale-110">
                                                    <i class="bi bi-check-lg text-3xl"></i>
                                                </div>
                                                <span class="text-xl font-bold text-gray-800 peer-checked:text-green-700">Approve</span>
                                                <p class="text-xs text-gray-400 mt-2 text-center">Cast an affirmative vote</p>
                                                <div class="absolute top-3 right-3 opacity-0 peer-checked:opacity-100 transition-opacity">
                                                    <i class="bi bi-check-circle-fill text-green-500 text-xl"></i>
                                                </div>
                                            </div>
                                        </label>

                                        <!-- Reject -->
                                        <label class="relative group cursor-pointer">
                                            <input type="radio" name="vote" value="reject" class="peer hidden" required>
                                            <div class="h-full p-6 bg-white border-2 border-gray-100 rounded-2xl flex flex-col items-center justify-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-300 hover:shadow-lg transform active:scale-95">
                                                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4 transition-all peer-checked:bg-red-600 peer-checked:text-white group-hover:scale-110">
                                                    <i class="bi bi-x-lg text-3xl"></i>
                                                </div>
                                                <span class="text-xl font-bold text-gray-800 peer-checked:text-red-700">Reject</span>
                                                <p class="text-xs text-gray-400 mt-2 text-center">Cast a negative vote</p>
                                                <div class="absolute top-3 right-3 opacity-0 peer-checked:opacity-100 transition-opacity">
                                                    <i class="bi bi-check-circle-fill text-red-500 text-xl"></i>
                                                </div>
                                            </div>
                                        </label>

                                        <!-- Abstain -->
                                        <label class="relative group cursor-pointer">
                                            <input type="radio" name="vote" value="abstain" class="peer hidden" required>
                                            <div class="h-full p-6 bg-white border-2 border-gray-100 rounded-2xl flex flex-col items-center justify-center transition-all peer-checked:border-gray-500 peer-checked:bg-gray-50 hover:border-gray-300 hover:shadow-lg transform active:scale-95">
                                                <div class="w-16 h-16 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center mb-4 transition-all peer-checked:bg-gray-600 peer-checked:text-white group-hover:scale-110">
                                                    <i class="bi bi-slash-circle text-3xl"></i>
                                                </div>
                                                <span class="text-xl font-bold text-gray-800 peer-checked:text-gray-700">Abstain</span>
                                                <p class="text-xs text-gray-400 mt-2 text-center">Formally decline to vote</p>
                                                <div class="absolute top-3 right-3 opacity-0 peer-checked:opacity-100 transition-opacity">
                                                    <i class="bi bi-check-circle-fill text-gray-500 text-xl"></i>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="mb-8">
                                        <label class="block text-sm font-bold text-gray-700 uppercase mb-2">Optional Remarks / Explanation</label>
                                        <textarea name="remarks" rows="2" 
                                                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all" 
                                                  placeholder="Provide context for your vote if necessary..."></textarea>
                                    </div>

                                    <button type="submit" class="w-full bg-red-800 text-white py-4 rounded-2xl font-bold text-xl hover:bg-red-700 transition-all shadow-xl flex items-center justify-center gap-3 transform hover:-translate-y-1 active:scale-95">
                                        Submit Decision
                                        <i class="bi bi-send-fill text-xl"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Session Info Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white p-5 rounded-2xl shadow-md border-b-4 border-gray-100">
                            <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 tracking-widest">Committee</h4>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center text-red-600">
                                    <i class="bi bi-building"></i>
                                </div>
                                <span class="font-bold text-gray-800"><?php echo e($session['committee_name'] ?? 'Plenary Session'); ?></span>
                            </div>
                        </div>
                        <div class="bg-white p-5 rounded-2xl shadow-md border-b-4 border-gray-100">
                            <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 tracking-widest">Attendance Status</h4>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center text-green-600">
                                    <i class="bi bi-person-check-fill"></i>
                                </div>
                                <span class="font-bold text-green-700">Currently Present</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Voting Progress & History -->
                <div class="w-full lg:w-80">
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sticky top-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="font-bold text-gray-800 uppercase tracking-widest text-sm">Progress</h3>
                            <span class="text-xs font-bold px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                                <?php echo count($documents) - count($pendingDocs); ?>/<?php echo count($documents); ?>
                            </span>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="w-full h-3 bg-gray-100 rounded-full mb-8 overflow-hidden">
                            <?php $percent = (count($documents) - count($pendingDocs)) / count($documents) * 100; ?>
                            <div class="h-full bg-red-600 transition-all duration-1000 ease-out shadow-sm" style="width: <?php echo $percent; ?>%"></div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="font-bold text-gray-800 text-xs uppercase tracking-widest mb-4">Voting History</h3>
                            <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                                <?php foreach ($documents as $doc): ?>
                                    <div class="p-3 rounded-xl border <?php echo $doc['my_vote'] ? 'bg-gray-50 border-gray-200 opacity-70' : 'bg-red-50 border-red-100 active-document'; ?> transition-all">
                                        <div class="flex justify-between items-start gap-2 mb-1">
                                            <span class="text-[10px] uppercase font-bold text-gray-400"><?php echo e($doc['doc_number']); ?></span>
                                            <?php if ($doc['my_vote']): ?>
                                                <span class="px-2 py-0.5 text-[9px] font-bold rounded-full uppercase shadow-sm <?php echo getVoteBadgeClass($doc['my_vote']); ?>">
                                                    <?php echo $doc['my_vote']; ?>
                                                </span>
                                            <?php elseif (!$doc['my_vote'] && $doc === reset($pendingDocs)): ?>
                                                <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-red-600 text-white uppercase shadow-sm pulse-effect">
                                                    Voting Now
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs font-bold text-gray-800 line-clamp-2 leading-tight"><?php echo e($doc['title']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<style>
    .animate-fade-in { animation: fadeIn 0.8s ease-out; }
    .animate-fade-in-up { animation: fadeInUp 0.8s ease-out forwards; opacity: 0; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 20px; }
    
    .pulse-effect { animation: pulseMini 2s infinite; }
    @keyframes pulseMini {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .active-document {
        border-left: 4px solid #991b1b !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
</style>
