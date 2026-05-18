<?php
/**
 * Decrypt existing documents back to original state
 * This script will decrypt documents that were encrypted during the bulk encryption
 */

require_once __DIR__ . '/../modules/core/config/database.php';
require_once __DIR__ . '/../modules/document-management/services/EncryptionService.php';

echo "Starting document decryption...\n";
echo "================================\n\n";

try {
    $db = getDatabase();
    $encryptionService = new EncryptionService();
    
    // Get all encrypted documents
    $stmt = $db->prepare("
        SELECT id, reference_number, title, file_path, encryption_key 
        FROM legislative_documents 
        WHERE is_encrypted = 1
        AND deleted_at IS NULL
        ORDER BY id ASC
    ");
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = count($documents);
    echo "Found {$total} encrypted documents to decrypt\n\n";
    
    if ($total === 0) {
        echo "No encrypted documents found.\n";
        exit;
    }
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($documents as $index => $doc) {
        echo "[" . ($index + 1) . "/{$total}] Processing: {$doc['reference_number']} - {$doc['title']}\n";
        
        try {
            // Check if file exists
            if (!file_exists($doc['file_path'])) {
                echo "  ✗ File not found: {$doc['file_path']}\n\n";
                $errorCount++;
                continue;
            }
            
            // Decrypt the file key
            $keyDecryptionResult = $encryptionService->decryptFileKey($doc['encryption_key']);
            if (!$keyDecryptionResult['success']) {
                echo "  ✗ File key decryption failed: " . $keyDecryptionResult['error'] . "\n\n";
                $errorCount++;
                continue;
            }
            $fileKey = $keyDecryptionResult['file_key'];
            
            // Decrypt the file
            $decryptionResult = $encryptionService->decryptFile($doc['file_path'], $fileKey);
            if (!$decryptionResult['success']) {
                echo "  ✗ File decryption failed: " . $decryptionResult['error'] . "\n\n";
                $errorCount++;
                continue;
            }
            
            // Write decrypted content back to file
            file_put_contents($doc['file_path'], $decryptionResult['content']);
            
            // Update database to remove encryption flags
            $updateStmt = $db->prepare("
                UPDATE legislative_documents 
                SET is_encrypted = 0, 
                    encryption_key = NULL,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $updateStmt->execute([':id' => $doc['id']]);
            
            echo "  ✓ Decrypted successfully\n\n";
            $successCount++;
            
        } catch (Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . "\n\n";
            $errorCount++;
        }
    }
    
    echo "================================\n";
    echo "Decryption complete!\n";
    echo "Success: {$successCount}\n";
    echo "Errors: {$errorCount}\n";
    echo "Total: {$total}\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
