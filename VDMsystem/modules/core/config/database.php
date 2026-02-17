<?php
/**
 * VDMsystem - Database Configuration
 */

function getDatabase() {
    static $db = null;
    
    if ($db === null) {
        $host = 'localhost';
        $dbname = 'vdm_db';
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
            $error = $e->getMessage();
            $help = '<div style="font-family: Arial; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; max-width: 600px; margin: 50px auto;">';
            $help .= '<h2 style="color: #721c24;">Database Connection Error</h2>';
            $help .= '<p><strong>Error:</strong> ' . htmlspecialchars($error) . '</p>';
            $help .= '<h3>Troubleshooting Steps:</h3>';
            $help .= '<ol>';
            $help .= '<li><strong>Start MySQL in XAMPP:</strong><br>Open XAMPP Control Panel and click "Start" next to MySQL</li>';
            $help .= '<li><strong>Import the database:</strong><br>Open phpMyAdmin and import <code>database/vdm_db.sql</code></li>';
            $help .= '<li><strong>Verify database exists:</strong><br>Check if "vdm_db" database exists in phpMyAdmin</li>';
            $help .= '</ol>';
            $help .= '<p><strong>Configuration:</strong> Host=' . $host . ', Port=' . $port . ', Database=' . $dbname . '</p>';
            $help .= '</div>';
            die($help);
        }
    }
    
    return $db;
}

/**
 * Execute a prepared statement and return results
 */
function dbQuery($sql, $params = []) {
    $db = getDatabase();
    $stmt = $db->prepare($sql);
    
    // Safety check: Ensure no nested arrays are passed to execute
    if (is_array($params)) {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                error_log("Array to string conversion detected in dbQuery for SQL: $sql. Parameter index/key: $key");
                $params[$key] = json_encode($value);
            }
        }
    }
    
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch all results from a query
 */
function dbFetchAll($sql, $params = []) {
    return dbQuery($sql, $params)->fetchAll();
}

/**
 * Fetch single row from a query
 */
function dbFetchOne($sql, $params = []) {
    return dbQuery($sql, $params)->fetch();
}

/**
 * Insert a record and return the last insert ID
 */
function dbInsert($table, $data) {
    $db = getDatabase();
    
    // Safety check: json_encode any arrays in the data
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $data[$key] = json_encode($value);
        }
    }
    
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_values($data));
    
    return $db->lastInsertId();
}

/**
 * Update records in a table
 */
function dbUpdate($table, $data, $where, $whereParams = []) {
    $db = getDatabase();
    
    // Safety check: json_encode any arrays in the data
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $data[$key] = json_encode($value);
        }
    }
    
    // Safety check: ensure whereParams are not nested arrays
    foreach ($whereParams as $key => $value) {
        if (is_array($value)) {
            $whereParams[$key] = json_encode($value);
        }
    }
    
    $set = implode(' = ?, ', array_keys($data)) . ' = ?';
    
    $sql = "UPDATE $table SET $set WHERE $where";
    $params = array_merge(array_values($data), $whereParams);
    
    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Delete records from a table
 */
function dbDelete($table, $where, $params = []) {
    $db = getDatabase();
    $sql = "DELETE FROM $table WHERE $where";
    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Count records in a table
 */
function dbCount($table, $where = '1=1', $params = []) {
    $sql = "SELECT COUNT(*) as count FROM $table WHERE $where";
    $result = dbFetchOne($sql, $params);
    return $result['count'] ?? 0;
}
