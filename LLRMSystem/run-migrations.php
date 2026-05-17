<?php
/**
 * Database Migration Script
 * Run this to apply all pending migrations
 */

require_once __DIR__ . '/modules/core/config/database.php';

try {
    $db = getDatabase();
    
    echo "Running database migrations...\n\n";
    
    // Migration 013: Add super_admin role
    echo "Migration 013: Add super_admin role...\n";
    $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('viewer', 'staff', 'officer', 'administrator', 'super_admin')");
    echo "✅ Migration 013 completed\n\n";
    
    // Migration 014: Add document confidentiality fields
    echo "Migration 014: Add document confidentiality fields...\n";
    try {
        $db->exec("ALTER TABLE legislative_documents ADD COLUMN confidentiality_level ENUM('public', 'internal', 'confidential', 'restricted') DEFAULT 'public' AFTER status");
        $db->exec("ALTER TABLE legislative_documents ADD COLUMN is_encrypted BOOLEAN DEFAULT FALSE AFTER confidentiality_level");
        $db->exec("ALTER TABLE legislative_documents ADD COLUMN encryption_key VARCHAR(255) NULL AFTER is_encrypted");
        $db->exec("CREATE INDEX idx_confidentiality_level ON legislative_documents(confidentiality_level)");
        echo "✅ Migration 014 completed\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "⚠️  Migration 014 columns already exist, skipping...\n\n";
        } else {
            throw $e;
        }
    }
    
    // Migration 015: Add personal_email column
    echo "Migration 015: Add personal_email column...\n";
    try {
        $db->exec("ALTER TABLE users ADD COLUMN personal_email VARCHAR(255) NULL AFTER email");
        $db->exec("CREATE INDEX idx_personal_email ON users(personal_email)");
        echo "✅ Migration 015 completed\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "⚠️  Migration 015 column already exists, skipping...\n\n";
        } else {
            throw $e;
        }
    }
    
    echo "✨ All migrations completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
