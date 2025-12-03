# Activity Logging System Documentation

## Overview

The LLRM System includes a comprehensive activity logging system that tracks all user actions for audit and monitoring purposes. This allows administrators to monitor user activities, detect suspicious behavior, and maintain compliance records.

## Key Features

1. **Comprehensive Action Tracking**
   - User authentication (login, logout, failed attempts)
   - Document operations (create, update, delete, download, view)
   - User management (create, update, delete, role changes)
   - Profile updates and password changes
   - Tag assignments and bulk operations

2. **Detailed Audit Trail**
   - Old and new values for all changes
   - IP address and user agent tracking
   - Timestamp for all activities
   - User context (who performed the action)

3. **Severity Levels**
   - INFO: Normal operations
   - WARNING: Failed attempts, security alerts
   - ERROR: System errors
   - CRITICAL: Security breaches, critical failures

## Activity Types

### Authentication Activities
- `login` - Successful user login
- `logout` - User logout
- `login_failed` - Failed login attempt

### Document Activities
- `document_create` - New document uploaded
- `document_update` - Document metadata modified
- `document_delete` - Document removed
- `document_download` - Document downloaded
- `document_view` - Document viewed
- `document_upload` - Document file uploaded

### User Management Activities
- `user_create` - New user account created
- `user_update` - User account modified
- `user_delete` - User account deleted
- `user_activate` - User account activated
- `user_deactivate` - User account deactivated
- `role_change` - User role changed

### Profile Activities
- `profile_update` - User updated their profile
- `password_change` - User changed their password

### Tag Activities
- `tag_assign` - Tag assigned to document
- `tag_create` - New tag created
- `tag_remove` - Tag removed from document

## Logger Class Usage

### Basic Logging
```php
require_once __DIR__ . '/../../core/utils/Logger.php';

$db = getDatabase();
$logger = new Logger($db);

// Simple activity log
$logger->logActivity(Logger::ACTION_DOCUMENT_CREATE, 'documents', $documentId,
    "Document created: $title");
```

### Logging with Old/New Values
```php
// Log with change tracking
$logger->logActivity(Logger::ACTION_USER_UPDATE, 'users', $userId,
    "User profile updated", 
    ['name' => 'New Name', 'email' => 'new@email.com'], // new values
    ['name' => 'Old Name', 'email' => 'old@email.com']  // old values
);
```

### Session Logging (Login/Logout)
```php
// Log login
$logger->logSession($userId, Logger::ACTION_LOGIN, [
    'email' => $email,
    'role' => $role,
    'remember_me' => true
]);

// Log logout
$logger->logSession($userId, Logger::ACTION_LOGOUT, [
    'session_duration_seconds' => $duration
]);
```

### Document Activity Logging
```php
// Log document activity
$logger->logDocumentActivity($documentId, Logger::ACTION_DOCUMENT_UPLOAD, $title, [
    'file_size' => $fileSize,
    'document_type' => $docType
]);
```

## Database Schema

### activity_logs Table
```sql
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50),
    table_name VARCHAR(50),
    record_id INT,
    description TEXT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    severity VARCHAR(20) DEFAULT 'info',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_user_id (user_id)
);
```

## Admin Audit Dashboard

The audit dashboard is accessible at `/modules/audit/views/index.php` and provides:

### Statistics
- Total activity logs
- Activities today
- Activities this week
- Most active user

### Filtering Options
- By user
- By action type
- By table/module
- By date range
- Text search

### Export
- CSV export with filters applied

## Security Considerations

1. **Failed Login Tracking**: Failed login attempts are logged with WARNING severity to detect brute force attacks.

2. **Role Change Auditing**: All role changes are specifically tracked for security compliance.

3. **Password Change Logging**: Password changes are logged (without storing actual passwords).

4. **IP Tracking**: All activities include IP address for security analysis.

5. **User Agent Logging**: Browser/client information is captured for security forensics.

## Implementation Files

| File | Purpose |
|------|---------|
| `modules/core/utils/Logger.php` | Core Logger class with all logging methods |
| `modules/audit/controllers/AuditController.php` | Audit view controller with filtering and export |
| `modules/audit/views/index.php` | Admin audit dashboard |
| `modules/authentication/controllers/LoginController.php` | Login activity logging |
| `modules/authentication/controllers/LogoutController.php` | Logout activity logging |
| `modules/user-management/controllers/UserController.php` | User CRUD activity logging |
| `modules/user-management/api/update-profile.php` | Profile update logging |
| `modules/user-management/api/change-password.php` | Password change logging |
| `modules/document-management/services/DocumentService.php` | Document operation logging |

## Best Practices

1. **Always log user context**: Include user ID and relevant details in all logs.

2. **Track changes**: When updating records, capture both old and new values.

3. **Use appropriate severity**: Use WARNING for security-related events, ERROR for system failures.

4. **Be descriptive**: Write clear descriptions that explain what happened.

5. **Include relevant metadata**: Add context like document titles, user names, etc.

## Example: Complete User Update Logging

```php
// Get old values before update
$oldValues = [
    'name' => $user['name'],
    'email' => $user['email'],
    'role' => $user['role']
];

// Perform update
$this->updateUser($id, $data);

// Log with full context
$logger->logActivity(
    Logger::ACTION_USER_UPDATE,
    'users',
    $id,
    "Updated user: {$user['name']}",
    $data,        // new values
    $oldValues    // old values
);
```

This logging system ensures complete visibility into system activities while maintaining performance through efficient database indexing and query optimization.
