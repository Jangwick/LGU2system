<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Check permissions
if (!hasRole(['admin'])) {
    $_SESSION['flash_error'] = "Only administrators can delete documents from the repository.";
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Get document to check status
$doc = dbFetchOne("SELECT title, status, file_path FROM documents WHERE id = ?", [$id]);

if (!$doc) {
    $_SESSION['flash_error'] = "Document not found.";
    header('Location: index.php');
    exit;
}

// Only allow deletion of drafts
if ($doc['status'] !== 'draft') {
    $_SESSION['flash_error'] = "Only documents in 'Draft' status can be deleted from the repository.";
    header('Location: index.php');
    exit;
}

// Delete file if exists
if ($doc['file_path']) {
    $fullPath = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $doc['file_path']);
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

// Delete from database
dbDelete('documents', "id = ?", [$id]);
logAudit('document_deleted', $_SESSION['user_id'], 'documents', 'documents', $id, "Deleted document: " . $doc['title']);

$_SESSION['flash_success'] = "Document permanently removed from the repository.";
header('Location: index.php');
exit;
