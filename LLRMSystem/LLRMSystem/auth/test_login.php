<?php
session_start();

echo "<h1>Login Test Page</h1>";
echo "<p>Testing authentication...</p>";

// Test accounts
$test_accounts = [
    'admin@lgu.gov.ph' => [
        'password' => 'Admin@123',
        'name' => 'Admin User',
        'role' => 'Administrator',
        'department' => 'IT Department'
    ],
    'officer@lgu.gov.ph' => [
        'password' => 'Officer@123',
        'name' => 'Legislative Officer',
        'role' => 'Officer',
        'department' => 'Legislative Office'
    ],
    'staff@lgu.gov.ph' => [
        'password' => 'Staff@123',
        'name' => 'Staff Member',
        'role' => 'Staff',
        'department' => 'Document Management'
    ],
    'viewer@lgu.gov.ph' => [
        'password' => 'Viewer@123',
        'name' => 'Document Viewer',
        'role' => 'Viewer',
        'department' => 'Public Services'
    ]
];

echo "<h2>Available Accounts:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Email</th><th>Password</th><th>Name</th><th>Role</th></tr>";
foreach ($test_accounts as $email => $account) {
    echo "<tr>";
    echo "<td>{$email}</td>";
    echo "<td>{$account['password']}</td>";
    echo "<td>{$account['name']}</td>";
    echo "<td>{$account['role']}</td>";
    echo "</tr>";
}
echo "</table>";

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<h2>Form Submitted:</h2>";
    echo "<p>Email: <strong>{$email}</strong></p>";
    echo "<p>Password: <strong>{$password}</strong></p>";
    
    if (isset($test_accounts[$email])) {
        if ($test_accounts[$email]['password'] === $password) {
            echo "<p style='color: green; font-weight: bold;'>✅ LOGIN SUCCESSFUL!</p>";
            
            $_SESSION['user_id'] = md5($email);
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $test_accounts[$email]['name'];
            $_SESSION['user_role'] = $test_accounts[$email]['role'];
            $_SESSION['user_department'] = $test_accounts[$email]['department'];
            
            echo "<p>Session data set:</p>";
            echo "<pre>";
            print_r($_SESSION);
            echo "</pre>";
            
            echo "<p><a href='/LLRMSystem/LLRMSystem/dashboard.php'>Go to Dashboard</a></p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ WRONG PASSWORD!</p>";
            echo "<p>Expected: {$test_accounts[$email]['password']}</p>";
            echo "<p>Got: {$password}</p>";
        }
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ EMAIL NOT FOUND!</p>";
    }
} else {
    // Show test form
    echo "<h2>Test Login Form:</h2>";
    echo "<form method='POST' style='margin: 20px 0;'>";
    echo "<p><input type='email' name='email' placeholder='Email' required style='padding: 10px; width: 300px;'></p>";
    echo "<p><input type='password' name='password' placeholder='Password' required style='padding: 10px; width: 300px;'></p>";
    echo "<p><button type='submit' style='padding: 10px 30px; background: blue; color: white; border: none; cursor: pointer;'>Test Login</button></p>";
    echo "</form>";
}

echo "<hr>";
echo "<p><a href='login.php'>Back to Login Page</a></p>";
?>
