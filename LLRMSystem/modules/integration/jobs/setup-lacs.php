<?php
/**
 * CLI helper to generate the LACS integration API key in LRMS.
 * Run: php modules/integration/jobs/setup-lacs.php
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../notifications/services/IntegrationAuth.php';

$auth = new IntegrationAuth();
$db = getDatabase();

// Check if a key already exists for lacs
$stmt = $db->prepare("SELECT api_key FROM integration_api_keys WHERE module_name = :module AND is_active = 1");
$stmt->execute([':module' => 'lacs']);
$existing = $stmt->fetchColumn();

if ($existing) {
    echo "LACS integration key already exists:\n";
    echo "api_key: {$existing}\n";
    echo "permissions: document_receive, send_file\n";
    exit(0);
}

$key = $auth->generateApiKey('lacs', ['document_receive' => true, 'send_file' => true], 1);

if ($key) {
    echo "LACS integration key created. Save this secret; it is shown only once.\n";
    echo json_encode($key, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "Failed to create LACS integration key.\n";
    exit(1);
}
