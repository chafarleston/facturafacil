# Reparacion segura para el cliente FacturaFacil
# Uso normal:  powershell -ExecutionPolicy Bypass -File .\reparar-cliente.ps1
# Simulacion:  powershell -ExecutionPolicy Bypass -File .\reparar-cliente.ps1 -DryRun
#
# Este script NO borra ventas, mesas, productos ni la base de datos.
# Solo verifica APP_KEY, permisos de cache/storage, enlace storage y regenera caches.

[CmdletBinding()]
param(
    [switch]$DryRun
)

$ErrorActionPreference = 'Stop'
$script:DryRun = $DryRun
$Root = $PSScriptRoot
$Artisan = Join-Path $Root 'artisan'
$EnvPath = Join-Path $Root '.env'
$BackupDir = Join-Path $Root 'storage\backup'

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

function Write-ErrorLine([string]$Text) {
    Write-Host "  XX  $Text" -ForegroundColor Red
}

function Find-Php {
    $cmd = Get-Command php.exe -ErrorAction SilentlyContinue
    if ($cmd) {
        return $cmd.Source
    }

    $candidate = Get-ChildItem 'C:\laragon\bin\php' -Recurse -Filter php.exe -ErrorAction SilentlyContinue |
        Sort-Object FullName -Descending |
        Select-Object -First 1 -ExpandProperty FullName

    if ($candidate) {
        return $candidate
    }

    return $null
}

function Backup-Env {
    if (-not (Test-Path -LiteralPath $EnvPath)) {
        return $null
    }

    if (-not (Test-Path -LiteralPath $BackupDir)) {
        if ($script:DryRun) {
            Write-Host "  [DRY-RUN] Crear $BackupDir" -ForegroundColor Yellow
        } else {
            New-Item -ItemType Directory -Force -Path $BackupDir | Out-Null
        }
    }

    $dest = Join-Path $BackupDir ('.env.' + (Get-Date -Format 'yyyyMMdd-HHmmss'))

    if ($script:DryRun) {
        Write-Host "  [DRY-RUN] Copiar .env -> $dest" -ForegroundColor Yellow
    } else {
        Copy-Item -LiteralPath $EnvPath -Destination $dest -Force
        Write-Ok "Backup del .env: $dest"
    }

    return $dest
}

function Test-Writable([string]$Path, [string]$Label) {
    if ($script:DryRun) {
        Write-Host "  [DRY-RUN] Probar escritura en $Label ($Path)" -ForegroundColor Yellow
        return $true
    }

    try {
        if (-not (Test-Path -LiteralPath $Path)) {
            New-Item -ItemType Directory -Force -Path $Path | Out-Null
        }

        $probe = Join-Path $Path ('.write-test-' + [guid]::NewGuid().ToString('N'))
        Set-Content -LiteralPath $probe -Value 'ok' -ErrorAction Stop
        Remove-Item -LiteralPath $probe -Force
        Write-Ok "Escritura en $Label"
        return $true
    } catch {
        Write-ErrorLine "Sin escritura en $Label"
        return $false
    }
}

function Set-EnvKey([string]$Value) {
    $content = [System.IO.File]::ReadAllText($EnvPath)

    if ($content -match '(?m)^APP_KEY=.*$') {
        $content = [regex]::Replace($content, '(?m)^APP_KEY=.*$', 'APP_KEY=' + $Value)
    } else {
        $content = $content.TrimEnd() + "`r`nAPP_KEY=" + $Value + "`r`n"
    }

    if ($script:DryRun) {
        Write-Host "  [DRY-RUN] Actualizar APP_KEY en .env" -ForegroundColor Yellow
    } else {
        [System.IO.File]::WriteAllText($EnvPath, $content, [System.Text.UTF8Encoding]::new($false))
        Write-Ok "APP_KEY restaurada en .env"
    }
}

function Invoke-Artisan([string]$Description, [string[]]$Arguments) {
    Write-Host ""
    Write-Host "==> $Description" -ForegroundColor Cyan
    Write-Host "    php artisan $($Arguments -join ' ')" -ForegroundColor DarkGray

    if ($script:DryRun) {
        Write-Host "    [DRY-RUN] No se ejecuta" -ForegroundColor Yellow
        return
    }

    & $Php @('artisan') @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "Fallo el comando: php artisan $($Arguments -join ' ') (codigo $LASTEXITCODE)"
    }
}

Write-Section "Reparacion FacturaFacil - Cliente"
Write-Host "Carpeta del sistema: $Root"
if ($script:DryRun) {
    Write-Warn "MODO SIMULACION: no se modificara nada."
}

if (-not (Test-Path -LiteralPath $Artisan)) {
    Write-ErrorLine "No se encontro artisan en $Root."
    Write-Warn "Ejecuta este script desde la raiz del proyecto facturafacil."
    if (-not $script:DryRun) { Read-Host "Presione ENTER para cerrar" | Out-Null }
    exit 1
}

if (-not (Test-Path -LiteralPath $EnvPath)) {
    Write-ErrorLine "No existe el archivo .env en $Root."
    Write-Warn "Restaura el .env desde un backup antes de continuar. No se hara ningun cambio."
    if (-not $script:DryRun) { Read-Host "Presione ENTER para cerrar" | Out-Null }
    exit 1
}

$Php = Find-Php
if (-not $Php) {
    Write-ErrorLine "No se encontro php.exe."
    Write-Warn "Agrega PHP al PATH o revisa C:\laragon\bin\php."
    if (-not $script:DryRun) { Read-Host "Presione ENTER para cerrar" | Out-Null }
    exit 1
}
Write-Ok "PHP: $Php"

# 1. APP_KEY
Write-Section "1. Verificando APP_KEY"
$keyLine = Get-Content -LiteralPath $EnvPath | Where-Object { $_ -match '^APP_KEY=' } | Select-Object -First 1
$keyValue = if ($keyLine) { ($keyLine -replace '^APP_KEY=', '').Trim() } else { '' }
$hasKey = $keyValue -ne '' -and $keyValue -ne 'base64:'

if ($hasKey) {
    Write-Ok "APP_KEY presente (no se muestra por seguridad)."
} else {
    Write-Warn "APP_KEY vacia o ausente."

    if ($script:DryRun) {
        Write-Host "  [DRY-RUN] Se pediria restaurar o generar APP_KEY." -ForegroundColor Yellow
    } else {
        Write-Host ""
        Write-Host "Opciones:"
        Write-Host "  1 = Restaurar una APP_KEY anterior (recomendado si habia datos cifrados)"
        Write-Host "  2 = Generar una APP_KEY nueva (invalida sesiones, cookies y datos cifrados)"
        $option = Read-Host "Escribe 1 o 2"

        if ($option -eq '1') {
            $source = Read-Host "Ruta del .env anterior o valor de APP_KEY (base64:...)"

            if (Test-Path -LiteralPath $source) {
                $oldLine = Get-Content -LiteralPath $source | Where-Object { $_ -match '^APP_KEY=' } | Select-Object -First 1
                $value = if ($oldLine) { ($oldLine -replace '^APP_KEY=', '').Trim() } else { '' }
            } else {
                $value = $source.Trim()
            }

            if (-not $value.StartsWith('base64:')) {
                Write-ErrorLine "La APP_KEY no parece valida (debe comenzar con base64:)."
                if (-not $script:DryRun) { Read-Host "Presione ENTER para cerrar" | Out-Null }
                exit 1
            }

            Backup-Env | Out-Null
            Set-EnvKey $value
        } elseif ($option -eq '2') {
            Write-Warn "Si habia datos cifrados, se perderan. Debes tener backup."
            $confirm = Read-Host "Escribe SI para generar una APP_KEY nueva"
            if ($confirm -ne 'SI') {
                Write-Warn "Cancelado. No se modifico la APP_KEY."
                Read-Host "Presione ENTER para cerrar" | Out-Null
                exit 1
            }

            Backup-Env | Out-Null
            Invoke-Artisan "Generar APP_KEY" @('key:generate', '--force')
        } else {
            Write-Warn "Opcion no valida. No se modifico la APP_KEY."
            Read-Host "Presione ENTER para cerrar" | Out-Null
            exit 1
        }
    }
}

# 2. Permisos
Write-Section "2. Verificando permisos de cache y storage"
$bootstrapOk = Test-Writable (Join-Path $Root 'bootstrap\cache') 'bootstrap/cache'
$storagePaths = @(
    (Join-Path $Root 'storage'),
    (Join-Path $Root 'storage\framework'),
    (Join-Path $Root 'storage\logs'),
    (Join-Path $Root 'storage\app'),
    (Join-Path $Root 'storage\backup')
)
$storageOk = $true
foreach ($path in $storagePaths) {
    if (-not (Test-Writable $path $path)) {
        $storageOk = $false
    }
}

if (-not $bootstrapOk -or -not $storageOk) {
    Write-Warn "Hay carpetas sin permisos de escritura. Si el script falla mas adelante, ejecuta como administrador:"
    Write-Host ""
    Write-Host "  icacls `"$Root\bootstrap\cache`" /grant `"$env:USERNAME:(OI)(CI)M`" /T" -ForegroundColor DarkYellow
    Write-Host "  icacls `"$Root\storage`" /grant `"$env:USERNAME:(OI)(CI)M`" /T" -ForegroundColor DarkYellow
    Write-Host ""
    Write-Warn "Si el servicio corre con otro usuario, reemplaza $env:USERNAME por ese usuario."
}

# 3. Enlace storage
Write-Section "3. Verificando enlace storage"
$publicStorage = Join-Path $Root 'public\storage'
if (Test-Path -LiteralPath $publicStorage) {
    Write-Ok "public/storage existe."
} else {
    Write-Warn "public/storage no existe. Se intentara crear."
    Invoke-Artisan "Crear enlace storage" @('storage:link', '--force')
}

# 4. Caches
Write-Section "4. Limpiando y regenerando caches"
Invoke-Artisan "Limpiar caches" @('optimize:clear')
Invoke-Artisan "Cache de configuracion" @('config:cache')
Invoke-Artisan "Cache de vistas" @('view:cache')
Invoke-Artisan "Limpiar cache de rutas" @('route:clear')

# 5. Salir de mantenimiento
Write-Section "5. Desactivando mantenimiento"
Invoke-Artisan "Activar sistema" @('up')

# 6. Verificacion
Write-Section "6. Verificacion final"
Invoke-Artisan "Informacion de Laravel" @('about')
Invoke-Artisan "Estado de migraciones" @('migrate:status')
Invoke-Artisan "Tareas programadas" @('schedule:list')

Write-Host ""
Write-Host "==> Verificar APP_KEY cargada" -ForegroundColor Cyan
if ($script:DryRun) {
    Write-Host "    [DRY-RUN] No se ejecuta tinker" -ForegroundColor Yellow
} else {
    & $Php artisan tinker --execute="echo config('app.key') ? 'APP_KEY_OK' : 'APP_KEY_FALTA';"
    Write-Host ""
}

# 7. Revision del entorno
Write-Section "7. Revision de configuracion"
$envContent = Get-Content -LiteralPath $EnvPath
$appEnv = ($envContent | Where-Object { $_ -match '^APP_ENV=' } | Select-Object -First 1) -replace '^APP_ENV=', ''
$appDebug = ($envContent | Where-Object { $_ -match '^APP_DEBUG=' } | Select-Object -First 1) -replace '^APP_DEBUG=', ''
$appUrl = ($envContent | Where-Object { $_ -match '^APP_URL=' } | Select-Object -First 1) -replace '^APP_URL=', ''

Write-Host "APP_ENV: $appEnv"
Write-Host "APP_DEBUG: $appDebug"
Write-Host "APP_URL: $appUrl"

if ($appEnv -ne 'production') {
    Write-Warn "En el servidor del cliente se recomienda APP_ENV=production."
}
if ($appDebug -ne 'false') {
    Write-Warn "En el servidor del cliente se recomienda APP_DEBUG=false."
}
if ($appUrl -notmatch '^https://') {
    Write-Warn "En el servidor del cliente se recomienda APP_URL con https."
}

Write-Section "Listo"
Write-Host "Revisa storage\logs\laravel.log para confirmar que ya no aparece MissingAppKeyException."
Write-Host "Si el login funciona, el APP_KEY y las caches quedaron correctos."
Write-Host ""
Write-Host "Recuerda: este script no recupera mesas borradas. Las mesas 55 y 56 deben recrearse desde Pisos/Mesas." -ForegroundColor Yellow
Write-Host "Las claves foraneas ya estan en modo restrict: no se pueden borrar pisos con mesas ni mesas con pedidos." -ForegroundColor Yellow

if (-not $script:DryRun) {
    Read-Host "Presione ENTER para cerrar" | Out-Null
}
