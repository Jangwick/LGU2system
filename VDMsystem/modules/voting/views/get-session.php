<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../controllers/VotingController.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$sessionId = $_GET['id'] ?? null;
if (!$sessionId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit;
}

$voting = new VotingController();
$session = $voting->getSession($sessionId);
$documents = $voting->getSessionDocuments($sessionId);
$attendees = $voting->getSessionAttendees($sessionId);

// Attach current user's votes to each document
$userId = $_SESSION['user_id'];
foreach ($documents as &$doc) {
    $docId = $doc['document_id'] ?? $doc['id'];
    $vote = dbFetchOne(
        "SELECT vote FROM votes WHERE document_id = ? AND session_id = ? AND councilor_id = ?",
        [(string)$docId, (string)$sessionId, (string)$userId]
    );
    $doc['my_vote'] = $vote['vote'] ?? null;
}

if ($session) {
    echo json_encode([
        'success' => true,
        'session' => $session,
        'documents' => $documents,
        'attendees' => $attendees
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Session not found']);
}
