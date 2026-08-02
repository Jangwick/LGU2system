<?php
/**
 * Migration: Add integration_outbound_events table for async webhook delivery.
 */
require_once __DIR__ . '/../../core/config/database.php';

$db = getDatabase();

try {
    $db->exec("CREATE TABLE IF NOT EXISTS integration_outbound_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        document_id INT NOT NULL,
        source_system VARCHAR(50) NOT NULL,
        event VARCHAR(50) NOT NULL,
        payload TEXT NOT NULL,
        status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
        response TEXT NULL,
        attempts INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_attempt_at TIMESTAMP NULL,
        sent_at TIMESTAMP NULL,
        INDEX idx_status_created (status, created_at),
        INDEX idx_document_id (document_id),
        CONSTRAINT fk_outbound_document
            FOREIGN KEY (document_id) REFERENCES legislative_documents(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created integration_outbound_events table.\n";
} catch (PDOException $e) {
    echo "Error creating integration_outbound_events: " . $e->getMessage() . "\n";
}

echo "Migration complete.\n";
