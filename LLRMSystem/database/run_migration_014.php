<?php
require_once __DIR__ . '/../modules/core/config/database.php';

try {
    $conn = getDatabase();
    
    // Add confidentiality level column
    $conn->exec("ALTER TABLE legislative_documents ADD COLUMN confidentiality_level ENUM('public', 'internal', 'confidential', 'restricted') DEFAULT 'public' AFTER status");
    
    // Add encryption flag column
    $conn->exec("ALTER TABLE legislative_documents ADD COLUMN is_encrypted BOOLEAN DEFAULT FALSE AFTER confidentiality_level");
    
    // Add encryption key column
    $conn->exec("ALTER TABLE legislative_documents ADD COLUMN encryption_key VARCHAR(255) NULL AFTER is_encrypted");
    
    // Add index for faster filtering
    $conn->exec("CREATE INDEX idx_confidentiality_level ON legislative_documents(confidentiality_level)");
    
    // Update existing documents to 'public' by default
    $conn->exec("UPDATE legislative_documents SET confidentiality_level = 'public' WHERE confidentiality_level IS NULL");
    
    echo "Migration 014 completed successfully.\n";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Migration 014 columns already exist. Skipping.\n";
    } else {
        echo "Migration 014 failed: " . $e->getMessage() . "\n";
    }
}
