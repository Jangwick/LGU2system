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

$service = new FileStorageService();
$reflection = new ReflectionClass($service);
$prop = $reflection->getProperty('storageBasePath');
$prop->setAccessible(true);
$storageBase = $prop->getValue($service);

$testFile = tempnam(sys_get_temp_dir(), 'llrm_test_');
// Write valid PNG magic bytes so finfo detects image/png
file_put_contents($testFile, pack('H*', '89504E470D0A1A0A0000000D49484452000000010000000108060000001F15C4890000000D4944415408D763F8FFFFFF3F0005FE02FAD5AB48D600000049454E44AE426082'));

// Test 1: reject oversized file
$prop = $reflection->getProperty('maxFileSize');
$prop->setAccessible(true);
$maxSize = $prop->getValue($service);

try {
    $service->uploadFile([
        'tmp_name' => $testFile,
        'name' => 'large.png',
        'size' => $maxSize + 1,
        'error' => UPLOAD_ERR_OK
    ], 'testtype');
    assert_test('Oversized file rejected', false);
} catch (Exception $e) {
    assert_test('Oversized file rejected', strpos($e->getMessage(), 'maximum size') !== false);
}

// Test 2: reject bad extension
try {
    $service->uploadFile([
        'tmp_name' => $testFile,
        'name' => 'malicious.exe',
        'size' => 100,
        'error' => UPLOAD_ERR_OK
    ], 'testtype');
    assert_test('Bad extension rejected', false);
} catch (Exception $e) {
    assert_test('Bad extension rejected', strpos($e->getMessage(), 'File type not allowed') !== false);
}

// Test 3: path traversal document type sanitized
$result = $service->uploadFile([
    'tmp_name' => $testFile,
    'name' => 'valid.png',
    'size' => 100,
    'error' => UPLOAD_ERR_OK
], '../../../etc/passwd');

assert_test('Path traversal in document type sanitized', strpos($result['path'], '/documents/etcpasswd/') !== false && strpos($result['path'], '..') === false && strpos($result['path'], 'etc/passwd') === false);

// Test 4: deleteFile path traversal rejected
assert_test('deleteFile rejects path traversal', $service->deleteFile('/etc/passwd') === false);

// Test 5: getFile path traversal rejected
try {
    $service->getFile('/etc/passwd');
    assert_test('getFile rejects path traversal', false);
} catch (Exception $e) {
    assert_test('getFile rejects path traversal', strpos($e->getMessage(), 'File not found') !== false);
}

if (file_exists($testFile)) {
    unlink($testFile);
}

// Cleanup uploaded test file
if (isset($result['path']) && file_exists($result['path'])) {
    unlink($result['path']);
}

echo "\nFile Upload CLI Tests: $passed passed, $failed failed\n";
exit($failed > 0 ? 1 : 0);
