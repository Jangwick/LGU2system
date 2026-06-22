<?php
session_start();
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/config/config.php';

header('Content-Type: application/json');

try {
    $db = getDatabase();
    
    // Get request data (sanitized)
    $input = json_decode(file_get_contents('php://input'), true);
    $documentId = Sanitizer::int($input['document_id'] ?? 0, 0);
    $password = Sanitizer::string($input['password'] ?? '');
    $action = Sanitizer::enum($input['action'] ?? 'download', ['download', 'view'], 'download');

    if (!$documentId || !$password) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    // Get user info
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'User not authenticated']);
        exit;
    }
    
    // Get user's password hash
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'error' => 'Incorrect password']);
        exit;
    }
    
    // Get document confidentiality level
    $stmt = $db->prepare("SELECT confidentiality_level FROM legislative_documents WHERE id = ?");
    $stmt->execute([$documentId]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$document) {
        echo json_encode(['success' => false, 'error' => 'Document not found']);
        exit;
    }
    
    // Check if user has permission to access this confidentiality level
    $userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
    $confidentialityLevel = strtolower($document['confidentiality_level'] ?? 'public');
    
    // Access rules based on confidentiality level
    $accessRules = [
        'public' => ['viewer', 'staff', 'officer', 'administrator', 'super_admin'],
        'internal' => ['staff', 'officer', 'administrator', 'super_admin'],
        'confidential' => ['officer', 'administrator', 'super_admin'],
        'restricted' => ['super_admin']
    ];
    
    if (!in_array($userRole, $accessRules[$confidentialityLevel] ?? $accessRules['public'])) {
        echo json_encode(['success' => false, 'error' => 'Insufficient permissions for this confidentiality level']);
        exit;
    }
    
    // Generate access token (valid for 5 minutes)
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    
    // Store token in session
    $_SESSION['document_access_token'] = $token;
    $_SESSION['document_access_token_expiry'] = $expiry;
    $_SESSION['document_access_document_id'] = $documentId;
    
    echo json_encode(['success' => true, 'token' => $token]);
    
} catch (Exception $e) {
    error_log("Verify Document Access error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
