<?php
// Temporary one-time installer for ORTS/CMS/PHMS/PCMS API keys. Remove after running.
$token = 'install_integration_2026_secure';
if (($_GET['token'] ?? '') !== $token) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../notifications/services/IntegrationAuth.php';

$auth = new IntegrationAuth();
$db = getDatabase();

$modules = ['orts', 'cms', 'phms', 'pcms'];
$permissions = ['document_receive' => true, 'send_file' => true];
$keys = [];

foreach ($modules as $module) {
    $stmt = $db->prepare("SELECT api_key FROM integration_api_keys WHERE module_name = :module AND is_active = 1");
    $stmt->execute([':module' => $module]);
    $existing = $stmt->fetchColumn();

    if ($existing) {
        $keys[$module] = ['api_key' => $existing, 'status' => 'existing'];
    } else {
        $key = $auth->generateApiKey($module, $permissions, 1);
        $keys[$module] = $key
            ? ['api_key' => $key['api_key'], 'api_secret' => $key['api_secret'], 'status' => 'created']
            : ['error' => 'failed'];
    }
}

echo json_encode(['success' => true, 'keys' => $keys], JSON_PRETTY_PRINT);
