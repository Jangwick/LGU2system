<?php

class Security {
    private $db;
    const MAX_FREE_ATTEMPTS = 5;
    const LOCKOUT_INCREMENT_MINUTES = 5;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Check if an IP/Identifier combination is currently locked out
     * Returns 0 if not locked, or the number of seconds remaining if locked.
     */
    public function checkLockout($ip, $identifier) {
        $stmt = $this->db->prepare("
            SELECT lockout_until 
            FROM login_attempts 
            WHERE ip_address = ? AND identifier = ?
            AND lockout_until > NOW()
            LIMIT 1
        ");
        $stmt->execute([$ip, $identifier]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $lockoutUntil = strtotime($result['lockout_until']);
            $remainingSeconds = $lockoutUntil - time();
            return $remainingSeconds > 0 ? $remainingSeconds : 0;
        }

        return 0;
    }

    /**
     * Record a failed login attempt and apply lockout if necessary
     */
    public function recordFailedAttempt($ip, $identifier) {
        // Check if record exists
        $stmt = $this->db->prepare("SELECT id, attempt_count FROM login_attempts WHERE ip_address = ? AND identifier = ? LIMIT 1");
        $stmt->execute([$ip, $identifier]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($record) {
            $newCount = $record['attempt_count'] + 1;
            $lockoutUntil = null;

            if ($newCount >= self::MAX_FREE_ATTEMPTS) {
                // Calculate lockout duration: (newCount - 4) * 5 minutes
                // Example: 5th attempt: (5-4)*5 = 5 mins
                //         6th attempt: (6-4)*5 = 10 mins
                $minutes = ($newCount - (self::MAX_FREE_ATTEMPTS - 1)) * self::LOCKOUT_INCREMENT_MINUTES;
                $lockoutUntil = date('Y-m-d H:i:s', strtotime("+$minutes minutes"));
            }

            $stmt = $this->db->prepare("
                UPDATE login_attempts 
                SET attempt_count = ?, lockout_until = ?, last_attempt = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$newCount, $lockoutUntil, $record['id']]);
            
            return [
                'count' => $newCount,
                'lockout_until' => $lockoutUntil
            ];
        } else {
            // First failure
            $stmt = $this->db->prepare("
                INSERT INTO login_attempts (ip_address, identifier, attempt_count, last_attempt) 
                VALUES (?, ?, 1, NOW())
            ");
            $stmt->execute([$ip, $identifier]);
            return [
                'count' => 1,
                'lockout_until' => null
            ];
        }
    }

    /**
     * Clear attempts after a successful login
     */
    public function clearAttempts($ip, $identifier) {
        $stmt = $this->db->prepare("DELETE FROM login_attempts WHERE ip_address = ? AND identifier = ?");
        return $stmt->execute([$ip, $identifier]);
    }

    /**
     * Get client IP address accurately
     */
    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }
    }
}
