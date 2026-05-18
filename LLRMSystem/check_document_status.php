<?php
/**
 * Check document statuses in database
 */

require_once __DIR__ . '/modules/core/config/database.php';

echo "Checking document statuses...\n";
echo "==============================\n\n";

try {
    $db = getDatabase();
    
    // Get all documents with status
    $stmt = $db->prepare("
        SELECT id, reference_number, title, status 
        FROM legislative_documents 
        WHERE deleted_at IS NULL
        ORDER BY id ASC
    ");
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = count($documents);
    echo "Total documents: {$total}\n\n";
    
    $statusCount = [];
    
    foreach ($documents as $doc) {
        $status = $doc['status'];
        if (!isset($statusCount[$status])) {
            $statusCount[$status] = 0;
        }
        $statusCount[$status]++;
        echo "[{$status}] {$doc['reference_number']} - {$doc['title']}\n";
    }
    
    echo "\n==============================\n";
    echo "Status Summary:\n";
    foreach ($statusCount as $status => $count) {
        echo "{$status}: {$count}\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
