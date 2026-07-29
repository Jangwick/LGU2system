<?php
require_once __DIR__ . '/modules/core/config/config.php';
require_once __DIR__ . '/modules/document-management/services/OcrService.php';

$ocr = new OcrService();
echo 'Tesseract available: ' . ($ocr->isTesseractAvailable() ? 'YES' : 'NO') . PHP_EOL;
echo 'Ghostscript available: ' . ($ocr->isGhostscriptAvailable() ? 'YES' : 'NO') . PHP_EOL;

$testFile = $argv[1] ?? '/home/llrm.spvalenzuela.com/public_html/storage/documents/INT_20260729_175429_8705b1e3.pdf';
if (!file_exists($testFile)) {
    echo 'Test file not found: ' . $testFile . PHP_EOL;
    exit(1);
}

echo 'Testing OCR on: ' . $testFile . PHP_EOL;
$result = $ocr->extractText($testFile);
echo 'Status: ' . $result['status'] . PHP_EOL;
echo 'Error: ' . ($result['error'] ?? 'none') . PHP_EOL;
echo 'Method: ' . ($result['method'] ?? 'unknown') . PHP_EOL;
echo 'Text length: ' . strlen($result['text'] ?? '') . PHP_EOL;
echo 'Preview: ' . substr($result['text'] ?? '', 0, 500) . PHP_EOL;
