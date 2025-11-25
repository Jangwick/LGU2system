# XAMPP MySQL "Shutdown Unexpectedly" - Complete Fix Guide

## Problem
MySQL won't start in XAMPP and shows: "MySQL shutdown unexpectedly"

## Solution Steps

### **Method 1: Backup and Restore Data (SAFEST)**

**Step 1: Stop MySQL** (if running)
- Open XAMPP Control Panel
- Click "Stop" next to MySQL
- Wait until it's completely stopped

**Step 2: Backup Your Data**
1. Navigate to: `C:\xampp\mysql\data`
2. Copy the entire `data` folder
3. Paste it somewhere safe (e.g., Desktop) and rename to `data_backup`

**Step 3: Fix Corrupted Files**
1. Go to: `C:\xampp\mysql\data`
2. Delete these files:
   - `ibdata1`
   - `ib_logfile0`
   - `ib_logfile1`
   - Any files starting with `aria_log`

**Step 4: Copy Default Files**
1. Go to: `C:\xampp\mysql\backup`
2. Copy all files from the `backup` folder
3. Paste into: `C:\xampp\mysql\data`
4. Choose "Skip" if asked to replace existing folders

**Step 5: Restore Your Databases**
1. Go to your backup: `data_backup` folder
2. Find your database folder: `lrms_db`
3. Copy the `lrms_db` folder
4. Paste it into: `C:\xampp\mysql\data`

**Step 6: Start MySQL**
1. Open XAMPP Control Panel
2. Click "Start" next to MySQL
3. Should start successfully now!

---

### **Method 2: Quick Fix (If Method 1 Doesn't Work)**

**Step 1: Rename Data Folder**
1. Go to: `C:\xampp\mysql`
2. Rename `data` to `data_old`

**Step 2: Copy Backup**
1. Copy the `backup` folder
2. Rename the copy to `data`

**Step 3: Restore Databases**
1. From `data_old`, copy your database folders (e.g., `lrms_db`)
2. Paste into the new `data` folder
3. Also copy: `mysql` folder, `performance_schema` folder, `phpmyadmin` folder

**Step 4: Start MySQL**
- Start MySQL from XAMPP Control Panel

---

### **Method 3: Port Conflict Check**

Sometimes another program uses port 3306:

**Check Port Usage:**
```cmd
netstat -ano | findstr :3306
```

**If port is in use:**
1. Open: `C:\xampp\mysql\bin\my.ini`
2. Find: `port=3306`
3. Change to: `port=3307`
4. Save file
5. In XAMPP Control Panel, click Config > my.ini
6. Change port there too
7. Restart MySQL

---

### **Method 4: Run as Administrator**

1. Close XAMPP
2. Right-click XAMPP Control Panel
3. Select "Run as Administrator"
4. Try starting MySQL

---

### **Method 5: Check Windows Services**

Sometimes MySQL is running as a Windows service:

1. Press `Win + R`
2. Type: `services.msc`
3. Press Enter
4. Look for "MySQL" service
5. If found, right-click > Stop
6. Then try starting from XAMPP

---

## After MySQL Starts Successfully

### Import Your Database (if needed)

**Option 1: Using phpMyAdmin**
1. Go to: http://localhost/phpmyadmin
2. Click "Import" tab
3. Choose your SQL file: `C:\xampp\htdocs\LLRMSystem\database\schema.sql`
4. Click "Go"

**Option 2: Using Command Line**
```cmd
cd C:\xampp\mysql\bin
mysql -u root -p
```
Then:
```sql
CREATE DATABASE IF NOT EXISTS lrms_db;
USE lrms_db;
SOURCE C:/xampp/htdocs/LLRMSystem/database/schema.sql;
```

---

## Run Database Migrations

After MySQL is running and database is created:

```cmd
cd C:\xampp\htdocs\LLRMSystem\database\migrations
run_user_profile_migrations.bat
```

---

## Prevention

To prevent this in the future:

1. **Always stop MySQL properly** before shutting down Windows
2. **Don't force-close XAMPP** when MySQL is running
3. **Use "Stop" button** in XAMPP Control Panel
4. **Regular backups**: Copy `C:\xampp\mysql\data` weekly

---

## If Nothing Works

**Nuclear Option (CLEAN REINSTALL):**

1. Export your databases first via phpMyAdmin
2. Uninstall XAMPP completely
3. Delete: `C:\xampp` folder
4. Download fresh XAMPP from: https://www.apachefriends.org
5. Install XAMPP
6. Import your databases back

---

## Need Help?

Check XAMPP logs:
- `C:\xampp\mysql\data\mysql_error.log`
- Click "Logs" button in XAMPP Control Panel

Common errors and solutions:
- **Port blocked**: Change port to 3307
- **Corrupted ibdata1**: Delete and restore from backup
- **Permission denied**: Run XAMPP as Administrator
- **Service conflict**: Stop MySQL Windows service
