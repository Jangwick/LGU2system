<?php
/**
 * CLI smoke test for the compliance/ordinance alignment feature.
 * Run from the project root:
 *   php tests/cli/test-compliance.php
 */

require_once __DIR__ . '/../../modules/core/config/config.php';
require_once __DIR__ . '/../../modules/core/config/database.php';
require_once __DIR__ . '/../../modules/document-management/services/ComplianceService.php';

$pass = 0;
$fail = 0;

function logResult($label, $success, $message = '')
{
    global $pass, $fail;
    if ($success) {
        echo "[PASS] $label" . ($message ? " - $message" : '') . "\n";
        $pass++;
    } else {
        echo "[FAIL] $label" . ($message ? " - $message" : '') . "\n";
        $fail++;
    }
}

try {
    $db = getDatabase();
} catch (Exception $e) {
    logResult('Database connection', false, $e->getMessage());
    echo "\nResults: $pass passed, $fail failed\n";
    exit($fail > 0 ? 1 : 0);
}

// Ensure test tables are present
$requiredTables = ['compliance_rules', 'document_compliance_results'];
foreach ($requiredTables as $table) {
    $stmt = $db->query("SHOW TABLES LIKE '$table'");
    logResult("Table $table exists", $stmt->rowCount() > 0, "Table $table");
}

// Seed a rule and a document
$db->beginTransaction();

try {
    $db->exec("DELETE FROM document_compliance_results");
    $db->exec("DELETE FROM compliance_rules WHERE code LIKE 'CLI-TEST-%'");

    $db->exec("
        INSERT INTO compliance_rules (code, title, summary, document_type_scope, keywords, is_active)
        VALUES ('CLI-TEST-CONSTITUTION', 'Constitutional Check', 'Test', 'all', 'constitution, republic', 1)
    ");

    $db->exec("
        INSERT INTO legislative_documents
        (reference_number, title, document_type, document_date, status, description, tags, extracted_text, uploaded_by, is_encrypted, file_name, file_type, file_size, file_path)
        VALUES
        ('2024-CLI-001', 'CLI Test Document', 'ordinance', CURDATE(), 'pending', '', '', 'This ordinance is consistent with the constitution and the republic.', 1, 0, 'test.txt', 'text/plain', 0, 'storage/test.txt')
    ");

    $docId = (int) $db->lastInsertId();

    $service = new ComplianceService($db);
    $result = $service->checkDocument($docId);

    logResult('ComplianceService checkDocument returned success', $result['success']);
    logResult('Compliance status is "compliant"', ($result['compliance_status'] ?? '') === 'compliant', 'status: ' . ($result['compliance_status'] ?? 'none'));
    logResult('Compliance results stored', !empty($result['results']), count($result['results'] ?? []) . ' result(s)');

    // Rejection test
    $reject = $service->rejectWithComment($docId, 'CLI non-compliance reason', 1);
    logResult('rejectWithComment succeeded', $reject['success']);

    $stmt = $db->prepare("SELECT status FROM legislative_documents WHERE id = :id");
    $stmt->execute([':id' => $docId]);
    $status = $stmt->fetchColumn();
    logResult('Document status is "rejected"', $status === 'rejected', 'status: ' . $status);

    $db->rollBack();
} catch (Exception $e) {
    $db->rollBack();
    logResult('Compliance check execution', false, $e->getMessage());
}

echo "\nResults: $pass passed, $fail failed\n";
exit($fail > 0 ? 1 : 0);
