@echo off
REM Prime EMS Performance Report Scheduler Setup
REM Bu script günlük performans raporları için Windows Task Scheduler'ı yapılandırır

echo Setting up automatic performance reporting...
echo %DATE% %TIME%

REM Yönetici yetkisi kontrolü
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Running with administrator privileges
) else (
    echo Please run this script as administrator for task scheduler setup
    pause
    exit /b 1
)

REM Görev adını tanımla
set TASK_NAME="PrimeEMS_Performance_Report"

REM Mevcut görevi sil (varsa)
schtasks /delete /tn %TASK_NAME% /f >nul 2>&1

REM Yeni görevi oluştur - günlük saat 02:00'da çalıştır
schtasks /create /tn %TASK_NAME% /tr "%~dp0run-performance-report.bat" /sc daily /st 02:00 /ru "SYSTEM" /rl highest /f

if %ERRORLEVEL% EQU 0 (
    echo Performance report scheduler created successfully
    echo Task will run daily at 02:00 AM
    echo Task name: %TASK_NAME%
) else (
    echo Failed to create performance report scheduler
    echo Error code: %ERRORLEVEL%
)

REM Görevi test et
echo Testing performance report generation...
call "%~dp0run-performance-report.bat"

echo Scheduler setup completed.
pause