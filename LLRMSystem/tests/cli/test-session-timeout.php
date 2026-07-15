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

$_SESSION = [];

// Test 1: no user_id returns early
$middleware = new SessionTimeoutMiddleware();
$result = $middleware->checkSessionTimeout();
assert_test('No user_id returns early', $result === null && !isset($_SESSION['last_activity']));

// Test 2: logged in within timeout updates activity
$_SESSION['user_id'] = 1;
$_SESSION['last_activity'] = time();
$middleware->checkSessionTimeout();
assert_test('Within timeout updates last_activity', isset($_SESSION['last_activity']) && isset($_SESSION['session_timeout_remaining']));

// Test 3: getRemainingTime returns correct value
$_SESSION['last_activity'] = time();
$remaining = $middleware->getRemainingTime();
assert_test('getRemainingTime returns positive value', $remaining > 0 && $remaining <= 300);

// Test 4: config is 5 minutes (300 seconds)
assert_test('SESSION_TIMEOUT_MINUTES is 5', defined('SESSION_TIMEOUT_MINUTES') && SESSION_TIMEOUT_MINUTES === 5);

// Test 5: getRemainingTime returns 0 when not logged in
$_SESSION = [];
assert_test('getRemainingTime returns 0 when not logged in', $middleware->getRemainingTime() === 0);

echo "\nSession Timeout CLI Tests: $passed passed, $failed failed\n";
exit($failed > 0 ? 1 : 0);
