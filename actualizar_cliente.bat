@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion
title Actualizar FacturaFacil
cd /d "%~dp0"

echo ============================================
echo   ACTUALIZAR FACTURAFACIL  (git - main)
echo   Carpeta: %CD%
echo ============================================
echo.

set "LARAGON=C:\laragon"
set "DBNAME="
set "DBUSER="
set "DBPASS="
set "DBHOST="
set "DBPORT="
set "MYSQLDIR="

echo [1/10] Backup de la base de datos
set "MYSQLDIR="
for /d %%d in ("%LARAGON%\bin\mysql\mysql-*") do set "MYSQLDIR=%%d"
if not defined MYSQLDIR for /d %%d in ("%LARAGON%\bin\mysql\mariadb-*") do set "MYSQLDIR=%%d"
if not defined MYSQLDIR ( echo   ERROR: no se encontro MySQL/MariaDB en %LARAGON%\bin\mysql & goto :error )
set "MYSQLDUMP=%MYSQLDIR%\bin\mysqldump.exe"
if not exist "%MYSQLDUMP%" ( echo   ERROR: mysqldump no existe & goto :error )

for /f "tokens=1,* delims==" %%a in ('findstr /b "DB_DATABASE=" .env') do set "DBNAME=%%b"
for /f "tokens=1,* delims==" %%a in ('findstr /b "DB_USERNAME=" .env') do set "DBUSER=%%b"
for /f "tokens=1,* delims==" %%a in ('findstr /b "DB_PASSWORD=" .env') do set "DBPASS=%%b"
for /f "tokens=1,* delims==" %%a in ('findstr /b "DB_HOST=" .env') do set "DBHOST=%%b"
for /f "tokens=1,* delims==" %%a in ('findstr /b "DB_PORT=" .env') do set "DBPORT=%%b"
if not defined DBNAME ( echo   ERROR: no hay DB_DATABASE en .env & goto :error )
if not defined DBUSER set "DBUSER=root"
if not defined DBHOST set "DBHOST=127.0.0.1"
if not defined DBPORT set "DBPORT=3306"
set "DBNAME=%DBNAME:"=%"
set "DBUSER=%DBUSER:"=%"
set "DBPASS=%DBPASS:"=%"
set "DBHOST=%DBHOST:"=%"
set "DBPORT=%DBPORT:"=%"

:: si DB_PORT viene vacio pero DB_HOST trae host:puerto, separarlos
set "DBPORT_CHECK="
for /f "tokens=1,* delims==" %%a in ('findstr /b "DB_PORT=" .env') do set "DBPORT_CHECK=%%b"
if not defined DBPORT_CHECK (
    set "DBHOST_TMP="
    set "DBPORT_TMP="
    echo !DBHOST! | findstr /r ":" >nul 2>&1
    if not errorlevel 1 (
        for /f "tokens=1,2 delims=:" %%x in ("!DBHOST!") do (
            set "DBHOST_TMP=%%x"
            set "DBPORT_TMP=%%y"
        )
        if defined DBHOST_TMP set "DBHOST=!DBHOST_TMP!"
        if defined DBPORT_TMP ( set "DBPORT=!DBPORT_TMP!" ) else set "DBPORT=3306"
    )
)

for /f "tokens=2 delims==" %%a in ('wmic os get localdatetime /value 2^>nul') do set "DT=%%a"
if defined DT (
    set "DT=%DT:~0,8%_%DT:~8,6%"
) else (
    for /f %%a in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd_HHmmss"') do set "DT=%%a"
)
if not defined DT set "DT=%RANDOM%"
if not exist "backups" mkdir "backups"
set "BACKUP=backups\backup_%DT%.sql"

if "%DBPASS%"=="" (
    "%MYSQLDUMP%" -h "%DBHOST%" -P "%DBPORT%" -u "%DBUSER%" "%DBNAME%" > "%BACKUP%"
) else (
    "%MYSQLDUMP%" -h "%DBHOST%" -P "%DBPORT%" -u "%DBUSER%" -p"%DBPASS%" "%DBNAME%" > "%BACKUP%"
)
if errorlevel 1 ( echo   ERROR en el backup de la BD & goto :error )
echo   Backup OK: %BACKUP%  (host=%DBHOST% puerto=%DBPORT% db=%DBNAME%)
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
