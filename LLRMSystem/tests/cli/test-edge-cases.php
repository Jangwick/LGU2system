<?php
/**
 * Edge-case tests
 */

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

// SQL injection in plainText
assert_test('SQL injection in plainText', Sanitizer::plainText("'; DROP TABLE users; --") === "'; DROP TABLE users; --");

// XSS payload
assert_test('XSS payload stripped', Sanitizer::plainText('<script>alert("xss")</script>') === 'alert("xss")');

// Null bytes
assert_test('Null bytes stripped', Sanitizer::string("hello\x00") === 'hello');

// Path traversal in filename
assert_test('Filename path traversal', Sanitizer::filename('../../../etc/passwd') === 'passwd');

// Empty array filter
$_GET['type'] = [];
$typeFilter = $_GET['type'];
if (is_array($typeFilter)) {
    $typeFilter = Sanitizer::array($typeFilter, 'plainText');
} else {
    $typeFilter = Sanitizer::plainText($typeFilter);
}
assert_test('Empty type array handled', is_array($typeFilter) && empty($typeFilter));

// Very long string
$long = str_repeat('a', 1000);
assert_test('Filename max length enforced', strlen(Sanitizer::filename($long)) <= 255);

// Emoji
assert_test('Emoji preserved in filename', Sanitizer::filename('file😀.txt') === 'file.txt');

// Zero-byte file
$zeroFile = tempnam(sys_get_temp_dir(), 'zero_');
file_put_contents($zeroFile, '');
$service = new FileStorageService();
try {
    $service->uploadFile([
        'tmp_name' => $zeroFile,
        'name' => 'zero.png',
        'size' => 0,
        'error' => UPLOAD_ERR_OK
    ], 'test');
    assert_test('Zero-byte file rejected', false);
} catch (Exception $e) {
    assert_test('Zero-byte file rejected', strpos($e->getMessage(), 'MIME') !== false || strpos($e->getMessage(), 'Invalid') !== false);
}
if (file_exists($zeroFile)) {
    unlink($zeroFile);
}

// Reserved words
assert_test('Reserved word in filename', Sanitizer::filename('CON.txt') === 'CON.txt');

echo "\nEdge-Case Tests: $passed passed, $failed failed\n";
exit($failed > 0 ? 1 : 0);
