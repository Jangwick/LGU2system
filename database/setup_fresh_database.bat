@echo off
echo ========================================
echo LRMS Database Fresh Install
echo ========================================
echo.
echo This script will create a fresh database with all tables.
echo.
echo IMPORTANT: Make sure MySQL is RUNNING in XAMPP!
echo.
pause

set MYSQL_PATH=C:\xampp\mysql\bin
set DB_NAME=lrms_db
set DB_USER=root
set DB_PASS=

echo.
echo [Step 1/4] Creating database...
"%MYSQL_PATH%\mysql.exe" -u %DB_USER% --password=%DB_PASS% -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to create database
    echo Make sure MySQL is running in XAMPP!
    goto error
)

echo Database created successfully
echo.

echo [Step 2/4] Importing schema...
"%MYSQL_PATH%\mysql.exe" -u %DB_USER% --password=%DB_PASS% %DB_NAME% < schema.sql

if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to import schema
    goto error
)

echo Schema imported successfully
echo.

echo [Step 3/4] Running migrations...
cd migrations

echo - Running migration 003...
"%MYSQL_PATH%\mysql.exe" -u %DB_USER% --password=%DB_PASS% %DB_NAME% < 003_create_user_preferences.sql

echo - Running migration 004...
"%MYSQL_PATH%\mysql.exe" -u %DB_USER% --password=%DB_PASS% %DB_NAME% < 004_add_user_profile_fields.sql

cd ..
echo Migrations completed
echo.

echo [Step 4/4] Creating admin user...
"%MYSQL_PATH%\mysql.exe" -u %DB_USER% --password=%DB_PASS% %DB_NAME% -e "INSERT INTO users (name, full_name, email, username, password, role, department, status) VALUES ('Admin User', 'Admin User', 'admin@lgu.gov.ph', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrator', 'IT Department', 'active');"

echo Admin user created
echo.

echo ========================================
echo SUCCESS! Database setup completed!
echo ========================================
echo.
echo Database: %DB_NAME%
echo Tables created successfully
echo.
echo Login Credentials:
echo URL: http://localhost/LLRMSystem/
echo Email: admin@lgu.gov.ph
echo Password: password
echo.
echo You can now access the system!
echo.
goto end

:error
echo.
echo ========================================
echo ERROR: Setup failed!
echo ========================================
echo.
echo Troubleshooting:
echo 1. Make sure MySQL is RUNNING (green) in XAMPP
echo 2. Check C:\xampp\mysql\data\mysql_error.log for details
echo 3. Try starting XAMPP as Administrator
echo.

:end
pause
