@echo off
echo ========================================
echo  LRMS Activity Logs Migration
echo  Migration: 002_update_activity_logs
echo ========================================
echo.

REM Configuration
set MYSQL_PATH=C:\xampp\mysql\bin
set DB_NAME=lrms_db
set DB_USER=root
set DB_PASS=

echo Running migration...
echo.

"%MYSQL_PATH%\mysql.exe" -u %DB_USER% %DB_NAME% < 002_update_activity_logs.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo  Migration completed successfully!
    echo ========================================
    echo.
    echo The activity_logs table has been updated with:
    echo  - table_name column
    echo  - record_id column
    echo.
    echo The users table has been updated with:
    echo  - username column
    echo  - full_name column
    echo.
) else (
    echo.
    echo ========================================
    echo  ERROR: Migration failed!
    echo ========================================
    echo.
    echo Please check:
    echo  1. MySQL is running
    echo  2. Database credentials are correct
    echo  3. Database 'lrms_db' exists
    echo.
)

pause
