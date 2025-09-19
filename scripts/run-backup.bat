@echo off
REM Prime EMS Database Backup Runner
REM Bu script veritabanı yedekleme script'ini çalıştırır

echo Prime EMS Database Backup Starting...
echo %DATE% %TIME%

REM Proje dizinine git
cd /d "%~dp0.."

REM PHP script'ini çalıştır
"C:\xampp\php\php.exe" scripts\backup-database.php

if %ERRORLEVEL% EQU 0 (
    echo Backup completed successfully at %DATE% %TIME%
) else (
    echo Backup failed with error code %ERRORLEVEL% at %DATE% %TIME%
)

echo Backup process finished.
pause