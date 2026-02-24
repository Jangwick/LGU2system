<?php
session_start();

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/audit.php';
require_once __DIR__ . '/../../users/controllers/UserController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'error' => 'Invalid request method']));
}

$data = $_POST;

try {
    // Basic validation
    if (empty($data['full_name']) || empty($data['email']) || empty($data['username']) || empty($data['password']) || empty($data['bound_email'])) {
        throw new Exception("All required fields must be filled.");
    }

    if (!preg_match('/@gmail\.com$/i', $data['bound_email'])) {
        throw new Exception("Please provide a valid Gmail address for OTP binding.");
    }

    if ($data['password'] !== $data['confirm_password']) {
        throw new Exception("Passwords do not match.");
    }

    $userController = new UserController();
    
    // Check if email already exists
    $db = getDatabase();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        throw new Exception("Email address is already registered.");
    }

    // Check if bound_email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE bound_email = ?");
    $stmt->execute([$data['bound_email']]);
    if ($stmt->fetch()) {
        throw new Exception("This Gmail address is already bound to another account.");
    }

    // Check if username already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    if ($stmt->fetch()) {
        throw new Exception("Username is already taken.");
    }

    // Create the user
    // Viewers are auto-approved. Councilors and Secretaries need admin approval.
    $role = $data['role'] ?? 'viewer';
    $needsApproval = in_array($role, ['councilor', 'secretary']);
    
    $result = $userController->create([
        'full_name' => $data['full_name'],
        'email' => $data['email'],
        'bound_email' => $data['bound_email'],
        'username' => $data['username'],
        'password' => $data['password'],
        'role' => $role,
        'position' => $data['position'] ?? '',
        'department' => $data['department'] ?? '',
        'is_active' => $needsApproval ? 0 : 1,
        'approval_status' => $needsApproval ? 'pending' : 'approved'
    ]);

    if ($result['success']) {
        if ($needsApproval) {
            $_SESSION['flash_success'] = "Account created successfully! Your account is pending administrator approval. You will be able to sign in once approved.";
        } else {
            $_SESSION['flash_success'] = "Account created successfully! Please sign in.";
        }
        header("Location: ../views/login.php");
        exit;
    } else {
        throw new Exception($result['error'] ?? "Failed to create account.");
    }

} catch (Exception $e) {
    $_SESSION['flash_error'] = $e->getMessage();
    header("Location: ../views/register.php");
    exit;
}
