<?php
require_once __DIR__ . '/modules/core/config/database.php';

try {
    $db = getDatabase();
    
    // Check the superadmin account
    $stmt = $db->prepare("SELECT id, email, personal_email, role, status FROM users WHERE email = ?");
    $stmt->execute(['superadmin@lgusystem.gov']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ Super Admin account found:\n";
        echo "   ID: " . $user['id'] . "\n";
        echo "   Email: " . $user['email'] . "\n";
        echo "   Personal Email: " . ($user['personal_email'] ?? 'NULL') . "\n";
        echo "   Role: " . $user['role'] . "\n";
        echo "   Status: " . $user['status'] . "\n";
    } else {
        echo "❌ Super Admin account not found\n";
    }
    
    // Check if personal_email column exists
    $columns = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_ASSOC);
    $hasPersonalEmail = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'personal_email') {
            $hasPersonalEmail = true;
            break;
        }
    }
    
    echo "\n📋 Database Schema Check:\n";
    echo "   personal_email column exists: " . ($hasPersonalEmail ? "✅" : "❌") . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
