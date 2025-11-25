# Backup Implementation Guide - Manual Multi-Version Strategy

## Overview
This setup provides:
- ✅ Multiple backup versions (not just one copy)
- ✅ Manual control (no automatic sync that propagates mistakes)
- ✅ Windows-accessible backup files (visible in Explorer)
- ✅ Separate storage from main database
- ✅ Easy restore process

## Implementation

### Step 1: Create Backups Directory
```powershell
# In your Chronicles project folder
New-Item -ItemType Directory -Path "backups" -Force
```

### Step 2: Add Backup Scripts

Create `scripts/backup.sh`:
```bash
#!/bin/sh
# Manual backup script - run when YOU want to backup

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="/backups/backup_${TIMESTAMP}.sql"

echo "Starting backup at $(date)"
mysqldump -h database -u chronicles_user -pChroniquesSecurePass2024! chronicles > $BACKUP_FILE

if [ $? -eq 0 ]; then
    echo "✓ Backup successful: backup_${TIMESTAMP}.sql"
    echo "✓ File size: $(du -h $BACKUP_FILE | cut -f1)"
else
    echo "✗ Backup failed!"
    exit 1
fi

# Keep only last 30 backups (configurable)
cd /backups
BACKUP_COUNT=$(ls -1 backup_*.sql 2>/dev/null | wc -l)
if [ $BACKUP_COUNT -gt 30 ]; then
    echo "Cleaning old backups (keeping last 30)..."
    ls -t backup_*.sql | tail -n +31 | xargs rm -f
    echo "✓ Cleanup complete"
fi

echo "Current backups: $(ls -1 backup_*.sql 2>/dev/null | wc -l) files"
```

Create `scripts/restore.sh`:
```bash
#!/bin/sh
# Restore from a specific backup file

if [ -z "$1" ]; then
    echo "Usage: ./restore.sh backup_YYYYMMDD_HHMMSS.sql"
    echo ""
    echo "Available backups:"
    ls -lth /backups/backup_*.sql | head -10
    exit 1
fi

BACKUP_FILE="/backups/$1"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "✗ Backup file not found: $BACKUP_FILE"
    exit 1
fi

echo "⚠️  WARNING: This will REPLACE current database with backup from $1"
echo "Press Ctrl+C to cancel, or wait 5 seconds to continue..."
sleep 5

echo "Restoring from $1..."
mysql -h database -u chronicles_user -pChroniquesSecurePass2024! chronicles < $BACKUP_FILE

if [ $? -eq 0 ]; then
    echo "✓ Restore successful!"
else
    echo "✗ Restore failed!"
    exit 1
fi
```

Create `scripts/list_backups.sh`:
```bash
#!/bin/sh
# List all available backups with details

echo "Available backups:"
echo "=================="
ls -lth /backups/backup_*.sql | head -20
echo ""
echo "Total backups: $(ls -1 /backups/backup_*.sql 2>/dev/null | wc -l)"
echo "Total size: $(du -sh /backups | cut -f1)"
```

### Step 3: Update compose.yaml

Add backup service to your `compose.yaml`:

```yaml
services:
  # ... existing php and database services ...

  backup:
    image: mysql:8.0
    restart: "no"  # Don't auto-restart, only run on demand
    depends_on:
      - database
    volumes:
      - ./backups:/backups
      - ./scripts:/scripts
    environment:
      - MYSQL_PWD=ChroniquesSecurePass2024!
    entrypoint: /bin/sh
    command: -c "tail -f /dev/null"  # Keep container running
    networks:
      - default

volumes:
  caddy_data:
  caddy_config:
  database_data:
  # No need for backup volume - using local folder
```

### Step 4: Make Scripts Executable
```bash
# In Git Bash or WSL
chmod +x scripts/backup.sh scripts/restore.sh scripts/list_backups.sh
```

Or create PowerShell versions:

**`scripts/backup.ps1`:**
```powershell
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupFile = "backup_$timestamp.sql"
$backupPath = "backups\$backupFile"

Write-Host "Starting backup at $(Get-Date)"
docker compose exec database mysqldump -u chronicles_user -pChroniquesSecurePass2024! chronicles > $backupPath

if ($LASTEXITCODE -eq 0) {
    $size = (Get-Item $backupPath).Length / 1MB
    Write-Host "✓ Backup successful: $backupFile" -ForegroundColor Green
    Write-Host "✓ File size: $([math]::Round($size, 2)) MB" -ForegroundColor Green
} else {
    Write-Host "✗ Backup failed!" -ForegroundColor Red
    exit 1
}

# Keep only last 30 backups
$backups = Get-ChildItem "backups\backup_*.sql" | Sort-Object LastWriteTime -Descending
if ($backups.Count -gt 30) {
    Write-Host "Cleaning old backups (keeping last 30)..."
    $backups | Select-Object -Skip 30 | Remove-Item -Force
    Write-Host "✓ Cleanup complete" -ForegroundColor Green
}

$currentCount = (Get-ChildItem "backups\backup_*.sql").Count
Write-Host "Current backups: $currentCount files" -ForegroundColor Cyan
```

**`scripts/restore.ps1`:**
```powershell
param(
    [Parameter(Mandatory=$true)]
    [string]$BackupFile
)

$backupPath = "backups\$BackupFile"

if (-not (Test-Path $backupPath)) {
    Write-Host "✗ Backup file not found: $backupPath" -ForegroundColor Red
    Write-Host "`nAvailable backups:"
    Get-ChildItem "backups\backup_*.sql" | 
        Sort-Object LastWriteTime -Descending | 
        Select-Object -First 10 | 
        Format-Table Name, LastWriteTime, @{Name="Size (MB)"; Expression={[math]::Round($_.Length / 1MB, 2)}}
    exit 1
}

Write-Host "⚠️  WARNING: This will REPLACE current database with backup from $BackupFile" -ForegroundColor Yellow
Write-Host "Press Ctrl+C to cancel, or wait 5 seconds to continue..."
Start-Sleep -Seconds 5

Write-Host "Restoring from $BackupFile..."
Get-Content $backupPath | docker compose exec -T database mysql -u chronicles_user -pChroniquesSecurePass2024! chronicles

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Restore successful!" -ForegroundColor Green
} else {
    Write-Host "✗ Restore failed!" -ForegroundColor Red
    exit 1
}
```

**`scripts/list_backups.ps1`:**
```powershell
Write-Host "Available backups:" -ForegroundColor Cyan
Write-Host "==================`n"

$backups = Get-ChildItem "backups\backup_*.sql" -ErrorAction SilentlyContinue | 
    Sort-Object LastWriteTime -Descending

if ($backups) {
    $backups | Select-Object -First 20 | 
        Format-Table Name, 
                     @{Name="Date"; Expression={$_.LastWriteTime.ToString("yyyy-MM-dd HH:mm:ss")}}, 
                     @{Name="Size (MB)"; Expression={[math]::Round($_.Length / 1MB, 2)}} -AutoSize
    
    $totalSize = ($backups | Measure-Object -Property Length -Sum).Sum / 1MB
    Write-Host "`nTotal backups: $($backups.Count)"
    Write-Host "Total size: $([math]::Round($totalSize, 2)) MB"
} else {
    Write-Host "No backups found." -ForegroundColor Yellow
}
```

## Usage

### Create a Backup (Manual - When YOU Want)
```powershell
# PowerShell (Windows)
.\scripts\backup.ps1

# Or using Docker
docker compose exec database mysqldump -u chronicles_user -pChroniquesSecurePass2024! chronicles > backups\backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql
```

### List Available Backups
```powershell
# PowerShell
.\scripts\list_backups.ps1

# Or manually
Get-ChildItem backups\backup_*.sql | Sort-Object LastWriteTime -Descending | Format-Table Name, LastWriteTime, Length
```

### Restore from Backup
```powershell
# PowerShell - replace with your backup file name
.\scripts\restore.ps1 backup_20251125_143000.sql

# Or manually
Get-Content backups\backup_20251125_143000.sql | docker compose exec -T database mysql -u chronicles_user -pChroniquesSecurePass2024! chronicles
```

### Before Making Risky Changes
```powershell
# 1. Create named backup
docker compose exec database mysqldump -u chronicles_user -pChroniquesSecurePass2024! chronicles > backups\before_migration_ideas_table.sql

# 2. Make your changes
docker compose exec php php bin/console make:migration
# Review the migration file!
docker compose exec php php bin/console doctrine:migrations:migrate

# 3. If something goes wrong, restore
Get-Content backups\before_migration_ideas_table.sql | docker compose exec -T database mysql -u chronicles_user -pChroniquesSecurePass2024! chronicles
```

## Storage Details

### Where Backups Are Stored
```
C:\Users\baill\Docker\Chronicles\backups\
├── backup_20251125_120000.sql (10.5 MB)
├── backup_20251125_130000.sql (10.6 MB)
├── backup_20251125_140000.sql (10.7 MB)
└── ... (up to 30 versions by default)
```

- ✅ **Visible in Windows Explorer**
- ✅ **Can copy to USB/external drive**
- ✅ **Can upload to cloud (OneDrive, Google Drive)**
- ✅ **Independent of Docker volumes**
- ✅ **Survives Docker reset/reinstall**

### Backup Versions Strategy
**Default: Keep last 30 backups**

Customize by editing the cleanup section in scripts:
- Keep last 30: Good for daily backups (1 month history)
- Keep last 50: If you backup multiple times per day
- Keep last 100: For very cautious approach

You can also create "permanent" backups by moving them to a subfolder:
```powershell
# Create important milestone backups
New-Item -ItemType Directory -Path "backups\milestones" -Force
Copy-Item "backups\backup_20251125_140000.sql" "backups\milestones\before_major_feature.sql"
```

## Workflow Examples

### Daily Development Workflow
```powershell
# Start of day - create checkpoint
.\scripts\backup.ps1

# Work on features...
# Make changes, test, etc.

# Before risky database change
.\scripts\backup.ps1  # Creates another version

# If something breaks
.\scripts\list_backups.ps1  # See available versions
.\scripts\restore.ps1 backup_20251125_090000.sql  # Restore to this morning
```

### Before/After Migration Workflow
```powershell
# 1. Named backup before migration
docker compose exec database mysqldump -u chronicles_user -pChroniquesSecurePass2024! chronicles > backups\pre_migration_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql

# 2. Run migration
docker compose exec php php bin/console make:migration
# REVIEW THE MIGRATION FILE!
docker compose exec php php bin/console doctrine:migrations:migrate

# 3. Test the changes
# If good: keep going
# If bad: restore from pre_migration backup
```

### Weekly Archive Workflow
```powershell
# Every week, archive the best backup to external location
# 1. Pick the most recent successful backup
$latest = Get-ChildItem "backups\backup_*.sql" | Sort-Object LastWriteTime -Descending | Select-Object -First 1

# 2. Copy to external drive (change path as needed)
Copy-Item $latest.FullName "D:\Chronicles_Backups\weekly_$(Get-Date -Format 'yyyy_week_WW').sql"

# 3. Or upload to cloud
# Copy-Item $latest.FullName "$env:OneDrive\Chronicles_Backups\"
```

## Optional: Separate Database Container

If you also want a live "backup database" (in addition to SQL files):

### Add to compose.yaml:
```yaml
  database_backup:
    image: mysql:8.0
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: chronicles_backup
      MYSQL_USER: chronicles_user
      MYSQL_PASSWORD: ChroniquesSecurePass2024!
      MYSQL_ROOT_PASSWORD: ChroniquesRootPass2024!
    volumes:
      - database_backup_data:/var/lib/mysql
    ports:
      - "3308:3306"
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      timeout: 20s
      retries: 10

volumes:
  caddy_data:
  caddy_config:
  database_data:
  database_backup_data:  # NEW separate volume
```

### Sync to backup database (manual):
```powershell
# Dump from main, import to backup
docker compose exec database mysqldump -u chronicles_user -pChroniquesSecurePass2024! chronicles | docker compose exec -T database_backup mysql -u chronicles_user -pChroniquesSecurePass2024! chronicles_backup
```

### Restore from backup database:
```powershell
# Dump from backup, import to main
docker compose exec database_backup mysqldump -u chronicles_user -pChroniquesSecurePass2024! chronicles_backup | docker compose exec -T database mysql -u chronicles_user -pChroniquesSecurePass2024! chronicles
```

**This creates a NEW separate Docker volume** that's independent of main database.

## Troubleshooting

### Backup script not found
```powershell
# Make sure scripts directory exists
New-Item -ItemType Directory -Path "scripts" -Force
```

### Permission denied on scripts
```powershell
# For PowerShell scripts, you may need to set execution policy once
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Restore seems successful but data not changed
- Check you're using the correct backup file
- Verify backup file is not empty: `Get-Content backups\backup.sql | Select-Object -First 20`
- Try restarting PHP container: `docker compose restart php`
- Clear Symfony cache: `docker compose exec php php bin/console cache:clear`

### Running out of disk space
```powershell
# Check backup folder size
$totalSize = (Get-ChildItem "backups\backup_*.sql" | Measure-Object -Property Length -Sum).Sum / 1GB
Write-Host "Total backup size: $([math]::Round($totalSize, 2)) GB"

# Reduce number of backups kept (edit backup.ps1)
# Or manually delete old backups
Get-ChildItem "backups\backup_*.sql" | Sort-Object LastWriteTime -Descending | Select-Object -Skip 10 | Remove-Item -Force
```

## Summary

**This setup gives you:**
- ✅ Multiple backup versions (30 by default)
- ✅ Full manual control (no automatic sync)
- ✅ Windows-accessible files (can copy anywhere)
- ✅ Separate from Docker volumes (survives Docker issues)
- ✅ Easy restore process
- ✅ Protection against mistakes (old versions remain)
- ✅ Flexible retention policy (customize how many to keep)

**Storage:** All backups in `C:\Users\baill\Docker\Chronicles\backups\` - completely separate from main database Docker volume.
