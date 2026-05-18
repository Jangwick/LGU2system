<?php
require_once __DIR__ . '/modules/core/config/database.php';

try {
    $db = getDatabase();
    $stmt = $db->query("DESCRIBE legislative_documents");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columns in legislative_documents table:\n";
    echo "========================================\n";
    foreach ($columns as $column) {
        echo $column['Field'] . " - " . $column['Type'] . "\n";
    }
    
    // Check specifically for encryption columns
    echo "\n\nChecking for encryption-related columns:\n";
    echo "========================================\n";
    $hasIsEncrypted = false;
    $hasEncryptionKey = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'is_encrypted') {
            $hasIsEncrypted = true;
            echo "✓ is_encrypted column exists\n";
        }
        if ($column['Field'] === 'encryption_key') {
            $hasEncryptionKey = true;
            echo "✓ encryption_key column exists\n";
        }
    }
    
    if (!$hasIsEncrypted) {
        echo "✗ is_encrypted column MISSING\n";
    }
    if (!$hasEncryptionKey) {
        echo "✗ encryption_key column MISSING\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
