<?php
/**
 * Temporary diagnostic for Groq integration
 */
require_once __DIR__ . '/modules/core/config/config.php';
require_once __DIR__ . '/modules/ai/services/GroqService.php';

header('Content-Type: application/json');

$g = new GroqService();
$rules = [
    [
        'code' => 'RA-7160-SEC16',
        'title' => 'General Welfare',
        'summary' => 'RA 7160, Section 16 - General Welfare Clause',
        'example_excerpt' => 'The ordinance must promote the general welfare of the residents.',
        'reference_url' => 'https://elibrary.judiciary.gov.ph//thebookshelf//showdocs/2/53542',
        'reference_text' => 'Section 16 General Welfare Clause of the Local Government Code.',
        'vector_score' => 60
    ]
];
$doc = "An ordinance establishing a public market in Valenzuela City to promote local trade and welfare.";

$result = $g->analyzeCompliance($doc, $rules);

$output = [
    'result' => $result,
    'error' => $g->getLastError(),
    'http' => $g->getLastHttpCode(),
    'key_loaded' => defined('GROQ_API_KEY') && GROQ_API_KEY !== '',
    'key_prefix' => defined('GROQ_API_KEY') ? substr(GROQ_API_KEY, 0, 10) : null,
    'model' => defined('GROQ_MODEL') ? GROQ_MODEL : null
];

echo json_encode($output);
