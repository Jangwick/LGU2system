<?php
session_start();
require_once __DIR__ . '/../../../core/config/config.php';
require_once __DIR__ . '/../../../core/config/database.php';

// Check authentication and role
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

if (!hasRole(['admin', 'secretary'])) {
    header('Location: sessions.php');
    exit;
}

$errors = [];
$success = false;

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
            // Generate session number
            $year = date('Y');
            $count = dbCount('voting_sessions', "YEAR(created_at) = ?", [$year]);
            $sessionNumber = sprintf("VS-%s-%04d", $year, $count + 1);
            
            // Insert session
            $sessionId = dbInsert('voting_sessions', [
                'session_number' => $sessionNumber,
                'title' => $title,
                'description' => $description,
                'session_date' => $sessionDate,
                'start_time' => $startTime,
                'end_time' => $endTime ?: null,
                'location' => $location,
                'vote_type' => $voteType,
                'quorum_required' => $quorumRequired,
                'committee_id' => $committeeId ?: null,
                'status' => 'scheduled',
                'created_by' => $_SESSION['user_id']
            ]);
            
            if ($sessionId) {
                // Add documents to session
                foreach ($selectedDocuments as $docId) {
                    dbInsert('session_documents', [
                        'session_id' => $sessionId,
                        'document_id' => $docId,
                        'voting_status' => 'pending'
                    ]);
                }
                
                // Add attendees
                foreach ($selectedAttendees as $userId) {
                    dbInsert('session_attendees', [
                        'session_id' => $sessionId,
                        'user_id' => $userId,
                        'is_present' => 0
                    ]);
                }
                
                // Log audit
                logAudit($_SESSION['user_id'], 'create', 'voting_sessions', $sessionId, null, [
                    'session_number' => $sessionNumber,
                    'title' => $title
                ]);
                
                $_SESSION['flash_success'] = "Voting session created successfully!";
                header("Location: session-details.php?id=$sessionId");
                exit;
            }
        } catch (Exception $e) {
            $errors[] = "Error creating session: " . $e->getMessage();
        }
    }
}

$pageTitle = 'Create Voting Session';
$currentPage = 'voting-sessions';
$breadcrumbs = [
    ['label' => 'Voting', 'url' => '#'],
    ['label' => 'Sessions', 'url' => 'sessions.php'],
    ['label' => 'Create New']
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
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Create Voting Session</h1>
                <p class="text-gray-600 text-sm mt-1">Schedule a new legislative voting session</p>
            </div>
            <a href="sessions.php" class="text-gray-500 hover:text-gray-700">
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
                                <li><?php echo htmlspecialchars($error); ?></li>
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
                    <i class="bi bi-info-circle text-blue-600 mr-2"></i>
                    Session Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Session Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., Regular Session - October 2024" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Session Date <span class="text-red-500">*</span></label>
                        <input type="date" name="session_date" value="<?php echo htmlspecialchars($_POST['session_date'] ?? ''); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time <span class="text-red-500">*</span></label>
                            <input type="time" name="start_time" value="<?php echo htmlspecialchars($_POST['start_time'] ?? '09:00'); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                            <input type="time" name="end_time" value="<?php echo htmlspecialchars($_POST['end_time'] ?? '17:00'); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($_POST['location'] ?? 'Session Hall'); ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., Session Hall, Conference Room A">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Committee (Optional)</label>
                        <select name="committee_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- No specific committee --</option>
                            <?php foreach ($committees as $committee): ?>
                                <option value="<?php echo $committee['id']; ?>"><?php echo htmlspecialchars($committee['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vote Type</label>
                        <select name="vote_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="roll_call">Roll Call Vote</option>
                            <option value="voice">Voice Vote</option>
                            <option value="ballot">Ballot Vote (Secret)</option>
                            <option value="unanimous">Unanimous Consent</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quorum Required</label>
                        <input type="number" name="quorum_required" value="<?php echo htmlspecialchars($_POST['quorum_required'] ?? '5'); ?>" 
                               min="1" max="50"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Minimum number of members required for a valid vote</p>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Describe the purpose and agenda of this session..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Documents Selection -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="bi bi-file-earmark-text text-blue-600 mr-2"></i>
                    Documents for Voting
                </h2>
                
                <?php if (empty($pendingDocuments)): ?>
                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                        <i class="bi bi-inbox text-4xl text-gray-300 mb-2"></i>
                        <p class="text-gray-500">No documents pending for vote</p>
                        <a href="<?php echo DOCUMENTS_INDEX_URL; ?>" class="text-blue-600 hover:text-blue-700 text-sm">
                            Manage Documents <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg">
                        <?php foreach ($pendingDocuments as $doc): ?>
                            <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                <input type="checkbox" name="documents[]" value="<?php echo $doc['id']; ?>" 
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <div class="ml-3 flex-1">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($doc['title']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($doc['doc_number']); ?> • <?php echo ucfirst($doc['type']); ?></div>
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
                    <i class="bi bi-people text-blue-600 mr-2"></i>
                    Expected Attendees
                </h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <?php foreach ($councilors as $councilor): ?>
                        <label class="flex items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg cursor-pointer transition-colors">
                            <input type="checkbox" name="attendees[]" value="<?php echo $councilor['id']; ?>" 
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($councilor['full_name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($councilor['position'] ?? 'Councilor'); ?></div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-4">
                <a href="sessions.php" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center">
                    <i class="bi bi-plus-circle mr-2"></i>
                    Create Session
                </button>
            </div>
        </form>
    </main>
</div>

<?php include_once __DIR__ . '/../../../core/layouts/footer.php'; ?>
