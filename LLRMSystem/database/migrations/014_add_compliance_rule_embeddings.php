<?php
/**
 * Migration 014: Add compliance rule example excerpts and embedding caches
 *
 * Adds example_excerpt/embedding_json to compliance_rules and creates
 * a dedicated document_compliance_embeddings table for cached document vectors.
 */

require_once __DIR__ . '/../../modules/core/config/database.php';

$db = getDatabase();

$columns = [
    'example_excerpt' => 'TEXT NULL AFTER summary',
    'embedding_json'   => 'LONGTEXT NULL AFTER example_excerpt',
];

echo "<h2>Migration 014: Compliance rule example/embedding columns</h2>\n";

foreach ($columns as $columnName => $definition) {
    try {
        $sql = "ALTER TABLE compliance_rules ADD COLUMN $columnName $definition";
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

try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS document_compliance_embeddings (
            document_id INT PRIMARY KEY,
            embedding LONGTEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (document_id) REFERENCES legislative_documents(id) ON DELETE CASCADE
        )
    ");
    echo "<p style='color:green;'>✓ Table <strong>document_compliance_embeddings</strong> ready.</p>\n";
} catch (PDOException $e) {
    echo "<p style='color:red;'>✗ Error creating table: " . $e->getMessage() . "</p>\n";
}

$examples = [
    'VAL-1987-CONSTITUTION' => 'This Ordinance is enacted pursuant to the powers granted to local government units under the Constitution of the Republic of the Philippines and shall not contravene any constitutional provision or national law.',
    'RA-7160-SEC16' => 'Pursuant to Section 16 of Republic Act No. 7160, this Ordinance promotes the general welfare of the residents of Valenzuela City by ensuring public health, safety, environmental protection, and economic development.',
    'RA-7160-LGC' => 'Be it ordained by the Sangguniang Panlungsod of Valenzuela City, in accordance with the Local Government Code of 1991 (RA 7160), that the following regulations shall govern...',
    'RA-7160-SEC458' => 'Be it ordained by the Sangguniang Panlungsod of Valenzuela City, in accordance with Section 458 of Republic Act No. 7160, that the following regulations shall govern...',
    'VAL-LEGAL-STRUCTURE' => "ORDINANCE NO. ____\nSeries of 2026\n\nAN ORDINANCE...\n\nWHEREAS,...\nWHEREAS,...\n\nNOW THEREFORE,\n\nBE IT ORDAINED by the Sangguniang Panlungsod of Valenzuela City...",
    'VAL-SECTION-NUMBERING' => "SECTION 1. Title.\nSECTION 2. Definition of Terms.\nSECTION 3. Coverage.\nSECTION 4. Prohibited Acts.\nSECTION 5. Penalties.",
    'VAL-EFFECTIVITY' => 'This Ordinance shall take effect fifteen (15) days after its publication in a newspaper of general circulation or posting as required by law.',
    'VAL-SEVERABILITY' => 'If any provision of this Ordinance is declared unconstitutional or invalid, the remaining provisions shall continue to be in full force and effect.',
    'VAL-REPEALING' => 'All ordinances, executive orders, rules and regulations inconsistent with this Ordinance are hereby repealed or modified accordingly.',
    'VAL-PENALTY' => 'Any person found violating this Ordinance shall be subject to a fine not exceeding Five Thousand Pesos (₱5,000.00) or imprisonment not exceeding one (1) year, or both, at the discretion of the court, as authorized by law.',
    'VAL-IMPLEMENTING-OFFICE' => 'The City Environment and Natural Resources Office (CENRO) shall be the primary implementing agency responsible for enforcing this Ordinance.',
    'VAL-FUNDING' => 'The funds necessary for the implementation of this Ordinance shall be charged against the current appropriations of the City Government, subject to applicable accounting and auditing rules.',
    'VAL-AMENDATORY' => 'Section 5 of Ordinance No. 1052, Series of 2022, is hereby amended to read as follows...',
    'VAL-PUBLIC-WELFARE' => 'This Ordinance is enacted to protect the health, safety, convenience, and general welfare of the residents of Valenzuela City.',
    'VAL-NATIONAL-LAWS' => 'Nothing in this Ordinance shall be construed to conflict with existing national laws, rules, or regulations.',
];

$updated = 0;
foreach ($examples as $code => $excerpt) {
    $stmt = $db->prepare("UPDATE compliance_rules SET example_excerpt = :excerpt WHERE code = :code");
    $stmt->execute([':excerpt' => $excerpt, ':code' => $code]);
    if ($stmt->rowCount() > 0) {
        $updated++;
    }
}

echo "<p><strong>Seeded $updated rule example excerpts.</strong></p>\n";
echo "<p><strong>Migration complete.</strong></p>\n";
