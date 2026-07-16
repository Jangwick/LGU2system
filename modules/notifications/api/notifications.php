<?php
/**
 * Notification API Endpoints
 * Internal API for web application
 */

session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$controller = new NotificationController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'list':
                    $limit = intval($_GET['limit'] ?? 20);
                    $offset = intval($_GET['offset'] ?? 0);
                    $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
                    
                    $result = $controller->getNotifications($limit, $offset, $unreadOnly);
                    echo json_encode($result);
                    break;
                    
                case 'count':
                    $count = $controller->getUnreadCount();
                    echo json_encode(['success' => true, 'count' => $count]);
                    break;
                    
                default:
                    // Default: get notifications
                    $result = $controller->getNotifications();
                    echo json_encode($result);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            switch ($action) {
                case 'read':
                    if (!isset($data['notification_id'])) {
                        throw new Exception('Notification ID required');
                    }
                    $result = $controller->markAsRead($data['notification_id']);
                    echo json_encode($result);
                    break;
                    
                case 'read_all':
                    $result = $controller->markAllAsRead();
                    echo json_encode($result);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;
            
        case 'DELETE':
            $notificationId = $_GET['id'] ?? null;
            if (!$notificationId) {
                throw new Exception('Notification ID required');
            }
            
            $result = $controller->deleteNotification($notificationId);
            echo json_encode($result);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
