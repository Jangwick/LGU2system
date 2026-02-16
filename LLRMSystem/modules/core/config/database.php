<?php

/**
 * Database Configuration
 */

function getDatabase() {
    static $db = null;
    
    if ($db === null) {
        $host = '127.0.0.1';
        $dbname = 'lrms_db';
        $username = 'root';
        $password = '';
        $port = '3306';
        
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
