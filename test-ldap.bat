@echo off
setlocal enabledelayedexpansion
title Uji Coba LDAP Active Directory - SBA Operations
color 0B

echo ============================================================
echo   UJI KONEKSI DAN AUTENTIKASI LDAP ACTIVE DIRECTORY (SIG)
echo ============================================================
echo.

REM Deteksi otomatis PHP 8.2+
set "PHP_EXE="

if exist "C:\Users\Administrator\Downloads\php-8.5.10-nts-Win32-vs17-x64\php.exe" (
    set "PHP_EXE=C:\Users\Administrator\Downloads\php-8.5.10-nts-Win32-vs17-x64\php.exe"
)

if not defined PHP_EXE (
    if exist "C:\xampp\php\php.exe" (
        set "PHP_EXE=C:\xampp\php\php.exe"
    )
)

if not defined PHP_EXE (
    where php >nul 2>&1
    if not errorlevel 1 set "PHP_EXE=php"
)

if not defined PHP_EXE (
    echo [ERROR] PHP tidak ditemukan di sistem!
    echo Pastikan PHP terinstall di C:\xampp\php atau Downloads.
    pause
    exit /b 1
)

for %%P in ("%PHP_EXE%") do set "PATH=%%~dpP;!PATH!"

echo [INFO] Menggunakan PHP: %PHP_EXE%
echo.

"%PHP_EXE%" artisan ldap:test %*

echo.
pause
