<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

$sessionId = $_GET['session'] ?? null;

// If specific session, show that session's results
if ($sessionId) {
    $session = dbFetchOne(
        "SELECT vs.*, u.full_name as created_by_name 
         FROM voting_sessions vs 
         LEFT JOIN users u ON vs.created_by = u.id 
         WHERE vs.id = ?",
        [$sessionId]
    );
    
    if (!$session) {
        $_SESSION['flash_error'] = "Session not found.";
        header('Location: results.php');
        exit;
    }
    
    // Get document results for this session
    $documentResults = dbFetchAll(
        "SELECT sd.*, d.doc_number, d.title, d.type, d.status as doc_status,
                (SELECT COUNT(*) FROM votes WHERE document_id = d.id AND session_id = sd.session_id AND vote = 'approve') as approve_count,
                (SELECT COUNT(*) FROM votes WHERE document_id = d.id AND session_id = sd.session_id AND vote = 'reject') as reject_count,
                (SELECT COUNT(*) FROM votes WHERE document_id = d.id AND session_id = sd.session_id AND vote = 'abstain') as abstain_count,
                (SELECT COUNT(*) FROM votes WHERE document_id = d.id AND session_id = sd.session_id) as total_votes
         FROM session_documents sd
         JOIN documents d ON sd.document_id = d.id
         WHERE sd.session_id = ?
         ORDER BY sd.voting_order, d.created_at",
        [$sessionId]
    );
    
    // Get individual votes for each document (only visible to admin/secretary)
    $showDetails = hasRole(['admin', 'secretary']);
    
} else {
    // Get all completed sessions with results summary
    $completedSessions = dbFetchAll(
        "SELECT vs.*, u.full_name as created_by_name,
                (SELECT COUNT(*) FROM session_documents WHERE session_id = vs.id) as doc_count,
                (SELECT COUNT(*) FROM votes WHERE session_id = vs.id) as total_votes,
                (SELECT COUNT(*) FROM votes WHERE session_id = vs.id AND vote = 'approve') as total_approved,
                (SELECT COUNT(*) FROM votes WHERE session_id = vs.id AND vote = 'reject') as total_rejected
         FROM voting_sessions vs
         LEFT JOIN users u ON vs.created_by = u.id
         WHERE vs.status IN ('completed', 'in_progress')
         ORDER BY vs.session_date DESC, vs.start_time DESC"
    );
}

$pageTitle = $sessionId ? 'Session Results' : 'Voting Results';
$currentPage = 'voting-results';
$breadcrumbs = [
    ['label' => 'Voting', 'url' => '#'],
    ['label' => 'Results']
];

if ($sessionId) {
    $breadcrumbs[] = ['label' => $session['session_number']];
}

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
        <?php if (!$sessionId): ?>
            <!-- Sessions Results List -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Voting Results</h1>
                    <p class="text-gray-600 text-sm mt-1">View voting outcomes and statistics</p>
                </div>
                <?php if (hasRole(['admin', 'secretary'])): ?>
                <a href="#" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                    <i class="bi bi-download mr-2"></i>
                    Export Report
                </a>
                <?php endif; ?>
            </div>
            
            <?php if (empty($completedSessions)): ?>
                <div class="bg-white rounded-xl shadow-md p-12 text-center">
                    <i class="bi bi-bar-chart text-6xl text-gray-300 mb-4"></i>
                    <h2 class="text-xl font-semibold text-gray-700 mb-2">No Results Available</h2>
                    <p class="text-gray-500">There are no completed voting sessions yet.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($completedSessions as $cs): ?>
                        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm text-gray-500"><?php echo htmlspecialchars($cs['session_number']); ?></span>
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $cs['status'] === 'completed' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $cs['status'])); ?>
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($cs['title']); ?></h3>
                                <div class="text-sm text-gray-500 mb-4">
                                    <i class="bi bi-calendar mr-1"></i> <?php echo formatDate($cs['session_date'], 'M d, Y'); ?>
                                </div>
                                
                                <!-- Mini Stats -->
                                <div class="grid grid-cols-3 gap-2 mb-4">
                                    <div class="text-center p-2 bg-green-50 rounded-lg">
                                        <div class="text-lg font-bold text-green-600"><?php echo $cs['total_approved']; ?></div>
                                        <div class="text-xs text-gray-500">Approved</div>
                                    </div>
                                    <div class="text-center p-2 bg-red-50 rounded-lg">
                                        <div class="text-lg font-bold text-red-600"><?php echo $cs['total_rejected']; ?></div>
                                        <div class="text-xs text-gray-500">Rejected</div>
                                    </div>
                                    <div class="text-center p-2 bg-red-50 rounded-lg">
                                        <div class="text-lg font-bold text-red-600"><?php echo $cs['total_votes']; ?></div>
                                        <div class="text-xs text-gray-500">Total</div>
                                    </div>
                                </div>
                                
                                <a href="results.php?session=<?php echo $cs['id']; ?>" 
                                   class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg font-medium transition-colors">
                                    View Details <i class="bi bi-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Single Session Results -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <a href="results.php" class="text-red-600 hover:text-red-700 text-sm mb-2 inline-block">
                        <i class="bi bi-arrow-left mr-1"></i> Back to All Results
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($session['title']); ?></h1>
                    <p class="text-gray-600 text-sm mt-1"><?php echo htmlspecialchars($session['session_number']); ?> • <?php echo formatDate($session['session_date'], 'F d, Y'); ?></p>
                </div>
                <?php if (hasRole(['admin', 'secretary'])): ?>
                <div class="flex gap-2">
                    <button onclick="window.print()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                        <i class="bi bi-printer mr-1"></i> Print
                    </button>
                    <a href="#" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        <i class="bi bi-download mr-1"></i> Export PDF
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Session Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <?php
                $totalApproved = array_sum(array_column($documentResults, 'approve_count'));
                $totalRejected = array_sum(array_column($documentResults, 'reject_count'));
                $totalAbstained = array_sum(array_column($documentResults, 'abstain_count'));
                $totalVotes = $totalApproved + $totalRejected + $totalAbstained;
                ?>
                <div class="bg-white rounded-xl shadow-md p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Total Votes</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $totalVotes; ?></p>
                        </div>
                        <div class="bg-red-100 rounded-full p-3">
                            <i class="bi bi-people text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Approved</p>
                            <p class="text-2xl font-bold text-green-600"><?php echo $totalApproved; ?></p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <i class="bi bi-hand-thumbs-up text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Rejected</p>
                            <p class="text-2xl font-bold text-red-600"><?php echo $totalRejected; ?></p>
                        </div>
                        <div class="bg-red-100 rounded-full p-3">
                            <i class="bi bi-hand-thumbs-down text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Abstained</p>
                            <p class="text-2xl font-bold text-gray-500"><?php echo $totalAbstained; ?></p>
                        </div>
                        <div class="bg-gray-100 rounded-full p-3">
                            <i class="bi bi-dash-circle text-gray-500 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Document Results -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">Document Results</h2>
                </div>
                
                <?php if (empty($documentResults)): ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="bi bi-inbox text-4xl mb-2"></i>
                        <p>No documents were voted on in this session.</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($documentResults as $docResult): ?>
                            <?php
                            $total = $docResult['approve_count'] + $docResult['reject_count'] + $docResult['abstain_count'];
                            $approvePercent = $total > 0 ? ($docResult['approve_count'] / $total) * 100 : 0;
                            $rejectPercent = $total > 0 ? ($docResult['reject_count'] / $total) * 100 : 0;
                            $abstainPercent = $total > 0 ? ($docResult['abstain_count'] / $total) * 100 : 0;
                            
                            // Determine result
                            $result = 'Pending';
                            $resultClass = 'bg-yellow-100 text-yellow-800';
                            if ($docResult['voting_status'] === 'passed') {
                                $result = 'Passed';
                                $resultClass = 'bg-green-100 text-green-800';
                            } elseif ($docResult['voting_status'] === 'failed') {
                                $result = 'Failed';
                                $resultClass = 'bg-red-100 text-red-800';
                            }
                            ?>
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-sm text-gray-500"><?php echo htmlspecialchars($docResult['doc_number']); ?></span>
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800"><?php echo ucfirst($docResult['type']); ?></span>
                                            <span class="px-2 py-0.5 text-xs rounded-full <?php echo $resultClass; ?>"><?php echo $result; ?></span>
                                        </div>
                                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($docResult['title']); ?></h3>
                                    </div>
                                    
                                    <div class="flex items-center gap-6">
                                        <!-- Vote Counts -->
                                        <div class="flex items-center gap-4">
                                            <div class="text-center">
                                                <div class="text-lg font-bold text-green-600"><?php echo $docResult['approve_count']; ?></div>
                                                <div class="text-xs text-gray-500">Approve</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-lg font-bold text-red-600"><?php echo $docResult['reject_count']; ?></div>
                                                <div class="text-xs text-gray-500">Reject</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-lg font-bold text-gray-500"><?php echo $docResult['abstain_count']; ?></div>
                                                <div class="text-xs text-gray-500">Abstain</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Progress Bar -->
                                        <div class="w-40 hidden md:block">
                                            <div class="flex h-4 rounded-full overflow-hidden bg-gray-200">
                                                <div class="bg-green-500 transition-all" style="width: <?php echo $approvePercent; ?>%"></div>
                                                <div class="bg-red-500 transition-all" style="width: <?php echo $rejectPercent; ?>%"></div>
                                                <div class="bg-gray-400 transition-all" style="width: <?php echo $abstainPercent; ?>%"></div>
                                            </div>
                                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                                <span><?php echo round($approvePercent); ?>%</span>
                                                <span><?php echo round($rejectPercent); ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($showDetails): ?>
                                    <!-- Expandable Vote Details -->
                                    <details class="mt-4">
                                        <summary class="cursor-pointer text-sm text-red-600 hover:text-red-700">
                                            View Individual Votes
                                        </summary>
                                        <div class="mt-3 pl-4 border-l-2 border-gray-200">
                                            <?php
                                            $individualVotes = dbFetchAll(
                                                "SELECT v.*, u.full_name 
                                                 FROM votes v 
                                                 JOIN users u ON v.councilor_id = u.id 
                                                 WHERE v.document_id = ? AND v.session_id = ?
                                                 ORDER BY v.cast_at",
                                                [$docResult['document_id'], $sessionId]
                                            );
                                            ?>
                                            <?php if (empty($individualVotes)): ?>
                                                <p class="text-sm text-gray-500">No votes recorded.</p>
                                            <?php else: ?>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                                    <?php foreach ($individualVotes as $iv): ?>
                                                        <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                                                            <span class="text-sm text-gray-700"><?php echo htmlspecialchars($iv['full_name']); ?></span>
                                                            <span class="px-2 py-0.5 text-xs rounded-full <?php echo getVoteBadgeClass($iv['vote']); ?>">
                                                                <?php echo ucfirst($iv['vote']); ?>
                                                            </span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </details>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
