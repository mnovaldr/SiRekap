@echo off
echo ========================================
echo Setup Database MCC REKAP
echo ========================================
echo.

REM Cari MySQL di Laragon
set MYSQL_PATH=C:\laragon\bin\mysql

REM Cek versi MySQL yang ada
if exist "%MYSQL_PATH%\mysql-8.0.30-winx64\bin\mysql.exe" (
    set MYSQL_EXE=%MYSQL_PATH%\mysql-8.0.30-winx64\bin\mysql.exe
) else if exist "%MYSQL_PATH%\mysql-8.0.31-winx64\bin\mysql.exe" (
    set MYSQL_EXE=%MYSQL_PATH%\mysql-8.0.31-winx64\bin\mysql.exe
) else if exist "%MYSQL_PATH%\mysql-8.4.0-winx64\bin\mysql.exe" (
    set MYSQL_EXE=%MYSQL_PATH%\mysql-8.4.0-winx64\bin\mysql.exe
) else (
    echo ERROR: MySQL tidak ditemukan di Laragon!
    echo Pastikan Laragon sudah terinstall dan MySQL sudah running.
    echo.
    pause
    exit /b 1
)

echo MySQL ditemukan: %MYSQL_EXE%
echo.

REM Buat database
echo Membuat database mcc_rekap...
"%MYSQL_EXE%" -u root -e "CREATE DATABASE IF NOT EXISTS mcc_rekap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if %ERRORLEVEL% EQU 0 (
    echo [OK] Database mcc_rekap berhasil dibuat!
    echo.
    
    REM Jalankan migration
    echo Menjalankan migration...
    php artisan migrate:fresh --seed
    
    if %ERRORLEVEL% EQU 0 (
        echo.
        echo ========================================
        echo SETUP BERHASIL!
        echo ========================================
        echo Database: mcc_rekap
        echo Admin: admin@mcc.com
        echo Password: password
        echo ========================================
    ) else (
        echo [ERROR] Migration gagal!
    )
) else (
    echo [ERROR] Gagal membuat database!
    echo Pastikan MySQL sudah running di Laragon.
)

echo.
pause
