# Database Import Instructions

## Problem
You're seeing this error: `SQLSTATE[HY000] [1049] Unknown database 'lrms_db'`

This means the database doesn't exist yet and needs to be created.

---

## ✅ RECOMMENDED: Method 1 - Run Batch Script (Easiest)

1. **Make sure XAMPP MySQL is running**
   - Open XAMPP Control Panel
   - Start "MySQL" if not already started

2. **Run the import script**
   - Navigate to: `C:\xampp\htdocs\LLRMSystem\database\`
   - Double-click: `import_database.bat`
   - When prompted for password, just press **ENTER** (XAMPP default has no password)

3. **Done!** The database is now created with:
   - Database name: `lrms_db`
   - 9 tables created
   - 4 test user accounts

---

## Method 2 - Using phpMyAdmin (Alternative)

1. **Open phpMyAdmin**
   - Go to: http://localhost/phpmyadmin

2. **Import the schema**
   - Click the **"Import"** tab at the top
   - Click **"Choose File"** button
   - Select: `C:\xampp\htdocs\LLRMSystem\database\schema.sql`
   - Click **"Go"** button at the bottom

3. **Verify**
   - You should see "Import has been successfully finished"
   - Click on `lrms_db` in the left sidebar
   - You should see 9 tables listed

---

## Method 3 - Using MySQL Command Line (Advanced)

1. **Open Command Prompt** (Run as Administrator)

2. **Navigate to MySQL bin folder**
   ```cmd
   cd C:\xampp\mysql\bin
   ```

3. **Import the database**
   ```cmd
   mysql -u root < C:\xampp\htdocs\LLRMSystem\database\schema.sql
   ```

4. **Verify import**
   ```cmd
   mysql -u root -e "USE lrms_db; SHOW TABLES;"
   ```

---

## After Import - Test the System

### Test Account Credentials
Once the database is imported, you can login with:

- **Administrator Account**
  - Email: `admin@lgu.gov.ph`
  - Password: `Admin@123`

- **Officer Account**
  - Email: `officer@lgu.gov.ph`
  - Password: `Admin@123`

- **Staff Account**
  - Email: `staff@lgu.gov.ph`
  - Password: `Admin@123`

- **Viewer Account**
  - Email: `viewer@lgu.gov.ph`
  - Password: `Admin@123`

### Access URLs
- **Login Page**: http://localhost/LLRMSystem/LLRMSystem/auth/login.php
- **Dashboard**: http://localhost/LLRMSystem/LLRMSystem/dashboard.php
- **Documents**: http://localhost/LLRMSystem/modules/document-management/views/index.php

---

## Verify Database Creation

### Check if database exists:
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Look for `lrms_db` in the left sidebar
3. Click on it to see the tables

### Expected Tables (9 total):
1. `users` - User accounts (4 test users)
2. `legislative_documents` - Main documents table
3. `document_versions` - Version control
4. `document_tags` - Tag definitions
5. `document_tag_relationships` - Document-tag links
6. `document_links` - Related documents
7. `activity_logs` - User activity tracking
8. `document_access_logs` - Document access tracking
9. `api_keys` - API authentication

---

## Troubleshooting

### Error: "Access denied for user 'root'@'localhost'"
**Solution**: Your MySQL root user has a password set.
- Try Method 2 (phpMyAdmin) instead, which handles authentication automatically
- OR find your MySQL password in XAMPP settings

### Error: "MySQL service not running"
**Solution**: 
1. Open XAMPP Control Panel
2. Click "Start" next to MySQL
3. Wait for it to show "Running" status
4. Try import again

### Error: "Can't connect to MySQL server"
**Solution**:
1. Make sure XAMPP is installed correctly
2. Check if MySQL service is running in XAMPP Control Panel
3. Try restarting MySQL service

### Database imported but still getting error
**Solution**:
1. Check `modules/core/config/database.php` has correct settings:
   - Host: `localhost`
   - Database: `lrms_db`
   - Username: `root`
   - Password: `` (empty, unless you changed it)
2. Clear browser cache and refresh the page

---

## Need Help?

If you continue to have issues:
1. Check if XAMPP MySQL is running (green indicator in control panel)
2. Try accessing phpMyAdmin directly: http://localhost/phpmyadmin
3. Verify the schema.sql file exists at: `C:\xampp\htdocs\LLRMSystem\database\schema.sql`
