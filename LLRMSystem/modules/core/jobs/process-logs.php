<?php

/**
 * Log Queue Worker
 *
 * Drain queued log payloads and insert them into the database in batches.
 * Designed to run from cron every minute.
 *
 * Usage:
 *   php modules/core/jobs/process-logs.php
 */

require __DIR__ . '/../../core/config/config.php';

function drainAndInsert(string $queue, callable $inserter): int {
    $drained = 0;
    while (true) {
        $items = LogQueue::drain($queue, 100);
        if (empty($items)) {
            break;
        }
        $inserter($items);
        $drained += count($items);
    }
    return $drained;
}

function insertActivityLogs(array $items): void {
    $db = getDatabase();
    $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, description, ip_address, user_agent, created_at) VALUES (:user_id, :action, :table_name, :record_id, :description, :ip_address, :user_agent, :created_at)");

    foreach ($items as $item) {
        try {
            $stmt->execute([
                ':user_id' => $item['user_id'] ?? null,
                ':action' => $item['action'] ?? '',
                ':table_name' => $item['table_name'] ?? 'system',
                ':record_id' => $item['record_id'] ?? null,
                ':description' => $item['description'] ?? '',
                ':ip_address' => $item['ip_address'] ?? '',
                ':user_agent' => $item['user_agent'] ?? '',
                ':created_at' => $item['created_at'] ?? date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            error_log('process-logs: activity_logs insert failed: ' . $e->getMessage());
        }
    }
}

function insertDocumentAccessLogs(array $items): void {
    $db = getDatabase();
    $stmt = $db->prepare("INSERT INTO document_access_logs (document_id, user_id, access_type, accessed_at) VALUES (:document_id, :user_id, :access_type, :accessed_at)");

    foreach ($items as $item) {
        try {
            $stmt->execute([
                ':document_id' => $item['document_id'] ?? null,
                ':user_id' => $item['user_id'] ?? null,
                ':access_type' => $item['access_type'] ?? 'view',
                ':accessed_at' => $item['accessed_at'] ?? date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            error_log('process-logs: document_access_logs insert failed: ' . $e->getMessage());
        }
    }
}

$activityCount = drainAndInsert('activity_logs', 'insertActivityLogs');
$accessCount = drainAndInsert('document_access_logs', 'insertDocumentAccessLogs');

if (php_sapi_name() === 'cli') {
    echo "Drained {$activityCount} activity log(s), {$accessCount} access log(s).\n";
}
