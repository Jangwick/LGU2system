<?php
/**
 * Outbound Webhook Worker
 *
 * Processes pending integration_outbound_events.
 * Run via cron or CLI: php process-outbound-webhooks.php
 */
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../services/IntegrationWebhookService.php';

$service = new IntegrationWebhookService();
$result = $service->processQueue(50);

header('Content-Type: application/json');
echo json_encode($result);
