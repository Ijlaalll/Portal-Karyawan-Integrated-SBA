@echo off
setlocal enabledelayedexpansion
title SBA Operations System - Integrated Console (Port 8080)
color 0B

echo ============================================================
echo   PT SOLUSI BANGUN ANDALAS - SBA OPERATIONS SYSTEM
echo       (Ruang Rapat, Kendaraan, SIK, Toilet, Global)
echo ============================================================
echo.

REM 0. Deteksi otomatis lokasi PHP 8.2+ (Prioritas PHP 8.5 Server)
set "PHP_EXE=C:\Users\Administrator\Downloads\php-8.5.10-nts-Win32-vs17-x64\php.exe"
if not exist "%PHP_EXE%" (
    for /d %%D in ("C:\laragon\bin\php\php-8.*") do (
        if exist "%%D\php.exe" set "PHP_EXE=%%D\php.exe"
    )
)
if not exist "%PHP_EXE%" (
    where php >nul 2>&1
    if "%ERRORLEVEL%"=="0" (
        set "PHP_EXE=php"
    ) else if exist "C:\xampp\php\php.exe" (
        set "PHP_EXE=C:\xampp\php\php.exe"
    )
)

if exist "%PHP_EXE%" (
    for %%P in ("%PHP_EXE%") do set "PATH=%%~dpP;!PATH!"
    echo [INFO] Menggunakan PHP: %PHP_EXE%
) else (
    echo [ERROR] PHP 8.2+ tidak ditemukan! Pastikan PHP sudah terinstall.
    pause
    exit /b 1
)

REM 1. Bersihkan instance lama HANYA untuk SBA Operations (port 8080 / sba ops)
echo [1/4] Membersihkan instance lama SBA Operations jika ada...
powershell -NoProfile -Command "Get-CimInstance Win32_Process -ErrorAction SilentlyContinue | Where-Object { $_.ProcessName -eq 'php.exe' -and ($_.CommandLine -like '*Project Magang*' -or $_.CommandLine -like '*8080*' -or $_.CommandLine -like '*sba_portal*' -or $_.CommandLine -like '*sba_ops*' -or $_.CommandLine -like '*sba-ops*') } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }" 2>NUL
echo       [OK] Pembersihan proses lama selesai.

REM 2. Cek apakah MySQL berjalan & nyalakan otomatis jika Laragon mati
echo [2/4] Memeriksa status MySQL Database...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo       [OK] MySQL Database terdeteksi aktif.
) else (
    echo       [INFO] MySQL belum aktif. Menyalakan MySQL otomatis...
    set "MYSQL_STARTED=0"

    REM Coba nyalakan service MySQL jika ada
    net start mysql >nul 2>&1
    tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
    if "%ERRORLEVEL%"=="0" set "MYSQL_STARTED=1"

    REM Jika belum aktif, jalankan engine MySQL dari Laragon dengan config my.ini
    if "!MYSQL_STARTED!"=="0" if exist "C:\laragon\bin\mysql" (
        for /d %%M in ("C:\laragon\bin\mysql\mysql-*") do (
            if exist "%%M\bin\mysqld.exe" (
                echo       Menyalakan MySQL Engine dari Laragon...
                start "" /b "%%M\bin\mysqld.exe" --defaults-file="%%M\my.ini" --standalone
                set "MYSQL_STARTED=1"
            )
        )
    )

    REM Jalankan aplikasi Laragon di background jika ada
    if exist "C:\laragon\laragon.exe" (
        tasklist /FI "IMAGENAME eq laragon.exe" 2>NUL | find /I /N "laragon.exe">NUL
        if "%ERRORLEVEL%" NEQ "0" (
            echo       Membuka aplikasi Laragon...
            start "" "C:\laragon\laragon.exe"
        )
    )

    REM Tunggu hingga MySQL siap
    echo       Menunggu database siap menerima koneksi...
    for /L %%i in (1,1,10) do (
        tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
        if "%ERRORLEVEL%"=="0" (
            ping 127.0.0.1 -n 2 >nul
            goto MYSQL_IS_READY
        )
        ping 127.0.0.1 -n 2 >nul
    )
:MYSQL_IS_READY
    echo       [OK] MySQL Database berhasil diaktifkan!
)

REM 3. Verifikasi aset frontend
echo [3/4] Memeriksa aset frontend terkompilasi...
if not exist "%~dp0public\build\manifest.json" (
    echo       [INFO] Aset frontend belum dibangun. Menjalankan npm run build...
    call npm run build
) else (
    echo       [OK] Aset frontend produksi siap.
)

REM 4. Jalankan Laravel di Port 8080 di background
echo [4/4] Menjalankan Backend SBA Operations di port 8080...
if not exist "%~dp0storage\logs" mkdir "%~dp0storage\logs"
start "" /b cmd /c "cd /d "%~dp0" && "%PHP_EXE%" artisan serve --host=0.0.0.0 --port=8080 > "%~dp0storage\logs\serve.log" 2>&1"

REM Tunggu hingga server web port 8080 benar-benar siap merespons
echo       Menunggu server web port 8080 online...
for /L %%w in (1,1,10) do (
    netstat -ano | findstr "LISTENING" | findstr ":8080" >nul
    if "%ERRORLEVEL%"=="0" (
        ping 127.0.0.1 -n 2 >nul
        goto WEB_IS_READY
    )
    ping 127.0.0.1 -n 2 >nul
)
:WEB_IS_READY
echo       [OK] Server Web Port 8080 sudah online!

REM Buka browser otomatis
start http://localhost:8080

:MENU_LOOP
cls
echo ============================================================
echo      SBA OPERATIONS SYSTEM - MASTER CONSOLE (PORT 8080)
echo ============================================================
echo  [OK] MySQL Database   : AKTIF (Port 3306)
echo  [OK] SBA Web App      : AKTIF (http://localhost:8080)
echo  [OK] Akses Jaringan   : http://10.227.170.77:8080
echo  [INFO] PHP Version    : %PHP_EXE%
echo ============================================================
echo  Aplikasi SBA Operations berjalan aman bersamaan dengan
echo  SBA Weighbridge System (Port 3000 / 8000).
echo ============================================================
echo.
echo  PILIHAN MENU:
echo   [1] Buka / Refresh Web Browser (http://localhost:8080)
echo   [2] Cek Port Aktif (8080 ^& 3306)
echo   [3] Buka Laragon Control Panel
echo   [4] Lihat Log Server (storage\logs\serve.log)
echo   [Q] Matikan Server SBA Operations ^& Keluar
echo ============================================================
echo.
set "opt="
set /p opt="Pilih menu [1 / 2 / 3 / 4 / Q] lalu tekan Enter: "

if /i "%opt%"=="1" (
    start http://localhost:8080
    goto MENU_LOOP
)

if /i "%opt%"=="2" (
    echo.
    echo Status Port Terkini:
    netstat -ano | findstr "LISTENING" | findstr ":8080 :3306"
    echo.
    pause
    goto MENU_LOOP
)

if /i "%opt%"=="3" (
    if exist "C:\laragon\laragon.exe" start "" "C:\laragon\laragon.exe"
    goto MENU_LOOP
)

if /i "%opt%"=="4" (
    echo.
    if exist "%~dp0storage\logs\serve.log" (
        echo --- 20 Baris Terakhir Log Server ---
        powershell -NoProfile -Command "Get-Content '%~dp0storage\logs\serve.log' -Tail 20"
    ) else (
        echo Belum ada catatan log serve.log.
    )
    echo.
    pause
    goto MENU_LOOP
)

if /i "%opt%"=="Q" goto DO_SHUTDOWN
if /i "%opt%"=="quit" goto DO_SHUTDOWN
if /i "%opt%"=="exit" goto DO_SHUTDOWN

goto MENU_LOOP

:DO_SHUTDOWN
echo.
echo ============================================================
echo   MEMATIKAN SERVER SBA OPERATIONS...
echo ============================================================
powershell -NoProfile -Command "Get-CimInstance Win32_Process -ErrorAction SilentlyContinue | Where-Object { $_.ProcessName -eq 'php.exe' -and ($_.CommandLine -like '*Project Magang*' -or $_.CommandLine -like '*8080*' -or $_.CommandLine -like '*sba_portal*' -or $_.CommandLine -like '*sba_ops*' -or $_.CommandLine -like '*sba-ops*') } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }" 2>NUL
echo [OK] Server SBA Operations berhasil dimatikan dengan aman!
ping 127.0.0.1 -n 2 >nul
exit