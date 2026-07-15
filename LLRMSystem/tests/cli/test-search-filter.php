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

// Simulate SearchController type filter logic
function sanitizeTypeFilter($typeFilter) {
    if (is_array($typeFilter)) {
        return Sanitizer::array($typeFilter, 'plainText');
    }
    return Sanitizer::plainText($typeFilter);
}

// Test 1: checkbox array input gets sanitized as array
$_GET['type'] = ['ordinance', 'resolution', '<script>alert(1)</script>'];
$result = sanitizeTypeFilter($_GET['type']);
assert_test('Type checkbox array sanitized as array', is_array($result) && $result[0] === 'ordinance' && $result[1] === 'resolution' && $result[2] === 'alert(1)');

// Test 2: single type string sanitized
$_GET['type'] = '<script>ordinance</script>';
$result = sanitizeTypeFilter($_GET['type']);
assert_test('Single type string sanitized as string', is_string($result) && $result === 'ordinance');

// Test 3: empty type returns empty string
$_GET['type'] = '';
$result = sanitizeTypeFilter($_GET['type']);
assert_test('Empty type returns empty string', $result === '');

// Test 4: SearchService applyTypeFilter handles array correctly (mock)
$types = ['ordinance', 'resolution'];
$placeholders = [];
$params = [];
if (is_array($types)) {
    $filtered = array_values(array_filter($types, 'strlen'));
    foreach ($filtered as $index => $type) {
        $placeholder = ':type' . $index;
        $placeholders[] = $placeholder;
        $params[$placeholder] = $type;
    }
}
assert_test('SearchService builds IN clause for array types', count($placeholders) === 2 && $params[':type0'] === 'ordinance');

echo "\nSearch Filter CLI Tests: $passed passed, $failed failed\n";
exit($failed > 0 ? 1 : 0);
