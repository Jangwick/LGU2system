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
