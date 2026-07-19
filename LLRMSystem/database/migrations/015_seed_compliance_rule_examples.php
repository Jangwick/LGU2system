<?php
/**
 * Migration 015: Seed full legal rule examples and clear cached embeddings
 */

require_once __DIR__ . '/../../modules/core/config/database.php';

$db = getDatabase();

$rules = [
    'VAL-1987-CONSTITUTION' => [
        'summary' => '1987 Constitution of the Philippines',
        'example_excerpt' => "The proposed ordinance shall not violate any provision of the 1987 Constitution of the Philippines. Constitutional rights, Equal protection, Due process, Separation of powers, Local autonomy.",
    ],
    'RA-7160-SEC16' => [
        'summary' => 'RA 7160, Section 16 - General Welfare Clause',
        'example_excerpt' => "The ordinance must promote the general welfare of the residents of Valenzuela City. It must protect public welfare, peace and order, health, safety, environment, and economic development.",
    ],
    'RA-7160-LGC' => [
        'summary' => 'Local Government Code of 1991 (RA 7160)',
        'example_excerpt' => "The ordinance must be consistent with the Local Government Code of 1991 (RA 7160) and the powers of the Sangguniang Panlungsod. It must serve the general welfare, follow proper legislative procedure, and be within the city's legislative powers, including business regulation, markets, public transportation, garbage, environment, health, and local taxation. It must not regulate immigration, national defense, foreign policy, or criminal law beyond LGU authority.",
    ],
    'RA-7160-SEC54' => [
        'summary' => 'RA 7160, Section 54 - Legislative Procedure',
        'example_excerpt' => "The ordinance follows the required legislative procedure. Voting, Quorum, Proper approval.",
    ],
    'RA-7160-SEC55' => [
        'summary' => 'RA 7160, Section 55 - Mayor\'s Approval',
        'example_excerpt' => "The ordinance must be approved or vetoed by the Mayor as required by law. The Mayor's action on the ordinance must comply with the Local Government Code.",
    ],
    'RA-7160-SEC56' => [
        'summary' => 'RA 7160, Section 56 - Review of Ordinances',
        'example_excerpt' => "The ordinance must be reviewed to ensure there is no conflict with national law. It must not conflict with the Constitution, laws, executive orders, proclamations, or other regulations of superior authorities.",
    ],
    'VAL-EXISTING-ORDINANCES' => [
        'summary' => 'Existing Valenzuela City Ordinances',
        'example_excerpt' => "The ordinance shall not duplicate or conflict with existing Valenzuela City Ordinances. It must not be a duplicate, must be a proper amendment or repeal, and must not conflict with existing local legislation.",
    ],
    'VAL-COMMITTEE' => [
        'summary' => 'Sangguniang Panlungsod Committee Review',
        'example_excerpt' => "The proposed ordinance is evaluated by the appropriate committee before deliberation. The proper committee should be recommended based on the ordinance's subject matter, such as Committee on Laws, Committee on Health, Committee on Finance, Committee on Environment, or Committee on Public Safety.",
    ],
    'VAL-LEGAL-REVIEW' => [
        'summary' => 'City Legal Office Review',
        'example_excerpt' => "The ordinance must pass legal review by the City Legal Office. It must be constitutional, use proper legal wording, not conflict with national law, and not conflict with existing ordinances. The City Legal Office assists in drafting ordinances and resolutions certified by the Mayor as urgent and reviews ordinances before the Mayor acts on them.",
    ],
    'VAL-PUBLIC-CONSULTATION' => [
        'summary' => 'Public Consultation Requirement',
        'example_excerpt' => "Where required by law or policy, the ordinance should undergo public hearings or stakeholder consultation before approval. Public hearing, Consultation, Stakeholder meeting.",
    ],
    'VAL-PUBLICATION' => [
        'summary' => 'Publication Requirement',
        'example_excerpt' => "The ordinance shall not become effective until the publication or posting requirements prescribed by law have been satisfied. Publication and posting must comply with the Local Government Code.",
    ],
    'VAL-NATIONAL-LAWS' => [
        'summary' => 'National Laws Conflict Review',
        'example_excerpt' => "The ordinance must not conflict with national law. It must not contravene the Constitution, Republic Acts, Presidential Decrees, executive orders, proclamations, or regulations of superior authorities.",
    ],
    'VAL-IMPLEMENTATION' => [
        'summary' => 'Implementation and Monitoring / Funding',
        'example_excerpt' => "The ordinance must include implementing office and funding clauses. The funds necessary for implementation shall be charged against current appropriations, subject to accounting and auditing rules. The implementing agency and monitoring mechanism must be identified.",
    ],
    'VAL-VOTING' => [
        'summary' => 'Voting and Quorum',
        'example_excerpt' => "The ordinance must meet voting and quorum requirements during the legislative procedure. Voting, Quorum, Proper approval by the Sangguniang Panlungsod.",
    ],
    'VAL-MAYOR-REVIEW' => [
        'summary' => 'Mayor\'s Review',
        'example_excerpt' => "The ordinance must be reviewed and acted upon by the Mayor. The Mayor shall approve or veto the ordinance in accordance with the Local Government Code.",
    ],
];

$stmt = $db->prepare("UPDATE compliance_rules SET summary = :summary, example_excerpt = :excerpt, embedding_json = NULL WHERE code = :code");

echo "<h2>Migration 015: Seed full legal rule examples</h2>\n";

foreach ($rules as $code => $data) {
    $stmt->execute([
        ':summary' => $data['summary'],
        ':excerpt' => $data['example_excerpt'],
        ':code' => $code,
    ]);
    $rowCount = $stmt->rowCount();
    echo "<p>" . ($rowCount ? 'Updated' : 'Skipped') . ': ' . htmlspecialchars($code) . "</p>\n";
}

echo "<p><strong>Migration complete.</strong> Cached rule embeddings cleared; they will be re-embedded on the next compliance check.</p>\n";
