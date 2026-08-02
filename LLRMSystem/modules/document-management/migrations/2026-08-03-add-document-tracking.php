<?php
/**
 * Migration: Add document tracking / provenance table
 * Run this script once to create the document_tracking table.
 */
require_once __DIR__ . '/../../core/config/database.php';

$db = getDatabase();

try {
    $db->exec("CREATE TABLE IF NOT EXISTS document_tracking (
        id INT AUTO_INCREMENT PRIMARY KEY,
        document_id INT NOT NULL,
        source_system VARCHAR(150) NOT NULL,
        external_reference VARCHAR(255) NULL,
        event_action VARCHAR(150) NOT NULL,
        description TEXT NULL,
        occurred_at DATETIME NOT NULL,
        recorded_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_document_id (document_id),
        INDEX idx_occurred_at (occurred_at),
        CONSTRAINT fk_document_tracking_document
            FOREIGN KEY (document_id) REFERENCES legislative_documents(id) ON DELETE CASCADE,
        CONSTRAINT fk_document_tracking_recorded_by
            FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created document_tracking table.\n";
} catch (PDOException $e) {
    echo "Error creating document_tracking: " . $e->getMessage() . "\n";
}

echo "Migration complete.\n";
