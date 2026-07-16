@echo off
echo ========================================
echo XAMPP MySQL Auto-Fix Script
echo ========================================
echo.
echo This script will automatically fix MySQL startup issues
echo by restoring from XAMPP's backup folder.
echo.
echo IMPORTANT: 
echo 1. Make sure MySQL is STOPPED in XAMPP Control Panel
echo 2. This will preserve your databases (lrms_db)
echo.
set /p confirm="Do you want to continue? (Y/N): "
if /i not "%confirm%"=="Y" goto :end

echo.
echo [Step 1/6] Checking XAMPP paths...
set XAMPP_PATH=C:\xampp
set MYSQL_DATA=%XAMPP_PATH%\mysql\data
set MYSQL_BACKUP=%XAMPP_PATH%\mysql\backup

if not exist "%XAMPP_PATH%" (
    echo ERROR: XAMPP not found at %XAMPP_PATH%
    echo Please edit this script and set the correct XAMPP path
    goto :end
)

echo Found XAMPP at: %XAMPP_PATH%
echo.

echo [Step 2/6] Creating safety backup...
set TIMESTAMP=%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%
set SAFETY_BACKUP=%XAMPP_PATH%\mysql\data_backup_%TIMESTAMP%

xcopy "%MYSQL_DATA%" "%SAFETY_BACKUP%" /E /I /H /Y /Q
if errorlevel 1 (
    echo ERROR: Failed to create backup
    goto :end
)
echo Backup created at: %SAFETY_BACKUP%
echo.

echo [Step 3/6] Removing corrupted files...
cd /d "%MYSQL_DATA%"
del /F /Q ibdata1 2>nul
del /F /Q ib_logfile0 2>nul
del /F /Q ib_logfile1 2>nul
del /F /Q aria_log.* 2>nul
echo Corrupted files removed
echo.

echo [Step 4/6] Restoring from XAMPP backup...
if not exist "%MYSQL_BACKUP%" (
    echo ERROR: Backup folder not found at %MYSQL_BACKUP%
    echo You may need to restore manually
    goto :end
)

xcopy "%MYSQL_BACKUP%\*" "%MYSQL_DATA%" /E /H /Y /Q
echo Backup restored
echo.

echo [Step 5/6] Preserving your databases...
if exist "%SAFETY_BACKUP%\lrms_db" (
    echo Found lrms_db database, restoring...
    xcopy "%SAFETY_BACKUP%\lrms_db" "%MYSQL_DATA%\lrms_db" /E /I /H /Y /Q
    echo Database restored
) else (
    echo No lrms_db found in backup
)
echo.

echo [Step 6/6] Final cleanup...
echo Done!
echo.

echo ========================================
echo MySQL Fix Completed Successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Open XAMPP Control Panel
echo 2. Click START next to MySQL
echo 3. It should start successfully now
echo.
echo If MySQL still won't start:
echo - Check the log: C:\xampp\mysql\data\mysql_error.log
echo - Try running XAMPP as Administrator
echo - See MYSQL_FIX_GUIDE.md for more solutions
echo.
echo Your original data is backed up at:
echo %SAFETY_BACKUP%
echo.
goto :end

:end
pause
