@echo off
setlocal enabledelayedexpansion
title Actualizar FacturaFacil
cd /d "%~dp0"

echo ============================================
echo   ACTUALIZAR FACTURAFACIL  (git - main)
echo   Carpeta: %CD%
echo ============================================
echo.

set "LARAGON=C:\laragon"

:: ============================================
::  CONFIGURACION DE BASE DE DATOS
::  (editar estos valores si difieren del cliente)
:: ============================================
set "DB_HOST=127.0.0.1"
set "DB_PORT=3306"
set "DB_NAME=facturafacil"
set "DB_USER=root"
set "DB_PASS="

echo [1/10] Backup de la base de datos
set "MYSQLDIR="
for /d %%d in ("%LARAGON%\bin\mysql\mysql-*") do set "MYSQLDIR=%%d"
if not defined MYSQLDIR for /d %%d in ("%LARAGON%\bin\mysql\mariadb-*") do set "MYSQLDIR=%%d"
if not defined MYSQLDIR ( echo   ERROR: no se encontro MySQL/MariaDB en %LARAGON%\bin\mysql & goto :error )
set "MYSQLDUMP=%MYSQLDIR%\bin\mysqldump.exe"
if not exist "%MYSQLDUMP%" ( echo   ERROR: mysqldump no existe & goto :error )

for /f "tokens=2 delims==" %%a in ('wmic os get localdatetime /value 2^>nul') do set "DT=%%a"
if defined DT (
    set "DT=%DT:~0,8%_%DT:~8,6%"
) else (
    for /f %%a in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd_HHmmss"') do set "DT=%%a"
)
if not defined DT set "DT=%RANDOM%"
if not exist "backups" mkdir "backups"
set "BACKUP=backups\backup_%DT%.sql"

if "%DB_PASS%"=="" (
    "%MYSQLDUMP%" --skip-column-statistics -h "%DB_HOST%" -P "%DB_PORT%" -u "%DB_USER%" "%DB_NAME%" > "%BACKUP%"
) else (
    "%MYSQLDUMP%" --skip-column-statistics -h "%DB_HOST%" -P "%DB_PORT%" -u "%DB_USER%" -p"%DB_PASS%" "%DB_NAME%" > "%BACKUP%"
)
if errorlevel 1 ( echo   ERROR en el backup de la BD & goto :error )
echo   Backup OK: %BACKUP%  (host=%DB_HOST% puerto=%DB_PORT% db=%DB_NAME%)
echo.

echo [2/10] git pull origin main
git pull origin main
if errorlevel 1 goto :error

echo [3/10] composer install
call composer install --no-dev --optimize-autoloader --no-interaction
if errorlevel 1 goto :error

echo [4/10] php artisan migrate --force
call php artisan migrate --force
if errorlevel 1 goto :error

echo [5/10] php artisan db:seed --class=PermissionsSeeder --force
echo        (SOLO permisos - NO resetea las series)
call php artisan db:seed --class=PermissionsSeeder --force
if errorlevel 1 goto :error

echo [6/10] npm install (solo si falta node_modules)
if not exist "node_modules" (
    call npm install
    if errorlevel 1 goto :error
)

echo [7/10] npm run build
call npm run build
if errorlevel 1 goto :error

echo [8/10] limpiar y regenerar caches
call php artisan optimize:clear
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache
if errorlevel 1 goto :error

echo [9/10] storage:link (solo si no existe)
if not exist "public\storage" (
    call php artisan storage:link
)

echo [10/10] verificar scheduler
schtasks /query /tn "FacturaFacilScheduler" >nul 2>&1
if errorlevel 1 (
    echo   AVISO: no se encontro la tarea FacturaFacilScheduler.
    echo   Usar scheduler.vbs o crear la tarea de Windows.
)

echo.
echo ============================================
echo   ACTUALIZACION COMPLETADA
echo ============================================
echo   Backup en:  %BACKUP%
echo   Reinicie el Print Server: print-server-node\start-hidden.vbs
echo.
pause
exit /b 0

:error
echo.
echo   *** ERROR EN LA ACTUALIZACION - revise los mensajes arriba ***
echo   El backup (si se creo) esta en backups\
pause
exit /b 1
