<?php
require_once __DIR__ . '/modules/core/config/database.php';
$db = getDatabase();
$stmt = $db->query('DESCRIBE users');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
