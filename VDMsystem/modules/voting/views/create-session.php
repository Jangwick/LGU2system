<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';
require_once __DIR__ . '/../controllers/VotingController.php';

// Check authentication and role
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

if (!hasRole(['admin', 'secretary'])) {
    $_SESSION['flash_error'] = "Access denied. Only administrators and secretaries can create voting sessions.";
    header('Location: sessions.php');
    exit;
}

$voting = new VotingController();
$errors = [];

// Get committees for dropdown
$committees = dbFetchAll("SELECT id, name FROM committees WHERE is_active = 1 ORDER BY name");

// Get documents that are pending vote
$pendingDocuments = dbFetchAll("SELECT id, doc_number, title, type FROM documents WHERE status = 'pending_vote' ORDER BY created_at DESC");

// Get councilors for attendees
$councilors = dbFetchAll("SELECT id, full_name, position FROM users WHERE role IN ('councilor', 'admin') AND is_active = 1 ORDER BY full_name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $sessionDate = $_POST['session_date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $location = trim($_POST['location'] ?? 'Session Hall');
    $voteType = $_POST['vote_type'] ?? 'roll_call';
    $quorumRequired = intval($_POST['quorum_required'] ?? 5);
    $committeeId = $_POST['committee_id'] ?? null;
    $description = trim($_POST['description'] ?? '');
    $selectedDocuments = $_POST['documents'] ?? [];
    $selectedAttendees = $_POST['attendees'] ?? [];
    
    // Validation
    if (empty($title)) $errors[] = "Session title is required.";
    if (empty($sessionDate)) $errors[] = "Session date is required.";
    if (empty($startTime)) $errors[] = "Start time is required.";
    if (empty($selectedDocuments)) $errors[] = "Please select at least one document for voting.";
    if (empty($selectedAttendees)) $errors[] = "Please select at least one attendee.";
    
    if (empty($errors)) {
        try {
            $sessionId = $voting->createSession([
                'title' => $title,
                'description' => $description,
                'session_date' => $sessionDate,
                'start_time' => $startTime,
                'end_time' => $endTime ?: null,
                'location' => $location,
                'vote_type' => $voteType,
                'quorum_required' => $quorumRequired,
                'committee_id' => $committeeId ?: null,
                'created_by' => $_SESSION['user_id'],
                'documents' => $selectedDocuments,
                'attendees' => $selectedAttendees
            ]);
            
            if ($sessionId) {
                // Log audit
                logAudit('session_create', $_SESSION['user_id'], 'voting', 'voting_sessions', $sessionId, 'Voting session created', [
                    'session_title' => $title
                ]);
                
                $_SESSION['flash_success'] = "Voting session created successfully!";
                header('Location: sessions.php');
                exit;
            }
        } catch (Exception $e) {
            $errors[] = "Error creating session: " . $e->getMessage();
        }
    }
}

$pageTitle = 'Create Voting Session';
$currentPage = 'sessions';
$breadcrumbs = [
    ['label' => 'Voting', 'url' => '#'],
    ['label' => 'Sessions', 'url' => 'sessions.php'],
    ['label' => 'Create']
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
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-6 text-white animate-fade-in">
            <h1 class="text-3xl font-bold mb-2">Configure New Session</h1>
            <p class="text-red-100 opacity-90">Set up legislative sessions, documents, and expected attendees.</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6 animate-shake">
                <div class="flex items-start">
                    <i class="bi bi-exclamation-octagon text-red-500 text-2xl mr-4"></i>
                    <div>
                        <h4 class="text-red-800 font-bold">Please correct the following:</h4>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6 pb-20 animate-fade-in-up">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Session Details Sidebar (Left) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information Card -->
                    <div class="bg-white rounded-2xl shadow-md p-6 md:p-8 border border-gray-100">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-red-100 text-red-600 rounded-full flex items-center justify-center">
                                <i class="bi bi-info-circle-fill"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Basic Information</h2>
                        </div>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Session Title <span class="text-red-500">*</span></label>
                                <input type="text" name="title" value="<?php echo e($_POST['title'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:bg-white transition-all outline-none"
                                       placeholder="e.g. Regular Session - Resolution Planning">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Session Date <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <i class="bi bi-calendar absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                        <input type="date" name="session_date" value="<?php echo e($_POST['session_date'] ?? date('Y-m-d')); ?>" required
                                               class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 transition-all outline-none">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Start <span class="text-red-500">*</span></label>
                                        <input type="time" name="start_time" value="<?php echo e($_POST['start_time'] ?? '14:00'); ?>" required
                                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 transition-all outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Est. End</label>
                                        <input type="time" name="end_time" value="<?php echo e($_POST['end_time'] ?? ''); ?>"
                                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 transition-all outline-none">
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Description / Agenda</label>
                                <textarea name="description" rows="4" 
                                          class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all"
                                          placeholder="Provide a brief summary of the session goals..."><?php echo e($_POST['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Selection Card -->
                    <div class="bg-white rounded-2xl shadow-md p-6 md:p-8 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                                    <i class="bi bi-file-earmark-check-fill"></i>
                                </div>
                                <h2 class="text-xl font-bold text-gray-800">Legislative Items</h2>
                            </div>
                            <span class="text-xs font-bold bg-blue-50 text-blue-700 px-3 py-1 rounded-full border border-blue-100">Pending Vote</span>
                        </div>
                        
                        <?php if (empty($pendingDocuments)): ?>
                            <div class="bg-gray-50 rounded-2xl p-12 text-center border-2 border-dashed border-gray-200">
                                <i class="bi bi-file-earmark-text text-4xl text-gray-300 mb-3 block"></i>
                                <p class="text-gray-500">No documents found with 'Pending Vote' status.</p>
                                <p class="text-xs text-gray-400 mt-2">Upload or approve documents in LRMS first.</p>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 gap-3 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                                <?php foreach ($pendingDocuments as $doc): ?>
                                    <label class="group relative bg-white border border-gray-200 rounded-xl p-4 flex items-center cursor-pointer hover:bg-red-50 transition-all">
                                        <input type="checkbox" name="documents[]" value="<?php echo $doc['id']; ?>" class="w-5 h-5 text-red-600 rounded-md border-gray-300 focus:ring-red-500 transition-all mr-4">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"><?php echo e($doc['doc_number']); ?></span>
                                                <span class="text-[10px] bg-white px-2 py-0.5 rounded-full border border-gray-100 shadow-sm uppercase font-bold text-gray-500"><?php echo e($doc['type']); ?></span>
                                            </div>
                                            <h4 class="text-sm font-bold text-gray-800"><?php echo e($doc['title']); ?></h4>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Session Configuration (Right) -->
                <div class="space-y-6">
                    <!-- Session Config Card -->
                    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
                        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-widest mb-6 border-b border-gray-100 pb-4">Session Settings</h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Committee</label>
                                <select name="committee_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all">
                                    <option value="">-- Plenary Session --</option>
                                    <?php foreach ($committees as $c): ?>
                                        <option value="<?php echo $c['id']; ?>"><?php echo e($c['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Vote Method</label>
                                <select name="vote_type" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all">
                                    <option value="roll_call">Roll Call Vote</option>
                                    <option value="voice">Voice Vote</option>
                                    <option value="ballot">Secret Ballot</option>
                                    <option value="unanimous">Unanimous Consent</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Location</label>
                                <input type="text" name="location" value="Main Session Hall"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Min. Quorum</label>
                                <input type="number" name="quorum_required" value="5" min="1" max="100"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all">
                                <p class="text-[10px] text-gray-400 mt-2 italic">* Required members present to validate session.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Participants Card -->
                    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 max-h-[500px] flex flex-col">
                        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-widest mb-6 border-b border-gray-100 pb-4">Expected Attendees</h3>
                        <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar space-y-2">
                            <?php foreach ($councilors as $user): ?>
                                <label class="flex items-center p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-blue-50 transition-all border border-transparent hover:border-blue-100">
                                    <input type="checkbox" name="attendees[]" value="<?php echo $user['id']; ?>" checked class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 mr-3">
                                    <div>
                                        <p class="text-sm font-bold text-gray-800 leading-none"><?php echo e($user['full_name']); ?></p>
                                        <p class="text-[10px] text-gray-500 font-medium uppercase mt-1"><?php echo e($user['position']); ?></p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Bottom Actions -->
            <div class="fixed bottom-0 left-0 right-0 bg-white bg-opacity-90 backdrop-blur-md border-t border-gray-200 p-4 z-50 shadow-2xl md:ml-64">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <a href="sessions.php" class="px-6 py-3 text-gray-600 font-bold hover:text-gray-800 transition-colors">
                        <i class="bi bi-x-lg mr-2"></i> Discard
                    </a>
                    <button type="submit" class="bg-red-800 text-white px-10 py-3 rounded-xl font-bold shadow-lg hover:bg-red-700 transform hover:-translate-y-1 active:scale-95 transition-all flex items-center gap-2">
                        Create Voting Session
                        <i class="bi bi-check-circle-fill"></i>
                    </button>
                </div>
            </div>
        </form>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<style>
    .animate-fade-in { animation: fadeIn 0.6s ease-out; }
    .animate-fade-in-up { animation: fadeInUp 0.6s ease-out forwards; opacity: 0; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    .animate-shake { animation: shake 0.6s cubic-bezier(.36,.07,.19,.97) both; }
    
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 20px; }
</style>
