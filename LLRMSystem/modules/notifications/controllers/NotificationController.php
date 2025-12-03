<?php
/**
 * Notification Controller
 * Handles notification operations for the web interface
 */

require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../../core/utils/Logger.php';

class NotificationController {
    private $notification;
    
    public function __construct() {
        $this->notification = new Notification();
    }
    
    /**
     * Get notifications for current user
     */
    public function getNotifications($limit = 20, $offset = 0, $unreadOnly = false) {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }
        
        $notifications = $this->notification->getForUser(
            $_SESSION['user_id'], 
            $limit, 
            $offset, 
            $unreadOnly
        );
        
        return [
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $this->notification->getUnreadCount($_SESSION['user_id'])
        ];
    }
    
    /**
     * Get unread notification count
     */
    public function getUnreadCount() {
        if (!isset($_SESSION['user_id'])) {
            return 0;
        }
        
        return $this->notification->getUnreadCount($_SESSION['user_id']);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId) {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }
        
        $result = $this->notification->markAsRead($notificationId, $_SESSION['user_id']);
        
        return [
            'success' => $result,
            'unread_count' => $this->notification->getUnreadCount($_SESSION['user_id'])
        ];
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead() {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }
        
        $result = $this->notification->markAllAsRead($_SESSION['user_id']);
        
        return [
            'success' => $result,
            'message' => $result ? 'All notifications marked as read' : 'Failed to update notifications'
        ];
    }
    
    /**
     * Delete a notification
     */
    public function deleteNotification($notificationId) {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }
        
        $result = $this->notification->delete($notificationId, $_SESSION['user_id']);
        
        return [
            'success' => $result,
            'message' => $result ? 'Notification deleted' : 'Failed to delete notification'
        ];
    }
    
    /**
     * Create a system notification (internal use)
     */
    public function createSystemNotification($title, $message, $userId = null, $priority = 'normal') {
        return $this->notification->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_SYSTEM,
            'title' => $title,
            'message' => $message,
            'priority' => $priority
        ]);
    }
    
    /**
     * Broadcast notification to all users
     */
    public function broadcastNotification($title, $message, $priority = 'normal') {
        return $this->notification->broadcast([
            'type' => Notification::TYPE_SYSTEM,
            'title' => $title,
            'message' => $message,
            'priority' => $priority
        ]);
    }
    
    /**
     * Get notification icon based on type
     */
    public static function getNotificationIcon($type) {
        return match($type) {
            'file' => 'bi-file-earmark',
            'message' => 'bi-chat-dots',
            'alert' => 'bi-exclamation-triangle',
            'integration' => 'bi-plug',
            default => 'bi-bell'
        };
    }
    
    /**
     * Get priority color class
     */
    public static function getPriorityClass($priority) {
        return match($priority) {
            'urgent' => 'text-red-600 bg-red-100',
            'high' => 'text-orange-600 bg-orange-100',
            'normal' => 'text-blue-600 bg-blue-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }
}
