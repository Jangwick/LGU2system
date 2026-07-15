<?php
/**
 * Migration: Add status tracking to document management
 * Run this script once to add the approved_by/approved_at columns and
 * create the document_status_history table.
 */
require_once __DIR__ . '/../../core/config/database.php';

$db = getDatabase();

try {
    $db->exec("ALTER TABLE legislative_documents
        ADD COLUMN approved_by INT NULL AFTER status,
        ADD COLUMN approved_at DATETIME NULL AFTER approved_by,
        ADD CONSTRAINT fk_legislative_documents_approved_by
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL");
    echo "Added approved_by and approved_at to legislative_documents.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false || strpos($e->getMessage(), 'already exists') !== false) {
        echo "approved_by/approved_at already exist.\n";
    } else {
        throw $e;
    }
}

try {
    $db->exec("ALTER TABLE legislative_documents
        ADD COLUMN status_changed_by INT NULL AFTER approved_at,
        ADD COLUMN status_changed_at DATETIME NULL AFTER status_changed_by,
        ADD CONSTRAINT fk_legislative_documents_status_changed_by
            FOREIGN KEY (status_changed_by) REFERENCES users(id) ON DELETE SET NULL");
    echo "Added status_changed_by and status_changed_at to legislative_documents.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false || strpos($e->getMessage(), 'already exists') !== false) {
        echo "status_changed_by/status_changed_at already exist.\n";
    } else {
        throw $e;
    }
}

try {
    $db->exec("CREATE TABLE IF NOT EXISTS document_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        document_id INT NOT NULL,
        old_status VARCHAR(50) NULL,
        new_status VARCHAR(50) NOT NULL,
        changed_by INT NULL,
        changed_at DATETIME NOT NULL DEFAULT NOW(),
        notes TEXT NULL,
        INDEX idx_document_id (document_id),
        INDEX idx_changed_at (changed_at),
        CONSTRAINT fk_document_status_history_document
            FOREIGN KEY (document_id) REFERENCES legislative_documents(id) ON DELETE CASCADE,
        CONSTRAINT fk_document_status_history_changed_by
            FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created document_status_history table.\n";
} catch (PDOException $e) {
    echo "Error creating document_status_history: " . $e->getMessage() . "\n";
}

echo "Migration complete.\n";
