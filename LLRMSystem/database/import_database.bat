@echo off
echo ====================================
echo LRMS Database Import Script
echo ====================================
echo.

cd C:\xampp\mysql\bin

echo Importing database schema...
echo.
echo NOTE: If MySQL asks for a password, just press ENTER (default XAMPP has no password)
echo.

mysql.exe -u root -p < "C:\xampp\htdocs\LLRMSystem\database\schema.sql"

if %errorlevel% equ 0 (
    echo.
    echo ====================================
    echo SUCCESS! Database imported successfully!
    echo ====================================
    echo.
    echo Database: lrms_db
    echo Tables created: 9
    echo Test users created: 4
    echo.
    echo You can now access the system at:
    echo http://localhost/LLRMSystem/LLRMSystem/auth/login.php
    echo.
    echo Test Account:
    echo Email: admin@lgu.gov.ph
    echo Password: Admin@123
    echo.
) else (
    echo.
    echo ====================================
    echo ERROR: Database import failed!
    echo ====================================
    echo.
    echo Please try using phpMyAdmin instead:
    echo 1. Open http://localhost/phpmyadmin
    echo 2. Click 'Import' tab
    echo 3. Choose file: C:\xampp\htdocs\LLRMSystem\database\schema.sql
    echo 4. Click 'Go'
    echo.
)

pause
