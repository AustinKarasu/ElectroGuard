@echo off
TITLE ElectroGuard Server - GodsPlanKangra
echo ======================================================================
echo   ELECTROGUARD: Smart Electrical Worker Safety Coordination System
echo   Team: GodsPlanKangra (Aayan Parmar, Manjinder Uldeen, Aayush Parmar)
echo ======================================================================
echo.

set PHP_BIN=php
if exist "C:\xampp\php\php.exe" (
    set PHP_BIN="C:\xampp\php\php.exe"
)

echo Checking PHP runtime...
%PHP_BIN% -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PHP is not found in PATH and not found at C:\xampp\php\php.exe.
    echo Please install PHP 8.0+ or XAMPP to run ElectroGuard.
    pause
    exit /b 1
)

echo Initializing database migrations and demonstration seed data...
%PHP_BIN% database/migrate.php

echo.
echo Starting ElectroGuard local development server at http://localhost:8000 ...
echo Press Ctrl+C at any time to stop the server.
echo.

%PHP_BIN% -S localhost:8000 -t public
pause
