# Quick Backup Reference

## Daily Commands

### Create Backup (Do this before risky changes!)
```powershell
.\scripts\backup.ps1
```

### List All Backups
```powershell
.\scripts\list_backups.ps1
```

### Restore from Backup
```powershell
# First, list backups to see available files
.\scripts\list_backups.ps1

# Then restore (replace with actual filename)
.\scripts\restore.ps1 backup_20251125_223001.sql
```

## Before Database Changes

**ALWAYS do this before:**
- Running migrations
- Modifying entities
- Testing new features that touch the database

```powershell
# 1. Create backup
.\scripts\backup.ps1

# 2. Make your changes
docker compose exec php php bin/console make:migration
# REVIEW THE MIGRATION FILE in migrations/ folder!
docker compose exec php php bin/console doctrine:migrations:migrate

# 3. Test your changes
# If something is wrong:
.\scripts\restore.ps1 backup_YYYYMMDD_HHMMSS.sql
```

## Quick One-Liners

```powershell
# Backup with custom name (for important milestones)
docker compose exec database mysqldump -u chronicles_user -p"ChroniquesSecurePass2024!" chronicles > backups\milestone_before_big_feature.sql

# Quick backup check (see if recent backup exists)
Get-ChildItem backups\backup_*.sql | Sort-Object LastWriteTime -Descending | Select-Object -First 1

# Count your backups
(Get-ChildItem backups\backup_*.sql).Count

# Total backup size
$totalSize = (Get-ChildItem backups\backup_*.sql | Measure-Object -Property Length -Sum).Sum / 1MB
Write-Host "Total: $([math]::Round($totalSize, 2)) MB"
```

## Backup System Details

- **Location:** `C:\Users\baill\Docker\Chronicles\backups\`
- **Retention:** Last 30 backups kept automatically
- **Format:** SQL dumps (portable, readable)
- **Frequency:** Manual (YOU decide when)

## Status

✅ Backup system installed and tested
✅ First backup created: `backup_20251125_223001.sql`
✅ Scripts ready to use

## Tips

- Backup before end of work day if you made database changes
- Keep important milestone backups in `backups\milestones\` subfolder
- You can copy backups to USB/cloud for extra safety
- Backups are plain text SQL - you can open them to see what's inside
