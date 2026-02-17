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
    header('Location: sessions.php');
    exit;
}

$voting = new VotingController();
$sessionId = $_GET['id'] ?? null;

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

if ($session['status'] !== 'scheduled') {
    $_SESSION['flash_error'] = "Only scheduled sessions can be edited.";
    header("Location: session-details.php?id=$sessionId");
    exit;
}

$errors = [];

// Get committees for dropdown  
$committees = dbFetchAll("SELECT id, name FROM committees WHERE is_active = 1 ORDER BY name");

// Get documents that are pending vote (or already assigned to this session)
$assignedDocIds = array_column(
    dbFetchAll("SELECT document_id FROM session_documents WHERE session_id = ?", [$sessionId]),
    'document_id'
);

$pendingDocuments = dbFetchAll(
    "SELECT id, doc_number, title, type FROM documents WHERE status = 'pending_vote' OR id IN (" . 
    (empty($assignedDocIds) ? '0' : implode(',', array_map('intval', $assignedDocIds))) . 
    ") ORDER BY created_at DESC"
);

// Get councilors for attendees
$councilors = dbFetchAll("SELECT id, full_name, position FROM users WHERE role IN ('councilor', 'admin') AND is_active = 1 ORDER BY full_name");

// Get currently assigned attendees
$assignedAttendeeIds = array_column(
    dbFetchAll("SELECT user_id FROM session_attendees WHERE session_id = ?", [$sessionId]),
    'user_id'
);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $sessionDate = $_POST['session_date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $voteType = $_POST['vote_type'] ?? 'roll_call';
    $quorumRequired = intval($_POST['quorum_required'] ?? 5);
    $committeeId = $_POST['committee_id'] ?? null;
    $description = trim($_POST['description'] ?? '');
    $selectedDocuments = $_POST['documents'] ?? [];
    $selectedAttendees = $_POST['attendees'] ?? [];
    
    // Validation
    if (empty($title)) {
        $errors[] = "Session title is required.";
    }
    if (empty($sessionDate)) {
        $errors[] = "Session date is required.";
    }
    if (empty($startTime)) {
        $errors[] = "Start time is required.";
    }
    
    if (empty($errors)) {
        try {
            $voting->updateSession($sessionId, [
                'title' => $title,
                'description' => $description,
                'session_date' => $sessionDate,
                'start_time' => $startTime,
                'end_time' => $endTime ?: null,
                'location' => $location,
                'vote_type' => $voteType,
                'quorum_required' => $quorumRequired,
                'committee_id' => $committeeId ?: null,
                'documents' => $selectedDocuments,
                'attendees' => $selectedAttendees,
            ]);
            
            logAudit('session_update', $_SESSION['user_id'], 'voting', 'voting_sessions', $sessionId, 'Voting session updated', [
                'session_number' => $session['session_number'],
                'title' => $title
            ]);
            
            $_SESSION['flash_success'] = "Session updated successfully!";
            header("Location: session-details.php?id=$sessionId");
            exit;
        } catch (Exception $e) {
            $errors[] = "Error updating session: " . $e->getMessage();
        }
    }
}

$pageTitle = 'Edit Session - ' . $session['session_number'];
$currentPage = 'voting-sessions';
$breadcrumbs = [
    ['label' => 'Voting', 'url' => '#'],
    ['label' => 'Sessions', 'url' => 'sessions.php'],
    ['label' => $session['session_number'], 'url' => 'session-details.php?id=' . $sessionId],
    ['label' => 'Edit']
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
    <main class="flex-1 overflow-y-auto vdm-page-bg p-3 md:p-6 custom-scrollbar">
        <!-- Page Header -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="relative flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight">Edit Voting Session</h1>
                    <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium"><?php echo e($session['session_number']); ?> — <?php echo e($session['title']); ?></p>
                </div>
                <div class="shrink-0">
                    <a href="session-details.php?id=<?php echo $sessionId; ?>" class="!bg-white !text-red-600 hover:!bg-gray-50 px-6 py-2.5 rounded-xl font-bold shadow-lg transition-all duration-500 transform hover:-translate-y-0.5 flex items-center group border border-red-600">
                        <i class="bi bi-arrow-left mr-2"></i>
                        Back to Details
                    </a>
                </div>
            </div>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded-[2rem] p-8 mb-8 animate-shake">
                <div class="flex items-start">
                    <div class="w-12 h-12 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center shrink-0 mr-6">
                        <i class="bi bi-exclamation-octagon-fill text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-red-800 font-black text-lg mb-2">Please correct the following:</h4>
                        <ul class="text-sm text-red-700 font-bold space-y-1">
                            <?php foreach ($errors as $error): ?>
                                <li class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                                    <?php echo e($error); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="vdm-form space-y-8 pb-32 animate-fade-in-up">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Session Details Sidebar (Left) -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Basic Information Card -->
                    <div class="vdm-card rounded-[2.5rem] shadow-sm p-8 md:p-10 border">
                        <div class="flex items-center gap-4 mb-10">
                            <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-2xl flex items-center justify-center text-xl">
                                <i class="bi bi-info-circle-fill"></i>
                            </div>
                            <h2 class="text-2xl font-black vdm-heading tracking-tight">Update Information</h2>
                        </div>
                        
                        <div class="space-y-8">
                            <div>
                                <label class="block text-[11px] font-black vdm-label uppercase tracking-widest mb-3 ml-1">Session Title <span class="text-red-500">*</span></label>
                                <input type="text" name="title" value="<?php echo e($_POST['title'] ?? $session['title']); ?>" required
                                       class="w-full px-6 py-4 vdm-input-field rounded-[1.5rem] border focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-black vdm-input"
                                       placeholder="e.g. Regular Session - Resolution Planning">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label class="block text-[11px] font-black vdm-label uppercase tracking-widest mb-3 ml-1">Session Date <span class="text-red-500">*</span></label>
                                    <div class="relative group">
                                        <i class="bi bi-calendar absolute left-5 top-1/2 transform -translate-y-1/2 text-slate-400 group-focus-within:text-red-500 transition-colors"></i>
                                        <input type="date" name="session_date" value="<?php echo e($_POST['session_date'] ?? $session['session_date']); ?>" required
                                               class="w-full pl-14 pr-6 py-4 vdm-input-field rounded-[1.5rem] border focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-black vdm-input">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-black vdm-label uppercase tracking-widest mb-3 ml-1">Start <span class="text-red-500">*</span></label>
                                        <input type="time" name="start_time" value="<?php echo e($_POST['start_time'] ?? $session['start_time']); ?>" required
                                               class="w-full px-6 py-4 vdm-input-field rounded-[1.5rem] border focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-black vdm-input text-center">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-black vdm-label uppercase tracking-widest mb-3 ml-1">Est. End</label>
                                        <input type="time" name="end_time" value="<?php echo e($_POST['end_time'] ?? $session['end_time']); ?>"
                                               class="w-full px-6 py-4 vdm-input-field rounded-[1.5rem] border focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-black vdm-input text-center">
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-[11px] font-black vdm-label uppercase tracking-widest mb-3 ml-1">Description / Agenda</label>
                                <textarea name="description" rows="4" 
                                          class="w-full px-6 py-4 vdm-input-field rounded-[1.5rem] border focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none font-black vdm-input resize-none"
                                          placeholder="Provide a brief summary of the session goals..."><?php echo e($_POST['description'] ?? $session['description']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Selection Card -->
                    <div class="vdm-card rounded-[2.5rem] shadow-sm p-8 md:p-10 border">
                        <div class="flex items-center justify-between mb-10">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center text-xl">
                                    <i class="bi bi-file-earmark-check-fill"></i>
                                </div>
                                <h2 class="text-2xl font-black vdm-heading tracking-tight">Legislative Items</h2>
                            </div>
                            <span class="text-[10px] font-black bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 px-4 py-1.5 rounded-xl border border-blue-100 dark:border-blue-800 uppercase tracking-widest">Select Documents</span>
                        </div>
                        
                        <?php if (empty($pendingDocuments)): ?>
                            <div class="vdm-card rounded-[2rem] p-16 text-center border-2 border-dashed">
                                <i class="bi bi-file-earmark-text-fill text-5xl vdm-muted mb-4 block"></i>
                                <p class="vdm-sub font-black">No documents available.</p>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 gap-4 max-h-[500px] overflow-y-auto pr-3 custom-scrollbar">
                                <?php foreach ($pendingDocuments as $doc): ?>
                                    <label class="group relative vdm-card rounded-[1.5rem] p-5 flex items-center cursor-pointer hover:border-red-200 dark:hover:border-red-900/50 transition-all shadow-sm border">
                                        <input type="checkbox" name="documents[]" value="<?php echo $doc['id']; ?>" 
                                               <?php echo in_array($doc['id'], $assignedDocIds) ? 'checked' : ''; ?>
                                               class="w-6 h-6 text-red-600 rounded-xl border-slate-200 dark:border-slate-700 focus:ring-red-500/20 transition-all mr-5">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <span class="text-[9px] font-black vdm-sub uppercase tracking-[0.1em] group-hover:text-red-500 transition-colors"><?php echo e($doc['doc_number']); ?></span>
                                                <span class="text-[8px] vdm-badge px-2.5 py-1 rounded-lg border uppercase font-black tracking-tighter"><?php echo e($doc['type']); ?></span>
                                            </div>
                                            <h4 class="text-sm font-black vdm-heading group-hover:text-red-800 dark:group-hover:text-red-500 transition-colors"><?php echo e($doc['title']); ?></h4>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Session Configuration (Right) -->
                <div class="space-y-8">
                    <!-- Session Config Card -->
                    <div class="vdm-card rounded-[2.5rem] shadow-sm p-8 border">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-11 h-11 bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 rounded-2xl flex items-center justify-center text-lg">
                                <i class="bi bi-gear-fill"></i>
                            </div>
                            <h3 class="text-xl font-black vdm-heading tracking-tight">Settings</h3>
                        </div>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-[11px] font-black vdm-label uppercase tracking-widest mb-3 ml-1">Committee</label>
                                <select name="committee_id" class="w-full px-5 py-3.5 vdm-input-field rounded-[1.25rem] border focus:ring-4 focus:ring-red-500/10 outline-none font-black vdm-input">
                                    <option value="">-- Plenary Session --</option>
                                    <?php foreach ($committees as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo ($session['committee_id'] == $c['id']) ? 'selected' : ''; ?>><?php echo e($c['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-[11px] font-black vdm-label uppercase tracking-widest mb-3 ml-1">Vote Method</label>
                                <select name="vote_type" class="w-full px-5 py-3.5 vdm-input-field rounded-[1.25rem] border focus:ring-4 focus:ring-red-500/10 outline-none font-black vdm-input">
                                    <?php
                                    $voteTypes = ['roll_call' => 'Roll Call Vote', 'voice' => 'Voice Vote', 'ballot' => 'Secret Ballot', 'unanimous' => 'Unanimous Consent'];
                                    foreach ($voteTypes as $val => $label):
                                    ?>
                                        <option value="<?php echo $val; ?>" <?php echo ($session['vote_type'] ?? 'roll_call') === $val ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-[11px] font-black vdm-label uppercase tracking-widest mb-3 ml-1">Location</label>
                                <input type="text" name="location" value="<?php echo e($session['location']); ?>"
                                       class="w-full px-5 py-3.5 vdm-input-field rounded-[1.25rem] border focus:ring-4 focus:ring-red-500/10 outline-none font-black vdm-input">
                            </div>
                            
                            <div>
                                <label class="block text-[11px] font-black vdm-label uppercase tracking-widest mb-3 ml-1">Min. Quorum</label>
                                <input type="number" name="quorum_required" value="<?php echo e($session['quorum_required']); ?>" min="1" max="100"
                                       class="w-full px-5 py-3.5 vdm-input-field rounded-[1.25rem] border focus:ring-4 focus:ring-red-500/10 outline-none font-black vdm-input">
                            </div>
                        </div>
                    </div>

                    <!-- Participants Card -->
                    <div class="vdm-card rounded-[2.5rem] shadow-sm p-8 border max-h-[600px] flex flex-col">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-4">
                                <div class="w-11 h-11 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-2xl flex items-center justify-center text-lg">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <h3 class="text-xl font-black vdm-heading tracking-tight">Expected Attendees</h3>
                            </div>
                        </div>
                        <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar space-y-3">
                            <?php foreach ($councilors as $user): ?>
                                <label class="flex items-center p-4 vdm-card rounded-[1.5rem] cursor-pointer hover:border-red-100 dark:hover:border-red-900/50 transition-all border group shadow-sm">
                                    <input type="checkbox" name="attendees[]" value="<?php echo $user['id']; ?>" 
                                           <?php echo in_array($user['id'], $assignedAttendeeIds) ? 'checked' : ''; ?>
                                           class="w-5 h-5 text-red-600 rounded-lg border-slate-200 dark:border-slate-700 focus:ring-red-500/20 mr-4">
                                    <div>
                                        <p class="text-sm font-black vdm-heading leading-none group-hover:text-red-700 dark:group-hover:text-red-500 transition-colors"><?php echo e($user['full_name']); ?></p>
                                        <p class="text-[9px] vdm-sub font-black uppercase tracking-widest mt-1.5"><?php echo e($user['position'] ?? 'Councilor'); ?></p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Bottom Actions -->
            <div class="fixed bottom-0 left-0 right-0 vdm-footer p-6 z-50 shadow-[0_-20px_50px_-20px_rgba(0,0,0,0.1)] md:ml-64 border-t">
                <div class="max-w-7xl mx-auto flex items-center justify-between gap-6">
                    <a href="session-details.php?id=<?php echo $sessionId; ?>" class="px-8 py-4 vdm-muted font-black hover:text-red-600 transition-all uppercase tracking-widest text-sm">
                        <i class="bi bi-x-lg mr-2"></i> Cancel
                    </a>
                    <button type="submit" class="bg-[#dc2626] text-white px-12 py-5 rounded-[2rem] font-black shadow-2xl shadow-red-200 hover:bg-red-700 hover:-translate-y-1 active:scale-95 transition-all flex items-center gap-3 uppercase tracking-tight text-sm">
                        Save Changes
                        <i class="bi bi-check-circle-fill text-lg"></i>
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
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #dc262620; border-radius: 20px; }
</style>
