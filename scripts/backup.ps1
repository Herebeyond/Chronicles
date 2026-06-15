$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$databaseBackupFile = "backup_$timestamp.sql"
$imagesBackupFile = "images_backup_$timestamp.zip"

# Create backup folders if they don't exist
$databaseFolder = "backups\database"
$imagesFolder = "backups\images"
New-Item -ItemType Directory -Path $databaseFolder -Force | Out-Null
New-Item -ItemType Directory -Path $imagesFolder -Force | Out-Null

Write-Host "Starting backup at $(Get-Date)" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# BACKUP DATABASE
Write-Host ""
Write-Host "Backing up database..." -ForegroundColor Yellow
$databaseBackupPath = Join-Path $databaseFolder $databaseBackupFile

$mysqlUser = if ($env:MYSQL_USER) { $env:MYSQL_USER } else { "chronicles_user" }
$mysqlPassword = if ($env:MYSQL_PASSWORD) { $env:MYSQL_PASSWORD } else { throw "MYSQL_PASSWORD environment variable is not set." }
$mysqlDatabase = if ($env:MYSQL_DATABASE) { $env:MYSQL_DATABASE } else { "chronicles" }

docker compose exec database mysqldump -u $mysqlUser -p"$mysqlPassword" $mysqlDatabase > $databaseBackupPath

if ($LASTEXITCODE -eq 0) {
    $size = (Get-Item $databaseBackupPath).Length / 1KB
    Write-Host "Database backup successful: $databaseBackupFile" -ForegroundColor Green
    Write-Host "File size: $([math]::Round($size, 2)) KB" -ForegroundColor Green
} else {
    Write-Host "Database backup failed!" -ForegroundColor Red
    exit 1
}

# BACKUP IMAGES
Write-Host ""
Write-Host "Backing up images..." -ForegroundColor Yellow
$imagesSource = "public\images"
$imagesBackupPath = Join-Path $imagesFolder $imagesBackupFile

if (Test-Path $imagesSource) {
    Compress-Archive -Path "$imagesSource\*" -DestinationPath $imagesBackupPath -Force
    
    if (Test-Path $imagesBackupPath) {
        $size = (Get-Item $imagesBackupPath).Length / 1MB
        Write-Host "Images backup successful: $imagesBackupFile" -ForegroundColor Green
        Write-Host "File size: $([math]::Round($size, 2)) MB" -ForegroundColor Green
    } else {
        Write-Host "Images backup failed!" -ForegroundColor Red
    }
} else {
    Write-Host "Images folder not found: $imagesSource" -ForegroundColor Yellow
}

# CLEANUP OLD BACKUPS
Write-Host ""
Write-Host "Cleaning old backups..." -ForegroundColor Yellow

# Keep only last 10 image backups
$imageBackups = Get-ChildItem "$imagesFolder\images_backup_*.zip" -ErrorAction SilentlyContinue | Sort-Object LastWriteTime -Descending
if ($imageBackups.Count -gt 10) {
    $toDelete = $imageBackups | Select-Object -Skip 10
    Write-Host "Removing $($toDelete.Count) old image backup(s)..." -ForegroundColor Gray
    $toDelete | Remove-Item -Force
}

# SUMMARY
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Backup Summary:" -ForegroundColor Cyan
$databaseCount = (Get-ChildItem "$databaseFolder\backup_*.sql" -ErrorAction SilentlyContinue).Count
$imagesCount = (Get-ChildItem "$imagesFolder\images_backup_*.zip" -ErrorAction SilentlyContinue).Count
Write-Host "Database backups: $databaseCount files" -ForegroundColor White
Write-Host "Images backups: $imagesCount files (max 10)" -ForegroundColor White
Write-Host "========================================" -ForegroundColor Cyan
