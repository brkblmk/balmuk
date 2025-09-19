@echo off
REM Prime EMS Maintenance Runner
REM Bu script bakım script'ini çalıştırır

echo Prime EMS Maintenance Started...
echo %DATE% %TIME%

REM Proje dizinine git
cd /d "%~dp0.."

REM PHP script'ini çalıştır
"C:\xampp\php\php.exe" scripts\maintenance.php run

if %ERRORLEVEL% EQU 0 (
    echo Maintenance completed successfully at %DATE% %TIME%
) else (
    echo Maintenance failed with error code %ERRORLEVEL% at %DATE% %TIME%
)

echo Maintenance process finished.
pause