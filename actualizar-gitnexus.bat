@echo off
setlocal
title Actualizar Indice GitNexus
cd /d "%~dp0"

echo ============================================
echo   ACTUALIZAR INDICE GITNEXUS - %CD%
echo ============================================
echo.

where gitnexus >nul 2>&1
if errorlevel 1 goto :nopexus

if exist ".gitnexus\run.cjs" (
    echo [1/2] Indice previo detectado, actualizando...
    node .gitnexus\run.cjs analyze
) else (
    echo [1/2] Sin indice previo - analisis inicial...
    gitnexus analyze --index-only --default-branch main
)
if errorlevel 1 goto :error

echo.
echo ============================================
echo   INDICE GITNEXUS ACTUALIZADO CORRECTAMENTE
echo ============================================
echo.
pause
exit /b 0

:error
echo.
echo   *** ERROR AL ACTUALIZAR EL INDICE GITNEXUS ***
echo.
pause
exit /b 1

:nopexus
echo.
echo   ERROR: gitnexus no esta instalado.
echo   Ejecutar: npm i -g gitnexus
echo.
pause
exit /b 1