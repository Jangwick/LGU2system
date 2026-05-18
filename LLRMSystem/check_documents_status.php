<?php
/**
 * Check document status - identify files that are not working
 */

require_once __DIR__ . '/modules/core/config/database.php';

echo "Checking document status...\n";
echo "=========================\n\n";

try {
    $db = getDatabase();
    
    // Get all documents
    $stmt = $db->prepare("
        SELECT id, reference_number, title, file_path, is_encrypted, encryption_key 
        FROM legislative_documents 
        WHERE deleted_at IS NULL
        ORDER BY id ASC
    ");
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = count($documents);
    echo "Total documents in database: {$total}\n\n";
    
    $encryptedCount = 0;
    $missingFiles = 0;
    $externalSync = 0;
    $working = 0;
    
    foreach ($documents as $doc) {
        // Check if encrypted
        if ($doc['is_encrypted'] == 1) {
            echo "[ENCRYPTED] {$doc['reference_number']} - {$doc['title']}\n";
            $encryptedCount++;
        }
        
        // Check if file exists
        if ($doc['file_path'] === 'external_sync') {
            echo "[EXTERNAL SYNC] {$doc['reference_number']} - {$doc['title']}\n";
            $externalSync++;
        } elseif (!file_exists($doc['file_path'])) {
            echo "[MISSING FILE] {$doc['reference_number']} - {$doc['title']}\n";
            echo "  Path: {$doc['file_path']}\n";
            $missingFiles++;
        } else {
            $working++;
        }
    }
    
    echo "\n=========================\n";
    echo "Summary:\n";
    echo "Working files: {$working}\n";
    echo "Still encrypted: {$encryptedCount}\n";
    echo "Missing files: {$missingFiles}\n";
    echo "External sync: {$externalSync}\n";
    echo "Total: {$total}\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
