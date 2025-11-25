@echo off
echo ========================================
echo XAMPP MySQL Fix - Backup and Restore
echo ========================================
echo.
echo This script will:
echo 1. Backup your current MySQL data
echo 2. Remove corrupted files
echo 3. Restore MySQL to working state
echo.
echo IMPORTANT: Make sure XAMPP MySQL is STOPPED before running this!
echo.
pause

set XAMPP_PATH=C:\xampp
set MYSQL_DATA=%XAMPP_PATH%\mysql\data
set BACKUP_PATH=%XAMPP_PATH%\mysql\backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set BACKUP_PATH=%BACKUP_PATH: =0%

echo.
echo Creating backup directory...
mkdir "%BACKUP_PATH%"

echo.
echo Backing up current data folder...
xcopy "%MYSQL_DATA%" "%BACKUP_PATH%" /E /I /H /Y

echo.
echo Backup completed at: %BACKUP_PATH%
echo.

echo ========================================
echo Next Steps:
echo ========================================
echo 1. The backup is saved at: %BACKUP_PATH%
echo 2. Close this window
echo 3. Follow the manual fix steps provided
echo ========================================
echo.
pause
