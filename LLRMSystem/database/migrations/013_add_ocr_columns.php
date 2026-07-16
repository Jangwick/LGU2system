<?php
/**
 * Migration 013: Add OCR and key points columns to legislative_documents table
 * 
 * Run this script once via browser or CLI to add the new columns.
 */

require_once __DIR__ . '/../../modules/core/config/database.php';

$db = getDatabase();

$columns = [
    'extracted_text'      => 'LONGTEXT NULL AFTER tags',
    'ocr_status'          => "ENUM('pending','processing','completed','failed','skipped') DEFAULT 'pending' AFTER extracted_text",
    'ocr_processed_at'    => 'DATETIME NULL AFTER ocr_status',
    'key_points'          => 'TEXT NULL AFTER ocr_processed_at',
    'key_points_generated_at' => 'DATETIME NULL AFTER key_points'
];

echo "<h2>Migration 013: Add OCR columns to legislative_documents</h2>\n";

foreach ($columns as $columnName => $definition) {
    try {
        $sql = "ALTER TABLE legislative_documents ADD COLUMN $columnName $definition";
        $db->exec($sql);
        echo "<p style='color:green;'>✓ Added column: <strong>$columnName</strong></p>\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<p style='color:orange;'>⊘ Column already exists: <strong>$columnName</strong> (skipped)</p>\n";
        } else {
            echo "<p style='color:red;'>✗ Error adding column <strong>$columnName</strong>: " . $e->getMessage() . "</p>\n";
        }
    }
}

echo "<p><strong>Migration complete.</strong></p>\n";
