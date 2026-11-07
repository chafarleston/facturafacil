@echo off
setlocal EnableExtensions
title Limpiar pedidos huerfanos - FacturaFacil
cd /d "%~dp0"

echo ============================================
echo   LIMPIAR HUERFANOS - FACTURAFACIL
echo ============================================
echo.
echo Este proceso NO elimina pedidos.
echo Cancela items de pedidos CANCELLED y lotes activos
echo de pedidos CANCELLED/COMPLETED.
echo Los items impagos de pedidos COMPLETED solo se listan
echo para reabrir/cobrar y NO se cancelan.
echo Tambien elimina items/lotes que ya no tienen pedido.
echo.
echo Se hara un backup automatico antes de aplicar cambios.
echo.

set "DB="
for /f "tokens=2 delims==" %%a in ('findstr /b /c:"DB_DATABASE=" .env 2^>nul') do set "DB=%%a"
set "DB=%DB:"=%"
if "%DB%"=="" set "DB=(no detectada en .env)"

echo Base de datos configurada en .env: %DB%
set /p "DBOVERRIDE=Base de datos a usar [dejar vacio para la del .env]: "
if not "%DBOVERRIDE%"=="" set "DB=%DBOVERRIDE%"

set "DBARG="
if not "%DBOVERRIDE%"=="" set "DBARG=--db=%DBOVERRIDE%"

echo.
echo Se usara la base de datos: %DB%
set /p "CONFIRM=Escriba SI para continuar con la simulacion: "
if /i not "%CONFIRM%"=="SI" goto :cancel

set "PHP=php"
where php >nul 2>&1
if errorlevel 1 (
    set "PHP="
    for /d %%d in ("C:\laragon\bin\php\php-*") do if exist "%%d\php.exe" set "PHP=%%d\php.exe"
)
if not defined PHP (
    echo ERROR: no se encontro PHP ni en PATH ni en C:\laragon\bin\php.
    goto :error
)

echo.
echo [1/2] Simulacion
"%PHP%" "%~dp0scripts\limpiar_huerfanos.php" %DBARG% --dry-run
if errorlevel 1 goto :error

echo.
echo [2/2] Aplicar cambios
echo Se creara un backup antes de modificar la base de datos.
set /p "APPLY=Escriba SI para aplicar los cambios: "
if /i not "%APPLY%"=="SI" goto :cancel

"%PHP%" "%~dp0scripts\limpiar_huerfanos.php" %DBARG% --apply
if errorlevel 1 goto :error

echo.
echo Limpieza finalizada.
echo Revisa la carpeta backups y verifica el KDS/restaurante.
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
