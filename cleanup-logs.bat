@echo off
echo ========================================
echo   SiREKAP MCC - Log Cleanup
echo ========================================
echo.

echo Current log files:
powershell -Command "Get-Item storage/logs/*.log | Select-Object Name, @{Name='Size(MB)';Expression={[math]::Round($_.Length/1MB,2)}}, LastWriteTime"
echo.

set /p confirm="Do you want to backup and clear logs? (Y/N): "
if /i "%confirm%"=="Y" (
    echo.
    echo [1/3] Creating backup...
    powershell -Command "Get-ChildItem storage/logs/*.log | ForEach-Object { Copy-Item $_.FullName ($_.FullName + '.backup') }"
    echo Backup created: *.log.backup
    echo.
    
    echo [2/3] Clearing log files...
    powershell -Command "Get-ChildItem storage/logs/*.log | ForEach-Object { Clear-Content $_.FullName }"
    echo Log files cleared!
    echo.
    
    echo [3/3] Removing old backups (older than 7 days)...
    powershell -Command "Get-ChildItem storage/logs/*.backup | Where-Object {$_.LastWriteTime -lt (Get-Date).AddDays(-7)} | Remove-Item"
    echo Old backups removed!
    echo.
    
    echo ========================================
    echo   Cleanup Complete!
    echo ========================================
) else (
    echo.
    echo Cleanup cancelled.
)
echo.
pause
