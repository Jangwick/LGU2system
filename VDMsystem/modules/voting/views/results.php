<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../controllers/VotingController.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

$voting = new VotingController();
$sessionId = $_GET['session'] ?? null;
$userId = $_SESSION['user_id'];

// Get appropriate data
if (!$sessionId) {
    // List completed or in-progress sessions for results viewing
    $sessions = $voting->getSessions(['status' => ['completed', 'in_progress']]);
} else {
    // Single session detail
    $session = $voting->getSession($sessionId);
    if (!$session) {
        $_SESSION['flash_error'] = "Session not found.";
        header('Location: results.php');
        exit;
    }
    
    $summary = $voting->getSessionResultsSummary($sessionId);
    $documents = $voting->getSessionDocuments($sessionId);
}

$pageTitle = 'Voting Results';
$currentPage = 'results';
$breadcrumbs = [
    ['label' => 'Voting', 'url' => '#'],
    ['label' => 'Results']
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
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4 animate-fade-in">
                <div class="flex items-center">
                    <i class="bi bi-exclamation-triangle text-red-500 mr-2 text-lg"></i>
                    <span class="text-red-700 font-medium"><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$sessionId): ?>
            <!-- Results List View -->
            <div class="animate-fade-in">
                <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-2xl shadow-xl p-8 mb-8 text-white">
                    <h1 class="text-3xl font-bold mb-2">Legislative Dashboards</h1>
                    <p class="text-gray-400">Review outcomes, analytics, and historical data of all voting sessions.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fade-in-up">
                    <?php if (empty($sessions)): ?>
                        <div class="lg:col-span-3 bg-white rounded-3xl p-16 text-center shadow-lg border-2 border-dashed border-gray-100">
                            <i class="bi bi-bar-chart text-7xl text-gray-200 mb-6 block"></i>
                            <h2 class="text-2xl font-bold text-gray-700">No Results Available</h2>
                            <p class="text-gray-500">Wait for sessions to complete or start to see voting results here.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($sessions as $s): ?>
                            <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-2xl transition-all transform hover:-translate-y-1 group border border-gray-100">
                                <div class="bg-gray-50 p-4 border-b border-gray-100 flex justify-between items-center">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"><?php echo $s['session_number']; ?></span>
                                    <?php
                                    $statusClass = $s['status'] === 'completed' ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700';
                                    ?>
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded-full uppercase <?php echo $statusClass; ?>">
                                        <?php echo $s['status']; ?>
                                    </span>
                                </div>
                                <div class="p-6">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4 group-hover:text-red-700 transition-colors line-clamp-2 h-14"><?php echo e($s['title']); ?></h3>
                                    
                                    <div class="grid grid-cols-3 gap-2 mb-6">
                                        <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100 group-hover:bg-red-50 transition-colors">
                                            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Docs</p>
                                            <p class="font-bold text-gray-800"><?php echo $s['document_count']; ?></p>
                                        </div>
                                        <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100 group-hover:bg-red-50 transition-colors">
                                            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Votes</p>
                                            <p class="font-bold text-gray-800"><?php echo $s['vote_count']; ?></p>
                                        </div>
                                        <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100 group-hover:bg-red-50 transition-colors">
                                            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Quorum</p>
                                            <p class="font-bold text-gray-800"><?php echo $s['attendee_count']; ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center text-xs text-gray-500 mb-6">
                                        <i class="bi bi-calendar-check mr-2 text-red-600"></i>
                                        <?php echo formatDate($s['session_date']); ?>
                                    </div>
                                    
                                    <a href="results.php?session=<?php echo $s['id']; ?>" class="w-full bg-gray-900 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-red-700 transition-all shadow-md transform active:scale-95">
                                        View Full Analytics
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Session Results Dashboard -->
            <div class="animate-fade-in">
                <!-- Dashboard Header -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <a href="results.php" class="text-red-600 hover:text-red-800 font-bold text-sm mb-4 inline-flex items-center group">
                            <i class="bi bi-arrow-left-circle mr-2 transition-transform group-hover:-translate-x-1"></i>
                            Back to Analytics List
                        </a>
                        <h1 class="text-3xl font-bold text-gray-800"><?php echo e($session['title']); ?></h1>
                        <p class="text-gray-500 font-medium">Session Results Dashboard • <?php echo e($session['session_number']); ?></p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="window.print()" class="bg-white text-gray-700 border border-gray-200 px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2">
                            <i class="bi bi-printer"></i> Print Report
                        </button>
                        <button class="bg-gray-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-gray-800 transition-all shadow-md flex items-center gap-2">
                            <i class="bi bi-download"></i> Export Data
                        </button>
                    </div>
                </div>

                <!-- Statistics Row -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-md border-b-4 border-red-600">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-2">Total Documents</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $summary['total_docs']; ?></p>
                        <p class="text-xs text-gray-500 mt-1">Processed during session</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-md border-b-4 border-green-600">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-2">Documents Passed</p>
                        <p class="text-3xl font-bold text-green-600"><?php echo $summary['passed_docs']; ?></p>
                        <p class="text-xs text-green-800 bg-green-50 px-2 py-0.5 rounded-full inline-block mt-1">
                            <?php echo $summary['total_docs'] > 0 ? round(($summary['passed_docs'] / $summary['total_docs']) * 100) : 0; ?>% Approval Rate
                        </p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-md border-b-4 border-red-400">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-2">Documents Failed</p>
                        <p class="text-3xl font-bold text-red-600"><?php echo $summary['failed_docs']; ?></p>
                        <p class="text-xs text-gray-500 mt-1">Action required</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-md border-b-4 border-gray-800">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-2">Total Votes</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $summary['total_approve'] + $summary['total_reject'] + $summary['total_abstain']; ?></p>
                        <p class="text-xs text-gray-500 mt-1">Cast by <?php echo $session['attendee_count']; ?> members</p>
                    </div>
                </div>

                <!-- Visual Analytics Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    <!-- Global Voting Distribution Chart -->
                    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
                        <h3 class="font-bold text-gray-800 text-lg mb-8 flex items-center gap-2">
                            <i class="bi bi-pie-chart-fill text-red-600"></i>
                            Voting Distribution
                        </h3>
                        <div class="max-w-[250px] mx-auto mb-8">
                            <canvas id="votingChart"></canvas>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-2xl">
                                <span class="flex items-center text-sm font-bold text-green-800"><span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span> Approvals</span>
                                <span class="font-bold text-green-900"><?php echo $summary['total_approve']; ?></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-2xl">
                                <span class="flex items-center text-sm font-bold text-red-800"><span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span> Rejections</span>
                                <span class="font-bold text-red-900"><?php echo $summary['total_reject']; ?></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-2xl">
                                <span class="flex items-center text-sm font-bold text-gray-800"><span class="w-3 h-3 bg-gray-400 rounded-full mr-2"></span> Abstentions</span>
                                <span class="font-bold text-gray-900"><?php echo $summary['total_abstain']; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Table of Decisions -->
                    <div class="lg:col-span-2 bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 flex flex-col">
                        <div class="p-6 border-b border-gray-100 bg-gray-50">
                            <h3 class="font-bold text-gray-800 flex items-center justify-between">
                                Itemized Outcomes
                                <span class="text-xs bg-white text-gray-400 px-3 py-1 rounded-full border border-gray-200 uppercase tracking-widest">Legislative Items</span>
                            </h3>
                        </div>
                        <div class="overflow-y-auto flex-1 custom-scrollbar">
                            <table class="w-full">
                                <thead class="text-[10px] text-gray-400 uppercase font-bold tracking-widest bg-white sticky top-0">
                                    <tr>
                                        <th class="px-6 py-4 text-left">Document</th>
                                        <th class="px-6 py-4 text-center">Result</th>
                                        <th class="px-6 py-4 text-center">Approve</th>
                                        <th class="px-6 py-4 text-center">Reject</th>
                                        <th class="px-6 py-4 text-center">Abstain</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($documents as $doc): ?>
                                        <tr class="hover:bg-red-50 transition-colors group">
                                            <td class="px-6 py-4">
                                                <div class="max-w-md">
                                                    <p class="text-[10px] font-bold text-red-400 mb-0.5"><?php echo e($doc['doc_number']); ?></p>
                                                    <h4 class="text-sm font-bold text-gray-800 line-clamp-1 group-hover:text-red-700 transition-colors"><?php echo e($doc['title']); ?></h4>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <?php
                                                $resClass = $doc['voting_status'] === 'passed' ? 'bg-green-100 text-green-700' : ($doc['voting_status'] === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700');
                                                ?>
                                                <span class="px-3 py-1 text-[10px] font-bold rounded-full uppercase shadow-sm <?php echo $resClass; ?>">
                                                    <?php echo $doc['voting_status']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center text-sm font-bold text-green-600"><?php echo $doc['approve_count']; ?></td>
                                            <td class="px-6 py-4 text-center text-sm font-bold text-red-600"><?php echo $doc['reject_count']; ?></td>
                                            <td class="px-6 py-4 text-center text-sm font-bold text-gray-400"><?php echo $doc['abstain_count']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Individual Votes Logs (Auditable) -->
                <?php if (hasRole(['admin', 'secretary'])): ?>
                    <div class="bg-gray-900 rounded-3xl shadow-2xl overflow-hidden mb-8 text-white p-8">
                        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4 border-b border-gray-800 pb-6">
                            <div>
                                <h3 class="text-xl font-bold mb-1 flex items-center gap-2">
                                    <i class="bi bi-shield-lock-fill text-red-500"></i>
                                    Individual Audit Log
                                </h3>
                                <p class="text-gray-400 text-sm">Review specific decisions made by each legislator.</p>
                            </div>
                            <div class="bg-gray-800 px-4 py-2 rounded-2xl border border-gray-700">
                                <span class="text-xs text-gray-500 font-bold uppercase tracking-widest italic flex items-center">
                                    <i class="bi bi-info-circle mr-2"></i> Authorized Access Only
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 custom-scrollbar max-h-[500px] overflow-y-auto">
                            <?php 
                            // Fetch all votes for audit
                            $allVotes = dbFetchAll(
                                "SELECT v.*, u.full_name as voter_name, u.position, d.title as doc_title
                                 FROM votes v
                                 JOIN users u ON v.councilor_id = u.id
                                 JOIN documents d ON v.document_id = d.id
                                 WHERE v.session_id = ?
                                 ORDER BY v.cast_at DESC", 
                                [$sessionId]
                            );
                            
                            if (empty($allVotes)): ?>
                                <div class="col-span-full py-12 text-center text-gray-500">
                                    <i class="bi bi-database-exclamation text-4xl mb-3 block"></i>
                                    No individual votes recorded for this session.
                                </div>
                            <?php else: ?>
                                <?php foreach ($allVotes as $v): ?>
                                    <div class="bg-gray-800 rounded-2xl p-5 border border-gray-700 hover:border-red-500 transition-all group">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-gray-700 text-gray-300 rounded-full flex items-center justify-center font-bold">
                                                    <?php echo strtoupper(substr($v['voter_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-white"><?php echo e($v['voter_name']); ?></p>
                                                    <p class="text-[10px] text-gray-500 uppercase font-bold"><?php echo e($v['position']); ?></p>
                                                </div>
                                            </div>
                                            <?php
                                            $vClass = $v['vote'] === 'approve' ? 'bg-green-900 text-green-300 border-green-800' : ($v['vote'] === 'reject' ? 'bg-red-900 text-red-300 border-red-800' : 'bg-gray-700 text-gray-300 border-gray-600');
                                            ?>
                                            <span class="px-2 py-0.5 text-[8px] font-bold rounded-full uppercase border <?php echo $vClass; ?>">
                                                <?php echo $v['vote']; ?>
                                            </span>
                                        </div>
                                        <div class="border-t border-gray-700 pt-3">
                                            <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">Document Item</p>
                                            <p class="text-xs font-bold text-gray-300 line-clamp-1 mb-2"><?php echo e($v['doc_title']); ?></p>
                                            <p class="text-[9px] text-gray-600 text-right italic font-medium">Cast on <?php echo formatDateTime($v['cast_at']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Chart Initialization Script -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('votingChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Approve', 'Reject', 'Abstain'],
                            datasets: [{
                                data: [
                                    <?php echo $summary['total_approve']; ?>, 
                                    <?php echo $summary['total_reject']; ?>, 
                                    <?php echo $summary['total_abstain']; ?>
                                ],
                                backgroundColor: ['#10b981', '#ef4444', '#9ca3af'],
                                borderWidth: 0,
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            cutout: '75%',
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });
                });
            </script>
        <?php endif; ?>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<style>
    .animate-fade-in { animation: fadeIn 0.8s ease-out; }
    .animate-fade-in-up { animation: fadeInUp 0.8s ease-out forwards; opacity: 0; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    
    .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 20px; }
    
    /* Document results table scroll styling for detail view */
    .custom-scrollbar { scrollbar-width: thin; scrollbar-color: #e5e7eb transparent; }
</style>
