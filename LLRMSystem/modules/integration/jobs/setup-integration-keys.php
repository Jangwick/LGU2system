<?php
/**
 * CLI helper to generate integration API keys for ORTS, CMS, PHMS, and PCMS.
 * Run: php modules/integration/jobs/setup-integration-keys.php
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../notifications/services/IntegrationAuth.php';

$modules = ['orts', 'cms', 'phms', 'pcms'];
$auth = new IntegrationAuth();
$db = getDatabase();

$permissions = ['document_receive' => true, 'send_file' => true];

foreach ($modules as $module) {
    $stmt = $db->prepare("SELECT api_key FROM integration_api_keys WHERE module_name = :module AND is_active = 1");
    $stmt->execute([':module' => $module]);
    $existing = $stmt->fetchColumn();

    if ($existing) {
        echo ucfirst($module) . " integration key already exists:\n";
        echo "  api_key: {$existing}\n\n";
        continue;
    }

    $key = $auth->generateApiKey($module, $permissions, 1);

    if ($key) {
        echo ucfirst($module) . " integration key created. Save this secret; it is shown only once.\n";
        echo "  api_key: {$key['api_key']}\n";
        echo "  api_secret: {$key['api_secret']}\n\n";
    } else {
        echo "Failed to create " . ucfirst($module) . " integration key.\n\n";
    }
}
