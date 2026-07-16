# Audit Module Setup & Verification Guide

## Issue Found
The `activity_logs` table was missing required columns:
- `table_name` - to track which table was affected
- `record_id` - to track which record was affected

The `users` table was also missing:
- `username` - for display in audit logs
- `full_name` - for full user names

## Solution

### Step 1: Run the Migration

**Option A: Using the batch file (Recommended)**
```bash
cd c:\xampp\htdocs\LLRMSystem\database\migrations
run_migration_002.bat
```

**Option B: Manually via phpMyAdmin**
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Select the `lrms_db` database
3. Click on "SQL" tab
4. Copy and paste the contents of `002_update_activity_logs.sql`
5. Click "Go" to execute

**Option C: Using MySQL command line**
```bash
cd c:\xampp\htdocs\LLRMSystem\database\migrations
mysql -u root lrms_db < 002_update_activity_logs.sql
```

### Step 2: Verify the Changes

Run this SQL to verify:
```sql
-- Check activity_logs columns
SHOW COLUMNS FROM activity_logs;

-- Check users columns
SHOW COLUMNS FROM users;
```

You should see:
- `activity_logs` has: `table_name` and `record_id` columns
- `users` has: `username` and `full_name` columns

### Step 3: Test the Audit Module

1. **Login as Administrator**
   - Email: `admin@lgu.gov.ph`
   - Password: `Admin@123`

2. **Access Audit Logs**
   - Go to sidebar → Management → Audit Logs
   - URL: http://localhost/LLRMSystem/modules/audit/views/index.php

3. **Expected Features**
   - ✅ Statistics cards (Total Logs, Today, This Week, Most Active User)
   - ✅ Filter by User, Action, Table, Date Range
   - ✅ Search functionality
   - ✅ Paginated results (50 per page)
   - ✅ Export to CSV button
   - ✅ Color-coded action badges

## Database Schema Updates

### activity_logs Table (Updated)
```sql
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),          -- NEW
    record_id INT,                     -- NEW
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_table_name (table_name),  -- NEW
    INDEX idx_record_id (record_id),    -- NEW
    INDEX idx_created (created_at)
);
```

### users Table (Updated)
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(100) UNIQUE,      -- NEW
    full_name VARCHAR(255),            -- NEW
    password VARCHAR(255) NOT NULL,
    role ENUM('administrator', 'officer', 'staff', 'viewer') DEFAULT 'viewer',
    department VARCHAR(255),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## How to Use the Audit Module

### Logging Activities (For Developers)

Use the Logger utility class to log activities:

```php
require_once __DIR__ . '/../../core/utils/Logger.php';

// Log a document creation
Logger::log('create', 'legislative_documents', $documentId, 
    'Created new document: ' . $documentTitle, $userId);

// Log a user login
Logger::log('login', 'users', $userId, 
    'User logged in successfully', $userId);

// Log a document edit
Logger::log('update', 'legislative_documents', $documentId, 
    'Updated document metadata', $userId);

// Log a deletion
Logger::log('delete', 'legislative_documents', $documentId, 
    'Soft deleted document', $userId);
```

### Viewing Audit Logs

**Filters Available:**
- **User**: Filter by specific user
- **Action**: create, update, delete, login, logout, etc.
- **Table**: legislative_documents, users, etc.
- **Date Range**: From and To dates
- **Search**: Search in descriptions and user names

**Export:**
- Click "Export CSV" to download filtered results
- Filename format: `audit_logs_YYYY-MM-DD_HHMMSS.csv`

## Troubleshooting

### Issue: "Audit Logs" link not visible in sidebar
**Solution:** 
- Make sure you're logged in as an administrator
- Check your session: `$_SESSION['user_role']` should be `'administrator'` or `'ADMIN'`

### Issue: Database errors when accessing audit page
**Solution:** 
- Run the migration script `002_update_activity_logs.sql`
- Verify columns exist using `SHOW COLUMNS FROM activity_logs`

### Issue: No logs showing up
**Solution:**
- The table might be empty initially
- Perform some actions (upload document, login, etc.) to generate logs
- Check if Logger class is being called in your controllers

### Issue: User names showing as "Unknown"
**Solution:**
- Run the migration to add `username` and `full_name` columns
- Update existing users: `UPDATE users SET username = SUBSTRING_INDEX(email, '@', 1), full_name = name`

## Files Modified/Created

**New Files:**
- ✅ `modules/audit/controllers/AuditController.php` (287 lines)
- ✅ `modules/audit/views/index.php` (299 lines)
- ✅ `modules/core/middleware/PermissionMiddleware.php` (273 lines)
- ✅ `database/migrations/002_update_activity_logs.sql` (Migration script)
- ✅ `database/migrations/run_migration_002.bat` (Batch file)

**Updated Files:**
- ✅ `database/schema.sql` (Updated activity_logs and users tables)
- ✅ `modules/core/layouts/sidebar.php` (Added Audit Logs link)
- ✅ `LLRMSystem/layouts/sidebar.php` (Added Audit Logs link)

## Permissions Required

The Audit Module uses the Permission Middleware:
- **Permission:** `audit.view`
- **Minimum Role:** `administrator`
- **Access Control:** Automatically enforced via `PermissionMiddleware`

Only administrators can:
- View audit logs
- Filter and search logs
- Export logs to CSV

## Next Steps

1. ✅ Run the migration script
2. ✅ Login as administrator
3. ✅ Access the Audit Logs page
4. ✅ Verify all features work correctly
5. ✅ Generate some test activities
6. ✅ Test filtering and exporting

---

**Status:** Migration required before use  
**Priority:** High - Required for production security  
**Estimated Time:** 5 minutes to complete migration
