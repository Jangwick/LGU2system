<?php
/**
 * Login Controller
 * Handles initial authentication and OTP generation
 */

// Disable all error output to prevent breaking JSON response
error_reporting(0);
ini_set('display_errors', 0);

// Start capture to ensure no accidental output
ob_start();

session_start();

// Set JSON header
header('Content-Type: application/json');

// Include configuration
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../core/utils/Logger.php';
require_once __DIR__ . '/../../core/utils/Mailer.php';
require_once __DIR__ . '/../../core/utils/Security.php';

// Clear any accidental output from included files
if (ob_get_length()) ob_clean();

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter both email and password.'
        ]);
        exit;
    }
    
    try {
        // Get database connection
        $conn = getDatabase();
        $logger = new Logger($conn);
        $security = new Security($conn);
        
        $ip = Security::getClientIP();
        
        // Check for lockout
        $remainingSeconds = $security->checkLockout($ip, $email);
        if ($remainingSeconds > 0) {
            $minutes = ceil($remainingSeconds / 60);
            echo json_encode([
                'success' => false,
                'message' => "Too many failed attempts. Your access is temporarily locked for security. Please try again in $minutes minutes."
            ]);
            exit;
        }

        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Identity verified - Clear any failed attempts
                $security->clearAttempts($ip, $email);
                
                // Determine target email for OTP
                $targetEmail = $user['email'];
                if ($user['email'] === 'admin@lgu.gov.ph') {
                    $targetEmail = 'Johnrick1214@gmail.com';
                }

                // Generate OTP instead of logging in
                $otpCode = sprintf("%06d", mt_rand(1, 999999));
                $expiry = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
                
                // Store OTP in database
                $stmt = $conn->prepare("INSERT INTO user_otps (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $otpCode, $expiry]);
                
                // Save user ID to temporary session for OTP verification
                $_SESSION['otp_pending_user_id'] = $user['id'];
                $_SESSION['otp_remember_me'] = $remember;
                
                // Send OTP email to the target email (Personal Gmail for Admin)
                $mailer = new Mailer();
                $emailSent = $mailer->sendOTP($targetEmail, $otpCode);
                
                // Log OTP generation
                $logger->logSession($user['id'], 'OTP_GENERATED', [
                    'account_email' => $user['email'],
                    'delivered_to' => $targetEmail,
                    'method' => 'email'
                ]);
                
                // Return success and requires_otp flag
                echo json_encode([
                    'success' => true,
                    'requires_otp' => true,
                    'message' => 'A verification code has been sent.',
                    'email' => $targetEmail // This will update the UI to show where it was sent
                ]);
                exit;
            } else {
                // Invalid password - log failed attempt
                $logger->logSession($user['id'], Logger::ACTION_LOGIN_FAILED, [
                    'email' => $email,
                    'reason' => 'invalid_password'
                ], Logger::SEVERITY_WARNING);
                
                // Record attempt for lockout
                $attemptInfo = $security->recordFailedAttempt($ip, $email);
                
                $message = 'Invalid password.';
                if ($attemptInfo['count'] >= 3) {
                    $remaining = 5 - $attemptInfo['count'];
                    if ($remaining > 0) {
                        $message .= " You have $remaining attempts remaining before temporary lockout.";
                    } else {
                        $message = "Too many failed attempts. Your access is temporarily locked for security. Please try again in 5 minutes.";
                    }
                }
                
                echo json_encode([
                    'success' => false,
                    'message' => $message
                ]);
                exit;
            }
        } else {
            // User not found or inactive - log failed attempt
            $logger->logSession(null, Logger::ACTION_LOGIN_FAILED, [
                'email' => $email,
                'reason' => 'user_not_found_or_inactive'
            ], Logger::SEVERITY_WARNING);
            
            // Record attempt for lockout
            $attemptInfo = $security->recordFailedAttempt($ip, $email);
            
            $message = 'Email address not found or account is inactive.';
            if ($attemptInfo['count'] >= 3) {
                $remaining = 5 - $attemptInfo['count'];
                if ($remaining > 0) {
                    $message .= " You have $remaining attempts remaining.";
                } else {
                    $message = "Too many failed attempts. Your access is temporarily locked for security. Please try again in 5 minutes.";
                }
            }

            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
            exit;
        }
    } catch (PDOException $e) {
        // Database error
        error_log("Login error: " . $e->getMessage());
        
        // Check if it's a connection error
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();
        
        if (strpos($errorMessage, 'Connection refused') !== false || 
            strpos($errorMessage, 'MySQL server has gone away') !== false ||
            $errorCode == 2002 || $errorCode == 2006) {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed. Please ensure MySQL is running in XAMPP Control Panel.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'A system error occurred. Please try again later.'
            ]);
        }
        exit;
    }
} else {
    // Not a POST request
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}
