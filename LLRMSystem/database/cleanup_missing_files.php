<?php
/**
 * Delete database records for documents with missing files
 * This script will soft delete documents whose files don't exist on the filesystem
 */

require_once __DIR__ . '/../modules/core/config/database.php';

echo "Starting cleanup of documents with missing files...\n";
echo "========================================================\n\n";

try {
    $db = getDatabase();
    
    // Get all documents that are not already deleted
    $stmt = $db->prepare("
        SELECT id, reference_number, title, file_path 
        FROM legislative_documents 
        WHERE deleted_at IS NULL
        ORDER BY id ASC
    ");
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = count($documents);
    echo "Checking {$total} documents for missing files...\n\n";
    
    if ($total === 0) {
        echo "No documents found.\n";
        exit;
    }
    
    $toDelete = [];
    
    foreach ($documents as $doc) {
        // Skip external sync documents
        if ($doc['file_path'] === 'external_sync') {
            echo "[SKIP] {$doc['reference_number']} - External sync document\n";
            $toDelete[] = $doc['id'];
            continue;
        }
        
        // Check if file exists
        if (!file_exists($doc['file_path'])) {
            echo "[MISSING] {$doc['reference_number']} - {$doc['title']}\n";
            echo "  File: {$doc['file_path']}\n";
            $toDelete[] = $doc['id'];
        }
    }
    
    $deleteCount = count($toDelete);
    
    if ($deleteCount === 0) {
        echo "\nNo documents with missing files found.\n";
        exit;
    }
    
    echo "\nFound {$deleteCount} documents to delete.\n";
    echo "Proceeding with soft delete...\n\n";
    
    // Soft delete the documents
    $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
    $updateStmt = $db->prepare("
        UPDATE legislative_documents 
        SET deleted_at = NOW(),
            updated_at = NOW()
        WHERE id IN ($placeholders)
    ");
    $updateStmt->execute($toDelete);
    
    echo "Successfully soft deleted {$deleteCount} documents.\n";
    echo "========================================================\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
