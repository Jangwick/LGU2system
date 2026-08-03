<?php
/**
 * IntegrationWebhookService
 *
 * Queues and sends outbound webhooks to external systems (ORTS, CMS, PHMS, PCMS)
 * when documents they originated are approved, rejected, or revised.
 */
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../../document-management/models/Document.php';
require_once __DIR__ . '/WebhookClient.php';
require_once __DIR__ . '/CurlWebhookClient.php';

class IntegrationWebhookService
{
    private $db;
    private $client;
    private $maxAttempts;

    public function __construct($db = null, ?WebhookClient $client = null, $maxAttempts = 3)
    {
        $this->db = $db ?: getDatabase();
        $this->client = $client ?: new CurlWebhookClient();
        $this->maxAttempts = $maxAttempts;
    }

    /**
     * Queue an outbound webhook event for a document.
     */
    public function queueEvent($documentId, $event)
    {
        $document = $this->getDocument($documentId);
        if (!$document) {
            return ['success' => false, 'error' => 'Document not found'];
        }

        $sourceSystem = $document['source_module'] ?? '';
        if (!in_array($sourceSystem, ['orts', 'cms', 'phms', 'pcms'], true)) {
            return ['success' => false, 'error' => 'No webhook integration for source: ' . $sourceSystem];
        }

        $integration = $this->getIntegration($document['source_id']);
        if (!$integration || empty($integration['source_system'])) {
            return ['success' => false, 'error' => 'Integration record not found'];
        }

        $settings = $this->getSettings($sourceSystem);
        if (!$settings || empty($settings['enabled'])) {
            return ['success' => false, 'error' => 'Webhook not enabled for source: ' . $sourceSystem];
        }

        if (empty($settings['webhook_url'])) {
            return ['success' => false, 'error' => 'Webhook URL not configured for source: ' . $sourceSystem];
        }

        $payload = $this->buildPayload($document, $event, $integration, $settings);

        $stmt = $this->db->prepare("
            INSERT INTO integration_outbound_events
                (document_id, source_system, event, payload, status, attempts, created_at)
            VALUES
                (:document_id, :source_system, :event, :payload, 'pending', 0, NOW())
        ");

        $stmt->execute([
            ':document_id' => $documentId,
            ':source_system' => $sourceSystem,
            ':event' => $event,
            ':payload' => $payload
        ]);

        return [
            'success' => true,
            'event_id' => $this->db->lastInsertId(),
            'payload' => $payload
        ];
    }

    /**
     * Queue a tracking event to all connected external systems.
     */
    public function queueTrackingEvent($documentId, $data)
    {
        $document = $this->getDocument($documentId);
        if (!$document) {
            return ['success' => false, 'error' => 'Document not found'];
        }

        $settings = $this->getAllEnabledSettings();
        if (empty($settings)) {
            return ['success' => false, 'error' => 'No enabled webhook destinations'];
        }

        $queued = 0;
        $payloadBase = [
            'event' => 'document_tracking_update',
            'reference_number' => $document['reference_number'] ?? null,
            'tracking_id' => $document['reference_number'] ?? null,
            'source_system' => $data['source_system'] ?? null,
            'activity' => $data['activity'] ?? null,
            'status' => $data['status'] ?? null,
            'performed_by' => $data['performed_by'] ?? null,
            'department' => $data['department'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'timestamp' => $data['timestamp'] ?? date('c'),
            'metadata' => $data['metadata'] ?? null,
        ];

        foreach ($settings as $setting) {
            if (empty($setting['webhook_url'])) {
                continue;
            }

            $json = json_encode($payloadBase);
            $secret = $setting['api_key'] ?? '';
            $signature = hash_hmac('sha256', $json, $secret);

            $payload = $payloadBase;
            $payload['signature'] = $signature;

            $stmt = $this->db->prepare("
                INSERT INTO integration_outbound_events
                    (document_id, source_system, event, payload, status, attempts, created_at)
                VALUES
                    (:document_id, :source_system, :event, :payload, 'pending', 0, NOW())
            ");

            $stmt->execute([
                ':document_id' => $documentId,
                ':source_system' => $setting['source_system'],
                ':event' => 'document_tracking_update',
                ':payload' => json_encode($payload)
            ]);
            $queued++;
        }

        return ['success' => true, 'queued' => $queued];
    }

    /**
     * Build the JSON payload and compute a signature.
     */
    public function buildPayload($document, $event, $integration, $settings)
    {
        $data = [
            'event' => $event,
            'reference_number' => $document['reference_number'] ?? null,
            'external_id' => $integration['external_id'] ?? null,
            'source_system' => $integration['source_system'] ?? $document['source_module'],
            'status' => $document['status'] ?? 'pending',
            'timestamp' => date('c'),
        ];

        $json = json_encode($data);
        $secret = $settings['api_key'] ?? '';
        $signature = hash_hmac('sha256', $json, $secret);

        $data['signature'] = $signature;

        return json_encode($data);
    }

    /**
     * Get all enabled integration settings with webhook URLs.
     */
    public function getAllEnabledSettings()
    {
        $stmt = $this->db->prepare("
            SELECT * FROM integration_settings
            WHERE enabled = 1 AND webhook_url IS NOT NULL AND webhook_url != ''
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Process pending outbound events.
     */
    public function processQueue($limit = 50)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM integration_outbound_events
            WHERE status = 'pending' AND attempts < :max_attempts
            ORDER BY created_at ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':max_attempts', $this->maxAttempts, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $processed = 0;
        $sent = 0;
        $failed = 0;

        foreach ($events as $event) {
            $settings = $this->getSettings($event['source_system']);
            if (!$settings || empty($settings['enabled']) || empty($settings['webhook_url'])) {
                $this->markFailed($event['id'], 'Webhook disabled or URL missing');
                $failed++;
                $processed++;
                continue;
            }

            $headers = [
                'Content-Type' => 'application/json',
                'X-Webhook-Event' => $event['event'],
                'X-Source-System' => $event['source_system']
            ];

            $result = $this->client->send($settings['webhook_url'], $event['payload'], $headers);

            if ($result['success']) {
                $this->markSent($event['id'], $result['response']);
                $sent++;
            } else {
                $this->markFailed($event['id'], ($result['error'] ?: '') . ' | HTTP ' . $result['http_code'] . ': ' . $result['response']);
                $failed++;
            }

            $processed++;
        }

        return [
            'processed' => $processed,
            'sent' => $sent,
            'failed' => $failed
        ];
    }

    /**
     * Get webhook settings for a source system.
     */
    public function getSettings($sourceSystem)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM integration_settings WHERE source_system = :source
        ");
        $stmt->execute([':source' => $sourceSystem]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Save or update webhook settings for a source system.
     */
    public function saveSettings($sourceSystem, $data)
    {
        $existing = $this->getSettings($sourceSystem);

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE integration_settings
                SET webhook_url = :webhook_url,
                    api_key = :api_key,
                    enabled = :enabled,
                    updated_at = NOW()
                WHERE source_system = :source
            ");
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO integration_settings
                    (source_system, webhook_url, api_key, enabled, created_at, updated_at)
                VALUES
                    (:source, :webhook_url, :api_key, :enabled, NOW(), NOW())
            ");
        }

        $stmt->execute([
            ':source' => $sourceSystem,
            ':webhook_url' => $data['webhook_url'] ?? null,
            ':api_key' => $data['api_key'] ?? null,
            ':enabled' => !empty($data['enabled']) ? 1 : 0
        ]);

        return ['success' => true];
    }

    private function getDocument($documentId)
    {
        $document = new Document($this->db);
        return $document->getById($documentId);
    }

    private function getIntegration($integrationId)
    {
        if (empty($integrationId)) {
            return null;
        }
        $stmt = $this->db->prepare("
            SELECT * FROM integrated_records WHERE id = :id
        ");
        $stmt->execute([':id' => $integrationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function markSent($eventId, $response)
    {
        $stmt = $this->db->prepare("
            UPDATE integration_outbound_events
            SET status = 'sent',
                response = :response,
                attempts = attempts + 1,
                last_attempt_at = NOW(),
                sent_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':id' => $eventId, ':response' => $response]);
    }

    private function markFailed($eventId, $response)
    {
        $stmt = $this->db->prepare("
            UPDATE integration_outbound_events
            SET status = CASE WHEN attempts + 1 >= :max THEN 'failed' ELSE 'pending' END,
                response = :response,
                attempts = attempts + 1,
                last_attempt_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':id' => $eventId,
            ':response' => $response,
            ':max' => $this->maxAttempts
        ]);
    }
}
