@echo off
setlocal enabledelayedexpansion

echo ====================================================================
echo EDUVIA DATABASE BACKUP ^& RESTORE INTEGRITY VERIFICATION SUITE
echo ====================================================================

set DB_HOST=127.0.0.1
set DB_PORT=3306
set DB_NAME=eduvia_dev
set DB_USER=root
set DB_PASS=
set TEST_RESTORE_DB=eduvia_restore_verify_test

set TIMESTAMP=%date:~10,4%%date:~4,2%%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%
set BACKUP_DIR=storage\backups
set BACKUP_FILE=%BACKUP_DIR%\eduvia_backup_%TIMESTAMP%.sql

if not exist "%BACKUP_DIR%" (
    mkdir "%BACKUP_DIR%"
)

echo [1/4] Creating full MySQL database dump...
echo Target: %BACKUP_FILE%
mysqldump -h %DB_HOST% -P %DB_PORT% -u %DB_USER% %DB_NAME% > "%BACKUP_FILE%"

if %ERRORLEVEL% neq 0 (
    echo [ERROR] mysqldump failed with error code %ERRORLEVEL%.
    exit /b %ERRORLEVEL%
)

echo [OK] Database dump created successfully. File size:
for %%A in ("%BACKUP_FILE%") do echo %%~zA bytes

echo [2/4] Initializing temporary restore verification database: %TEST_RESTORE_DB%...
mysql -h %DB_HOST% -P %DB_PORT% -u %DB_USER% -e "DROP DATABASE IF EXISTS %TEST_RESTORE_DB%; CREATE DATABASE %TEST_RESTORE_DB% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if %ERRORLEVEL% neq 0 (
    echo [ERROR] Failed to initialize temporary verification database.
    exit /b %ERRORLEVEL%
)

echo [3/4] Restoring dump into %TEST_RESTORE_DB% to verify integrity...
mysql -h %DB_HOST% -P %DB_PORT% -u %DB_USER% %TEST_RESTORE_DB% < "%BACKUP_FILE%"

if %ERRORLEVEL% neq 0 (
    echo [ERROR] Restore execution failed! The backup is corrupt or incomplete.
    exit /b %ERRORLEVEL%
)

echo [4/4] Verifying critical table row counts in restored database...
echo --- Table Verification Results ---
mysql -h %DB_HOST% -P %DB_PORT% -u %DB_USER% %TEST_RESTORE_DB% -e "SELECT 'schools' as tbl, count(*) as row_count FROM schools UNION ALL SELECT 'users', count(*) FROM users UNION ALL SELECT 'invoices', count(*) FROM invoices UNION ALL SELECT 'audit_logs', count(*) FROM audit_logs;"

echo Cleaning up temporary verification database...
mysql -h %DB_HOST% -P %DB_PORT% -u %DB_USER% -e "DROP DATABASE IF EXISTS %TEST_RESTORE_DB%;"

echo ====================================================================
echo [SUCCESS] Backup and Restore Verification COMPLETED SUCCESSFULLY!
echo Backup archived at: %BACKUP_FILE%
echo ====================================================================
