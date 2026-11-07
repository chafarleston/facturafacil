<#
    Actualizacion segura para el cliente FacturaFacil.
    Uso: powershell -ExecutionPolicy Bypass -File .\actualizar_cliente.ps1
    Simulacion: powershell -ExecutionPolicy Bypass -File .\actualizar_cliente.ps1 -DryRun
    Otra base:  powershell -ExecutionPolicy Bypass -File .\actualizar_cliente.ps1 -Database facturafacil2
#>

[CmdletBinding()]
param(
    [switch]$DryRun,
    [switch]$RunSeeder,
    [string]$Database,
    [switch]$Yes
)

$ErrorActionPreference = 'Stop'

$Root = $PSScriptRoot
$EnvPath = Join-Path $Root '.env'
$BackupDir = Join-Path $Root 'backups'
$Timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$LogPath = Join-Path $BackupDir ("update_$Timestamp.log")

$Down = $false
$TranscriptStarted = $false
$PreviousCommit = $null
$DbBackup = $null
$EnvBackup = $null
$Failed = $false

function Write-Section([string]$Text) {
    Write-Host ""
    Write-Host "============================================================" -ForegroundColor DarkCyan
    Write-Host " $Text" -ForegroundColor Cyan
    Write-Host "============================================================" -ForegroundColor DarkCyan
}

function Write-Ok([string]$Text) {
    Write-Host "  OK  $Text" -ForegroundColor Green
}

function Write-Warn([string]$Text) {
    Write-Host "  !!  $Text" -ForegroundColor Yellow
}

function Write-Fail([string]$Text) {
    Write-Host "  XX  $Text" -ForegroundColor Red
}

function Find-CommandPath([string]$Name) {
    $command = Get-Command $Name -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    return $null
}

function Find-Php {
    $php = Find-CommandPath 'php.exe'
    if ($php) {
        return $php
    }

    $candidate = Get-ChildItem 'C:\laragon\bin\php' -Recurse -Filter php.exe -ErrorAction SilentlyContinue |
        Sort-Object FullName -Descending |
        Select-Object -First 1 -ExpandProperty FullName

    return $candidate
}

function Find-Mysqldump {
    foreach ($base in @('C:\laragon\bin\mysql', 'C:\laragon\bin\mariadb')) {
        if (-not (Test-Path -LiteralPath $base)) {
            continue
        }

        $candidate = Get-ChildItem $base -Recurse -Filter mysqldump.exe -ErrorAction SilentlyContinue |
            Sort-Object FullName -Descending |
            Select-Object -First 1 -ExpandProperty FullName

        if ($candidate) {
            return $candidate
        }
    }

    return Find-CommandPath 'mysqldump.exe'
}

function Read-DotEnv([string]$Path) {
    $values = @{}

    if (-not (Test-Path -LiteralPath $Path)) {
        return $values
    }

    Get-Content -LiteralPath $Path | ForEach-Object {
        if ($_ -match '^\s*(DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME|DB_PASSWORD)\s*=\s*(.*)\s*$') {
            $value = $Matches[2].Trim()

            if ($value.Length -ge 2 -and
                (($value.StartsWith('"') -and $value.EndsWith('"')) -or
                 ($value.StartsWith("'") -and $value.EndsWith("'")))) {
                $value = $value.Substring(1, $value.Length - 2)
            }

            $values[$Matches[1]] = $value
        }
    }

    return $values
}

function Invoke-Checked([string]$Description, [scriptblock]$Action) {
    Write-Host "  > $Description" -ForegroundColor DarkGray
    & $Action

    if ($LASTEXITCODE -ne 0) {
        throw "$Description fallo (exit $LASTEXITCODE)"
    }
}

function Invoke-Artisan([string]$Description, [string[]]$Arguments) {
    Invoke-Checked $Description { & $script:Php @Arguments }
}

try {
    Write-Section 'ACTUALIZAR FACTURAFACIL - MODO SEGURO'
    Write-Host "Carpeta: $Root"
    Write-Host "Modo: $(if ($DryRun) { 'SIMULACION' } else { 'APLICAR CAMBIOS' })"

    Write-Section '1. Verificaciones previas'

    if (-not (Test-Path -LiteralPath $EnvPath)) {
        throw "No existe .env en $Root"
    }

    $Php = Find-Php
    $Git = Find-CommandPath 'git.exe'
    $Composer = Find-CommandPath 'composer'
    $Npm = Find-CommandPath 'npm.cmd'

    if (-not $Npm) {
        $Npm = Find-CommandPath 'npm'
    }

    $MySqlDump = Find-Mysqldump

    if (-not $Php) { throw 'No se encontro PHP (PATH ni C:\laragon\bin\php).' }
    if (-not $Git) { throw 'No se encontro git.exe.' }
    if (-not $Composer) { throw 'No se encontro composer.' }
    if (-not $Npm) { throw 'No se encontro npm.' }
    if (-not $MySqlDump) { throw 'No se encontro mysqldump.exe.' }

    Write-Ok "PHP: $Php"
    Write-Ok "Git: $Git"
    Write-Ok "Composer: $Composer"
    Write-Ok "npm: $Npm"
    Write-Ok "mysqldump: $MySqlDump"

    $envValues = Read-DotEnv $EnvPath
    $detectedDb = $envValues['DB_DATABASE']
    $dbHost = $envValues['DB_HOST']
    $dbPort = $envValues['DB_PORT']
    $dbUser = $envValues['DB_USERNAME']
    $dbPass = $envValues['DB_PASSWORD']

    if (-not $detectedDb) {
        throw 'No se pudo leer DB_DATABASE desde .env'
    }

    if (-not $dbHost) { $dbHost = '127.0.0.1' }
    if (-not $dbPort) { $dbPort = '3306' }
    if (-not $dbUser) { $dbUser = 'root' }

    $db = if ($Database) { $Database.Trim() } else { $detectedDb }

    if ($db -notmatch '^[A-Za-z0-9_-]+$') {
        throw "Nombre de base de datos invalido: $db"
    }

    Write-Host "Base detectada en .env: $detectedDb"
    Write-Host "Base que se usara:       $db"

    if ($db -ne $detectedDb) {
        Write-Warn "La base indicada no coincide con .env. Se usara la indicada."
    }

    if (-not $Database -and -not $Yes -and -not $DryRun) {
        Write-Host 'Si el cliente usa otra base (por ejemplo facturafacil2), escribala ahora.' -ForegroundColor Yellow
        $dbInput = Read-Host "Base de datos [$db]"

        if ($dbInput.Trim()) {
            $db = $dbInput.Trim()

            if ($db -notmatch '^[A-Za-z0-9_-]+$') {
                throw "Nombre de base de datos invalido: $db"
            }
        }
    }

    $branch = (& $Git rev-parse --abbrev-ref HEAD 2>$null | Out-String).Trim()
    if ($branch -ne 'main') {
        $message = "La rama actual es '$branch'. Se esperaba 'main'."

        if ($DryRun) {
            Write-Warn $message
        } else {
            throw $message
        }
    } else {
        Write-Ok 'Rama actual: main'
    }

    $gitStatus = (& $Git status --porcelain 2>$null | Out-String).Trim()
    if ($gitStatus) {
        $message = 'Hay cambios locales sin confirmar. No se actualizara para no pisarlos.'

        if ($DryRun) {
            Write-Warn "$message (simulacion continua)"
        } else {
            throw $message
        }
    } else {
        Write-Ok 'Repositorio limpio'
    }

    if (-not $DryRun) {
        Invoke-Checked 'git fetch --all --prune' { & $Git fetch --all --prune }
    }

    $PreviousCommit = (& $Git rev-parse HEAD 2>$null | Out-String).Trim()
    Write-Host "Commit actual: $PreviousCommit"

    $safeDb = $db -replace '[^A-Za-z0-9_.-]', '_'
    $dbBackup = Join-Path $BackupDir "db_${safeDb}_$Timestamp.sql"
    $envBackup = Join-Path $BackupDir "env_$Timestamp.bak"
    $certBackup = Join-Path $BackupDir "certificates_$Timestamp"

    if ($DryRun) {
        Write-Section '2. Backup que se crearia'
        Write-Host "  BD:    $dbBackup"
        Write-Host "  .env:  $envBackup"
        Write-Host "  Certs: $certBackup (si existen)"

        Write-Section '3. Pasos que se ejecutarian'
        Write-Host '  - php artisan down --retry=60'
        Write-Host '  - git pull --ff-only origin main'
        Write-Host '  - composer install --no-dev --optimize-autoloader --no-interaction'
        Write-Host '  - php artisan migrate --force'
        Write-Host "  - ActualizarSeeder: $(if ($RunSeeder) { 'SI' } else { 'omitido' })"
        Write-Host '  - npm ci'
        Write-Host '  - npm run build'
        Write-Host '  - optimize:clear, config:cache, view:cache, event:cache'
        Write-Host '  - php artisan up'

        if ($RunSeeder) {
            Write-Warn 'ActualizarSeeder esta activado y puede resetear permisos personalizados.'
        }

        Write-Section 'SIMULACION COMPLETADA - NO SE MODIFICO NADA'
        exit 0
    }

    if (-not (Test-Path -LiteralPath $BackupDir)) {
        New-Item -ItemType Directory -Force -Path $BackupDir | Out-Null
    }

    Start-Transcript -Path $LogPath -Append | Out-Null
    $TranscriptStarted = $true

    Write-Section '2. Backup de seguridad'

    Copy-Item -LiteralPath $EnvPath -Destination $envBackup -Force
    Write-Ok ".env respaldado: $envBackup"

    $certSource = Join-Path $Root 'storage\app\certificates'
    if (Test-Path -LiteralPath $certSource) {
        Copy-Item -LiteralPath $certSource -Destination $certBackup -Recurse -Force
        Write-Ok "Certificados respaldados: $certBackup"
    } else {
        Write-Warn 'No existe storage\app\certificates; no se respaldaron certificados.'
    }

    $mysqlConfig = Join-Path $env:TEMP ("ff_mysql_" + [guid]::NewGuid().ToString('N') + '.cnf')
    $mysqlError = "$dbBackup.err"
    $mysqlConfigLines = @(
        '[client]',
        "host=$dbHost",
        "port=$dbPort",
        "user=$dbUser"
    )

    if ($dbPass -ne '') {
        $mysqlConfigLines += "password=$dbPass"
    }

    Set-Content -LiteralPath $mysqlConfig -Value $mysqlConfigLines -Encoding ASCII

    try {
        $dumpCommand = '"{0}" --defaults-extra-file="{1}" --single-transaction --quick --routines --triggers --default-character-set=utf8mb4 "{2}" > "{3}" 2> "{4}"' -f `
            $MySqlDump, $mysqlConfig, $db, $dbBackup, $mysqlError

        cmd.exe /c $dumpCommand
        $dumpExit = $LASTEXITCODE

        if ($dumpExit -ne 0) {
            $details = if (Test-Path -LiteralPath $mysqlError) {
                (Get-Content -LiteralPath $mysqlError -Raw).Trim()
            } else {
                'sin detalle'
            }

            throw "Fallo el backup de la base de datos (exit $dumpExit): $details"
        }
    } finally {
        Remove-Item -LiteralPath $mysqlConfig -Force -ErrorAction SilentlyContinue
    }

    if (-not (Test-Path -LiteralPath $dbBackup)) {
        throw 'El backup de la base de datos no fue creado.'
    }

    $dbBackupInfo = Get-Item -LiteralPath $dbBackup
    if ($dbBackupInfo.Length -lt 1024) {
        throw "El backup de la base de datos parece vacio ($($dbBackupInfo.Length) bytes)."
    }

    Remove-Item -LiteralPath $mysqlError -Force -ErrorAction SilentlyContinue
    Write-Ok ("Backup BD: {0} ({1:N2} MB)" -f $dbBackup, ($dbBackupInfo.Length / 1MB))
    Write-Ok "Base respaldada: $db"

    Write-Section '3. Confirmacion'

    Write-Host "Se actualizara FacturaFacil en: $Root"
    Write-Host "Base de datos: $db"
    Write-Host "Commit anterior: $PreviousCommit"
    Write-Host "Backup: $dbBackup"

    if (-not $Yes) {
        $answer = Read-Host 'Escriba SI para continuar'
        if ($answer -cne 'SI') {
            throw 'Actualizacion cancelada por el usuario.'
        }
    }

    Write-Section '4. Actualizacion'

    Write-Host '  > php artisan down --retry=60' -ForegroundColor DarkGray
    & $Php artisan down --retry=60
    if ($LASTEXITCODE -ne 0) {
        throw 'No se pudo activar el modo mantenimiento.'
    }
    $Down = $true

    Invoke-Checked 'git pull --ff-only origin main' { & $Git pull --ff-only origin main }

    Invoke-Checked 'composer install' {
        & $Composer install --no-dev --optimize-autoloader --no-interaction
    }

    Invoke-Artisan 'php artisan migrate --force' @('artisan', 'migrate', '--force')

    if ($RunSeeder) {
        Write-Warn 'Ejecutando ActualizarSeeder: puede resetear permisos personalizados.'
        Invoke-Artisan 'php artisan db:seed --class=ActualizarSeeder --force' @(
            'artisan', 'db:seed', '--class=ActualizarSeeder', '--force'
        )
    } else {
        Write-Warn 'ActualizarSeeder omitido. Si se necesitan permisos nuevos, ejecutarlo manualmente.'
    }

    Invoke-Checked 'npm ci' { & $Npm ci }
    Invoke-Checked 'npm run build' { & $Npm run build }

    Invoke-Artisan 'php artisan optimize:clear' @('artisan', 'optimize:clear')
    Invoke-Artisan 'php artisan config:cache' @('artisan', 'config:cache')
    Invoke-Artisan 'php artisan view:cache' @('artisan', 'view:cache')

    try {
        Invoke-Artisan 'php artisan route:cache' @('artisan', 'route:cache')
    } catch {
        Write-Warn 'route:cache fallo; se limpiara la cache de rutas.'
        Invoke-Artisan 'php artisan route:clear' @('artisan', 'route:clear')
    }

    try {
        Invoke-Artisan 'php artisan event:cache' @('artisan', 'event:cache')
    } catch {
        Write-Warn 'event:cache fallo; se continua sin cache de eventos.'
    }

    if (-not (Test-Path -LiteralPath (Join-Path $Root 'public\storage'))) {
        Invoke-Artisan 'php artisan storage:link' @('artisan', 'storage:link')
    } else {
        Write-Ok 'public\storage ya existe'
    }

    Write-Section '5. Verificacion'

    & schtasks /query /tn 'FacturaFacilScheduler' > $null 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Warn 'No se encontro la tarea FacturaFacilScheduler.'
        Write-Warn 'Revisar scheduler.vbs o crear la tarea de Windows.'
    } else {
        Write-Ok 'Tarea FacturaFacilScheduler encontrada'
    }

    try {
        & $Php artisan migrate:status | Out-Host
    } catch {
        Write-Warn 'No se pudo mostrar migrate:status.'
    }

    try {
        Invoke-RestMethod -Uri 'http://127.0.0.1:9100/status' -TimeoutSec 3 | Out-Null
        Write-Ok 'Print Server responde en 127.0.0.1:9100'
    } catch {
        Write-Warn 'Print Server no responde. Reiniciar print-server-node\start-hidden.vbs.'
    }

    Write-Host '  > php artisan up' -ForegroundColor DarkGray
    & $Php artisan up
    if ($LASTEXITCODE -ne 0) {
        throw 'No se pudo desactivar el modo mantenimiento.'
    }
    $Down = $false

    Write-Section 'ACTUALIZACION COMPLETADA'
    Write-Ok "Backup BD: $dbBackup"
    Write-Ok "Backup .env: $envBackup"
    Write-Host "Commit anterior: $PreviousCommit"
    Write-Host 'Si algo falla, restaurar el backup y volver al commit anterior.'
    Write-Host 'Reiniciar Print Server: print-server-node\start-hidden.vbs'
} catch {
    $Failed = $true
    Write-Fail $_.Exception.Message

    if ($PreviousCommit) {
        Write-Warn "Commit anterior: $PreviousCommit"
        Write-Warn "Rollback de codigo: git reset --hard $PreviousCommit"
    }

    if ($DbBackup -and (Test-Path -LiteralPath $DbBackup)) {
        Write-Warn "Backup de base disponible en: $DbBackup"
        Write-Warn 'Restaurar solo despues de respaldar el estado actual.'
    }
} finally {
    if ($Down) {
        Write-Host 'Restaurando aplicacion (php artisan up)...' -ForegroundColor Yellow
        try {
            & $Php artisan up
        } catch {
            Write-Warn 'No se pudo ejecutar php artisan up. Revisar manualmente.'
        }
    }

    if ($TranscriptStarted) {
        Stop-Transcript | Out-Null
    }
}

if ($Failed) {
    exit 1
}

exit 0
