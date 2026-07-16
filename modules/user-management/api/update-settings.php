<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/../../core/config/database.php';

try {
    $db = getDatabase();
    
    // Get all POST data
    $settings = [];
    foreach ($_POST as $key => $value) {
        $settings[$key] = $value;
    }
    
    // Check if preferences exist
    $checkStmt = $db->prepare("SELECT id FROM user_preferences WHERE user_id = ?");
    $checkStmt->execute([$_SESSION['user_id']]);
    $exists = $checkStmt->fetch();
    
    if ($exists) {
        // Update existing preferences
        $updateFields = [];
        $updateValues = [];
        
        foreach ($settings as $key => $value) {
            $updateFields[] = "$key = ?";
            $updateValues[] = $value;
        }
        
        $updateValues[] = $_SESSION['user_id'];
        
        $sql = "UPDATE user_preferences SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($updateValues);
    } else {
        // Insert new preferences
        $settings['user_id'] = $_SESSION['user_id'];
        $settings['created_at'] = date('Y-m-d H:i:s');
        
        $columns = array_keys($settings);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO user_preferences (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $db->prepare($sql);
        $stmt->execute(array_values($settings));
    }
    
    // Log activity
    $logStmt = $db->prepare("
        INSERT INTO activity_logs (user_id, action, description, created_at) 
        VALUES (?, 'update', 'Updated account settings', NOW())
    ");
    $logStmt->execute([$_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Settings updated successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating settings: ' . $e->getMessage()]);
}
