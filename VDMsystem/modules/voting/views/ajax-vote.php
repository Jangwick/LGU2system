<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';
require_once __DIR__ . '/../controllers/VotingController.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$userId = $_SESSION['user_id'];
$voting = new VotingController();

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $sessionId  = $input['session_id']  ?? null;
    $documentId = $input['document_id'] ?? null;
    $vote       = $input['vote']        ?? null;
    $remarks    = $input['remarks']     ?? '';

    if (!$sessionId || !$documentId || !$vote) {
        throw new Exception('Missing required fields.');
    }

    // Mark attendance
    $voting->markAttendance($sessionId, $userId, true);

    // Cast vote
    $voteId = $voting->castVote([
        'session_id'  => $sessionId,
        'document_id' => $documentId,
        'councilor_id' => $userId,
        'vote'        => $vote,
        'remarks'     => $remarks,
        'ip_address'  => $_SERVER['REMOTE_ADDR'],
        'user_agent'  => $_SERVER['HTTP_USER_AGENT']
    ]);

    // Return updated document list with user's votes
    $documents = $voting->getSessionDocuments($sessionId);
    foreach ($documents as &$doc) {
        $v = dbFetchOne(
            "SELECT vote FROM votes WHERE document_id = ? AND session_id = ? AND councilor_id = ?",
            [$doc['document_id'], $sessionId, $userId]
        );
        $doc['my_vote'] = $v['vote'] ?? null;
    }

    echo json_encode([
        'success'   => true,
        'message'   => 'Vote recorded successfully.',
        'vote_id'   => $voteId,
        'documents' => $documents
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
