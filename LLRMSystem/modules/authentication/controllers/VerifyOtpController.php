<?php
/**
 * Verify OTP Controller
 */

// Disable all error output
error_reporting(0);
ini_set('display_errors', 0);

ob_start();

session_start();

// Set JSON header
header('Content-Type: application/json');

// Include configuration
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

// Clear any output buffer
if (ob_get_length()) ob_clean();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');
    $userId = $_SESSION['otp_pending_user_id'] ?? null;

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
        exit;
    }

    if (empty($otp)) {
        echo json_encode(['success' => false, 'message' => 'Please enter the verification code.']);
        exit;
    }

    try {
        $conn = getDatabase();
        $logger = new Logger($conn);

        // Fetch all valid (unused, non-expired) OTPs for this user and verify via hash.
        // Direct SQL comparison is not possible because OTPs are stored as bcrypt hashes.
        $stmt = $conn->prepare("
            SELECT * FROM user_otps 
            WHERE user_id = ? AND is_used = 0 AND expires_at > NOW() 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $validOtp = null;
        foreach ($rows as $row) {
            if (password_verify($otp, $row['otp_code'])) {
                $validOtp = $row;
                break;
            }
        }

        if ($validOtp) {
            // Mark OTP as used
            $stmt = $conn->prepare("UPDATE user_otps SET is_used = 1 WHERE id = ?");
            $stmt->execute([$validOtp['id']]);

            // Get user details
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Set final login session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'] ?? $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_department'] = $user['department'] ?? '';
            $_SESSION['login_time'] = time();

            // Track current session ID to prevent concurrent logins
            $currentSessionId = session_id();
            $stmt = $conn->prepare("UPDATE users SET last_session_id = ? WHERE id = ?");
            $stmt->execute([$currentSessionId, $userId]);
            $_SESSION['current_session_id'] = $currentSessionId;

            // Clear pending OTP data
            unset($_SESSION['otp_pending_user_id']);

            // Log successful verification
            $logger->logSession($userId, 'LOGIN_SUCCESS_OTP', [
                'email' => $user['email']
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Verification successful',
                'redirect' => DASHBOARD_INDEX_URL
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code.']);
            exit;
        }
    } catch (PDOException $e) {
        error_log("OTP Verif error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Internal system error.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
