@echo off
setlocal enabledelayedexpansion
title Uji Coba LDAP Active Directory - SBA Operations
color 0B

echo ============================================================
echo   UJI KONEKSI DAN AUTENTIKASI LDAP ACTIVE DIRECTORY (SIG)
echo ============================================================
echo.

REM Deteksi otomatis PHP 8.2+ (Prioritas Drive E, Laragon, XAMPP, Drive C)
set "PHP_EXE="

REM 0.1 Cek Drive E: (Lokasi Server Baru)
if exist "E:\php-8.5.10-nts-Win32-vs17-x64\php.exe" (
    set "PHP_EXE=E:\php-8.5.10-nts-Win32-vs17-x64\php.exe"
)

if not defined PHP_EXE (
    for /d %%D in ("E:\laragon\bin\php\php-8.*") do (
        if exist "%%D\php.exe" (
            if not defined PHP_EXE set "PHP_EXE=%%D\php.exe"
        )
    )
)

if not defined PHP_EXE (
    if exist "E:\xampp\php\php.exe" (
        set "PHP_EXE=E:\xampp\php\php.exe"
    )
)

REM 0.2 Cek Drive C: (Fallback)
if not defined PHP_EXE (
    if exist "C:\Users\Administrator\Downloads\php-8.5.10-nts-Win32-vs17-x64\php.exe" (
        set "PHP_EXE=C:\Users\Administrator\Downloads\php-8.5.10-nts-Win32-vs17-x64\php.exe"
    )
)

if not defined PHP_EXE (
    for /d %%D in ("C:\laragon\bin\php\php-8.*") do (
        if exist "%%D\php.exe" (
            if not defined PHP_EXE set "PHP_EXE=%%D\php.exe"
        )
    )
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
    echo Pastikan PHP terinstall di Drive E/C, Laragon, XAMPP, atau terdaftar di PATH.
    pause
    exit /b 1
)

for %%P in ("%PHP_EXE%") do set "PATH=%%~dpP;!PATH!"

echo [INFO] Menggunakan PHP: %PHP_EXE%
echo.

"%PHP_EXE%" artisan ldap:test %*

echo.
pause
