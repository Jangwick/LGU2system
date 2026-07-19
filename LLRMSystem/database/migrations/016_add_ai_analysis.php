<?php
/**
 * Migration 016: Add AI analysis and legal reference columns
 */

require_once __DIR__ . '/../../modules/core/config/database.php';

$db = getDatabase();

echo "<h2>Migration 016: Add AI analysis and legal reference columns</h2>\n";

$columns = [
    'document_compliance_results' => [
        'ai_analysis' => 'TEXT NULL AFTER explanation',
    ],
    'compliance_rules' => [
        'reference_url' => 'VARCHAR(500) NULL AFTER example_excerpt',
        'reference_text' => 'LONGTEXT NULL AFTER reference_url',
    ],
];

foreach ($columns as $table => $defs) {
    foreach ($defs as $column => $definition) {
        try {
            $db->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
            echo "<p style='color:green;'>Added column {$column} to {$table}</p>\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "<p style='color:orange;'>Column {$column} already exists (skipped)</p>\n";
            } else {
                echo "<p style='color:red;'>Error adding {$column}: " . $e->getMessage() . "</p>\n";
            }
        }
    }
}

$references = [
    'VAL-1987-CONSTITUTION' => [
        'url' => 'https://www.officialgazette.gov.ph/constitutions/1987-constitution/',
        'text' => 'The 1987 Constitution of the Philippines is the supreme law of the land. No local ordinance may violate constitutional rights, equal protection, due process, separation of powers, or local autonomy. Article II states the Philippines is a republican and democratic State where sovereignty resides in the people. Article III enumerates civil and political rights. Local autonomy is recognized under Article X, which provides for autonomous regions and local government units.',
    ],
    'RA-7160-LGC' => [
        'url' => 'https://elibrary.judiciary.gov.ph//thebookshelf//showdocs/2/53542',
        'text' => 'Republic Act No. 7160, the Local Government Code of 1991, is the primary statute governing local government units. It grants powers to the Sangguniang Panlungsod to enact ordinances, approve resolutions, and appropriate funds for the general welfare. The Code defines the scope of local legislative authority, the process for enacting ordinances, the roles of the Mayor, and the requirements for publication and review.',
    ],
    'RA-7160-SEC16' => [
        'url' => 'https://elibrary.judiciary.gov.ph//thebookshelf//showdocs/2/53542',
        'text' => 'Section 16 of the Local Government Code, the General Welfare Clause, mandates every local government unit to exercise the powers expressly granted, those necessarily implied therefrom, as well as powers necessary, appropriate, or incidental for its efficient and effective governance, and those which are essential to the promotion of the general welfare. It must ensure and promote the health and safety, enhance the right of the people to a balanced ecology, encourage and support the development of appropriate and self-reliant scientific and technological capabilities, improve public morals, enhance economic prosperity and social justice, promote full employment, and support education.',
    ],
    'RA-7160-SEC54' => [
        'url' => 'https://elibrary.judiciary.gov.ph//thebookshelf//showdocs/2/53542',
        'text' => 'Section 54 of the Local Government Code provides the procedure for the enactment of ordinances. No ordinance shall be passed unless it is first assigned to the proper committee and a quorum is present. The affirmative vote of a majority of the members present, there being a quorum, is necessary for the passage of an ordinance. The ayes and nays shall be recorded and entered into the minutes of the Sangguniang Panlungsod.',
    ],
    'RA-7160-SEC55' => [
        'url' => 'https://elibrary.judiciary.gov.ph//thebookshelf//showdocs/2/53542',
        'text' => 'Section 55 of the Local Government Code states that every ordinance enacted by the Sangguniang Panlungsod shall be presented to the Mayor for approval. If the Mayor approves the same, it shall be signed by him; otherwise, he shall veto it and return it with his objections to the Sangguniang Panlungsod. The veto may be overridden by a two-thirds vote of all members of the Sangguniang Panlungsod.',
    ],
    'RA-7160-SEC56' => [
        'url' => 'https://elibrary.judiciary.gov.ph//thebookshelf//showdocs/2/53542',
        'text' => 'Section 56 of the Local Government Code governs the review of ordinances. Within three days after the approval of an ordinance, the Secretary to the Sangguniang Panlungsod shall forward copies of the same to the Sangguniang Panlalawigan or, in the case of a highly urbanized city, to the Office of the President or the Department of Interior and Local Government for review as to whether it is within the powers granted and does not contravene any provision of law, executive order, proclamation, or regulation.',
    ],
    'VAL-EXISTING-ORDINANCES' => [
        'url' => 'https://valenzuela.gov.ph/',
        'text' => 'Existing Valenzuela City ordinances remain in force until repealed, amended, or superseded. A new ordinance must not duplicate or conflict with existing local legislation. When an ordinance is an amendment, it must specifically identify the provision being amended and the manner of amendment. A repealing clause must identify inconsistent prior ordinances.',
    ],
    'VAL-COMMITTEE' => [
        'url' => 'https://valenzuela.gov.ph/',
        'text' => 'Valenzuela City Sangguniang Panlungsod committees review proposed ordinances before deliberation. The Committee on Laws, Health, Finance, Environment, Public Safety, and other standing committees evaluate measures within their jurisdiction. The proposed ordinance must be referred to the proper committee and receive a committee report before plenary action.',
    ],
    'VAL-LEGAL-REVIEW' => [
        'url' => 'https://valenzuela.gov.ph/city-legal-office/',
        'text' => 'The Valenzuela City Legal Office assists in drafting ordinances and resolutions certified by the Mayor as urgent and reviews ordinances and resolutions before the Mayor acts on them. It ensures proposed legislation is constitutional, uses proper legal wording, does not conflict with national law, and does not conflict with existing Valenzuela City ordinances.',
    ],
    'VAL-PUBLIC-CONSULTATION' => [
        'url' => 'https://valenzuela.gov.ph/files/citizen_charter/VC-CITIZENS-CHARTER%202026-V.3.pdf',
        'text' => 'Public hearings and stakeholder consultations are required for ordinances that affect public rights, livelihood, or welfare. The Sangguniang Panlungsod may conduct public hearings, and the City Legal Office provides legal assistance for committee hearings. Consultation records should be reflected in the ordinance preamble or minutes.',
    ],
    'VAL-PUBLICATION' => [
        'url' => 'https://elibrary.judiciary.gov.ph//thebookshelf//showdocs/2/53542',
        'text' => 'Under the Local Government Code, an ordinance shall not become effective unless it is approved by the Mayor or, if vetoed, the veto is overridden, and it is posted for at least three weeks in at least three conspicuous places within the local government unit and published once in a newspaper of general circulation, where available. Failure to comply with publication or posting requirements prevents the ordinance from becoming enforceable.',
    ],
    'VAL-NATIONAL-LAWS' => [
        'url' => 'https://elibrary.judiciary.gov.ph//thebookshelf//showdocs/2/53542',
        'text' => 'No local ordinance may contravene the Constitution, any statute, executive order, proclamation, or other regulation of the national government. Section 56 of the Local Government Code specifically provides for review of ordinances to ensure they are within the powers granted and do not conflict with superior authorities.',
    ],
    'VAL-IMPLEMENTATION' => [
        'url' => 'https://elibrary.judiciary.gov.ph//thebookshelf//showdocs/2/53542',
        'text' => 'A valid ordinance must identify the office responsible for implementation and, where applicable, the source of funding. The Local Government Code provides that appropriations are made by ordinance, and the local budget must comply with accounting and auditing rules. Implementation and monitoring mechanisms should be clearly stated.',
    ],
    'VAL-VOTING' => [
        'url' => 'https://elibrary.judiciary.gov.ph//thebookshelf//showdocs/2/53542',
        'text' => 'Section 54 of the Local Government Code requires that ordinances be passed only when a quorum is present and by a majority vote of the members present. The ayes and nays must be recorded in the minutes. No ordinance shall be considered on final reading unless it has passed the required readings.',
    ],
    'VAL-MAYOR-REVIEW' => [
        'url' => 'https://elibrary.judiciary.gov.ph//thebookshelf//showdocs/2/53542',
        'text' => 'The Mayor has the duty to review and either approve or veto every ordinance enacted by the Sangguniang Panlungsod. The Mayor must act on the ordinance within a specified period. A veto may be overridden by a two-thirds vote of all members of the Sangguniang Panlungsod.',
    ],
];

$stmt = $db->prepare("UPDATE compliance_rules SET reference_url = :url, reference_text = :text WHERE code = :code");

foreach ($references as $code => $ref) {
    $stmt->execute([':url' => $ref['url'], ':text' => $ref['text'], ':code' => $code]);
    echo "<p>Updated references for: " . htmlspecialchars($code) . "</p>\n";
}

echo "<p><strong>Migration complete.</strong></p>\n";
