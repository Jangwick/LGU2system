<?php

/**
 * Database Configuration
 */

function getDatabase() {
    static $db = null;
    
    if ($db === null) {
        $host = 'localhost';
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
            // Provide helpful error message
            $error = $e->getMessage();
            $help = '<div style="font-family: Arial; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;">';
            $help .= '<h2 style="color: #721c24;">Database Connection Error</h2>';
            $help .= '<p><strong>Error:</strong> ' . htmlspecialchars($error) . '</p>';
            $help .= '<h3>Troubleshooting Steps:</h3>';
            $help .= '<ol>';
            $help .= '<li><strong>Start MySQL in XAMPP:</strong><br>Open XAMPP Control Panel and click "Start" next to MySQL</li>';
            $help .= '<li><strong>Check if MySQL is running:</strong><br>Look for a green background next to MySQL in XAMPP</li>';
            $help .= '<li><strong>Verify database exists:</strong><br>Open phpMyAdmin (http://localhost/phpmyadmin) and check if "lrms_db" database exists</li>';
            $help .= '<li><strong>Check port:</strong><br>Default MySQL port is 3306. If changed, update database.php</li>';
            $help .= '</ol>';
            $help .= '<p><strong>Configuration:</strong> Host=' . $host . ', Port=' . $port . ', Database=' . $dbname . ', User=' . $username . '</p>';
            $help .= '</div>';
            die($help);
        }
    }
    
    return $db;
}
