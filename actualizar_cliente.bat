@echo off
setlocal
cd /d "%~dp0"
title Actualizar FacturaFacil

echo ============================================
echo   ACTUALIZAR FACTURAFACIL - MODO SEGURO
echo   Carpeta: %CD%
echo ============================================
echo.

where powershell >nul 2>&1
if errorlevel 1 (
    echo ERROR: no se encontro PowerShell.
    pause
    exit /b 1
)

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0actualizar_cliente.ps1" %*
set "RC=%ERRORLEVEL%"

echo.
pause
exit /b %RC%
