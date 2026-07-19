<?php
require_once __DIR__ . '/modules/document-management/services/OcrService.php';
$service = new OcrService();
$home = getenv('HOME');
$posix = null;
if (function_exists('posix_getpwuid') && function_exists('posix_getuid')) {
    $posix = posix_getpwuid(posix_getuid());
}
echo json_encode([
    'tesseract_available' => $service->isTesseractAvailable(),
    'home_env' => $home,
    'posix_dir' => $posix['dir'] ?? null,
    'wrapper_exists' => file_exists(($home ?: ($posix['dir'] ?? '/tmp')) . '/bin/tesseract'),
], JSON_PRETTY_PRINT);
