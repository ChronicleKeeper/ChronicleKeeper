@echo off
setlocal EnableDelayedExpansion

echo "Starting Chronicle Keeper with Symfony web server locally, please wait..."

REM Silently kill existing processes
taskkill /F /IM symfony.exe >nul 2>&1
taskkill /F /IM php.exe >nul 2>&1
timeout /t 2 >nul

REM Check prerequisites silently
if not exist "%~dp0php" exit /b 1
if not exist "symfony.exe" exit /b 1

REM Setup PATH
SET OLD_PATH=%PATH%
SET PATH=%PATH%;%~dp0php

REM Start server and capture output
start /B .\symfony.exe local:server:start --no-tls --dir .\www\ >nul 2>&1
timeout /t 5 >nul

REM Launch browser directly
start "" "http://127.0.0.1:8000"

echo "Chronicle Keeper is started, a browser window should have been opened, close window to stop server."

REM Monitor server silently
:mainloop
tasklist /FI "IMAGENAME eq symfony.exe" 2>nul | find /I /N "symfony.exe" >nul
if "%ERRORLEVEL%"=="1" goto cleanup
timeout /t 2 >nul
goto mainloop

:cleanup
taskkill /F /IM symfony.exe >nul 2>&1
taskkill /F /IM php.exe >nul 2>&1
SET PATH=%OLD_PATH%
endlocal
exit /b 0
