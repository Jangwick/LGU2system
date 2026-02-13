<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../core/config/database.php';

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    $reportType = $input['report_type'] ?? '';
    $frequency = $input['frequency'] ?? 'monthly';
    $recipients = $input['recipients'] ?? [$_SESSION['email']];
    
    if (empty($reportType)) {
        throw new Exception('Report type is required');
    }
    
    if (!in_array($frequency, ['daily', 'weekly', 'monthly'])) {
        throw new Exception('Invalid frequency');
    }
    
    $db = getDatabase();
    
    // Insert scheduled report
    $stmt = $db->prepare("
        INSERT INTO scheduled_reports (
            report_type,
            frequency,
            recipients,
            created_by,
            created_at,
            next_run_at
        ) VALUES (
            :report_type,
            :frequency,
            :recipients,
            :created_by,
            NOW(),
            :next_run_at
        )
    ");
    
    // Calculate next run date
    $nextRun = match($frequency) {
        'daily' => date('Y-m-d H:i:s', strtotime('+1 day')),
        'weekly' => date('Y-m-d H:i:s', strtotime('+1 week')),
        'monthly' => date('Y-m-d H:i:s', strtotime('+1 month')),
        default => date('Y-m-d H:i:s', strtotime('+1 month'))
    };
    
    $stmt->execute([
        ':report_type' => $reportType,
        ':frequency' => $frequency,
        ':recipients' => json_encode($recipients),
        ':created_by' => $_SESSION['user_id'],
        ':next_run_at' => $nextRun
    ]);
    
    // Log activity
    $logStmt = $db->prepare("
        INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, created_at)
        VALUES (:user_id, 'schedule_report', 'report', :report_id, :description, NOW())
    ");
    
    $logStmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':report_id' => $db->lastInsertId(),
        ':description' => "Scheduled {$reportType} report to run {$frequency}"
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Report scheduled successfully',
        'schedule_id' => $db->lastInsertId(),
        'next_run' => $nextRun
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
