@echo off
setlocal enabledelayedexpansion
title SBA Operations System - Integrated Console (Port 8080 / HTTPS 8443)
color 0B

echo ============================================================
echo   PT SOLUSI BANGUN ANDALAS - SBA OPERATIONS SYSTEM
echo       (Ruang Rapat, Kendaraan, SIK, Toilet, Global)
echo ============================================================
echo.

REM 0. Deteksi otomatis lokasi PHP 8.2+ (Prioritas PHP Server / XAMPP / Laragon 8.2+)
set "PHP_EXE="

if exist "C:\Users\Administrator\Downloads\php-8.5.10-nts-Win32-vs17-x64\php.exe" (
    "C:\Users\Administrator\Downloads\php-8.5.10-nts-Win32-vs17-x64\php.exe" -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);" >nul 2>&1
    if not errorlevel 1 set "PHP_EXE=C:\Users\Administrator\Downloads\php-8.5.10-nts-Win32-vs17-x64\php.exe"
)

if not defined PHP_EXE (
    if exist "C:\xampp\php\php.exe" (
        "C:\xampp\php\php.exe" -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);" >nul 2>&1
        if not errorlevel 1 set "PHP_EXE=C:\xampp\php\php.exe"
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
    where php >nul 2>&1
    if not errorlevel 1 (
        php -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);" >nul 2>&1
        if not errorlevel 1 set "PHP_EXE=php"
    )
)

if defined PHP_EXE (
    for %%P in ("%PHP_EXE%") do set "PATH=%%~dpP;!PATH!"
    echo [INFO] Menggunakan PHP: %PHP_EXE%
) else (
    echo [ERROR] PHP 8.2+ tidak ditemukan! Pastikan PHP 8.2 atau lebih baru terinstall.
    pause
    exit /b 1
)

REM 1. Bersihkan instance lama HANYA untuk SBA Operations (port 8080 & 8443)
echo [1/5] Membersihkan instance lama SBA Operations jika ada...
powershell -NoProfile -Command "Get-CimInstance Win32_Process -ErrorAction SilentlyContinue | Where-Object { ($_.ProcessName -eq 'php.exe' -and ($_.CommandLine -like '*Project Magang*' -or $_.CommandLine -like '*8080*' -or $_.CommandLine -like '*sba_portal*' -or $_.CommandLine -like '*sba_ops*' -or $_.CommandLine -like '*sba-ops*')) -or ($_.ProcessName -eq 'caddy.exe' -and ($_.CommandLine -like '*8443*' -or $_.CommandLine -like '*Caddyfile*')) } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }" 2>NUL
echo       [OK] Pembersihan proses lama selesai.

REM 2. Cek apakah MySQL berjalan & nyalakan diam-diam di background jika belum aktif
echo [2/5] Memeriksa status MySQL Database...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo       [OK] MySQL Database terdeteksi aktif.
) else (
    echo       [INFO] MySQL belum aktif. Menyalakan di background...
    net start mysql >nul 2>&1
    if exist "C:\laragon\bin\mysql" (
        for /d %%M in ("C:\laragon\bin\mysql\mysql-*") do (
            if exist "%%M\bin\mysqld.exe" (
                start "" /b "%%M\bin\mysqld.exe" >nul 2>&1
            )
        )
    )
    if exist "C:\xampp\mysql\bin\mysqld.exe" (
        start "" /b "C:\xampp\mysql\bin\mysqld.exe" >nul 2>&1
    )
    ping 127.0.0.1 -n 3 >nul
    echo       [OK] MySQL Database berhasil diaktifkan.
)

REM 3. Verifikasi aset frontend
echo [3/5] Memeriksa aset frontend terkompilasi...
if not exist "%~dp0public\build\manifest.json" (
    echo       [INFO] Aset frontend belum dibangun. Menjalankan npm run build...
    call npm run build
) else (
    echo       [OK] Aset frontend produksi siap.
)

REM 4. Jalankan Laravel di Port 8080 di background
echo [4/5] Menjalankan Backend SBA Operations di port 8080...
if not exist "%~dp0storage\logs" mkdir "%~dp0storage\logs"
start "" /b cmd /c "cd /d "%~dp0" && "%PHP_EXE%" artisan serve --host=0.0.0.0 --port=8080 > "%~dp0storage\logs\serve.log" 2>&1"

REM 5. Jalankan Caddy HTTPS Reverse Proxy di Port 8443 (Untuk Scanner Kamera QR SIK)
echo [5/5] Menjalankan HTTPS Gateway (Caddy) di port 8443...
if not exist "%~dp0caddy.exe" (
    echo       [INFO] Mengunduh binary Caddy untuk HTTPS...
    powershell -NoProfile -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; (New-Object Net.WebClient).DownloadFile('https://github.com/caddyserver/caddy/releases/download/v2.9.1/caddy_2.9.1_windows_amd64.zip', '%~dp0caddy.zip'); Expand-Archive -Path '%~dp0caddy.zip' -DestinationPath '%~dp0caddy_tmp' -Force; Move-Item '%~dp0caddy_tmp\caddy.exe' '%~dp0caddy.exe' -Force; Remove-Item '%~dp0caddy_tmp', '%~dp0caddy.zip' -Recurse -Force" >nul 2>&1
)
if exist "%~dp0caddy.exe" (
    start "" /b cmd /c "cd /d "%~dp0" && "%~dp0caddy.exe" run > "%~dp0storage\logs\caddy.log" 2>&1"
    echo       [OK] HTTPS Gateway aktif di port 8443.
)

REM Beri jeda 3 detik agar server siap
ping 127.0.0.1 -n 4 >nul

REM Deteksi IP LAN untuk akses Satpam
set "LAN_IP=127.0.0.1"
for /f "tokens=2 delims=:" %%I in ('ipconfig ^| findstr /i "IPv4" ^| findstr /v "192.168.116 192.168.190 192.168.137"') do (
    if "!LAN_IP!"=="127.0.0.1" (
        set "TEMP_IP=%%I"
        set "TEMP_IP=!TEMP_IP: =!"
        if not "!TEMP_IP!"=="" set "LAN_IP=!TEMP_IP!"
    )
)

REM Buka browser otomatis
start https://localhost:8443

:MENU_LOOP
cls
echo ============================================================
echo      SBA OPERATIONS SYSTEM - MASTER CONSOLE
echo ============================================================
echo  [OK] MySQL Database        : AKTIF (Port 3306)
echo  [OK] SBA Web App (HTTP)    : http://localhost:8080
echo  [OK] SBA Web App (HTTPS)   : https://localhost:8443
echo  [OK] Scanner Pos (Local)   : https://localhost:8443/sik-security/scanner
echo  [OK] Scanner Pos (Satpam)  : https://!LAN_IP!:8443/sik-security/scanner
echo  [INFO] PHP Executable      : %PHP_EXE%
echo ============================================================
echo  PETUNJUK KAMERA SATPAM (TANPA CHROME FLAG):
echo  1. Satpam buka URL HTTPS di HP/Tablet:
echo     https://!LAN_IP!:8443/sik-security/scanner
echo  2. Jika muncul peringatan keamanan SSL pertama kali:
echo     Klik 'Advanced / Lanjutan' -^> 'Proceed / Lanjutkan ke situs'.
echo  3. Begitu terbuka, browser mengizinkan kamera otomatis!
echo ============================================================
echo.
echo  PILIHAN MENU:
echo   [1] Buka Web Browser HTTPS Local (https://localhost:8443)
echo   [2] Buka Scanner QR SIK Local    (https://localhost:8443/sik-security/scanner)
echo   [3] Cek Port Aktif (8080, 8443, 3306)
echo   [4] Lihat Log Server (Laravel & Caddy)
echo   [Q] Matikan Server SBA Operations ^& Keluar
echo ============================================================
echo.
set "opt="
set /p opt="Pilih menu [1 / 2 / 3 / 4 / Q] lalu tekan Enter: "

if /i "%opt%"=="1" (
    start https://localhost:8443
    goto MENU_LOOP
)

if /i "%opt%"=="2" (
    start https://localhost:8443/sik-security/scanner
    goto MENU_LOOP
)

if /i "%opt%"=="3" (
    echo.
    echo Status Port Terkini:
    netstat -ano | findstr "LISTENING" | findstr ":8080 :8443 :3306"
    echo.
    pause
    goto MENU_LOOP
)

if /i "%opt%"=="4" (
    echo.
    if exist "%~dp0storage\logs\serve.log" (
        echo --- 15 Baris Terakhir Log Laravel (serve.log) ---
        powershell -NoProfile -Command "Get-Content '%~dp0storage\logs\serve.log' -Tail 15"
    )
    if exist "%~dp0storage\logs\caddy.log" (
        echo.
        echo --- 15 Baris Terakhir Log Caddy (caddy.log) ---
        powershell -NoProfile -Command "Get-Content '%~dp0storage\logs\caddy.log' -Tail 15"
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
powershell -NoProfile -Command "Get-CimInstance Win32_Process -ErrorAction SilentlyContinue | Where-Object { ($_.ProcessName -eq 'php.exe' -and ($_.CommandLine -like '*Project Magang*' -or $_.CommandLine -like '*8080*' -or $_.CommandLine -like '*sba_portal*' -or $_.CommandLine -like '*sba_ops*' -or $_.CommandLine -like '*sba-ops*')) -or ($_.ProcessName -eq 'caddy.exe' -and ($_.CommandLine -like '*8443*' -or $_.CommandLine -like '*Caddyfile*')) } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }" 2>NUL
echo [OK] Server SBA Operations berhasil dimatikan dengan aman!
ping 127.0.0.1 -n 2 >nul
exit
