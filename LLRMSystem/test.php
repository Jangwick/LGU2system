<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
try {
    require_once __DIR__ . '/modules/core/config/config.php';
    require_once __DIR__ . '/modules/core/config/database.php';
    $db = getDatabase();
    echo "DB OK";
} catch (Throwable $t) {
    echo "ERROR: " . $t->getMessage();
}
