<?php
/**
 * Migration: Add integration_settings table for outbound webhook configuration.
 */
require_once __DIR__ . '/../../core/config/database.php';

$db = getDatabase();

try {
    $db->exec("CREATE TABLE IF NOT EXISTS integration_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        source_system VARCHAR(50) NOT NULL,
        webhook_url VARCHAR(500) NULL,
        api_key VARCHAR(255) NULL,
        enabled TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_source_system (source_system)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created integration_settings table.\n";
} catch (PDOException $e) {
    echo "Error creating integration_settings: " . $e->getMessage() . "\n";
}

echo "Migration complete.\n";
