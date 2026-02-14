# Login Lockout Mechanism Plan

## Overview
To enhance system security against brute-force attacks, a login lockout mechanism will be implemented. This mechanism will track failed login attempts and temporarily block access after a specific threshold is reached, with increasing penalties for repeated failures.

## 1. Requirement Specifications
- **Failure Threshold**: 5 failed login attempts.
- **Initial Lockout Duration**: 5 minutes after the 5th failed attempt.
- **Incremental Lockout**: Every subsequent failed attempt after the 5th will increase the lockout duration by an additional 5 minutes.
- **Tracking**: Attempts will be tracked by both IP address and User Identifier (Email/Username) to prevent distributed brute-force attacks on a single account and targeted attacks from a single IP.

## 2. Lockout Logic Table

| Failed Attempt # | Action | Lockout Duration | Time until next allowed attempt |
|-----------------|--------|------------------|--------------------------------|
| 1 - 4           | Log failure | None | Immediate |
| 5               | **Lock Account** | 5 Minutes | 5 Minutes |
| 6               | **Lock Account** | 10 Minutes | 10 Minutes |
| 7               | **Lock Account** | 15 Minutes | 15 Minutes |
| 8               | **Lock Account** | 20 Minutes | 20 Minutes |
| $n$ (where $n \ge 5$) | **Lock Account** | $(n - 4) \times 5$ Minutes | $(n - 4) \times 5$ Minutes |

## 3. Database Schema
A new table `login_attempts` will be created to track these events.

```sql
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    identifier VARCHAR(255) NOT NULL, -- email or username
    attempt_count INT DEFAULT 1,
    lockout_until DATETIME NULL,
    last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ip_identifier (ip_address, identifier)
);
```

## 4. Implementation Strategy

### A. Pre-Authentication Check
Before verifying credentials in `LoginController.php`:
1. Query the `login_attempts` table for the current `ip_address` AND `identifier`.
2. Check if `lockout_until` is set and is in the future (`lockout_until > NOW()`).
3. If locked, return a JSON error response:
   ```json
   {
     "success": false,
     "message": "Too many failed attempts. Please try again in X minutes.",
     "lockout_remaining": 300 // seconds
   }
   ```

### B. On Authentication Failure
If the password verification fails:
1. Check if a record exists in `login_attempts` for the IP and identifier.
2. **If no record exists**: Create a new record with `attempt_count = 1`.
3. **If record exists**:
   - Increment `attempt_count`.
   - If `attempt_count >= 5`:
     - Calculate lockout duration using the formula: `(attempt_count - 4) * 5`.
     - Update `lockout_until = DATE_ADD(NOW(), INTERVAL lockout_duration MINUTE)`.
   - Update `last_attempt = NOW()`.

### C. On Authentication Success
When the user successfully authenticates:
1. Delete the record in `login_attempts` for that IP and identifier (or reset `attempt_count = 0` and `lockout_until = NULL`).
2. Proceed with normal login/OTP flow.

## 5. Security Considerations
- **IP anonymization**: Ensure IP addresses are handled correctly (support for IPv6).
- **Grace Period**: Attempts older than 24 hours should probably be cleared or ignored to prevent accidental lockouts for users who occasionally forget passwords.
- **Notification**: Consider logging "CRITICAL" severity events in `activity_logs` when a 10+ attempt threshold is reached, indicating a likely automated attack.

## 6. Development Roadmap
1. **Migration**: Create and run the SQL migration for `login_attempts`.
2. **Utils Update**: Add a helper method to `Auth` or a new `Security` utility class to handle lockout checks.
3. **Controller Integration**: Modify `LoginController.php` to include the check and update logic.
4. **UI Update**: Modify the login frontend to display the remaining lockout time if applicable.
