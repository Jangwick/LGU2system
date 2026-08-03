<?php
/**
 * Migration: Extend document_tracking with cross-system fields.
 */
require_once __DIR__ . '/../../core/config/database.php';

$db = getDatabase();

function columnExists($db, $table, $column) {
    $stmt = $db->prepare("SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column");
    $stmt->execute([':table' => $table, ':column' => $column]);
    return (bool) $stmt->fetch();
}

$columns = [
    'local_document_id' => 'VARCHAR(255) NULL AFTER external_reference',
    'status'            => 'VARCHAR(150) NULL AFTER event_action',
    'performed_by'      => 'VARCHAR(255) NULL AFTER status',
    'department'        => 'VARCHAR(255) NULL AFTER performed_by',
    'remarks'           => 'TEXT NULL AFTER department',
    'metadata'          => 'JSON NULL AFTER remarks',
    'idempotency_key'   => 'VARCHAR(255) NULL AFTER metadata',
];

try {
    foreach ($columns as $col => $def) {
        if (!columnExists($db, 'document_tracking', $col)) {
            $db->exec("ALTER TABLE document_tracking ADD COLUMN {$col} {$def}");
            echo "Added column {$col}.\n";
        } else {
            echo "Column {$col} already exists.\n";
        }
    }

    if (!columnExists($db, 'document_tracking', 'idempotency_key')) {
        $db->exec("ALTER TABLE document_tracking ADD UNIQUE KEY uq_idempotency (idempotency_key)");
        echo "Added idempotency unique key.\n";
    }
    $db->exec("ALTER TABLE document_tracking ADD INDEX IF NOT EXISTS idx_source_system (source_system)");
    echo "Extended document_tracking table.\n";
} catch (PDOException $e) {
    echo "Error extending document_tracking: " . $e->getMessage() . "\n";
}

echo "Migration complete.\n";
