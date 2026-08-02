<?php
/**
 * Integration Settings API
 *
 * GET  - list all integration_settings (admin only)
 * POST - upsert settings for a source_system (admin only)
 */

session_start();

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../services/IntegrationWebhookService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
if (!in_array($userRole, ['admin', 'super_admin', 'superadmin', 'administrator'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Admin access required']);
    exit;
}

$service = new IntegrationWebhookService();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = getDatabase()->query("SELECT * FROM integration_settings ORDER BY source_system ASC");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'settings' => $settings]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sourceSystem = Sanitizer::plainText($data['source_system'] ?? '');

    if (empty($sourceSystem)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'source_system is required']);
        exit;
    }

    $result = $service->saveSettings($sourceSystem, [
        'webhook_url' => $data['webhook_url'] ?? null,
        'api_key' => $data['api_key'] ?? null,
        'enabled' => !empty($data['enabled'])
    ]);

    echo json_encode($result);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
