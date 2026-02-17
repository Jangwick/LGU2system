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

        <!-- Page Header -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="relative flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight">Cast Your Vote</h1>
                    <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium">Select an active session and cast your vote on legislative documents.</p>
                </div>
                <div class="shrink-0">
                    <a href="sessions.php" class="!bg-white !text-red-600 hover:!bg-gray-50 px-6 py-2.5 rounded-xl font-bold shadow-lg transition-all duration-500 transform hover:-translate-y-0.5 flex items-center group border border-red-600">
                        <i class="bi bi-list-ul mr-2"></i>
                        All Sessions
                    </a>
                </div>
            </div>
        </div>

        <?php if (!$sessionId): ?>
            <!-- Session Selection View -->
            <div class="max-w-4xl mx-auto">
                
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
                            <div onclick="openVotingTerminal(<?php echo $s['id']; ?>)" class="group cursor-pointer">
                                <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800 hover:border-gray-600 transition-all transform hover:-translate-y-1 relative overflow-hidden h-full hover:shadow-2xl">
                                    
                                    <div class="flex justify-between items-start mb-4">
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-green-500/40 text-green-400 bg-green-500/10">
                                            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                                            Live Session
                                        </span>
                                        <span class="text-xs text-gray-500 font-medium"><?php echo e($s['session_number']); ?></span>
                                    </div>
                                    
                                    <h3 class="text-lg font-black text-white mb-2 group-hover:text-red-100 transition-colors"><?php echo e($s['title']); ?></h3>
                                    <p class="text-gray-400 text-sm mb-5 line-clamp-2"><?php echo e($s['description'] ?: 'No description provided.'); ?></p>
                                    
                                    <div class="flex items-center gap-5 mb-5">
                                        <span class="flex items-center gap-1.5 text-gray-300 text-sm font-semibold">
                                            <i class="bi bi-file-earmark-text text-red-500"></i>
                                            <?php echo $s['document_count']; ?> Document<?php echo $s['document_count'] != 1 ? 's' : ''; ?>
                                        </span>
                                        <span class="flex items-center gap-1.5 text-gray-300 text-sm font-semibold">
                                            <i class="bi bi-people-fill text-blue-400"></i>
                                            <?php echo $s['attendee_count']; ?> Present
                                        </span>
                                    </div>
                                    
                                    <div class="pt-4 border-t border-gray-700/50 flex items-center justify-between">
                                        <span class="text-gray-500 text-xs font-black uppercase tracking-wider">Started: <?php echo date('h:i A', strtotime($s['actual_start_time'] ?? $s['start_time'])); ?></span>
                                        <span class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-xs font-black uppercase tracking-wider px-4 py-2.5 rounded-lg transition-all">
                                            Enter Terminal <i class="bi bi-arrow-right"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Voting Terminal Modal -->
            <div id="votingTerminalModal" class="fixed inset-0 z-[120] hidden">
                <div class="flex items-center justify-center min-h-screen">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-black/85 backdrop-blur-sm transition-opacity duration-300" onclick="closeVotingTerminal()"></div>
                    
                    <!-- Modal Box -->
                    <div class="relative bg-gray-950 w-full max-w-5xl mx-4 rounded-[2rem] shadow-2xl overflow-hidden transform transition-all animate-modal-in flex flex-col max-h-[95vh] border border-gray-800">
                        <!-- Terminal Header Bar -->
                        <div class="bg-gradient-to-r from-gray-900 to-gray-800 px-6 py-4 text-white flex items-center justify-between shrink-0 border-b border-gray-700/50">
                            <div class="flex items-center gap-4">
                                <button onclick="closeVotingTerminal()" class="text-gray-400 hover:text-white transition-colors" title="Exit Terminal">
                                    <i class="bi bi-x-circle text-xl"></i>
                                </button>
                                <div>
                                    <h2 id="terminalTitle" class="font-bold text-white text-sm md:text-base"></h2>
                                    <p id="terminalNumber" class="text-[10px] text-gray-500 uppercase tracking-widest font-bold"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="hidden md:block text-right">
                                    <p class="text-[9px] text-gray-500 uppercase font-bold tracking-wider">Attendee</p>
                                    <p class="text-xs font-bold text-gray-300"><?php echo e($_SESSION['user_name'] ?? 'User'); ?></p>
                                </div>
                                <div class="bg-red-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase animate-pulse tracking-widest shadow-lg shadow-red-600/30">
                                    Live Terminal
                                </div>
                            </div>
                        </div>

                        <!-- Terminal Content -->
                        <div id="terminalBody" class="flex-1 overflow-y-auto custom-scrollbar">
                            <div class="flex items-center justify-center py-20">
                                <div class="animate-spin rounded-full h-12 w-12 border-4 border-red-600 border-t-transparent"></div>
                            </div>
                        </div>

                        <!-- Terminal Footer -->
                        <div class="bg-gray-900 px-6 py-3 border-t border-gray-800 flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                                <span class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Session Active</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <span id="terminalProgress" class="text-[10px] text-gray-500 font-bold uppercase tracking-wider"></span>
                                <button onclick="closeVotingTerminal()" class="text-[10px] text-gray-500 hover:text-red-400 font-bold uppercase tracking-wider transition-colors">
                                    <i class="bi bi-box-arrow-left mr-1"></i> Exit Terminal
                                </button>
                            </div>
                        </div>
                    </div>
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
    
    @keyframes modalIn {
        from { opacity: 0; transform: scale(0.95) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .animate-modal-in { animation: modalIn 0.3s ease-out forwards; }
</style>

<script>
    // ======= VOTING TERMINAL MODAL =======
    let currentSessionId = null;
    let selectedVotes = {};

    async function openVotingTerminal(sessionId) {
        currentSessionId = sessionId;
        const modal = document.getElementById('votingTerminalModal');
        const body = document.getElementById('terminalBody');
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        try {
            const response = await fetch(`get-session.php?id=${sessionId}`);
            const data = await response.json();
            
            if (!data.success) {
                body.innerHTML = `<div class="text-center py-20 text-red-400 font-bold">Failed to load session.</div>`;
                return;
            }

            const s = data.session;
            const docs = data.documents;
            const atts = data.attendees;

            document.getElementById('terminalTitle').textContent = s.title;
            document.getElementById('terminalNumber').textContent = s.session_number;

            renderTerminal(s, docs, atts);
        } catch (error) {
            body.innerHTML = `<div class="text-center py-20 text-red-400 font-bold">Connection error. Please try again.</div>`;
        }
    }

    function renderTerminal(session, docs, attendees) {
        const body = document.getElementById('terminalBody');
        const totalPresent = attendees.filter(a => a.status === 'present').length;
        const voted = docs.filter(d => d.my_vote).length;
        const pending = docs.filter(d => !d.my_vote);
        const total = docs.length;
        const percent = total > 0 ? (voted / total * 100) : 0;

        // Update footer progress
        updateProgress(docs);

        // === NO DOCUMENTS STATE ===
        if (total === 0) {
            body.innerHTML = `
                <div class="p-6 md:p-10 animate-fade-in">
                    <!-- Session Overview Header -->
                    ${renderSessionHeader(session, totalPresent, attendees.length, voted, total, percent)}
                    
                    <!-- Empty State -->
                    <div class="bg-gray-900/60 rounded-2xl border border-gray-800 p-16 text-center mt-6">
                        <div class="w-20 h-20 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-5">
                            <i class="bi bi-inbox text-3xl text-gray-600"></i>
                        </div>
                        <h3 class="text-xl font-black text-white mb-2">No Documents Queued</h3>
                        <p class="text-gray-500 text-sm max-w-sm mx-auto mb-6">This session has no legislative items assigned for voting yet. The session administrator will add documents when ready.</p>
                        <button onclick="closeVotingTerminal()" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-6 py-2.5 rounded-xl font-bold text-sm transition-all border border-gray-700">
                            <i class="bi bi-arrow-left mr-2"></i> Back to Sessions
                        </button>
                    </div>

                    <!-- Attendees List -->
                    ${renderAttendeesList(attendees)}
                </div>
            `;
            return;
        }

        // === ALL VOTED STATE ===
        if (pending.length === 0) {
            body.innerHTML = `
                <div class="p-6 md:p-10 animate-fade-in">
                    ${renderSessionHeader(session, totalPresent, attendees.length, voted, total, percent)}
                    
                    <div class="bg-gray-900/60 rounded-2xl border border-gray-800 overflow-hidden mt-6">
                        <div class="p-12 md:p-16 text-center">
                            <div class="w-24 h-24 bg-green-500/10 text-green-400 rounded-full flex items-center justify-center mx-auto mb-6 ring-4 ring-green-500/5">
                                <i class="bi bi-check2-all text-5xl"></i>
                            </div>
                            <h3 class="text-2xl font-black text-white mb-2">Voting Complete!</h3>
                            <p class="text-gray-400 mb-8 max-w-md mx-auto text-sm">You have successfully cast your votes for all ${total} document${total !== 1 ? 's' : ''} in this session.</p>
                            <div class="flex flex-wrap justify-center gap-3">
                                <a href="results.php?session=${session.id}" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-red-600/20 text-sm">
                                    <i class="bi bi-bar-chart-fill"></i> View Results
                                </a>
                                <button onclick="closeVotingTerminal()" class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 text-gray-300 px-6 py-3 rounded-xl font-bold transition-all text-sm">
                                    <i class="bi bi-box-arrow-left"></i> Exit Session
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Vote Summary -->
                    <div class="mt-6">
                        <h4 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-3">Your Decisions</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            ${docs.map(doc => renderVotedDocCard(doc)).join('')}
                        </div>
                    </div>
                </div>
            `;
            return;
        }

        // === ACTIVE VOTING STATE ===
        const currentDoc = pending[0];

        body.innerHTML = `
            <div class="p-6 md:p-10 animate-fade-in">
                ${renderSessionHeader(session, totalPresent, attendees.length, voted, total, percent)}
                
                <!-- Main Content: Voting + Sidebar -->
                <div class="flex flex-col lg:flex-row gap-6 mt-6">
                    <!-- Left: Active Document -->
                    <div class="flex-1 min-w-0">
                        <div class="bg-gray-900/60 rounded-2xl border border-gray-800 overflow-hidden">
                            <!-- Document Info -->
                            <div class="p-6 md:p-8">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="px-3 py-1 bg-red-500/10 text-red-400 text-[10px] font-black rounded-full uppercase border border-red-500/20 tracking-wider">${(doc => doc.type ? doc.type.charAt(0).toUpperCase() + doc.type.slice(1) : 'Document')(currentDoc)}</span>
                                    <span class="text-gray-700">&bull;</span>
                                    <span class="text-xs text-gray-500 font-bold">${currentDoc.doc_number || ''}</span>
                                    <span class="ml-auto text-[10px] text-red-400 font-black uppercase tracking-wider animate-pulse"><i class="bi bi-circle-fill text-[6px] mr-1"></i> Awaiting Your Vote</span>
                                </div>
                                <h1 class="text-xl md:text-2xl font-black text-white mb-3 leading-tight">${currentDoc.title}</h1>
                                <div class="text-gray-400 leading-relaxed text-sm bg-gray-800/40 p-4 rounded-xl border border-gray-800/80">
                                    ${currentDoc.summary || 'No summary available for this document.'}
                                </div>
                            </div>

                            <!-- Vote Buttons -->
                            <div class="px-6 md:px-8 pb-6 md:pb-8">
                                <div class="grid grid-cols-3 gap-3 mb-4" id="vote-buttons-${currentDoc.document_id}">
                                    <button onclick="selectVote(${currentDoc.document_id}, 'approve')" data-vote="approve"
                                        class="vote-opt-${currentDoc.document_id} py-4 rounded-xl border-2 border-gray-800 hover:border-green-500 hover:bg-green-500/10 text-gray-500 hover:text-green-400 font-bold text-xs uppercase tracking-wider transition-all flex flex-col items-center gap-2 group">
                                        <div class="w-12 h-12 rounded-full bg-gray-800 group-hover:bg-green-500/20 flex items-center justify-center transition-all">
                                            <i class="bi bi-check-lg text-xl"></i>
                                        </div>
                                        Approve
                                    </button>
                                    <button onclick="selectVote(${currentDoc.document_id}, 'reject')" data-vote="reject"
                                        class="vote-opt-${currentDoc.document_id} py-4 rounded-xl border-2 border-gray-800 hover:border-red-500 hover:bg-red-500/10 text-gray-500 hover:text-red-400 font-bold text-xs uppercase tracking-wider transition-all flex flex-col items-center gap-2 group">
                                        <div class="w-12 h-12 rounded-full bg-gray-800 group-hover:bg-red-500/20 flex items-center justify-center transition-all">
                                            <i class="bi bi-x-lg text-xl"></i>
                                        </div>
                                        Reject
                                    </button>
                                    <button onclick="selectVote(${currentDoc.document_id}, 'abstain')" data-vote="abstain"
                                        class="vote-opt-${currentDoc.document_id} py-4 rounded-xl border-2 border-gray-800 hover:border-gray-500 hover:bg-gray-500/10 text-gray-500 hover:text-gray-300 font-bold text-xs uppercase tracking-wider transition-all flex flex-col items-center gap-2 group">
                                        <div class="w-12 h-12 rounded-full bg-gray-800 group-hover:bg-gray-500/20 flex items-center justify-center transition-all">
                                            <i class="bi bi-slash-circle text-xl"></i>
                                        </div>
                                        Abstain
                                    </button>
                                </div>
                                <button onclick="submitVote(${currentDoc.document_id})" id="submit-btn-${currentDoc.document_id}"
                                    class="w-full py-3.5 rounded-xl bg-gray-800 text-gray-600 font-black text-xs uppercase tracking-widest cursor-not-allowed transition-all" disabled>
                                    Select your vote above
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Sidebar -->
                    <div class="w-full lg:w-64 shrink-0">
                        <!-- Progress Card -->
                        <div class="bg-gray-900/60 rounded-2xl border border-gray-800 p-5 mb-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-gray-300 uppercase tracking-widest text-[10px]">Progress</h3>
                                <span class="text-[10px] font-bold px-2 py-1 bg-gray-800 text-gray-400 rounded-full">${voted}/${total}</span>
                            </div>
                            <div class="w-full h-2 bg-gray-800 rounded-full mb-1 overflow-hidden">
                                <div class="h-full bg-red-600 transition-all duration-1000 ease-out rounded-full" style="width: ${percent}%"></div>
                            </div>
                        </div>

                        <!-- Document Queue -->
                        <div class="bg-gray-900/60 rounded-2xl border border-gray-800 p-5">
                            <h3 class="font-bold text-gray-400 text-[10px] uppercase tracking-widest mb-3">Document Queue</h3>
                            <div class="space-y-2 max-h-[40vh] overflow-y-auto pr-1 custom-scrollbar">
                                ${docs.map(doc => {
                                    const isCurrent = doc.document_id === currentDoc.document_id;
                                    if (doc.my_vote) {
                                        const clr = doc.my_vote === 'approve' ? 'text-green-400' : doc.my_vote === 'reject' ? 'text-red-400' : 'text-gray-400';
                                        return `<div class="p-2.5 rounded-lg bg-gray-800/40 border border-gray-800/50 opacity-50">
                                            <div class="flex justify-between items-center gap-2">
                                                <p class="text-[10px] font-bold text-gray-500 line-clamp-1 flex-1">${doc.title}</p>
                                                <span class="text-[8px] font-black uppercase ${clr}"><i class="bi bi-check2"></i> ${doc.my_vote}</span>
                                            </div>
                                        </div>`;
                                    } else if (isCurrent) {
                                        return `<div class="p-2.5 rounded-lg bg-red-500/5 border border-red-500/20" style="border-left: 3px solid #dc2626;">
                                            <div class="flex justify-between items-center gap-2">
                                                <p class="text-[10px] font-bold text-white line-clamp-1 flex-1">${doc.title}</p>
                                                <span class="text-[7px] font-black bg-red-600 text-white px-1.5 py-0.5 rounded uppercase">Now</span>
                                            </div>
                                        </div>`;
                                    } else {
                                        return `<div class="p-2.5 rounded-lg bg-gray-900/30 border border-gray-800/30 opacity-30">
                                            <p class="text-[10px] font-bold text-gray-500 line-clamp-1">${doc.title}</p>
                                        </div>`;
                                    }
                                }).join('')}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderSessionHeader(session, present, totalAttendees, voted, totalDocs, percent) {
        return `
            <!-- Session Overview Strip -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-500/10 rounded-lg flex items-center justify-center text-red-500 shrink-0">
                        <i class="bi bi-file-earmark-text text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-600 font-black uppercase tracking-widest">Documents</p>
                        <p class="text-lg font-black text-white">${totalDocs}</p>
                    </div>
                </div>
                <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center text-green-400 shrink-0">
                        <i class="bi bi-people-fill text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-600 font-black uppercase tracking-widest">Present</p>
                        <p class="text-lg font-black text-green-400">${present}<span class="text-gray-600 text-xs">/${totalAttendees}</span></p>
                    </div>
                </div>
                <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-500/10 rounded-lg flex items-center justify-center text-purple-400 shrink-0">
                        <i class="bi bi-building text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-600 font-black uppercase tracking-widest">Committee</p>
                        <p class="text-sm font-bold text-gray-300 mt-0.5 line-clamp-1">${session.committee_name || 'Plenary'}</p>
                    </div>
                </div>
                <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-500/10 rounded-lg flex items-center justify-center text-amber-400 shrink-0">
                        <i class="bi bi-check2-square text-lg"></i>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-600 font-black uppercase tracking-widest">My Votes</p>
                        <p class="text-lg font-black text-amber-400">${voted}<span class="text-gray-600 text-xs">/${totalDocs}</span></p>
                    </div>
                </div>
            </div>
        `;
    }

    function renderVotedDocCard(doc) {
        const clr = doc.my_vote === 'approve' ? 'border-green-500/30 bg-green-500/5' :
                    doc.my_vote === 'reject' ? 'border-red-500/30 bg-red-500/5' :
                    'border-gray-700 bg-gray-800/30';
        const badge = doc.my_vote === 'approve' ? 'bg-green-500/10 text-green-400 border-green-500/20' :
                      doc.my_vote === 'reject' ? 'bg-red-500/10 text-red-400 border-red-500/20' :
                      'bg-gray-800 text-gray-400 border-gray-700';
        return `
            <div class="p-4 rounded-xl border ${clr}">
                <div class="flex justify-between items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-[9px] text-gray-600 font-bold uppercase mb-1">${doc.doc_number || ''}</p>
                        <p class="text-xs font-bold text-gray-300 line-clamp-1">${doc.title}</p>
                    </div>
                    <span class="px-2 py-1 text-[8px] font-black rounded-lg uppercase border ${badge} shrink-0">${doc.my_vote}</span>
                </div>
            </div>
        `;
    }

    function renderAttendeesList(attendees) {
        if (!attendees.length) return '';
        return `
            <div class="mt-6 bg-gray-900/60 border border-gray-800 rounded-2xl p-5">
                <h4 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">Session Attendees</h4>
                <div class="flex flex-wrap gap-2">
                    ${attendees.map(a => `
                        <div class="flex items-center gap-2 bg-gray-800/60 rounded-lg px-3 py-2 border border-gray-700/50">
                            <div class="w-7 h-7 rounded-full ${a.status === 'present' ? 'bg-green-500/15 text-green-400' : 'bg-gray-700 text-gray-500'} flex items-center justify-center text-[10px] font-black">
                                ${a.full_name.charAt(0)}
                            </div>
                            <span class="text-xs font-bold ${a.status === 'present' ? 'text-gray-300' : 'text-gray-600'}">${a.full_name}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    function selectVote(docId, vote) {
        selectedVotes[docId] = vote;
        
        // Update button styles
        document.querySelectorAll(`.vote-opt-${docId}`).forEach(btn => {
            const bv = btn.getAttribute('data-vote');
            btn.classList.remove('!border-green-500', '!bg-green-500/10', '!text-green-400',
                                 '!border-red-500', '!bg-red-500/10', '!text-red-400',
                                 '!border-gray-500', '!bg-gray-500/10', '!text-gray-300');
            if (bv === vote) {
                const colors = {
                    approve: ['!border-green-500', '!bg-green-500/10', '!text-green-400'],
                    reject: ['!border-red-500', '!bg-red-500/10', '!text-red-400'],
                    abstain: ['!border-gray-500', '!bg-gray-500/10', '!text-gray-300']
                };
                btn.classList.add(...colors[vote]);
            }
        });

        const submitBtn = document.getElementById(`submit-btn-${docId}`);
        submitBtn.disabled = false;
        submitBtn.className = 'w-full py-4 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-black text-sm uppercase tracking-widest transition-all cursor-pointer shadow-lg shadow-red-600/20';
        submitBtn.innerHTML = `Submit Decision: <span class="capitalize">${vote}</span> <i class="bi bi-send-fill ml-2"></i>`;
    }

    async function submitVote(docId) {
        const vote = selectedVotes[docId];
        if (!vote) return;

        const submitBtn = document.getElementById(`submit-btn-${docId}`);
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split mr-2"></i> Recording vote...';

        try {
            const res = await fetch('ajax-vote.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: currentSessionId,
                    document_id: docId,
                    vote: vote,
                    remarks: ''
                })
            });
            const data = await res.json();
            
            if (data.success) {
                // Re-fetch session and re-render entire terminal with next doc
                const sessionRes = await fetch(`get-session.php?id=${currentSessionId}`);
                const sessionData = await sessionRes.json();
                if (sessionData.success) {
                    selectedVotes = {};
                    renderTerminal(sessionData.session, sessionData.documents, sessionData.attendees);
                }
            } else {
                submitBtn.disabled = false;
                submitBtn.className = 'w-full py-4 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-black text-sm uppercase tracking-widest transition-all cursor-pointer';
                submitBtn.innerHTML = `Error: ${data.message} — Click to retry`;
            }
        } catch (err) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Connection error — Click to retry';
        }
    }

    function updateProgress(docs) {
        const voted = docs.filter(d => d.my_vote).length;
        const total = docs.length;
        const el = document.getElementById('terminalProgress');
        if (el) el.textContent = `${voted}/${total} Voted`;
    }

    function closeVotingTerminal() {
        document.getElementById('votingTerminalModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        currentSessionId = null;
        selectedVotes = {};
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeVotingTerminal();
    });
</script>
