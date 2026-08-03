<?php
/**
 * Migration: Extend integration_settings with status and last_seen.
 */
require_once __DIR__ . '/../../core/config/database.php';

$db = getDatabase();

function columnExists2($db, $table, $column) {
    $stmt = $db->prepare("SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column");
    $stmt->execute([':table' => $table, ':column' => $column]);
    return (bool) $stmt->fetch();
}

$columns = [
    'last_seen' => 'DATETIME NULL AFTER enabled',
    'status'    => 'VARCHAR(50) NULL DEFAULT \'active\' AFTER last_seen',
];

try {
    foreach ($columns as $col => $def) {
        if (!columnExists2($db, 'integration_settings', $col)) {
            $db->exec("ALTER TABLE integration_settings ADD COLUMN {$col} {$def}");
            echo "Added column {$col}.\n";
        } else {
            echo "Column {$col} already exists.\n";
        }
    }
    echo "Extended integration_settings table.\n";
} catch (PDOException $e) {
    echo "Error extending integration_settings: " . $e->getMessage() . "\n";
}

echo "Migration complete.\n";
