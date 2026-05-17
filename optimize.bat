@echo off
echo ========================================
echo   SiREKAP MCC - Performance Optimizer
echo ========================================
echo.

echo [1/5] Clearing old caches...
php artisan optimize:clear
echo.

echo [2/5] Caching configuration...
php artisan config:cache
echo.

echo [3/5] Caching routes...
php artisan route:cache
echo.

echo [4/5] Caching views...
php artisan view:cache
echo.

echo [5/5] Checking log file size...
powershell -Command "Get-Item storage/logs/*.log | Select-Object Name, @{Name='Size(MB)';Expression={[math]::Round($_.Length/1MB,2)}}"
echo.

echo ========================================
echo   Optimization Complete!
echo ========================================
echo.
echo Tips:
echo - Run this script after updating code
echo - Check storage/logs/ regularly
echo - Use 'php artisan optimize:clear' when developing
echo.
pause
