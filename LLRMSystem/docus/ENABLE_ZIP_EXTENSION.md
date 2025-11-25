# Enable ZIP Extension in XAMPP

## Problem
`Fatal error: Uncaught Error: Class "ZipArchive" not found`

This error occurs because the PHP ZIP extension is not enabled in your XAMPP installation.

## Solution

### **Step 1: Locate php.ini File**

1. Open XAMPP Control Panel
2. Click **"Config"** button next to Apache
3. Select **"PHP (php.ini)"**

**OR**

Navigate to: `C:\xampp\php\php.ini`

### **Step 2: Enable ZIP Extension**

1. Open `php.ini` in a text editor (Notepad++)
2. Search for: `extension=zip`
3. Find the line that looks like:
   ```ini
   ;extension=zip
   ```
4. Remove the semicolon (`;`) to uncomment it:
   ```ini
   extension=zip
   ```
5. Save the file

### **Step 3: Restart Apache**

1. Open XAMPP Control Panel
2. Click **"Stop"** on Apache
3. Wait 2-3 seconds
4. Click **"Start"** on Apache

### **Step 4: Verify ZIP Extension is Enabled**

Create a test file: `C:\xampp\htdocs\test_zip.php`

```php
<?php
if (class_exists('ZipArchive')) {
    echo "✅ ZIP extension is ENABLED";
} else {
    echo "❌ ZIP extension is NOT enabled";
}

echo "<br><br>";
echo "Loaded Extensions:<br>";
print_r(get_loaded_extensions());
?>
```

Visit: `http://localhost/test_zip.php`

You should see: **"✅ ZIP extension is ENABLED"**

---

## Alternative: Quick Fix via XAMPP Shell

1. Open XAMPP Control Panel
2. Click **"Shell"** button
3. Run:
   ```bash
   php -m | grep zip
   ```
4. If nothing appears, ZIP is not enabled
5. Edit php.ini as described above
6. Restart Apache
7. Run command again - you should see "zip" in the output

---

## Common Issues

### **Issue 1: Still not working after uncommenting**

**Solution:** Make sure you edited the correct php.ini file.

XAMPP may have multiple php.ini files:
- `C:\xampp\php\php.ini` (CLI version)
- `C:\xampp\apache\bin\php.ini` (Apache version)

Check which one Apache uses:
```php
<?php phpinfo(); ?>
```
Look for "Loaded Configuration File"

### **Issue 2: Can't find `;extension=zip`**

**Solution:** The line might already be uncommented or use a different format.

Search for these variations:
- `extension=zip`
- `;extension=php_zip.dll`
- `extension=php_zip.dll`

For Windows, it might be:
```ini
extension=php_zip.dll
```

### **Issue 3: ZIP file is in extensions directory**

Verify `php_zip.dll` exists in:
```
C:\xampp\php\ext\php_zip.dll
```

If missing, reinstall XAMPP or download the DLL file.

---

## For Production Servers

### **Linux/Ubuntu:**
```bash
sudo apt-get install php-zip
sudo systemctl restart apache2
```

### **CentOS/RHEL:**
```bash
sudo yum install php-zip
sudo systemctl restart httpd
```

### **Check if installed:**
```bash
php -m | grep zip
```

---

## Verification Checklist

- [ ] Located correct php.ini file
- [ ] Uncommented `extension=zip`
- [ ] Saved php.ini file
- [ ] Restarted Apache server
- [ ] Verified with test script
- [ ] Export functionality working

---

## Quick Reference

**XAMPP php.ini location:**
```
C:\xampp\php\php.ini
```

**Line to uncomment:**
```ini
extension=zip
```

**Restart Apache:**
XAMPP Control Panel → Stop Apache → Start Apache

**Test URL:**
```
http://localhost/test_zip.php
```

---

## After Enabling ZIP Extension

The document export functionality will now work:

1. **Export Selected Files (ZIP)** - Downloads checked documents as ZIP
2. **Export All Files (ZIP)** - Downloads all visible documents as ZIP

Both features require the ZIP extension to package multiple files into a single archive.

---

## Need Help?

If you still encounter issues:

1. Check Apache error log:
   - XAMPP Control Panel → Apache → Logs → Error Log
   
2. Check PHP version compatibility:
   ```php
   <?php echo phpversion(); ?>
   ```
   
3. Verify extension directory in php.ini:
   ```ini
   extension_dir = "C:\xampp\php\ext"
   ```

4. Try alternative:
   - Use individual file downloads instead of ZIP
   - Or upgrade to latest XAMPP version

---

**Status after fix:** ✅ ZIP extension enabled and working
**Date:** November 22, 2025
