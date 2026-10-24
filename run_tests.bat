@echo off
TITLE ElectroGuard Acceptance Tests - GodsPlanKangra
echo ======================================================================
echo   RUNNING ELECTROGUARD AUTOMATED ACCEPTANCE TESTS
echo ======================================================================

set PHP_BIN=php
if exist "C:\xampp\php\php.exe" (
    set PHP_BIN="C:\xampp\php\php.exe"
)

%PHP_BIN% tests/AcceptanceTests.php
pause
