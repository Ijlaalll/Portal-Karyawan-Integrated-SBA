@echo off
setlocal enabledelayedexpansion
title Uji Coba LDAP Active Directory - SBA Operations
color 0B

echo ============================================================
echo   UJI KONEKSI DAN AUTENTIKASI LDAP ACTIVE DIRECTORY (SIG)
echo ============================================================
echo.

REM Deteksi otomatis PHP 8.2+ (Prioritas E:\Applications\laragon, E:\laragon, Drive C)
set "PHP_EXE="

REM 0.1 Cek E:\Applications\laragon (Lokasi Server Terkini)
if not defined PHP_EXE (
    for /d %%D in ("E:\Applications\laragon\bin\php\php-8.*") do (
        if exist "%%D\php.exe" (
            "%%D\php.exe" -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);" >nul 2>&1
            if not errorlevel 1 (
                if not defined PHP_EXE set "PHP_EXE=%%D\php.exe"
            )
        )
    )
)

REM 0.2 Cek Drive E: (Lokasi Server Lainnya)
if not defined PHP_EXE (
    for /d %%D in ("E:\laragon\bin\php\php-8.*") do (
        if exist "%%D\php.exe" (
            "%%D\php.exe" -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);" >nul 2>&1
            if not errorlevel 1 (
                if not defined PHP_EXE set "PHP_EXE=%%D\php.exe"
            )
        )
    )
)

if not defined PHP_EXE (
    if exist "E:\xampp\php\php.exe" (
        "E:\xampp\php\php.exe" -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);" >nul 2>&1
        if not errorlevel 1 set "PHP_EXE=E:\xampp\php\php.exe"
    )
)

REM 0.3 Cek Drive C: (Fallback)
if not defined PHP_EXE (
    if exist "C:\Users\Administrator\Downloads\php-8.5.10-nts-Win32-vs17-x64\php.exe" (
        "C:\Users\Administrator\Downloads\php-8.5.10-nts-Win32-vs17-x64\php.exe" -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);" >nul 2>&1
        if not errorlevel 1 set "PHP_EXE=C:\Users\Administrator\Downloads\php-8.5.10-nts-Win32-vs17-x64\php.exe"
    )
)

if not defined PHP_EXE (
    for /d %%D in ("C:\laragon\bin\php\php-8.*") do (
        if exist "%%D\php.exe" (
            "%%D\php.exe" -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);" >nul 2>&1
            if not errorlevel 1 (
                if not defined PHP_EXE set "PHP_EXE=%%D\php.exe"
            )
        )
    )
)

if not defined PHP_EXE (
    if exist "C:\xampp\php\php.exe" (
        "C:\xampp\php\php.exe" -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);" >nul 2>&1
        if not errorlevel 1 set "PHP_EXE=C:\xampp\php\php.exe"
    )
)

if not defined PHP_EXE (
    where php >nul 2>&1
    if not errorlevel 1 (
        php -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);" >nul 2>&1
        if not errorlevel 1 set "PHP_EXE=php"
    )
)

if not defined PHP_EXE (
    echo [ERROR] PHP 8.2+ tidak ditemukan di sistem!
    echo Pastikan PHP 8.2 atau lebih baru terinstall di Drive E/C, Laragon, XAMPP, atau terdaftar di PATH.
    pause
    exit /b 1
)

for %%P in ("%PHP_EXE%") do set "PATH=%%~dpP;!PATH!"

echo [INFO] Menggunakan PHP: %PHP_EXE%
echo.

"%PHP_EXE%" artisan ldap:test %*

echo.
pause
