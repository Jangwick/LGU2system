<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Debug log
file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Login attempt started\n", FILE_APPEND);
file_put_contents('login_debug.log', "Method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
file_put_contents('login_debug.log', "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Test accounts for development
$test_accounts = [
    // Administrator account
    'admin@lgu.gov.ph' => [
        'password' => 'Admin@123',
        'name' => 'Admin User',
        'role' => 'Administrator',
        'department' => 'IT Department'
    ],
    // Legislative Officer account
    'officer@lgu.gov.ph' => [
        'password' => 'Officer@123',
        'name' => 'Legislative Officer',
        'role' => 'Officer',
        'department' => 'Legislative Office'
    ],
    // Staff account
    'staff@lgu.gov.ph' => [
        'password' => 'Staff@123',
        'name' => 'Staff Member',
        'role' => 'Staff',
        'department' => 'Document Management'
    ],
    // Viewer account (read-only)
    'viewer@lgu.gov.ph' => [
        'password' => 'Viewer@123',
        'name' => 'Document Viewer',
        'role' => 'Viewer',
        'department' => 'Public Services'
    ]
];

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    file_put_contents('login_debug.log', "Email: {$email}\n", FILE_APPEND);
    file_put_contents('login_debug.log', "Password: {$password}\n", FILE_APPEND);
    
    // Validate credentials
    if (isset($test_accounts[$email])) {
        file_put_contents('login_debug.log', "User found in database\n", FILE_APPEND);
        
        if ($test_accounts[$email]['password'] === $password) {
            file_put_contents('login_debug.log', "Password match! Login successful\n", FILE_APPEND);
            // Login successful
            $_SESSION['user_id'] = md5($email);
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $test_accounts[$email]['name'];
            $_SESSION['user_role'] = $test_accounts[$email]['role'];
            $_SESSION['user_department'] = $test_accounts[$email]['department'];
            $_SESSION['login_time'] = time();
            
            // Set remember me cookie (7 days)
            if ($remember) {
                setcookie('remember_token', md5($email . time()), time() + (7 * 24 * 60 * 60), '/');
            }
            
            // Log activity
            error_log("User logged in: {$email} - {$test_accounts[$email]['name']}");
            
            // Redirect to dashboard
            header('Location: /LLRMSystem/LLRMSystem/dashboard.php');
            exit;
        } else {
            // Invalid password
            $_SESSION['login_error'] = 'Invalid password. Please try again.';
            header('Location: /LLRMSystem/LLRMSystem/auth/login.php?error=invalid_password');
            exit;
        }
    } else {
        // User not found
        $_SESSION['login_error'] = 'Email address not found.';
        header('Location: /LLRMSystem/LLRMSystem/auth/login.php?error=user_not_found');
        exit;
    }
}

// If not POST request, redirect to login page
header('Location: /LLRMSystem/LLRMSystem/auth/login.php');
exit;
