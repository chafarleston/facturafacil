@echo off
setlocal
cd /d "%~dp0"

echo Ejecutando reparacion de FacturaFacil...
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0reparar-cliente.ps1" %*

echo.
pause
