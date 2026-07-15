<?php
/**
 * Performance smoke test
 * Checks that key endpoints respond quickly (< 2 seconds)
 */

require_once __DIR__ . '/../bootstrap.php';

$baseUrl = 'http://localhost:8000';
$endpoints = [
    '/' => 'Homepage',
    '/modules/public-portal/views/search.php' => 'Public Search',
    '/modules/authentication/views/login.php' => 'Login Page',
    '/modules/authentication/views/register.php' => 'Register Page'
];

$pass = 0;
$fail = 0;
$maxTimeMs = 2000;

foreach ($endpoints as $path => $name) {
    $start = microtime(true);
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    $response = @file_get_contents($baseUrl . $path, false, $context);
    $duration = (microtime(true) - $start) * 1000;

    $status = $http_response_header[0] ?? 'HTTP/1.0 0 Unknown';
    $code = (int) explode(' ', $status)[1];

    $ok = $code === 200 && $duration < $maxTimeMs;

    if ($ok) {
        echo "[PASS] $name: $status, " . round($duration, 2) . " ms\n";
        $pass++;
    } else {
        echo "[FAIL] $name: $status, " . round($duration, 2) . " ms\n";
        $fail++;
    }
}

echo "\nPerformance Smoke Tests: $pass passed, $fail failed\n";
exit($fail > 0 ? 1 : 0);
