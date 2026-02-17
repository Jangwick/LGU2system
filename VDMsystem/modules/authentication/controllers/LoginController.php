<?php
/**
 * VDMsystem - Login Controller
 */
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(LOGIN_URL);
}

// Get form data
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validate input
if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'Please enter your email and password.';
    redirect(LOGIN_URL);
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_error'] = 'Please enter a valid email address.';
    redirect(LOGIN_URL);
}

try {
    // Find user by email
    $user = dbFetchOne(
        "SELECT id, username, email, password, full_name, role, department, position, is_active, approval_status, last_login 
         FROM users WHERE email = ?",
        [$email]
    );
    
    // Check if user exists and password is correct
    if (!$user || !password_verify($password, $user['password'])) {
        // Log failed login attempt
        logAudit('login_failed', null, 'authentication', null, null, 'Failed login attempt', [
            'email' => $email,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        $_SESSION['login_error'] = 'Invalid email or password.';
        redirect(LOGIN_URL);
    }
    
    // Check approval status for roles that require it
    $approvalStatus = $user['approval_status'] ?? 'approved';
    if ($approvalStatus === 'pending') {
        $_SESSION['login_error'] = 'Your account is pending administrator approval. You will be able to sign in once your account has been reviewed and approved.';
        redirect(LOGIN_URL);
    }
    
    if ($approvalStatus === 'rejected') {
        $_SESSION['login_error'] = 'Your account registration has been declined. Please contact the administrator for more information.';
        redirect(LOGIN_URL);
    }
    
    // Check if account is active
    if (!$user['is_active']) {
        $_SESSION['login_error'] = 'Your account has been deactivated. Please contact the administrator.';
        redirect(LOGIN_URL);
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_department'] = $user['department'];
    $_SESSION['user_position'] = $user['position'];
    $_SESSION['logged_in_at'] = date('Y-m-d H:i:s');
    
    // Update last login
    dbUpdate('users', 
        ['last_login' => date('Y-m-d H:i:s')],
        'id = ?',
        [$user['id']]
    );
    
    // Log successful login
    logAudit('login_success', $user['id'], 'authentication', 'user', $user['id'], 'User logged in successfully', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
    
    // Handle remember me
    if ($remember) {
        // Set a longer session cookie
        $params = session_get_cookie_params();
        setcookie(session_name(), session_id(), time() + (86400 * 30), // 30 days
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }
    
    // Redirect to dashboard
    redirect(DASHBOARD_INDEX_URL);
    
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    $_SESSION['login_error'] = 'An error occurred. Please try again later.';
    redirect(LOGIN_URL);
}

/**
 * Log audit event
 */
function logAudit($eventType, $userId, $module, $entityType, $entityId, $action, $details = []) {
    try {
        $db = getDatabase();
        
        // Get previous hash for chain
        $lastLog = dbFetchOne("SELECT current_hash FROM audit_logs ORDER BY id DESC LIMIT 1");
        $previousHash = $lastLog['current_hash'] ?? '';
        
        // Create current hash
        $hashData = json_encode([
            'event_type' => $eventType,
            'user_id' => $userId,
            'action' => $action,
            'timestamp' => date('Y-m-d H:i:s'),
            'previous_hash' => $previousHash
        ]);
        $currentHash = hash('sha256', $hashData);
        
        // Insert audit log
        $stmt = $db->prepare(
            "INSERT INTO audit_logs (event_type, user_id, module, entity_type, entity_id, action, details, ip_address, user_agent, previous_hash, current_hash) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->execute([
            $eventType,
            $userId,
            $module,
            $entityType,
            $entityId,
            $action,
            json_encode($details),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $previousHash,
            $currentHash
        ]);
    } catch (Exception $e) {
        error_log('Audit log error: ' . $e->getMessage());
    }
}
