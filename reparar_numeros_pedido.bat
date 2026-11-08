@echo off
setlocal EnableExtensions
title Reparar numeros de pedido - FacturaFacil
cd /d "%~dp0"

echo ============================================
echo   REPARAR NUMEROS DE PEDIDO DUPLICADOS
echo ============================================
echo.
echo Conserva el pedido mas antiguo de cada grupo y renumera los demas.
echo No elimina pedidos, facturas, items pagados, mesas ni stock.
echo Crea un backup antes de aplicar.
echo.

set "DB=facturafacil2"
set /p "DBINPUT=Base de datos [%DB%]: "
if not "%DBINPUT%"=="" set "DB=%DBINPUT%"

echo.
echo Base de datos: %DB%
set /p "CONFIRM=Escriba SI para ejecutar la simulacion: "
if /i not "%CONFIRM%"=="SI" goto :cancel

where php >nul 2>&1
if errorlevel 1 (
    echo ERROR: no se encontro PHP en PATH.
    goto :error
)

echo.
echo [1/2] Simulacion
php artisan orders:renumber-duplicates --db=%DB% --dry-run
if errorlevel 1 goto :error

echo.
echo [2/2] Aplicar cambios
set /p "APPLY=Escriba SI para renumerar los pedidos: "
if /i not "%APPLY%"=="SI" goto :cancel

call php artisan down --retry=60
if errorlevel 1 goto :error
set "DOWN=1"

php artisan orders:renumber-duplicates --db=%DB% --apply
if errorlevel 1 goto :apply_error

call php artisan up
set "DOWN="
echo.
echo Renumeracion finalizada. Revisa el backup y el CSV de auditoria.
pause
exit /b 0

:apply_error
echo.
echo *** ERROR AL RENUMERAR - se intentara reactivar el sistema ***
if defined DOWN call php artisan up
pause
exit /b 1

:cancel
echo.
echo Operacion cancelada. No se modifico nada.
pause
exit /b 0

:error
echo.
echo *** ERROR EN LA REPARACION - revise los mensajes anteriores ***
if defined DOWN call php artisan up
pause
exit /b 1
