<?php
// Prevent any output before JSON
ob_start();

session_start();

// Set JSON header
header('Content-Type: application/json');

// Disable error display (log errors instead)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Include configuration
require_once __DIR__ . '/../../core/config/config.php';

// Include database configuration
require_once __DIR__ . '/../../core/config/database.php';

// Clear any output buffer
ob_end_clean();

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
        
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Login successful - set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'] ?? $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_department'] = $user['department'] ?? '';
                $_SESSION['login_time'] = time();
                
                // Set remember me cookie (7 days)
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (7 * 24 * 60 * 60), '/');
                    
                    // Store token in database (optional - for better security)
                    $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $stmt->execute([$token, $user['id']]);
                }
                
                // Log activity
                $stmt = $conn->prepare("
                    INSERT INTO activity_logs (user_id, action, description, ip_address) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $user['id'],
                    'login',
                    'User logged in successfully',
                    $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]);
                
                // Return success response
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => DASHBOARD_INDEX_URL
                ]);
                exit;
            } else {
                // Invalid password
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid password. Please try again.'
                ]);
                exit;
            }
        } else {
            // User not found or inactive
            echo json_encode([
                'success' => false,
                'message' => 'Email address not found or account is inactive.'
            ]);
            exit;
        }
    } catch (PDOException $e) {
        // Database error
        error_log("Login error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'A system error occurred. Please try again later.'
        ]);
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
