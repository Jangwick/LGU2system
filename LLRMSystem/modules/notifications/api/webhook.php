<?php
/**
 * Integration Webhook API
 * External endpoint for integrated modules to send notifications/files/messages
 * 
 * USAGE FOR EXTERNAL SYSTEMS:
 * 
 * 1. Send Notification:
 *    POST /modules/notifications/api/webhook.php
 *    Headers: 
 *      - X-API-Key: your_api_key
 *      - Content-Type: application/json
 *    Body:
 *    {
 *      "action": "send_notification",
 *      "title": "New Document from Committee",
 *      "message": "A new resolution has been filed",
 *      "type": "file|message|alert|integration",
 *      "priority": "low|normal|high|urgent",
 *      "target_user_id": null (for broadcast) or user_id,
 *      "target_role": "administrator|officer|viewer" (optional),
 *      "source_id": "external_reference_123",
 *      "data": { "any": "additional data" },
 *      "expires_at": "2025-12-31 23:59:59" (optional)
 *    }
 * 
 * 2. Send File Notification:
 *    POST /modules/notifications/api/webhook.php
 *    Headers: 
 *      - X-API-Key: your_api_key
 *      - Content-Type: application/json
 *    Body:
 *    {
 *      "action": "send_file",
 *      "title": "New File Received",
 *      "message": "Resolution No. 2025-001 has been submitted",
 *      "file_name": "resolution_2025_001.pdf",
 *      "file_url": "https://external-system.com/files/resolution.pdf",
 *      "file_type": "resolution|ordinance|agenda|committee|hearing",
 *      "priority": "normal",
 *      "target_role": "administrator"
 *    }
 * 
 * 3. Send Message:
 *    POST /modules/notifications/api/webhook.php
 *    Headers: 
 *      - X-API-Key: your_api_key
 *      - Content-Type: application/json
 *    Body:
 *    {
 *      "action": "send_message",
 *      "title": "Message from Public Consultation",
 *      "message": "A citizen has submitted feedback on Ordinance #123",
 *      "sender_name": "Juan Dela Cruz",
 *      "sender_email": "juan@example.com",
 *      "target_role": "officer"
 *    }
 */

require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../services/IntegrationAuth.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key, X-API-Secret');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'error' => 'Method not allowed',
        'allowed_methods' => ['POST']
    ]);
    exit;
}

// Get API key from header
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? null;

if (!$apiKey) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'error' => 'API key required',
        'hint' => 'Include X-API-Key header in your request'
    ]);
    exit;
}

// Validate API key
$auth = new IntegrationAuth();
$module = $auth->validateApiKey($apiKey);

if (!$module) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid or inactive API key'
    ]);
    
    // Log failed attempt
    Logger::log('integration_auth_failed', null, null, "Failed integration auth attempt with key: " . substr($apiKey, 0, 10) . "...");
    exit;
}

// Parse request body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid JSON body'
    ]);
    exit;
}

$action = $input['action'] ?? '';
$notification = new Notification();

try {
    switch ($action) {
        case 'send_notification':
            // Check permission
            if (!$auth->hasPermission($module, 'send_notification')) {
                throw new Exception('Permission denied: send_notification');
            }
            
            // Validate required fields
            if (empty($input['title']) || empty($input['message'])) {
                throw new Exception('Title and message are required');
            }
            
            $notificationData = [
                'type' => $input['type'] ?? Notification::TYPE_INTEGRATION,
                'title' => $input['title'],
                'message' => $input['message'],
                'source_module' => $module['module_name'],
                'source_id' => $input['source_id'] ?? null,
                'priority' => $input['priority'] ?? Notification::PRIORITY_NORMAL,
                'data' => $input['data'] ?? null,
                'expires_at' => $input['expires_at'] ?? null
            ];
            
            // Handle targeting
            if (!empty($input['target_role'])) {
                // Send to all users with specific role
                $result = $notification->notifyRole($input['target_role'], $notificationData);
                $response = [
                    'success' => $result !== false,
                    'notification_ids' => $result,
                    'message' => 'Notification sent to role: ' . $input['target_role']
                ];
            } elseif (!empty($input['target_user_id'])) {
                // Send to specific user
                $notificationData['user_id'] = $input['target_user_id'];
                $notificationId = $notification->create($notificationData);
                $response = [
                    'success' => $notificationId !== false,
                    'notification_id' => $notificationId
                ];
            } else {
                // Broadcast to all
                $notificationId = $notification->broadcast($notificationData);
                $response = [
                    'success' => $notificationId !== false,
                    'notification_id' => $notificationId,
                    'message' => 'Notification broadcast to all users'
                ];
            }
            
            // Log the integration activity
            Logger::log('integration_notification', 'notifications', $response['notification_id'] ?? null, 
                "Notification from {$module['module_name']}: {$input['title']}");
            
            echo json_encode($response);
            break;
            
        case 'send_file':
            // Check permission
            if (!$auth->hasPermission($module, 'send_file')) {
                throw new Exception('Permission denied: send_file');
            }
            
            // Validate required fields
            if (empty($input['title']) || empty($input['message'])) {
                throw new Exception('Title and message are required');
            }
            
            $fileData = [
                'file_name' => $input['file_name'] ?? null,
                'file_url' => $input['file_url'] ?? null,
                'file_type' => $input['file_type'] ?? null,
                'file_size' => $input['file_size'] ?? null
            ];
            
            $notificationData = [
                'type' => Notification::TYPE_FILE,
                'title' => $input['title'],
                'message' => $input['message'],
                'source_module' => $module['module_name'],
                'source_id' => $input['source_id'] ?? null,
                'priority' => $input['priority'] ?? Notification::PRIORITY_NORMAL,
                'data' => array_merge($fileData, $input['data'] ?? [])
            ];
            
            // Handle targeting
            if (!empty($input['target_role'])) {
                $result = $notification->notifyRole($input['target_role'], $notificationData);
                $response = [
                    'success' => $result !== false,
                    'notification_ids' => $result,
                    'message' => 'File notification sent to role: ' . $input['target_role']
                ];
            } else {
                $notificationData['user_id'] = $input['target_user_id'] ?? null;
                $notificationId = $notification->create($notificationData);
                $response = [
                    'success' => $notificationId !== false,
                    'notification_id' => $notificationId
                ];
            }
            
            Logger::log('integration_file', 'notifications', $response['notification_id'] ?? null, 
                "File notification from {$module['module_name']}: {$input['file_name']}");
            
            echo json_encode($response);
            break;
            
        case 'send_message':
            // Check permission
            if (!$auth->hasPermission($module, 'send_message')) {
                throw new Exception('Permission denied: send_message');
            }
            
            // Validate required fields
            if (empty($input['title']) || empty($input['message'])) {
                throw new Exception('Title and message are required');
            }
            
            $messageData = [
                'sender_name' => $input['sender_name'] ?? null,
                'sender_email' => $input['sender_email'] ?? null,
                'reply_to' => $input['reply_to'] ?? null
            ];
            
            $notificationData = [
                'type' => Notification::TYPE_MESSAGE,
                'title' => $input['title'],
                'message' => $input['message'],
                'source_module' => $module['module_name'],
                'source_id' => $input['source_id'] ?? null,
                'priority' => $input['priority'] ?? Notification::PRIORITY_NORMAL,
                'data' => array_merge($messageData, $input['data'] ?? [])
            ];
            
            // Handle targeting
            if (!empty($input['target_role'])) {
                $result = $notification->notifyRole($input['target_role'], $notificationData);
                $response = [
                    'success' => $result !== false,
                    'notification_ids' => $result,
                    'message' => 'Message notification sent to role: ' . $input['target_role']
                ];
            } else {
                $notificationData['user_id'] = $input['target_user_id'] ?? null;
                $notificationId = $notification->create($notificationData);
                $response = [
                    'success' => $notificationId !== false,
                    'notification_id' => $notificationId
                ];
            }
            
            Logger::log('integration_message', 'notifications', $response['notification_id'] ?? null, 
                "Message from {$module['module_name']}: {$input['title']}");
            
            echo json_encode($response);
            break;
            
        case 'ping':
            // Health check endpoint
            echo json_encode([
                'success' => true,
                'message' => 'Integration webhook is active',
                'module' => $module['module_name'],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        default:
            throw new Exception('Invalid action. Allowed: send_notification, send_file, send_message, ping');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    
    Logger::log('integration_error', null, null, 
        "Integration error from {$module['module_name']}: {$e->getMessage()}");
}
