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
    $remember = $_SESSION['otp_remember_me'] ?? false;

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

        // Verify OTP
        $stmt = $conn->prepare("
            SELECT * FROM user_otps 
            WHERE user_id = ? AND otp_code = ? AND is_used = 0 AND expires_at > NOW() 
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$userId, $otp]);
        $validOtp = $stmt->fetch(PDO::FETCH_ASSOC);

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

            // Clear pending OTP data
            unset($_SESSION['otp_pending_user_id']);
            unset($_SESSION['otp_remember_me']);

            // Set remember me cookie if needed
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (7 * 24 * 60 * 60), '/');
                $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $stmt->execute([$token, $userId]);
            }

            // Log successful verification
            $logger->logSession($userId, 'LOGIN_SUCCESS_OTP', [
                'email' => $user['email'],
                'remember_me' => $remember
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
