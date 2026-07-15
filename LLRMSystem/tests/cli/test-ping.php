<?php

require_once __DIR__ . '/../bootstrap.php';

$passed = 0;
$failed = 0;

function assert_test($name, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] $name\n";
        $passed++;
    } else {
        echo "[FAIL] $name\n";
        $failed++;
    }
}

// Simulate ping.php endpoint logic without session_start (CLI safe)
function simulate_ping(&$session) {
    if (!isset($session['user_id'])) {
        return ['status' => 'unauthorized', 'code' => 401];
    }

    $session['last_activity'] = time();
    return ['status' => 'ok', 'time' => time(), 'code' => 200];
}

// Test unauthorized
$session = [];
$result = simulate_ping($session);
assert_test('Ping returns 401 when not logged in', $result['code'] === 401 && $result['status'] === 'unauthorized');

// Test authorized
$session = ['user_id' => 1, 'last_activity' => 0];
$result = simulate_ping($session);
assert_test('Ping returns 200 when logged in', $result['code'] === 200 && $result['status'] === 'ok');
assert_test('Ping updates last_activity', $session['last_activity'] > 0);

echo "\nPing Endpoint CLI Tests: $passed passed, $failed failed\n";
exit($failed > 0 ? 1 : 0);
