@echo off
echo =========================================
echo  LRMS - Notifications Table Migration
echo =========================================
echo.

set MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe
set DB_HOST=localhost
set DB_USER=root
set DB_PASS=
set DB_NAME=lrms_db

echo Running notifications table migration...
"%MYSQL_PATH%" -h %DB_HOST% -u %DB_USER% %DB_NAME% < "%~dp0migrations\create_notifications_table.sql"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [SUCCESS] Notifications tables created successfully!
    echo.
    echo Tables created:
    echo   - notifications
    echo   - integration_api_keys
    echo.
) else (
    echo.
    echo [ERROR] Failed to create tables. Please check your database connection.
    echo.
)

pause
