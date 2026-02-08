<?php
/**
 * Notification Model
 * Handles all notification database operations
 */

require_once __DIR__ . '/../../core/config/database.php';

class Notification {
    private $db;
    
    // Notification types
    const TYPE_FILE = 'file';
    const TYPE_MESSAGE = 'message';
    const TYPE_ALERT = 'alert';
    const TYPE_SYSTEM = 'system';
    const TYPE_INTEGRATION = 'integration';
    
    // Priority levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    
    public function __construct() {
        $this->db = getDatabase();
    }
    
    /**
     * Create a new notification
     */
    public function create($data) {
        try {
            $sql = "INSERT INTO notifications (user_id, type, title, message, source_module, source_id, priority, data, expires_at) 
                    VALUES (:user_id, :type, :title, :message, :source_module, :source_id, :priority, :data, :expires_at)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $data['user_id'] ?? null,
                ':type' => $data['type'] ?? self::TYPE_SYSTEM,
                ':title' => $data['title'],
                ':message' => $data['message'],
                ':source_module' => $data['source_module'] ?? null,
                ':source_id' => $data['source_id'] ?? null,
                ':priority' => $data['priority'] ?? self::PRIORITY_NORMAL,
                ':data' => isset($data['data']) ? json_encode($data['data']) : null,
                ':expires_at' => $data['expires_at'] ?? null
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Notification create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create notification for all users (broadcast)
     */
    public function broadcast($data) {
        $data['user_id'] = null; // NULL means for all users
        return $this->create($data);
    }
    
    /**
     * Create notification for specific role
     */
    public function notifyRole($role, $data) {
        try {
            $sql = "SELECT id FROM users WHERE role = :role AND status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':role' => $role]);
            $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $notificationIds = [];
            foreach ($users as $userId) {
                $data['user_id'] = $userId;
                $notificationIds[] = $this->create($data);
            }
            
            return $notificationIds;
        } catch (PDOException $e) {
            error_log("Notification notifyRole error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notifications for a user
     */
    public function getForUser($userId, $limit = 20, $offset = 0, $unreadOnly = false) {
        try {
            $sql = "SELECT * FROM notifications 
                    WHERE (user_id = :user_id OR user_id IS NULL)
                    AND (expires_at IS NULL OR expires_at > NOW())";
            
            if ($unreadOnly) {
                $sql .= " AND is_read = 0";
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON data
            foreach ($notifications as &$notification) {
                if ($notification['data']) {
                    $notification['data'] = json_decode($notification['data'], true);
                }
            }
            
            return $notifications;
        } catch (PDOException $e) {
            error_log("Notification getForUser error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get unread count for a user
     */
    public function getUnreadCount($userId) {
        try {
            $sql = "SELECT COUNT(*) FROM notifications 
                    WHERE (user_id = :user_id OR user_id IS NULL)
                    AND is_read = 0
                    AND (expires_at IS NULL OR expires_at > NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Notification getUnreadCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId) {
        try {
            $sql = "UPDATE notifications 
                    SET is_read = 1, read_at = NOW() 
                    WHERE id = :id AND (user_id = :user_id OR user_id IS NULL)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $notificationId,
                ':user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log("Notification markAsRead error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId) {
        try {
            $sql = "UPDATE notifications 
                    SET is_read = 1, read_at = NOW() 
                    WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = 0";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_id' => $userId]);
        } catch (PDOException $e) {
            error_log("Notification markAllAsRead error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a notification
     */
    public function delete($notificationId, $userId = null) {
        try {
            $sql = "DELETE FROM notifications WHERE id = :id";
            $params = [':id' => $notificationId];
            
            if ($userId) {
                $sql .= " AND (user_id = :user_id OR user_id IS NULL)";
                $params[':user_id'] = $userId;
            }
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Notification delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete expired notifications (cleanup)
     */
    public function deleteExpired() {
        try {
            $sql = "DELETE FROM notifications WHERE expires_at IS NOT NULL AND expires_at < NOW()";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Notification deleteExpired error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notification by ID
     */
    public function getById($id) {
        try {
            $sql = "SELECT * FROM notifications WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $notification = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($notification && $notification['data']) {
                $notification['data'] = json_decode($notification['data'], true);
            }
            
            return $notification;
        } catch (PDOException $e) {
            error_log("Notification getById error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get notifications from a specific source module
     */
    public function getBySourceModule($sourceModule, $limit = 50) {
        try {
            $sql = "SELECT * FROM notifications 
                    WHERE source_module = :source_module 
                    ORDER BY created_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':source_module', $sourceModule, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Notification getBySourceModule error: " . $e->getMessage());
            return [];
        }
    }
}
