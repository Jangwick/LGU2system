@echo off
set "DB_USER=root"
set "DB_PASS="
set "DB_NAME=lrms_db"

echo Running OTP table migration...
mysql -u %DB_USER% %DB_PASS% %DB_NAME% < "c:\xampp\htdocs\LGU2system\LLRMSystem\database\migrations\007_create_user_otps_table.sql"

if %ERRORLEVEL% equ 0 (
    echo Migration completed successfully.
) else (
    echo Error running migration.
)
pause