@echo off
title Stop SBA Operations System
echo ============================================================
echo   MEMATIKAN SERVER SBA OPERATIONS SECARA AMAN (PORT 8080)
echo ============================================================
echo.
echo Menghentikan proses PHP Laravel SBA Operations...
powershell -NoProfile -Command "Get-CimInstance Win32_Process -ErrorAction SilentlyContinue | Where-Object { $_.ProcessName -eq 'php.exe' -and ($_.CommandLine -like '*Project Magang*' -or $_.CommandLine -like '*8080*' -or $_.CommandLine -like '*sba_portal*' -or $_.CommandLine -like '*sba_ops*' -or $_.CommandLine -like '*sba-ops*') } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }" 2>NUL
echo [OK] Server SBA Operations berhasil dimatikan.
echo (Sistem Weighbridge dan proses lain tetap aman dan tidak terganggu)
powershell -NoProfile -Command "Start-Sleep -Seconds 2"