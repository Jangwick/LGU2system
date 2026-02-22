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
            $db = new PDO(
                "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            // Rethrow the exception so the caller (like LoginController) can handle it as JSON
            throw $e;
        }
    }
    
    return $db;
}
