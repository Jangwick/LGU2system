<?php
// One-time LACS API key installer. Remove after running.
$token = 'install_lacs_2026_secure';
if (($_GET['token'] ?? '') !== $token) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../notifications/services/IntegrationAuth.php';

$auth = new IntegrationAuth();
$db = getDatabase();
$existing = $db->query("SELECT api_key FROM integration_api_keys WHERE module_name = 'lacs' AND is_active = 1 LIMIT 1")->fetchColumn();

if ($existing) {
    echo json_encode(['success' => true, 'api_key' => $existing, 'message' => 'Existing key']);
    exit;
}

$key = $auth->generateApiKey('lacs', ['document_receive' => true, 'send_file' => true], 1);
echo json_encode(['success' => true] + $key);
