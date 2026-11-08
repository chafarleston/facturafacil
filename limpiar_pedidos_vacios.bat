@echo off
setlocal EnableExtensions
title Limpiar pedidos vacios - FacturaFacil
cd /d "%~dp0"

echo ============================================
echo   LIMPIAR PEDIDOS VACIOS - FACTURAFACIL
echo ============================================
echo.
echo Este proceso elimina:
echo - Pedidos CANCELLED sin ningun item.
echo - Pedidos COMPLETED sin ningun item pagado: IDs 3,4,6,7,8,9,10.
echo.
echo NO elimina pedidos con facturas/items pagados.
echo NO elimina pedidos activos, mesas, facturas ni stock.
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
php "%~dp0scripts\limpiar_pedidos_vacios.php" --db=%DB% --dry-run
if errorlevel 1 goto :error

echo.
echo [2/2] Aplicar cambios
set /p "APPLY=Escriba SI para eliminar los pedidos: "
if /i not "%APPLY%"=="SI" goto :cancel

php "%~dp0scripts\limpiar_pedidos_vacios.php" --db=%DB% --apply
if errorlevel 1 goto :error

echo.
echo Limpieza finalizada. Revisa el backup en backups\.
pause
exit /b 0

:cancel
echo.
echo Operacion cancelada. No se modifico nada.
pause
exit /b 0

:error
echo.
echo *** ERROR EN LA LIMPIEZA - revise los mensajes anteriores ***
pause
exit /b 1
