param(
    [Parameter(Mandatory=$true)]
    [string]$BackupFile
)

$backupPath = "backups\$BackupFile"

if (-not (Test-Path $backupPath)) {
    Write-Host "✗ Backup file not found: $backupPath" -ForegroundColor Red
    Write-Host "`nAvailable backups:" -ForegroundColor Cyan
    Get-ChildItem "backups\backup_*.sql" -ErrorAction SilentlyContinue | 
        Sort-Object LastWriteTime -Descending | 
        Select-Object -First 10 | 
        Format-Table Name, LastWriteTime, @{Name="Size (MB)"; Expression={[math]::Round($_.Length / 1MB, 2)}} -AutoSize
    exit 1
}

Write-Host "⚠️  WARNING: This will REPLACE current database with backup from $BackupFile" -ForegroundColor Yellow
Write-Host "Press Ctrl+C to cancel, or wait 5 seconds to continue..."
Start-Sleep -Seconds 5

Write-Host "Restoring from $BackupFile..." -ForegroundColor Cyan
Get-Content $backupPath | docker compose exec -T database mysql -u chronicles_user -p"ChroniquesSecurePass2024!" chronicles

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Restore successful!" -ForegroundColor Green
    Write-Host "Clearing Symfony cache..." -ForegroundColor Cyan
    docker compose exec php php bin/console cache:clear
} else {
    Write-Host "✗ Restore failed!" -ForegroundColor Red
    exit 1
}
