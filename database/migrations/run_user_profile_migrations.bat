@echo off
echo ========================================
echo Running Database Migrations for User Profile Features
echo ========================================
echo.

set MYSQL_PATH=C:\xampp\mysql\bin
set DB_NAME=lrms_db
set DB_USER=root
set DB_PASS=

echo Connecting to database: %DB_NAME%
echo.

echo [1/2] Creating user_preferences table...
"%MYSQL_PATH%\mysql.exe" -u %DB_USER% --password=%DB_PASS% %DB_NAME% < 003_create_user_preferences.sql

if %ERRORLEVEL% EQU 0 (
    echo [1/2] ✓ User preferences table created successfully!
) else (
    echo [1/2] ✗ Failed to create user preferences table!
    goto error
)

echo.
echo [2/2] Adding phone and position columns to users table...
"%MYSQL_PATH%\mysql.exe" -u %DB_USER% --password=%DB_PASS% %DB_NAME% < 004_add_user_profile_fields.sql

if %ERRORLEVEL% EQU 0 (
    echo [2/2] ✓ User profile fields added successfully!
) else (
    echo [2/2] ✗ Failed to add user profile fields!
    goto error
)

echo.
echo ========================================
echo All migrations completed successfully!
echo ========================================
echo.
echo You can now use:
echo - My Profile page (edit profile, change password)
echo - Settings page (preferences and notifications)
echo - Help ^& Support page (FAQs and contact)
echo.
goto end

:error
echo.
echo ========================================
echo ERROR: One or more migrations failed!
echo Please check the error messages above.
echo ========================================
echo.

:end
pause
