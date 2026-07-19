<?php
/**
 * Run all CLI tests and report a summary
 */

require_once __DIR__ . '/../bootstrap.php';

$testFiles = [
    'test-sanitizer.php',
    'test-search-filter.php',
    'test-session-timeout.php',
    'test-file-upload.php',
    'test-ping.php',
    'test-smoke.php',
    'test-edge-cases.php',
    'test-accessibility.php',
    'test-compliance.php'
];

$totalPassed = 0;
$totalFailed = 0;
$exitCode = 0;

$cliDir = __DIR__;

foreach ($testFiles as $file) {
    $path = $cliDir . '/' . $file;
    if (!file_exists($path)) {
        echo "[SKIP] $file not found\n";
        continue;
    }

    echo "\n=== Running $file ===\n";
    $output = [];
    $return = 0;
    exec('php "' . $path . '"', $output, $return);
    echo implode("\n", $output) . "\n";

    if ($return !== 0) {
        $exitCode = 1;
    }

    // Parse passed/failed count from output
    foreach ($output as $line) {
        if (preg_match('/(\d+) passed, (\d+) failed/', $line, $matches)) {
            $totalPassed += (int) $matches[1];
            $totalFailed += (int) $matches[2];
        }
    }
}

echo "\n========================================";
echo "\nTOTAL CLI TESTS: $totalPassed passed, $totalFailed failed";
echo "\n========================================\n";

exit($exitCode);
