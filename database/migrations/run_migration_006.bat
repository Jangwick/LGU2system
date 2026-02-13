@echo off
set "DB_USER=root"
set "DB_PASS="
set "DB_NAME=lrms_db"
set "MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe"

echo Creating document_embeddings table...
"%MYSQL_PATH%" -u%DB_USER% %DB_PASS% %DB_NAME% < "006_create_document_embeddings_table.sql"

if %ERRORLEVEL% equ 0 (
    echo Migration successful!
) else (
    echo Migration failed. Please check your MySQL settings in this batch file.
)
pause
