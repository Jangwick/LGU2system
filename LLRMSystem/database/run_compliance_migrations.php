<?php
/**
 * Compliance / Ordinance Alignment Migration Runner
 * Creates compliance_rules and document_compliance_results tables,
 * adds compliance columns to legislative_documents, and seeds the
 * Valenzuela City ordinance alignment standards.
 */

require_once __DIR__ . '/../modules/core/config/database.php';

$db = getDatabase();

function execOrSkip($db, $sql) {
    try {
        $db->exec($sql);
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (stripos($msg, 'Duplicate column') !== false ||
            stripos($msg, 'already exists') !== false ||
            stripos($msg, 'duplicate key') !== false) {
            return;
        }
        throw $e;
    }
}

// 1. Compliance rules master table
$db->exec("
CREATE TABLE IF NOT EXISTS compliance_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    summary TEXT,
    document_type_scope VARCHAR(50) DEFAULT 'all',
    keywords TEXT,
    required_tags TEXT,
    forbidden_keywords TEXT,
    reference_pattern VARCHAR(255),
    weight INT DEFAULT 1,
    is_mandatory TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    effective_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 2. Document compliance results (one row per matched rule per check)
$db->exec("
CREATE TABLE IF NOT EXISTS document_compliance_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    rule_id INT NULL,
    status ENUM('compliant','non_compliant','pending') NOT NULL DEFAULT 'pending',
    score INT DEFAULT 0,
    matched_keywords TEXT,
    explanation TEXT,
    reviewer_comment TEXT,
    checked_by INT NULL,
    checked_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_document (document_id),
    KEY idx_rule (rule_id),
    KEY idx_status (status),
    CONSTRAINT fk_dcr_document FOREIGN KEY (document_id) REFERENCES legislative_documents(id) ON DELETE CASCADE,
    CONSTRAINT fk_dcr_rule FOREIGN KEY (rule_id) REFERENCES compliance_rules(id) ON DELETE CASCADE,
    CONSTRAINT fk_dcr_user FOREIGN KEY (checked_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 3. Add compliance columns to legislative_documents
execOrSkip($db, "ALTER TABLE legislative_documents ADD COLUMN compliance_status ENUM('pending','compliant','non_compliant') NOT NULL DEFAULT 'pending' AFTER status");
execOrSkip($db, "ALTER TABLE legislative_documents ADD COLUMN compliance_checked_at TIMESTAMP NULL AFTER compliance_status");
execOrSkip($db, "CREATE INDEX idx_compliance_status ON legislative_documents(compliance_status)");

// 4. Seed the Valenzuela City ordinance alignment standards
$rules = [
    [
        'code' => 'VAL-1987-CONSTITUTION',
        'title' => 'Compliance with the 1987 Constitution of the Philippines',
        'summary' => 'The proposed document must comply with the 1987 Constitution of the Philippines, the supreme law of the country.',
        'document_type_scope' => 'all',
        'keywords' => '1987 Constitution, constitutional, supreme law, constitutional principles',
    ],
    [
        'code' => 'RA-7160-LGC',
        'title' => 'Compliance with the Local Government Code of 1991 (RA 7160)',
        'summary' => 'The document must be consistent with the Local Government Code of 1991 (Republic Act No. 7160) which grants LGUs authority to enact local ordinances.',
        'document_type_scope' => 'all',
        'keywords' => 'RA 7160, Local Government Code, LGU, local ordinance',
    ],
    [
        'code' => 'RA-7160-SEC16',
        'title' => 'General Welfare Clause (Section 16, RA 7160)',
        'summary' => 'The ordinance should contribute to public welfare, peace and order, environmental protection, economic development, and public health and safety.',
        'document_type_scope' => 'all',
        'keywords' => 'general welfare, public welfare, peace and order, environmental protection, economic development, public health, safety',
    ],
    [
        'code' => 'RA-7160-SEC447',
        'title' => 'Legislative Powers of Sangguniang Bayan (Section 447)',
        'summary' => 'Grants legislative powers to the Sangguniang Bayan to enact municipal ordinances.',
        'document_type_scope' => 'municipality',
        'keywords' => 'Sangguniang Bayan, municipal ordinance',
    ],
    [
        'code' => 'RA-7160-SEC458',
        'title' => 'Legislative Powers of Sangguniang Panlungsod (Section 458)',
        'summary' => 'Primary legal basis for the Sangguniang Panlungsod of Valenzuela City to enact, amend, repeal ordinances and approve local policies.',
        'document_type_scope' => 'city',
        'keywords' => 'Sangguniang Panlungsod, city council, Valenzuela, enact ordinance, amend, repeal, approve policies',
    ],
    [
        'code' => 'RA-7160-SEC54',
        'title' => 'Legislative Process for Passing Ordinances (Section 54)',
        'summary' => 'Outlines quorum requirements, voting procedures, and the approval process for ordinances.',
        'document_type_scope' => 'all',
        'keywords' => 'quorum, voting, approval process, legislative process',
    ],
    [
        'code' => 'RA-7160-SEC55',
        'title' => "Mayor's Approval (Section 55)",
        'summary' => 'An ordinance approved by the Sanggunian is forwarded to the Mayor, who may approve or veto it.',
        'document_type_scope' => 'all',
        'keywords' => 'mayor approval, veto, forward to mayor',
    ],
    [
        'code' => 'RA-7160-SEC56',
        'title' => 'Review of Component City Ordinances (Section 56)',
        'summary' => 'Ordinances may undergo further review to ensure they are legally valid and do not conflict with existing laws.',
        'document_type_scope' => 'city',
        'keywords' => 'review, legal validity, component city',
    ],
    [
        'code' => 'VAL-NATIONAL-LAWS',
        'title' => 'Review of Existing National Laws',
        'summary' => 'The document must not conflict with national legal issuances such as Republic Acts, Presidential Decrees, Executive Orders, Administrative Orders, Supreme Court decisions, or Department Circulars.',
        'document_type_scope' => 'all',
        'keywords' => 'Republic Act, Presidential Decree, Executive Order, Administrative Order, Supreme Court decision, Department Circular, national law',
    ],
    [
        'code' => 'VAL-EXISTING-ORDINANCES',
        'title' => 'Review of Existing Local Ordinances',
        'summary' => 'The proposed ordinance must be compared with existing Valenzuela City ordinances to avoid duplication, conflict, or need for amendment/repeal.',
        'document_type_scope' => 'ordinance',
        'keywords' => 'existing ordinance, Valenzuela, amendment, repeal, duplicate, conflict',
    ],
    [
        'code' => 'VAL-COMMITTEE',
        'title' => 'Committee Evaluation',
        'summary' => 'The document is referred to the appropriate committee for initial evaluation of purpose, legal basis, and policy implications.',
        'document_type_scope' => 'all',
        'keywords' => 'committee evaluation, Committee on Laws, Committee on Health, Committee on Education, Committee on Environment, Committee on Finance, Committee on Public Safety',
    ],
    [
        'code' => 'VAL-LEGAL-REVIEW',
        'title' => 'Legal Review by City Legal Office',
        'summary' => 'The City Legal Office determines whether the document is legally valid, constitutional, consistent with national and local laws, and properly written.',
        'document_type_scope' => 'all',
        'keywords' => 'City Legal Office, legal review, legally valid, constitutional, consistent, legal terminology',
    ],
    [
        'code' => 'VAL-PUBLIC-CONSULTATION',
        'title' => 'Public Consultation',
        'summary' => 'For documents that significantly affect the community, the LGU may conduct public hearings, consultations, or stakeholder meetings.',
        'document_type_scope' => 'all',
        'keywords' => 'public hearing, public consultation, stakeholder meeting, barangay, business owner, NGO, resident',
    ],
    [
        'code' => 'VAL-SP-DELIBERATION',
        'title' => 'Deliberation by the Sangguniang Panlungsod',
        'summary' => 'The document undergoes deliberation by the full City Council, including interpellation, amendments, discussions, and recommendations.',
        'document_type_scope' => 'city',
        'keywords' => 'deliberation, interpellation, amendments, discussion, recommendation, City Council',
    ],
    [
        'code' => 'VAL-VOTING',
        'title' => 'Voting',
        'summary' => 'Council members vote, a quorum must be present, and the required number of affirmative votes must be obtained.',
        'document_type_scope' => 'all',
        'keywords' => 'quorum, affirmative votes, council members, voting',
    ],
    [
        'code' => 'VAL-MAYOR-REVIEW',
        'title' => "Mayor's Review",
        'summary' => 'The Mayor reviews the approved document and may approve or veto it; a veto may be returned for amendment or override.',
        'document_type_scope' => 'all',
        'keywords' => 'mayor review, approve, veto, veto override',
    ],
    [
        'code' => 'VAL-PUBLICATION',
        'title' => 'Publication Requirements',
        'summary' => 'The document must satisfy publication requirements such as public posting, official publication, and public dissemination before it becomes enforceable.',
        'document_type_scope' => 'all',
        'keywords' => 'publication, public posting, official publication, dissemination, enforceable',
    ],
    [
        'code' => 'VAL-IMPLEMENTATION',
        'title' => 'Implementation and Monitoring',
        'summary' => 'Once effective, the appropriate LGU departments implement provisions and monitor compliance; amendments may be proposed if issues arise.',
        'document_type_scope' => 'all',
        'keywords' => 'implementation, LGU department, monitor, amendment',
    ],
];

$stmt = $db->prepare("
    INSERT IGNORE INTO compliance_rules
    (code, title, summary, document_type_scope, keywords, weight, is_mandatory, is_active)
    VALUES (:code, :title, :summary, :document_type_scope, :keywords, :weight, :is_mandatory, :is_active)
");

foreach ($rules as $rule) {
    $stmt->execute([
        ':code' => $rule['code'],
        ':title' => $rule['title'],
        ':summary' => $rule['summary'],
        ':document_type_scope' => $rule['document_type_scope'],
        ':keywords' => $rule['keywords'],
        ':weight' => $rule['document_type_scope'] === 'all' ? 1 : 2,
        ':is_mandatory' => 0,
        ':is_active' => 1,
    ]);
}

echo "Compliance migration completed successfully.\n";
