<?php
/**
 * Smoke tests
 * Verify major services and pages are reachable
 */

require_once __DIR__ . '/../bootstrap.php';

$baseUrl = 'http://localhost:8000';
$checks = [
    'PHP server reachable' => function() use ($baseUrl) {
        return @file_get_contents($baseUrl) !== false;
    },
    'Login page HTTP 200' => function() use ($baseUrl) {
        $headers = @get_headers($baseUrl . '/modules/authentication/views/login.php');
        return $headers && strpos($headers[0], '200') !== false;
    },
    'Public search page HTTP 200' => function() use ($baseUrl) {
        $headers = @get_headers($baseUrl . '/modules/public-portal/views/search.php');
        return $headers && strpos($headers[0], '200') !== false;
    },
    'MySQL database reachable' => function() {
        try {
            $db = getDatabase();
            $db->query('SELECT 1');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
];

$pass = 0;
$fail = 0;

foreach ($checks as $name => $check) {
    if ($check()) {
        echo "[PASS] $name\n";
        $pass++;
    } else {
        echo "[FAIL] $name\n";
        $fail++;
    }
}

echo "\nSmoke Tests: $pass passed, $fail failed\n";
exit($fail > 0 ? 1 : 0);
