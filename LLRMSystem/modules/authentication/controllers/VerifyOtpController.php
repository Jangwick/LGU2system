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

    // Brute-force guard: reject immediately if the attempt ceiling has already been hit.
    // (Defensive check — the session is destroyed on lockout, so $userId would be null on
    //  subsequent requests, but this handles edge-cases like a same-process retry.)
    $otpAttempts = $_SESSION['otp_attempts'] ?? 0;
    if ($otpAttempts >= 5) {
        session_destroy();
        echo json_encode(['success' => false, 'message' => 'Too many failed attempts. Please log in again.']);
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

            // Regenerate session ID to prevent session fixation attacks.
            // The old (pre-authentication) session ID is discarded and a fresh one
            // is issued now that the user's privilege level has been elevated.
            session_regenerate_id(true);

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

            // Clear pending OTP data and brute-force counter
            unset($_SESSION['otp_pending_user_id']);
            unset($_SESSION['otp_attempts']);

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
            // Increment failed-attempt counter
            $_SESSION['otp_attempts'] = $otpAttempts + 1;

            if ($_SESSION['otp_attempts'] >= 5) {
                // Invalidate all pending OTPs for this user and force re-login
                $stmt = $conn->prepare("UPDATE user_otps SET is_used = 1 WHERE user_id = ? AND is_used = 0");
                $stmt->execute([$userId]);
                $logger->logSession($userId, 'OTP_LOCKOUT', [
                    'failed_attempts' => $_SESSION['otp_attempts']
                ]);
                session_destroy();
                echo json_encode(['success' => false, 'message' => 'Too many failed attempts. Please log in again.']);
                exit;
            }

            $remaining = 5 - $_SESSION['otp_attempts'];
            $logger->logSession($userId, 'OTP_FAILED', [
                'attempts' => $_SESSION['otp_attempts'],
                'remaining' => $remaining
            ]);
            echo json_encode([
                'success' => false,
                'message'  => "Invalid or expired verification code. {$remaining} attempt(s) remaining."
            ]);
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
