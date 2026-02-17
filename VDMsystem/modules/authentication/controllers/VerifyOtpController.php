<?php
/**
 * VDMsystem - Verify OTP Controller
 */
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(LOGIN_URL);
}

// Get pending verification data
$userId = $_SESSION['otp_pending_user_id'] ?? null;
$remember = $_SESSION['otp_remember_me'] ?? false;
$otp = trim($_POST['otp'] ?? '');

if (!$userId) {
    $_SESSION['login_error'] = 'Your session has expired. Please sign in again.';
    redirect(LOGIN_URL);
}

if (empty($otp)) {
    $_SESSION['otp_error'] = 'Please enter the verification code.';
    header('Location: ../views/verify-otp.php');
    exit;
}

try {
    // Verify OTP
    // Recent valid, unused OTP that hasn't expired
    $validOtp = dbFetchOne(
        "SELECT id FROM user_otps 
         WHERE user_id = ? AND otp_code = ? AND is_used = 0 AND expires_at > NOW() 
         ORDER BY created_at DESC LIMIT 1",
        [$userId, $otp]
    );

    if ($validOtp) {
        // Mark OTP as used
        dbUpdate('user_otps', ['is_used' => 1], 'id = ?', [$validOtp['id']]);

        // Get full user details
        $user = dbFetchOne(
            "SELECT * FROM users WHERE id = ?",
            [$userId]
        );

        if (!$user) {
            $_SESSION['login_error'] = 'User account not found.';
            redirect(LOGIN_URL);
        }

        // --- SUCCESSFUL LOGIN FLOW ---
        // Set final session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_department'] = $user['department'];
        $_SESSION['user_position'] = $user['position'];
        $_SESSION['logged_in_at'] = date('Y-m-d H:i:s');
        
        // Clear OTP pending data
        unset($_SESSION['otp_pending_user_id']);
        unset($_SESSION['otp_remember_me']);
        unset($_SESSION['otp_target_email']);

        // Update last login
        dbUpdate('users', 
            ['last_login' => date('Y-m-d H:i:s')],
            'id = ?',
            [$user['id']]
        );

        // Handle remember me
        if ($remember) {
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), time() + (86400 * 30), // 30 days
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }

        // Log successful login
        logAudit('login_success_otp', $user['id'], 'authentication', 'user', $user['id'], 'User verified identity and logged in successfully', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);

        // Redirect to dashboard
        redirect(DASHBOARD_INDEX_URL);

    } else {
        // Invalid or expired code
        $_SESSION['otp_error'] = 'Invalid or expired verification code. Please check your email or resend the code.';
        header('Location: ../views/verify-otp.php');
        exit;
    }

} catch (Exception $e) {
    error_log('OTP Verification error: ' . $e->getMessage());
    $_SESSION['otp_error'] = 'An internal system error occurred. Please try again.';
    header('Location: ../views/verify-otp.php');
    exit;
}

/**
 * Log audit event (Copied from LoginController)
 */
function logAudit($eventType, $userId, $module, $entityType, $entityId, $action, $details = []) {
    try {
        $db = getDatabase();
        $lastLog = dbFetchOne("SELECT current_hash FROM audit_logs ORDER BY id DESC LIMIT 1");
        $previousHash = $lastLog['current_hash'] ?? '';
        
        $hashData = json_encode([
            'event_type' => $eventType,
            'user_id' => $userId,
            'action' => $action,
            'timestamp' => date('Y-m-d H:i:s'),
            'previous_hash' => $previousHash
        ]);
        $currentHash = hash('sha256', $hashData);
        
        $stmt = $db->prepare(
            "INSERT INTO audit_logs (event_type, user_id, module, entity_type, entity_id, action, details, ip_address, user_agent, previous_hash, current_hash) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->execute([
            $eventType, $userId, $module, $entityType, $entityId, $action,
            json_encode($details),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $previousHash, $currentHash
        ]);
    } catch (Exception $e) {
        error_log('Audit log error: ' . $e->getMessage());
    }
}
