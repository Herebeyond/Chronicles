$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupFile = "backup_$timestamp.sql"
$backupPath = "backups\$backupFile"

Write-Host "Starting backup at $(Get-Date)" -ForegroundColor Cyan
docker compose exec database mysqldump -u chronicles_user -p"ChroniquesSecurePass2024!" chronicles > $backupPath

if ($LASTEXITCODE -eq 0) {
    $size = (Get-Item $backupPath).Length / 1MB
    Write-Host "Backup successful: $backupFile" -ForegroundColor Green
    Write-Host "File size: $([math]::Round($size, 2)) MB" -ForegroundColor Green
} else {
    Write-Host "Backup failed!" -ForegroundColor Red
    exit 1
}

# Keep only last 30 backups
$backups = Get-ChildItem "backups\backup_*.sql" -ErrorAction SilentlyContinue | Sort-Object LastWriteTime -Descending
if ($backups.Count -gt 30) {
    Write-Host "Cleaning old backups (keeping last 30)..." -ForegroundColor Yellow
    $backups | Select-Object -Skip 30 | Remove-Item -Force
    Write-Host "Cleanup complete" -ForegroundColor Green
}

$currentCount = (Get-ChildItem "backups\backup_*.sql" -ErrorAction SilentlyContinue).Count
Write-Host "Current backups: $currentCount files" -ForegroundColor Cyan
