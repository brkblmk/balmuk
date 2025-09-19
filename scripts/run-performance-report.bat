@echo off
REM Prime EMS Performance Report Generator
REM Bu script otomatik performans raporları oluşturur

echo Prime EMS Performance Report Started...
echo %DATE% %TIME%

REM Proje ana dizinine git
cd /d "C:\xampp\htdocs"

REM PHP script'ini çalıştır (sadece rapor oluşturma)
"C:\xampp\php\php.exe" scripts\maintenance.php report

if %ERRORLEVEL% EQU 0 (
    echo Performance report generated successfully at %DATE% %TIME%
) else (
    echo Performance report failed with error code %ERRORLEVEL% at %DATE% %TIME%
)

echo Performance report process finished.