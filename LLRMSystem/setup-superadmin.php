<?php
/**
 * Super Admin Account Setup Script
 * Run this file to create/update a Super Admin account
 */

require_once __DIR__ . '/modules/core/config/database.php';

$email = 'superadmin@lgusystem.gov';
$personalEmail = 'Johnrick5609@gmail.com';
$password = 'superadmin123';
$fullName = 'Super Administrator';
$role = 'super_admin';
$status = 'active';
$department = 'IT Department';
$employeeId = 'SA-ADMIN-001';

try {
    $db = getDatabase();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Generate password hash
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    if ($existingUser) {
        // Update existing user
        $stmt = $db->prepare("
            UPDATE users 
            SET password = ?, 
                role = ?, 
                full_name = ?, 
                status = ?,
                department = ?,
                employee_id = ?,
                personal_email = ?
            WHERE email = ?
        ");
        $stmt->execute([
            $passwordHash,
            $role,
            $fullName,
            $status,
            $department,
            $employeeId,
            $personalEmail,
            $email
        ]);
        
        echo "✅ Updated existing user to Super Admin:\n";
        echo "   Login Email: $email\n";
        echo "   Personal Email (for OTP): $personalEmail\n";
        echo "   Password: $password\n";
        echo "   Previous role: " . $existingUser['role'] . "\n";
        echo "   New role: $role\n";
    } else {
        // Create new user
        $stmt = $db->prepare("
            INSERT INTO users 
            (email, personal_email, password, full_name, role, status, department, employee_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $email,
            $personalEmail,
            $passwordHash,
            $fullName,
            $role,
            $status,
            $department,
            $employeeId
        ]);
        
        echo "✅ Created new Super Admin account:\n";
        echo "   Login Email: $email\n";
        echo "   Personal Email (for OTP): $personalEmail\n";
        echo "   Password: $password\n";
        echo "   Role: $role\n";
        echo "   Full Name: $fullName\n";
    }
    
    // Verify the update
    $stmt = $db->prepare("SELECT id, email, role, status FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\n📋 Verification:\n";
    echo "   User ID: " . $user['id'] . "\n";
    echo "   Email: " . $user['email'] . "\n";
    echo "   Role: " . $user['role'] . "\n";
    echo "   Status: " . $user['status'] . "\n";
    
    echo "\n✨ Super Admin account is ready to use!\n";
    echo "   Login at: " . BASE_URL . "/modules/authentication/views/login.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
