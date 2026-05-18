<?php
/**
 * Encrypt all existing documents
 * This script will:
 * 1. Get all documents without encryption keys
 * 2. Generate unique file keys for each
 * 3. Encrypt the files
 * 4. Update the database with encrypted keys
 */

require_once __DIR__ . '/../modules/core/config/database.php';
require_once __DIR__ . '/../modules/document-management/services/EncryptionService.php';

echo "Starting document encryption...\n";
echo "================================\n\n";

try {
    $db = getDatabase();
    $encryptionService = new EncryptionService();
    
    // Get all documents without encryption keys
    $stmt = $db->prepare("
        SELECT id, reference_number, title, file_path 
        FROM legislative_documents 
        WHERE is_encrypted = 0 OR is_encrypted IS NULL
        AND deleted_at IS NULL
        ORDER BY id ASC
    ");
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = count($documents);
    echo "Found {$total} documents to encrypt\n\n";
    
    if ($total === 0) {
        echo "No documents need encryption.\n";
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
            
            // Generate a unique file key
            $fileKey = $encryptionService->generateFileKey();
            
            // Encrypt the file with the file key
            $encryptionResult = $encryptionService->encryptFile($doc['file_path'], $fileKey);
            if (!$encryptionResult['success']) {
                echo "  ✗ File encryption failed: " . $encryptionResult['error'] . "\n\n";
                $errorCount++;
                continue;
            }
            
            // Encrypt the file key with the master key
            $keyEncryptionResult = $encryptionService->encryptFileKey($fileKey);
            if (!$keyEncryptionResult['success']) {
                echo "  ✗ File key encryption failed: " . $keyEncryptionResult['error'] . "\n\n";
                $errorCount++;
                continue;
            }
            $encryptedFileKey = $keyEncryptionResult['encrypted_key'];
            
            // Update database
            $updateStmt = $db->prepare("
                UPDATE legislative_documents 
                SET is_encrypted = 1, 
                    encryption_key = :encryption_key,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':encryption_key' => $encryptedFileKey,
                ':id' => $doc['id']
            ]);
            
            echo "  ✓ Encrypted successfully\n\n";
            $successCount++;
            
        } catch (Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . "\n\n";
            $errorCount++;
        }
    }
    
    echo "================================\n";
    echo "Encryption complete!\n";
    echo "Success: {$successCount}\n";
    echo "Errors: {$errorCount}\n";
    echo "Total: {$total}\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
