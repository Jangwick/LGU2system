@echo off
echo ========================================
echo Running Migration: Add User Profile Fields
echo ========================================
echo.

set MYSQL_PATH=C:\xampp\mysql\bin
set DB_NAME=lrms_db
set DB_USER=root
set DB_PASS=

echo Connecting to database: %DB_NAME%
echo.

"%MYSQL_PATH%\mysql.exe" -u %DB_USER% --password=%DB_PASS% %DB_NAME% < 004_add_user_profile_fields.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo Migration completed successfully!
    echo ========================================
) else (
    echo.
    echo ========================================
    echo ERROR: Migration failed!
    echo Please check the error message above.
    echo ========================================
)

echo.
pause
