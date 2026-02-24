<?php
require_once __DIR__ . '/modules/core/config/database.php';
$db = getDatabase();
try {
    $db->exec("ALTER TABLE users ADD COLUMN bound_email VARCHAR(255) NULL AFTER email");
    echo "Added bound_email column successfully.\n";
    
    // Set admin's bound email
    $db->exec("UPDATE users SET bound_email = 'Johnrick1214@gmail.com' WHERE email = 'admin@lgu.gov.ph'");
    echo "Set admin bound_email successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
