<?php

/**
 * Database Configuration
 */

require_once __DIR__ . '/config.php';

function getDatabase() {
    static $db = null;
    
    if ($db === null) {
        $host = DB_HOST;
        $dbname = DB_NAME;
        $username = DB_USER;
        $password = DB_PASS;
        $port = DB_PORT;
        
        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => DB_TIMEOUT,
                PDO::ATTR_PERSISTENT => DB_PERSISTENT,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            $db = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Rethrow the exception so the caller (like LoginController) can handle it as JSON
            throw $e;
        }
    }
    
    return $db;
}
