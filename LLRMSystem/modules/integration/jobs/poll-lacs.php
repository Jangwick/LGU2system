<?php
/**
 * Poll LACS for new agenda files and stage them as pending integrated records in LRMS.
 * Run via cron: php modules/integration/jobs/poll-lacs.php
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

$config = require __DIR__ . '/../config/lacs.php';

if (empty($config['bearer_token']) || empty($config['base_url']) || empty($config['list_endpoint'])) {
    exit("LACS config is incomplete. Edit modules/integration/config/lacs.php\n");
}

if (!extension_loaded('curl')) {
    exit("The cURL extension is required.\n");
}

$db = getDatabase();

// Retrieve the LRMS API key for the lacs module
$stmt = $db->prepare("SELECT api_key FROM integration_api_keys WHERE module_name = :module AND is_active = 1 LIMIT 1");
$stmt->execute([':module' => $config['lrms_module_name'] ?? 'lacs']);
$lrmsApiKey = $stmt->fetchColumn();

if (!$lrmsApiKey) {
    exit("No active LRMS API key for module '{$config['lrms_module_name']}'. Run: php modules/integration/jobs/setup-lacs.php\n");
}

// Determine the last successful poll time
$sinceStmt = $db->prepare("SELECT COALESCE(MAX(received_at), '1970-01-01 00:00:00') FROM integrated_records WHERE source_system = :source");
$sinceStmt->execute([':source' => $config['source_system']]);
$since = $sinceStmt->fetchColumn();

$listUrl = rtrim($config['base_url'], '/') . '/' . ltrim($config['list_endpoint'], '/');
$queryParam = $config['query_param'] ?? 'since';
$listUrl .= '?' . $queryParam . '=' . urlencode($since);

echo "Polling LACS: {$listUrl}\n";

$ch = curl_init($listUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $config['bearer_token'],
        'Accept: application/json',
    ],
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT => 60,
]);
$listResponse = curl_exec($ch);
$listError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($listError) {
    exit("LACS list request failed: {$listError}\n");
}

if ($httpCode !== 200) {
    exit("LACS returned HTTP {$httpCode}: {$listResponse}\n");
}

$items = json_decode($listResponse, true);

if (!is_array($items)) {
    exit("Invalid JSON response from LACS.\n");
}

// Allow top-level array or {"data": [...]} wrapper
$records = isset($items['data']) && is_array($items['data']) ? $items['data'] : $items;
$processed = 0;
$skipped = 0;

foreach ($records as $item) {
    if (!is_array($item)) {
        continue;
    }

    $externalId = $item['id'] ?? $item['external_id'] ?? null;
    $title = $item['title'] ?? $item['name'] ?? null;
    $fileUrl = $item['file_url'] ?? $item['download_url'] ?? $item['url'] ?? null;

    if (empty($externalId) || empty($title) || empty($fileUrl)) {
        echo "Skipping item without id/title/file_url\n";
        $skipped++;
        continue;
    }

    // Make file URL absolute
    if (strpos($fileUrl, 'http') !== 0) {
        $fileUrl = rtrim($config['base_url'], '/') . '/' . ltrim($fileUrl, '/');
    }

    $extension = pathinfo(parse_url($fileUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'bin';
    $tmpPath = sys_get_temp_dir() . '/lacs_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$externalId) . '_' . uniqid() . '.' . $extension;

    // Download file from LACS
    $fh = curl_init($fileUrl);
    curl_setopt_array($fh, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $config['bearer_token'],
        ],
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 120,
    ]);
    $fileContents = curl_exec($fh);
    $fileError = curl_error($fh);
    curl_close($fh);

    if ($fileError || empty($fileContents)) {
        echo "Failed to download file for {$externalId}: {$fileError}\n";
        $skipped++;
        continue;
    }

    if (!file_put_contents($tmpPath, $fileContents)) {
        echo "Failed to save temp file for {$externalId}\n";
        $skipped++;
        continue;
    }

    // Post to LRMS receive_document.php for direct import as pending
    $post = [
        'api_key'       => $lrmsApiKey,
        'title'         => $title,
        'document_type' => $config['document_type'] ?? rtrim($config['module_type'], 's'),
        'source_system' => $config['source_system'],
        'external_id'   => $externalId,
        'document_date' => $item['document_date'] ?? $item['date'] ?? date('Y-m-d'),
        'description'   => $item['summary'] ?? $item['description'] ?? '',
        'tags'          => is_array($item['tags'] ?? null) ? implode(',', $item['tags']) : ($item['tags'] ?? ''),
    ];

    $mimeType = mime_content_type($tmpPath) ?: 'application/octet-stream';
    $post['file'] = new CURLFile($tmpPath, $mimeType, basename($fileUrl));

    $ph = curl_init($config['lrms_receive_url']);
    curl_setopt_array($ph, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $lrmsApiKey,
        ],
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 120,
    ]);
    $receiveResponse = curl_exec($ph);
    $receiveError = curl_error($ph);
    $receiveHttp = curl_getinfo($ph, CURLINFO_HTTP_CODE);
    curl_close($ph);

    @unlink($tmpPath);

    if ($receiveError || $receiveHttp >= 400) {
        echo "Failed to import {$externalId}: HTTP {$receiveHttp} - {$receiveResponse} - {$receiveError}\n";
        $skipped++;
        continue;
    }

    $result = json_decode($receiveResponse, true);
    if (!empty($result['already_exists'])) {
        echo "Already imported: {$externalId} (doc id {$result['document_id']})\n";
    } else {
        echo "Imported: {$externalId} (doc id {$result['document_id']}, ref {$result['reference_number']})\n";
    }
    $processed++;
}

echo "Processed {$processed}, skipped {$skipped}.\n";
