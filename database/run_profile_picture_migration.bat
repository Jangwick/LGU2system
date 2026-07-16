@echo off
echo Running Profile Picture Migration...
echo.

cd /d "%~dp0.."

rem MySQL connection details
set MYSQL_HOST=localhost
set MYSQL_USER=root
set MYSQL_PASS=
set MYSQL_DB=lrms_db

rem Run migration
mysql -h%MYSQL_HOST% -u%MYSQL_USER% %MYSQL_DB% < migrations\add_profile_picture_column.sql

if %errorlevel% equ 0 (
    echo.
    echo ====================================
    echo Migration completed successfully!
    echo ====================================
    echo.
    echo Profile picture column has been added to users table.
    echo.
) else (
    echo.
    echo ====================================
    echo ERROR: Migration failed!
    echo ====================================
    echo.
)

pause
