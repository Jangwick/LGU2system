<?php

require_once __DIR__ . '/../bootstrap.php';

$tests = [
    ['Sanitizer::string strips control chars', Sanitizer::string("hello\x00\x01") === 'hello'],
    ['Sanitizer::plainText strips HTML', Sanitizer::plainText('<script>alert(1)</script>') === 'alert(1)'],
    ['Sanitizer::int parses valid integer', Sanitizer::int('42', 0) === 42],
    ['Sanitizer::int returns default for invalid', Sanitizer::int('abc', 0) === 0],
    ['Sanitizer::enum whitelists', Sanitizer::enum('admin', ['admin', 'user'], 'viewer') === 'admin'],
    ['Sanitizer::enum rejects invalid', Sanitizer::enum('hacker', ['admin', 'user'], 'viewer') === 'viewer'],
    ['Sanitizer::filename prevents path traversal', Sanitizer::filename('../../../etc/passwd') === 'passwd'],
    ['Sanitizer::date normalizes', Sanitizer::date('July 15, 2026', '') === '2026-07-15'],
    ['Sanitizer::array recursively sanitizes', Sanitizer::array(['<script>test</script>'], 'plainText')[0] === 'test'],
    ['Sanitizer::forHtml escapes', Sanitizer::forHtml('<script>') === '&lt;script&gt;']
];

$pass = 0;
$fail = 0;

foreach ($tests as [$name, $result]) {
    if ($result) {
        echo "[PASS] $name\n";
        $pass++;
    } else {
        echo "[FAIL] $name\n";
        $fail++;
    }
}

echo "\nSanitizer CLI Tests: $pass passed, $fail failed\n";
exit($fail > 0 ? 1 : 0);
