<?php
/**
 * Migration: Add last_seen_at to users for new-document notifications
 */
require_once __DIR__ . '/../../core/config/database.php';

$db = getDatabase();

try {
    $db->exec("ALTER TABLE users
        ADD COLUMN last_seen_at DATETIME NULL AFTER updated_at");
    echo "Added last_seen_at to users.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false || strpos($e->getMessage(), 'already exists') !== false) {
        echo "last_seen_at already exists.\n";
    } else {
        throw $e;
    }
}

echo "Migration complete.\n";
