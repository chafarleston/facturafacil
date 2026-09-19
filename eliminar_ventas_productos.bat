@echo off
setlocal enabledelayedexpansion
title Eliminar Ventas / Productos - FacturaFacil
cd /d "%~dp0"

echo ============================================
echo   ELIMINACION DE VENTAS Y/O PRODUCTOS
echo   Carpeta: %CD%
echo ============================================
echo.
echo  ADVERTENCIA: operacion DESTRUCTIVA y permanente.
echo   - VENTAS      : invoices, pedidos (restaurant), cajas registradoras,
echo                   cola de impresion, mesas liberadas, series a 0
echo   - PRODUCTOS   : todos los productos y sus componentes
echo.
echo  NOTA: se crea un BACKUP de la BD antes de eliminar.
echo.

where php >nul 2>&1
if errorlevel 1 (
    echo   ERROR: php no encontrado en PATH.
    goto :error
)

echo  Seleccione una opcion:
echo.
echo   1) Solo VENTAS
echo   2) Solo PRODUCTOS
echo   3) VENTAS + PRODUCTOS (ambos)
echo   0) Salir
echo.
set /p "opcion=Opcion: "

if "!opcion!"=="0" exit /b 0
if "!opcion!"=="1" set "opcion=ventas" & goto :confirmar
if "!opcion!"=="2" set "opcion=productos" & goto :confirmar
if "!opcion!"=="3" set "opcion=ambos" & goto :confirmar
echo   Opcion invalida: !opcion!
goto :fin

:confirmar
echo.
echo   Va a ejecutar: !opcion!
set /p "confir=Escriba SI para confirmar (no se puede deshacer): "
if /i not "!confir!"=="SI" (
    echo   Cancelado.
    goto :fin
)

echo.
echo [1/2] Creando backup de la base de datos...
set "LARAGON=C:\laragon"
set "DB_HOST=127.0.0.1"
set "DB_PORT=3306"
set "DB_NAME=facturafacil"
set "DB_USER=root"
set "DB_PASS="

set "MYSQLDIR="
for /d %%d in ("%LARAGON%\bin\mysql\mysql-*") do set "MYSQLDIR=%%d"
if not defined MYSQLDIR for /d %%d in ("%LARAGON%\bin\mysql\mariadb-*") do set "MYSQLDIR=%%d"
if not defined MYSQLDIR ( echo   ERROR: no se encontro MySQL/MariaDB en %LARAGON%\bin\mysql & goto :error )
set "MYSQLDUMP=%MYSQLDIR%\bin\mysqldump.exe"
if not exist "%MYSQLDUMP%" ( echo   ERROR: mysqldump no existe & goto :error )

for /f "tokens=2 delims==" %%a in ('wmic os get localdatetime /value 2^>nul') do set "DT=%%a"
if defined DT ( set "DT=!DT:~0,8!_!DT:~8,6!" ) else (
    for /f %%a in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd_HHmmss"') do set "DT=%%a"
)
if not defined DT set "DT=%RANDOM%"
if not exist "eliminar-ventas-productos\backup" mkdir "eliminar-ventas-productos\backup"
set "BACKUP=eliminar-ventas-productos\backup\backup_!DT!.sql"

if "%DB_PASS%"=="" (
    "%MYSQLDUMP%" -h "%DB_HOST%" -P "%DB_PORT%" -u "%DB_USER%" "%DB_NAME%" > "%BACKUP%"
) else (
    "%MYSQLDUMP%" -h "%DB_HOST%" -P "%DB_PORT%" -u "%DB_USER%" -p"%DB_PASS%" "%DB_NAME%" > "%BACKUP%"
)
if errorlevel 1 ( echo   ERROR en el backup de la BD & goto :error )
echo   Backup OK: %BACKUP%

echo.
echo [2/2] Ejecutando limpieza...
if "!opcion!"=="ventas" (
    call php artisan tinker --execute="include base_path('eliminar-ventas-productos/clean_ventas.php');"
) 
if "!opcion!"=="productos" (
    call php artisan tinker --execute="include base_path('eliminar-ventas-productos/clean_productos.php');"
)
if "!opcion!"=="ambos" (
    call php artisan tinker --execute="include base_path('eliminar-ventas-productos/clean_ventas.php');"
    if errorlevel 1 goto :error
    call php artisan tinker --execute="include base_path('eliminar-ventas-productos/clean_productos.php');"
)
if errorlevel 1 goto :error

echo.
echo  Limpiando caches...
call php artisan cache:clear

echo.
echo ============================================
echo   OPERACION COMPLETADA
echo   Backup en: %BACKUP%
echo ============================================
goto :fin

:error
echo.
echo   *** ERROR EN LA OPERACION - revise los mensajes ***
echo   El backup (si se creo) esta en eliminar-ventas-productos\backup\
echo.
pause
exit /b 1

:fin
echo.
pause
exit /b 0