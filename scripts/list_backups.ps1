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
    Write-Host "Run .\scripts\backup.ps1 to create your first backup." -ForegroundColor Cyan
}
