<?php
require_once '/home/llrm.spvalenzuela.com/public_html/modules/core/config/config.php';
require_once '/home/llrm.spvalenzuela.com/public_html/modules/core/config/database.php';
require_once '/home/llrm.spvalenzuela.com/public_html/modules/search/services/EmbeddingService.php';

$db = getDatabase();
$service = new EmbeddingService();

$docIds = $db->query('SELECT id FROM legislative_documents ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);

$total = count($docIds);
$done = 0;
$failed = 0;

foreach ($docIds as $id) {
    $ok = $service->embedDocument($db, $id);
    if ($ok) {
        $done++;
        echo "OK $id ($done/$total)\n";
    } else {
        $failed++;
        echo "FAIL $id: " . $service->getLastError() . " ($done ok, $failed fail)\n";
    }
    flush();
    ob_flush();
}

echo "Done: $done success, $failed failed, $total total\n";
