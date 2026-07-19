<?php
/**
 * Temporary opcache reset for deployment
 */
$files = [
    __DIR__ . '/modules/core/config/config.php',
    __DIR__ . '/modules/core/config/config.local.php',
    __DIR__ . '/modules/ai/services/GroqService.php',
    __DIR__ . '/modules/document-management/services/ComplianceService.php',
    __DIR__ . '/modules/document-management/models/DocumentComplianceResult.php',
    __DIR__ . '/remote-test-groq.php',
];

$invalidated = [];
if (function_exists('opcache_invalidate')) {
    foreach ($files as $f) {
        if (file_exists($f)) {
            $invalidated[$f] = opcache_invalidate($f, true) ? 'ok' : 'skipped';
        }
    }
}

$reset = false;
if (function_exists('opcache_reset')) {
    $reset = opcache_reset();
}

echo json_encode(['opcache_reset' => $reset ? 'cleared' : 'failed_or_not_installed', 'invalidated' => $invalidated]);
