<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../modules/integration/services/IntegrationWebhookService.php';
require_once __DIR__ . '/../../modules/integration/services/CurlWebhookClient.php';
require_once __DIR__ . '/MockWebhookClient.php';

use PHPUnit\Framework\TestCase;

class IntegrationWebhookServiceTest extends TestCase
{
    private $db;
    private $service;

    protected function setUp(): void
    {
        $this->db = getDatabase();
        $this->service = new IntegrationWebhookService($this->db, new MockWebhookClient());

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS integration_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                source_system VARCHAR(50) NOT NULL UNIQUE,
                webhook_url VARCHAR(500) NULL,
                api_key VARCHAR(255) NULL,
                enabled TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS integration_outbound_events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                document_id INT NOT NULL,
                source_system VARCHAR(50) NOT NULL,
                event VARCHAR(50) NOT NULL,
                payload TEXT NOT NULL,
                status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
                response TEXT NULL,
                attempts INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_attempt_at TIMESTAMP NULL,
                sent_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->db->exec("DELETE FROM integration_outbound_events");
        $this->db->exec("DELETE FROM integration_settings");
    }

    public function testQueueEventCreatesPendingRow()
    {
        // Insert a fake integration record
        $this->db->prepare("
            INSERT INTO integrated_records (module_type, external_id, title, summary, data_payload, source_system, status)
            VALUES ('ordinances', 'EXT-123', 'Test', 'Test', '{}', 'orts', 'synced')
        ")->execute([]);
        $integrationId = $this->db->lastInsertId();

        // Insert a fake document
        $this->db->prepare("
            INSERT INTO legislative_documents
                (reference_number, title, document_type, document_date, status, description, source_module, source_id, uploaded_by, created_at, file_path, file_name, file_size, file_type)
            VALUES
                ('REF-123', 'Test Doc', 'ordinance', '2026-08-03', 'approved', '', 'orts', ?, 1, NOW(), 'x', 'x', 0, 'application/pdf')
        ")->execute([$integrationId]);
        $documentId = $this->db->lastInsertId();

        // Configure webhook
        $this->service->saveSettings('orts', [
            'webhook_url' => 'https://ort.spvalenzuela.com/webhook',
            'api_key' => 'secret123',
            'enabled' => 1
        ]);

        $result = $this->service->queueEvent($documentId, 'approved');

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['payload']);

        $stmt = $this->db->prepare("SELECT * FROM integration_outbound_events WHERE document_id = ?");
        $stmt->execute([$documentId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($event);
        $this->assertEquals('pending', $event['status']);
        $this->assertEquals('approved', $event['event']);
        $this->assertEquals('orts', $event['source_system']);
    }

    public function testQueueEventSkipsDisabledSystem()
    {
        $this->db->prepare("
            INSERT INTO integrated_records (module_type, external_id, title, summary, data_payload, source_system, status)
            VALUES ('ordinances', 'EXT-456', 'Test', 'Test', '{}', 'cms', 'synced')
        ")->execute([]);
        $integrationId = $this->db->lastInsertId();

        $this->db->prepare("
            INSERT INTO legislative_documents
                (reference_number, title, document_type, document_date, status, description, source_module, source_id, uploaded_by, created_at, file_path, file_name, file_size, file_type)
            VALUES
                ('REF-456', 'Test Doc', 'committee_report', '2026-08-03', 'approved', '', 'cms', ?, 1, NOW(), 'x', 'x', 0, 'application/pdf')
        ")->execute([$integrationId]);
        $documentId = $this->db->lastInsertId();

        $this->service->saveSettings('cms', [
            'webhook_url' => 'https://cms.spvalenzuela.com/webhook',
            'api_key' => 'secret',
            'enabled' => 0
        ]);

        $result = $this->service->queueEvent($documentId, 'approved');
        $this->assertFalse($result['success']);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM integration_outbound_events WHERE document_id = ?");
        $stmt->execute([$documentId]);
        $this->assertEquals(0, $stmt->fetchColumn());
    }

    public function testBuildPayloadContainsSignature()
    {
        $document = ['reference_number' => 'REF-789', 'status' => 'approved'];
        $integration = ['external_id' => 'EXT-789', 'source_system' => 'orts'];
        $settings = ['api_key' => 'mysecret'];

        $payload = $this->service->buildPayload($document, 'approved', $integration, $settings);
        $decoded = json_decode($payload, true);

        $this->assertArrayHasKey('signature', $decoded);
        $this->assertEquals('approved', $decoded['event']);
        $this->assertEquals('EXT-789', $decoded['external_id']);
        $this->assertEquals('orts', $decoded['source_system']);

        $expected = json_encode([
            'event' => 'approved',
            'reference_number' => 'REF-789',
            'external_id' => 'EXT-789',
            'source_system' => 'orts',
            'status' => 'approved',
            'timestamp' => $decoded['timestamp']
        ]);
        $expectedSig = hash_hmac('sha256', $expected, 'mysecret');
        $this->assertEquals($expectedSig, $decoded['signature']);
    }

    public function testProcessQueueSendsAndMarksSent()
    {
        $client = new MockWebhookClient();
        $service = new IntegrationWebhookService($this->db, $client);

        $this->db->prepare("
            INSERT INTO integration_settings (source_system, webhook_url, api_key, enabled)
            VALUES ('phms', 'https://phms.spvalenzuela.com/webhook', 'secret', 1)
        ")->execute([]);

        $this->db->prepare("
            INSERT INTO integration_outbound_events
                (document_id, source_system, event, payload, status, attempts, created_at)
            VALUES (1, 'phms', 'approved', '{\"event\":\"approved\"}', 'pending', 0, NOW())
        ")->execute([]);

        $result = $service->processQueue(10);

        $this->assertEquals(1, $result['processed']);
        $this->assertEquals(1, $result['sent']);
        $this->assertEquals(0, $result['failed']);

        $this->assertTrue($client->wasCalled());
        $lastCall = $client->getLastCall();
        $this->assertEquals('https://phms.spvalenzuela.com/webhook', $lastCall['url']);

        $stmt = $this->db->prepare("SELECT * FROM integration_outbound_events LIMIT 1");
        $stmt->execute([]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('sent', $event['status']);
    }

    public function testProcessQueueRetriesFailedEvents()
    {
        $client = new MockWebhookClient();
        $client->pushResponse(['success' => false, 'http_code' => 500, 'response' => 'Error', 'error' => null]);

        $service = new IntegrationWebhookService($this->db, $client);

        $this->db->prepare("
            INSERT INTO integration_settings (source_system, webhook_url, api_key, enabled)
            VALUES ('pcms', 'https://pcms.spvalenzuela.com/webhook', 'secret', 1)
        ")->execute([]);

        $this->db->prepare("
            INSERT INTO integration_outbound_events
                (document_id, source_system, event, payload, status, attempts, created_at)
            VALUES (1, 'pcms', 'approved', '{\"event\":\"approved\"}', 'pending', 0, NOW())
        ")->execute([]);

        $result = $service->processQueue(10);

        $this->assertEquals(1, $result['processed']);
        $this->assertEquals(0, $result['sent']);
        $this->assertEquals(1, $result['failed']);

        $stmt = $this->db->prepare("SELECT * FROM integration_outbound_events LIMIT 1");
        $stmt->execute([]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(1, (int)$event['attempts']);
    }
}
