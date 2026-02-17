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
    <main class="flex-1 overflow-y-auto bg-gray-100 p-3 md:p-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <a href="session-details.php?id=<?php echo $sessionId; ?>" class="text-red-600 hover:text-red-700 text-sm mb-2 inline-flex items-center">
                    <i class="bi bi-arrow-left mr-1"></i> Back to Session
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Edit Voting Session</h1>
                <p class="text-gray-600 text-sm mt-1"><?php echo e($session['session_number']); ?> • <?php echo e($session['title']); ?></p>
            </div>
            <a href="session-details.php?id=<?php echo $sessionId; ?>" class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-x-lg text-xl"></i>
            </a>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="bi bi-exclamation-triangle text-red-500 text-xl mr-3"></i>
                    <div>
                        <h4 class="text-red-800 font-medium">Please fix the following errors:</h4>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="bi bi-info-circle text-red-600 mr-2"></i>
                    Session Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Session Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="<?php echo e($_POST['title'] ?? $session['title']); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="e.g., Regular Session - October 2024" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Session Date <span class="text-red-500">*</span></label>
                        <input type="date" name="session_date" value="<?php echo e($_POST['session_date'] ?? $session['session_date']); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" required>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time <span class="text-red-500">*</span></label>
                            <input type="time" name="start_time" value="<?php echo e($_POST['start_time'] ?? $session['start_time']); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                            <input type="time" name="end_time" value="<?php echo e($_POST['end_time'] ?? $session['end_time']); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" name="location" value="<?php echo e($_POST['location'] ?? $session['location']); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="e.g., Session Hall, Conference Room A">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Committee (Optional)</label>
                        <select name="committee_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="">-- No specific committee --</option>
                            <?php foreach ($committees as $committee): ?>
                                <option value="<?php echo $committee['id']; ?>" 
                                    <?php echo ($session['committee_id'] == $committee['id']) ? 'selected' : ''; ?>>
                                    <?php echo e($committee['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vote Type</label>
                        <select name="vote_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <?php
                            $voteTypes = ['roll_call' => 'Roll Call Vote', 'voice' => 'Voice Vote', 'ballot' => 'Ballot Vote (Secret)', 'unanimous' => 'Unanimous Consent'];
                            foreach ($voteTypes as $val => $label):
                            ?>
                                <option value="<?php echo $val; ?>" <?php echo ($session['vote_type'] ?? 'roll_call') === $val ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quorum Required</label>
                        <input type="number" name="quorum_required" value="<?php echo e($_POST['quorum_required'] ?? $session['quorum_required']); ?>" 
                               min="1" max="50"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <p class="text-xs text-gray-500 mt-1">Minimum number of members required for a valid vote</p>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Describe the purpose and agenda of this session..."><?php echo e($_POST['description'] ?? $session['description']); ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Documents Selection -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="bi bi-file-earmark-text text-red-600 mr-2"></i>
                    Documents for Voting
                </h2>
                
                <?php if (empty($pendingDocuments)): ?>
                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                        <i class="bi bi-inbox text-4xl text-gray-300 mb-2"></i>
                        <p class="text-gray-500">No documents available for voting</p>
                    </div>
                <?php else: ?>
                    <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg">
                        <?php foreach ($pendingDocuments as $doc): ?>
                            <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                <input type="checkbox" name="documents[]" value="<?php echo $doc['id']; ?>" 
                                       class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
                                       <?php echo in_array($doc['id'], $assignedDocIds) ? 'checked' : ''; ?>>
                                <div class="ml-3 flex-1">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($doc['title']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo e($doc['doc_number']); ?> • <?php echo ucfirst($doc['type']); ?></div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-2"><i class="bi bi-info-circle mr-1"></i> Select documents to be included in this voting session</p>
                <?php endif; ?>
            </div>
            
            <!-- Attendees Selection -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="bi bi-people text-red-600 mr-2"></i>
                    Expected Attendees
                </h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <?php foreach ($councilors as $councilor): ?>
                        <label class="flex items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg cursor-pointer transition-colors">
                            <input type="checkbox" name="attendees[]" value="<?php echo $councilor['id']; ?>" 
                                   class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500" 
                                   <?php echo in_array($councilor['id'], $assignedAttendeeIds) ? 'checked' : ''; ?>>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($councilor['full_name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e($councilor['position'] ?? 'Councilor'); ?></div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="flex items-center justify-between">
                <a href="session-details.php?id=<?php echo $sessionId; ?>" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors inline-flex items-center">
                    <i class="bi bi-check-circle mr-2"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
