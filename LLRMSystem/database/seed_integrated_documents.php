<?php
/**
 * Idempotent seed: one compliance-passing document per integrated system
 * (las, pcms, phms, cms, orts, lacs). Safe to re-run — skips systems whose
 * SEED-* external_id already exists in integrated_records.
 *
 * Each seed carries a compliance certification annex in extracted_text that
 * satisfies every applicable rule's keywords, then runs the real
 * ComplianceService::checkDocument so the stored verdict is genuine.
 *
 * Usage: php database/seed_integrated_documents.php
 */

$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);

// Keep the compliance check deterministic: no external AI calls during seeding.
// checkDocument falls back to keyword scoring, which the annex text covers 100%.
define('GROQ_API_KEY', '');
define('GEMINI_API_KEY', '');

require_once __DIR__ . '/../modules/core/config/config.php';
require_once __DIR__ . '/../modules/core/config/database.php';
require_once __DIR__ . '/../modules/core/utils/SimplePDF.php';
require_once __DIR__ . '/../modules/document-management/services/ComplianceService.php';
require_once __DIR__ . '/../modules/document-management/services/SummarizationService.php';
require_once __DIR__ . '/../modules/document-management/services/DocumentTrackingService.php';

$db = getDatabase();

// Annex text covering every applicable compliance rule keyword set
// (constitution, RA 7160 general welfare/procedure/mayor/review, national laws,
// existing ordinances, committee, legal review, consultation, voting, mayor
// review, publication, implementation).
$annex = <<<TXT
COMPLIANCE CERTIFICATION AND LEGISLATIVE RECORD

This local ordinance of the City of Valenzuela was enacted by the Sangguniang Panlungsod pursuant to RA 7160, the Local Government Code of 1991, in the exercise of the LGU's delegated legislative powers over local ordinance making.

Constitutional Review: This measure was reviewed for consistency with the 1987 Constitution, the supreme law of the land, and is founded on constitutional principles of equal protection, due process, and local autonomy. It is constitutional in form and substance.

General Welfare: This ordinance promotes the general welfare and public welfare of the people of Valenzuela City, advancing peace and order, environmental protection, economic development, public health, and safety.

National Law Consistency: This ordinance does not conflict with any national law, Republic Act, Presidential Decree, Executive Order, Administrative Order, Supreme Court decision, or Department Circular.

Existing Legislation: This measure does not duplicate or conflict with any existing ordinance of Valenzuela City; where applicable it operates as a proper amendment and provides for repeal of inconsistent provisions.

Committee Evaluation: This measure underwent committee evaluation before the Committee on Laws, with input from the Committee on Health, Committee on Education, Committee on Environment, Committee on Finance, and Committee on Public Safety.

Legal Review: The City Legal Office conducted legal review and certified this measure as legally valid, constitutional, consistent with superior law, and drafted in proper legal terminology.

Public Participation: A public hearing and public consultation were conducted, including a stakeholder meeting attended by barangay officials, business owner representatives, NGO delegates, and resident participants.

Legislative Process: The legislative process included deliberation, interpellation, and amendments during session discussion, with the recommendation of the City Council adopted. A quorum was present and the measure obtained the required affirmative votes of the council members during voting. The approval process complied with the prescribed procedure.

Mayor's Action: Following mayor review, the engrossed copy was prepared to forward to mayor for mayor approval; the Mayor may approve or veto the measure, and any veto override shall follow the approval process under the Local Government Code.

Publication: This ordinance becomes enforceable only after the publication and public posting requirements are satisfied through official publication and dissemination to all barangays.

Implementation: The implementation of this ordinance is assigned to the concerned LGU department, which shall monitor compliance; funding is charged against current appropriations subject to accounting and auditing rules, and any amendment shall follow the same legislative process.
TXT;

$seeds = [
    ['system' => 'las',  'type' => 'archive',      'ext' => 'SEED-LAS-001',  'ref' => 'ARC-2026-901', 'date' => '2026-08-20',
     'title' => 'Archived Ordinance No. 2024-015 - Regulating Single-Use Plastics in Public Markets',
     'desc'  => 'Archival record transmitted by the Legislative Archive System (LAS).'],
    ['system' => 'pcms', 'type' => 'consultation', 'ext' => 'SEED-PCMS-001', 'ref' => 'CON-2026-901', 'date' => '2026-08-24',
     'title' => 'Consultation Summary - Proposed Barangay Health Station Accreditation Ordinance',
     'desc'  => 'Public consultation summary transmitted by the Public Consultation Management System (PCMS).'],
    ['system' => 'phms', 'type' => 'hearing',      'ext' => 'SEED-PHMS-001', 'ref' => 'HEA-2026-901', 'date' => '2026-08-27',
     'title' => 'Public Hearing Record - Proposed Ordinance on Food Establishment Sanitation',
     'desc'  => 'Public hearing record transmitted by the Public Hearing Management System (PHMS).'],
    ['system' => 'cms',  'type' => 'committee',    'ext' => 'SEED-CMS-001',  'ref' => 'COM-2026-901', 'date' => '2026-09-01',
     'title' => 'Committee Report - Committee on Laws Review of the Proposed Traffic Management Ordinance',
     'desc'  => 'Committee report transmitted by the Committee Management System (CMS).'],
    ['system' => 'orts', 'type' => 'ordinance',    'ext' => 'SEED-ORTS-001', 'ref' => 'ORD-2026-901', 'date' => '2026-09-03',
     'title' => 'Proposed Ordinance No. 2026-007 - Establishing the Valenzuela Green Procurement Program',
     'desc'  => 'Ordinance draft transmitted by the Ordinance Review and Tracking System (ORTS).'],
    ['system' => 'lacs', 'type' => 'agenda',       'ext' => 'SEED-LACS-001', 'ref' => 'AGE-2026-901', 'date' => '2026-09-08',
     'title' => 'Regular Session Agenda No. 2026-18 - Ordinances for Second Reading',
     'desc'  => 'Session agenda transmitted by the Legislative Agenda and Calendar System (LACS).'],
];

$uploadDir = BASE_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'documents';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$exists = $db->prepare("SELECT id FROM integrated_records WHERE source_system = :s AND external_id = :e");
$insRec = $db->prepare("
    INSERT INTO integrated_records (module_type, external_id, title, summary, data_payload, source_system, status)
    VALUES (:type, :ext, :title, :summary, :payload, :source, 'synced')
");
$insDoc = $db->prepare("
    INSERT INTO legislative_documents (
        reference_number, title, document_type, document_date,
        status, description, tags, source_module, source_id,
        uploaded_by, created_at, file_path, file_name, file_size, file_type,
        is_encrypted, extracted_text, ocr_status, ocr_processed_at,
        key_points, key_points_generated_at
    ) VALUES (
        :ref, :title, :type, :doc_date,
        'pending', :desc, :tags, :source_module, :source_id,
        1, NOW(), :file_path, :file_name, :file_size, 'application/pdf',
        0, :extracted_text, 'completed', NOW(),
        :key_points, NOW()
    )
");

$summarizer = new SummarizationService();
$compliance = new ComplianceService($db);
$tracking = new DocumentTrackingService($db);

foreach ($seeds as $s) {
    $exists->execute([':s' => $s['system'], ':e' => $s['ext']]);
    if ($rec = $exists->fetch()) {
        // Heal docs seeded previously whose compliance check never completed
        $docStmt = $db->prepare("SELECT id, compliance_status FROM legislative_documents WHERE source_id = ? AND source_module = ? AND deleted_at IS NULL ORDER BY id DESC LIMIT 1");
        $docStmt->execute([$rec['id'], $s['system']]);
        $doc = $docStmt->fetch(PDO::FETCH_ASSOC);
        if ($doc && in_array($doc['compliance_status'], [null, '', 'pending'], true)) {
            try {
                $result = $compliance->checkDocument($doc['id'], 1);
                echo "[healed] {$s['system']} doc#{$doc['id']} -> compliance: {$result['compliance_status']}\n";
            } catch (Exception $e) {
                echo "[healed] {$s['system']} doc#{$doc['id']} -> compliance check failed: {$e->getMessage()}\n";
            }
        } else {
            echo "[skip] {$s['system']}: {$s['ext']} already seeded\n";
        }
        continue;
    }

    $body = $s['title'] . "\n\n" . $s['desc'] . "\n\n" . $annex;

    // Real downloadable file
    $pdf = new SimplePDF();
    $pdf->SetTitle($s['title']);
    $pdf->AddText($s['title'], 11, 'center', 'bold');
    foreach (explode("\n", wordwrap($body, 95)) as $line) {
        $pdf->AddText($line, 9);
    }
    $fileName = 'SEED_' . strtoupper($s['system']) . '.pdf';
    file_put_contents($uploadDir . DIRECTORY_SEPARATOR . $fileName, $pdf->Output());
    $fileSize = filesize($uploadDir . DIRECTORY_SEPARATOR . $fileName);
    $storedPath = 'storage/documents/' . $fileName;

    $keyPoints = null;
    try {
        $keyPoints = $summarizer->generateKeyPointsString($body, 7);
    } catch (Exception $e) { /* optional */ }

    // Bump reference suffix until unique (prod may already hold ORD-2026-901 etc.)
    $ref = $s['ref'];
    $refCheck = $db->prepare("SELECT 1 FROM legislative_documents WHERE reference_number = ?");
    $refCheck->execute([$ref]);
    while ($refCheck->fetch()) {
        $ref = preg_replace_callback('/\d+$/', fn($m) => $m[0] + 1, $ref);
        $refCheck->execute([$ref]);
    }

    $db->beginTransaction();
    try {
    $insRec->execute([
        ':type' => $s['type'] . 's',
        ':ext' => $s['ext'],
        ':title' => $s['title'],
        ':summary' => $s['desc'],
        ':payload' => json_encode(['document_date' => $s['date'], 'tags' => 'seed,integration,' . $s['system'], 'description' => $s['desc']]),
        ':source' => $s['system'],
    ]);
    $recId = $db->lastInsertId();

    $insDoc->execute([
        ':ref' => $ref,
        ':title' => $s['title'],
        ':type' => $s['type'],
        ':doc_date' => $s['date'],
        ':desc' => $s['desc'],
        ':tags' => 'seed,integration,' . $s['system'],
        ':source_module' => $s['system'],
        ':source_id' => $recId,
        ':file_path' => $storedPath,
        ':file_name' => $fileName,
        ':file_size' => $fileSize,
        ':extracted_text' => $body,
        ':key_points' => $keyPoints,
    ]);
    $docId = $db->lastInsertId();
    $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        echo "[error] {$s['system']}: {$e->getMessage()}\n";
        continue;
    }

    try {
        $tracking->recordDocumentReceipt($docId, $s['system'], $s['ext'], 1);
    } catch (Exception $e) { /* tracking is best-effort */ }

    try {
        $result = $compliance->checkDocument($docId, 1);
        echo "[seeded] {$s['system']} doc#{$docId} {$ref} -> compliance: {$result['compliance_status']} ({$result['explanation']})\n";
    } catch (Exception $e) {
        echo "[seeded] {$s['system']} doc#{$docId} {$ref} -> compliance check failed: {$e->getMessage()}\n";
    }
}

echo "Done.\n";
