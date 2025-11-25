# Database Backups

This folder contains SQL dump backups of your Chronicles database.

## Usage

### Create a backup
```powershell
.\scripts\backup.ps1
```

### List available backups
```powershell
.\scripts\list_backups.ps1
```

### Restore from a backup
```powershell
.\scripts\restore.ps1 backup_20251125_143000.sql
```

## Backup Strategy

- Backups are created manually when you run `backup.ps1`
- Last 30 backups are kept automatically
- Older backups are automatically deleted
- Each backup is a complete SQL dump of the database

## Before Important Changes

**Always create a backup before:**
- Running database migrations
- Modifying entity structures
- Bulk data operations
- Major feature development

```powershell
# Example: Before migration
.\scripts\backup.ps1
docker compose exec php php bin/console make:migration
# Review the migration file!
docker compose exec php php bin/console doctrine:migrations:migrate
```

## Storage Location

All backups are stored in this folder:
`C:\Users\baill\Docker\Chronicles\backups\`

You can:
- Copy files to external drives
- Upload to cloud storage
- Email specific backups
- Move important ones to a `milestones\` subfolder

## File Naming

Backups are named with timestamp: `backup_YYYYMMDD_HHMMSS.sql`

Example: `backup_20251125_143522.sql` = Created on Nov 25, 2025 at 14:35:22

## See Also

- `docs/backup_implementation.md` - Full backup guide
- `docs/database_backup.md` - Database storage details
