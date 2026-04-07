$ErrorActionPreference = 'Stop'
$INIT_FLAG = 'C:\xampp\mysql\data\.sahamid_initialized'

# ── Start MariaDB ────────────────────────────────────────────────────────────
Write-Host "[1/4] Starting MariaDB..."
Start-Process -FilePath 'C:\xampp\mysql\bin\mysqld.exe' `
    -ArgumentList '--defaults-file=C:\xampp\mysql\bin\my.ini', '--standalone' `
    -NoNewWindow

# Wait for MariaDB to be ready
Write-Host "      Waiting for MariaDB to accept connections..."
$ready = $false
for ($i = 0; $i -lt 30; $i++) {
    Start-Sleep -Seconds 2
    $result = & 'C:\xampp\mysql\bin\mysqladmin.exe' -u root ping 2>&1
    if ($result -match 'alive') { $ready = $true; break }
}
if (-not $ready) { throw "MariaDB did not start within 60 seconds." }
Write-Host "      MariaDB is ready."

# ── Initialize database (first run only) ────────────────────────────────────
if (-not (Test-Path $INIT_FLAG)) {
    Write-Host "[2/4] First run: creating database and user..."
    & 'C:\xampp\mysql\bin\mysql.exe' -u root -e @"
CREATE DATABASE IF NOT EXISTS ``sahamid`` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE DATABASE IF NOT EXISTS ``sah_saherp`` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER IF NOT EXISTS 'irtiza'@'%' IDENTIFIED BY 'netetech321';
CREATE USER IF NOT EXISTS 'irtiza'@'localhost' IDENTIFIED BY 'netetech321';
GRANT ALL PRIVILEGES ON ``sahamid``.* TO 'irtiza'@'%';
GRANT ALL PRIVILEGES ON ``sahamid``.* TO 'irtiza'@'localhost';
GRANT ALL PRIVILEGES ON ``sah_saherp``.* TO 'irtiza'@'%';
GRANT ALL PRIVILEGES ON ``sah_saherp``.* TO 'irtiza'@'localhost';
FLUSH PRIVILEGES;
"@

    Write-Host "      Importing structure (sahamid_structure.sql)..."
    & 'C:\xampp\mysql\bin\mysql.exe' -u irtiza '-pnetetech321' sahamid `
        -e "source C:/docker/sahamid_structure.sql;"

    Write-Host "      Importing data (sahamid_data.sql) - this may take several minutes..."
    & 'C:\xampp\mysql\bin\mysql.exe' -u irtiza '-pnetetech321' sahamid `
        -e "source C:/docker/sahamid_data.sql;"

    New-Item -Path $INIT_FLAG -ItemType File | Out-Null
    Write-Host "      Database initialized successfully."
} else {
    Write-Host "[2/4] Database already initialized, skipping import."
}

# ── Start Apache ─────────────────────────────────────────────────────────────
Write-Host "[3/4] Starting Apache..."
Start-Process -FilePath 'C:\xampp\apache\bin\httpd.exe' -NoNewWindow

Start-Sleep -Seconds 3
Write-Host ""
Write-Host "[4/4] All services running."
Write-Host "      Application : http://localhost"
Write-Host "      phpMyAdmin  : http://localhost/phpmyadmin"
Write-Host "      MariaDB     : localhost:3306  user=irtiza  db=sahamid"
Write-Host ""

# Keep container alive and monitor both processes
while ($true) {
    $mysqld = Get-Process 'mysqld'  -ErrorAction SilentlyContinue
    $httpd  = Get-Process 'httpd'   -ErrorAction SilentlyContinue
    if (-not $mysqld) { Write-Host "[WARN] MariaDB process stopped."; break }
    if (-not $httpd)  { Write-Host "[WARN] Apache process stopped.";  break }
    Start-Sleep -Seconds 15
}
