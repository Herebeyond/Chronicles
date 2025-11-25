# Database Backup & Storage Guide

## Where Database Data is Stored

### Container (Virtual Location)
- **Path inside container:** `/var/lib/mysql/`
- **Database files location:** `/var/lib/mysql/chronicles/`
- This is where MySQL sees and accesses its data

### Physical Storage on Windows

**Docker Desktop stores volumes inside a virtual disk:**
```
C:\Users\baill\AppData\Local\Docker\wsl\data\ext4.vhdx
```

**WSL2 path (if accessible):**
```
\\wsl$\docker-desktop\var\lib\docker\volumes\chronicles_database_data\_data\
```

**Key Points:**
- ✅ Data IS persistent across container restarts
- ✅ Volume name: `chronicles_database_data`
- ✅ Defined in `compose.yaml` as a named Docker volume
- ❌ Not directly accessible from Windows Explorer (abstracted in WSL2 VM)

## Accessing Your Database Data

### Option 1: Via Container Shell
```bash
# Enter the database container
docker compose exec database bash

# Navigate to database directory
cd /var/lib/mysql/chronicles
ls -la
```

### Option 2: Copy Files from Container
```bash
# Copy entire database directory to Windows filesystem
docker cp chronicles-database-1:/var/lib/mysql/chronicles ./chronicles_backup_data

# Copy specific files
docker cp chronicles-database-1:/var/lib/mysql/chronicles/table_name.ibd ./backup/
```

### Option 3: SQL Dump (Recommended)
```bash
# Export complete database as SQL file
docker compose exec database mysqldump -u chronicles_user -pChroniquesSecurePass2024! chronicles > backup.sql

# Export with timestamp
docker compose exec database mysqldump -u chronicles_user -pChroniquesSecurePass2024! chronicles > backup_$(date +%Y%m%d_%H%M%S).sql

# Export specific tables
docker compose exec database mysqldump -u chronicles_user -pChroniquesSecurePass2024! chronicles characters species races > partial_backup.sql
```

### Option 4: Inspect Volume Details
```bash
# Get volume information
docker volume inspect chronicles_database_data

# List all volumes
docker volume ls

# Check volume size
docker system df -v
```

## Backup Strategies

### Manual Backup (Before Schema Changes)
**Always run before migrations or schema updates:**
```bash
docker compose exec database mysqldump -u chronicles_user -pChroniquesSecurePass2024! chronicles > backups/pre_migration_$(date +%Y%m%d_%H%M%S).sql
```

### Automated Scheduled Backups
Create a backup service in `compose.yaml` (see Automated Backup Setup section below).

### Quick Daily Backup Command
Add to your daily workflow or task scheduler:
```powershell
# PowerShell script for daily backup
docker compose exec database mysqldump -u chronicles_user -pChroniquesSecurePass2024! chronicles > "backups\daily_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"
```

## Restoring from Backup

### Restore Complete Database
```bash
# From SQL dump
docker compose exec -i database mysql -u chronicles_user -pChroniquesSecurePass2024! chronicles < backup.sql

# Or using docker cp + restore
docker cp backup.sql chronicles-database-1:/tmp/backup.sql
docker compose exec database mysql -u chronicles_user -pChroniquesSecurePass2024! chronicles -e "source /tmp/backup.sql"
```

### Restore Specific Tables
```bash
# Extract and restore only specific tables
docker compose exec -i database mysql -u chronicles_user -pChroniquesSecurePass2024! chronicles < partial_backup.sql
```

### Emergency Recovery
```bash
# If database is corrupted or data lost
docker compose down
docker volume rm chronicles_database_data
docker compose up -d
# Wait for database to initialize, then restore
docker compose exec -i database mysql -u chronicles_user -pChroniquesSecurePass2024! chronicles < backup.sql
```

## Best Practices

### Before Any Schema Change
1. ✅ Create SQL backup
2. ✅ Review generated migration file
3. ✅ Check for `DROP TABLE` or `TRUNCATE` statements
4. ✅ Test on development data first
5. ✅ Only then apply to production

### Regular Backup Schedule
- **Daily:** Automated SQL dumps
- **Before migrations:** Manual backup with descriptive name
- **Before major features:** Full backup + copy to external storage
- **Weekly:** Copy backups off-server (cloud/external drive)

### Backup Storage Recommendations
- ✅ Store SQL dumps in `backups/` folder (Windows accessible)
- ✅ Keep last 30 daily backups
- ✅ Keep weekly backups for 3 months
- ✅ Sync important backups to cloud (OneDrive, Google Drive)
- ✅ Before major changes, copy backup to external drive

### What to Backup
**Essential:**
- SQL dumps (complete database state)
- Migration files (track schema changes)

**Optional:**
- Application uploads (`public/images/`)
- Configuration files (`.env`)
- Custom scripts

## Automated Backup Setup

### Option 1: Docker Backup Service
Add to `compose.yaml`:

```yaml
  backup:
    image: mysql:8.0
    restart: unless-stopped
    depends_on:
      - database
    volumes:
      - ./backups:/backups
      - ./scripts/backup.sh:/backup.sh
    entrypoint: /bin/sh
    command: -c "while true; do sleep 3600; /backup.sh; done"
```

Create `scripts/backup.sh`:
```bash
#!/bin/sh
# Hourly automated backup
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
mysqldump -h database -u chronicles_user -pChroniquesSecurePass2024! chronicles > /backups/auto_${TIMESTAMP}.sql

# Keep only last 24 backups (24 hours)
cd /backups
ls -t auto_*.sql | tail -n +25 | xargs rm -f 2>/dev/null
```

### Option 2: Windows Task Scheduler
Create a scheduled task to run daily:

**PowerShell script (`scripts/daily_backup.ps1`):**
```powershell
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupPath = "C:\Users\baill\Docker\Chronicles\backups\daily_$timestamp.sql"

docker compose exec database mysqldump -u chronicles_user -pChroniquesSecurePass2024! chronicles > $backupPath

# Cleanup old backups (keep last 30 days)
Get-ChildItem "C:\Users\baill\Docker\Chronicles\backups\daily_*.sql" | 
    Sort-Object LastWriteTime -Descending | 
    Select-Object -Skip 30 | 
    Remove-Item -Force
```

Schedule in Task Scheduler:
- Run daily at 2 AM
- Action: `powershell.exe -File "C:\Users\baill\Docker\Chronicles\scripts\daily_backup.ps1"`

### Option 3: Separate Backup Database Container
Add second database for live backup:

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

volumes:
  database_data:
  database_backup_data:  # Add this
```

## Troubleshooting

### Can't Access Backup Files
- Check `backups/` folder exists in project root
- Verify file permissions (may need admin rights)
- Use `docker compose logs backup` to see errors

### Backup File is Empty
- Container may not have network access to database
- Check database connection with `docker compose exec backup ping database`
- Verify credentials in backup script

### Restore Fails
- Ensure database is running: `docker compose ps database`
- Check SQL file syntax: `head -20 backup.sql`
- Try restoring to empty database first

### Volume Not Found
- List volumes: `docker volume ls | grep chronicles`
- Recreate if needed: `docker compose down && docker compose up -d`

## Quick Reference Commands

```bash
# Backup
docker compose exec database mysqldump -u chronicles_user -pChroniquesSecurePass2024! chronicles > backup.sql

# Restore
docker compose exec -i database mysql -u chronicles_user -pChroniquesSecurePass2024! chronicles < backup.sql

# Check database size
docker compose exec database mysql -u chronicles_user -pChroniquesSecurePass2024! -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.tables WHERE table_schema = 'chronicles';"

# List all tables
docker compose exec database mysql -u chronicles_user -pChroniquesSecurePass2024! chronicles -e "SHOW TABLES;"

# Check last backup date
ls -lt backups/ | head -5  # Linux/Mac
Get-ChildItem backups\ | Sort-Object LastWriteTime -Descending | Select-Object -First 5  # PowerShell
```

## See Also
- `docs/troubleshooting.md` - General troubleshooting guide
- `.github/copilot-instructions.md` - Database protection rules
- `migrations/` - Database schema version history
