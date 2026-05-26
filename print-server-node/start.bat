@echo off
title FacturaFacil Print Server
color 0A
cls

REM ── Deshabilitar Quick Edit Mode ──
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0disable-quick-edit.ps1" >nul 2>nul

echo ============================================
echo   FacturaFacil Print Server
echo ============================================
echo.

REM ── Verificar Node.js ──
node --version >nul 2>nul
if errorlevel 1 goto NONODE
for /f "tokens=*" %%a in ('node --version') do set NODEV=%%a
echo [OK] Node.js detectado: %NODEV%
goto CHECK_MODULES

:NONODE
echo [ERROR] Node.js NO esta instalado.
echo Descargalo desde https://nodejs.org/
echo.
pause
exit

:CHECK_MODULES
if exist "node_modules" goto CHECK_PS1
echo [INFO] Instalando dependencias...
call npm install
if errorlevel 1 goto NPMFAIL
echo [OK] Dependencias instaladas.
goto CHECK_PS1

:NPMFAIL
echo [ERROR] Fallo npm install. Sin conexion a internet?
pause
exit

:CHECK_PS1
if exist "raw-print.ps1" goto LOOP
echo [AVISO] No se encontro raw-print.ps1
echo La impresion local puede fallar.
echo.

REM ── Bucle principal con autoreinicio ──
:LOOP
echo ============================================
echo   Servidor corriendo en http://localhost:9100
echo   Presiona Ctrl+C para detener permanentemente
echo ============================================
echo.

node server.js

echo.
echo [AVISO] El servidor se detuvo inesperadamente.
echo [INFO] Reiniciando en 3 segundos...
echo.
timeout /t 3 /nobreak >nul
echo [INFO] Reiniciando servidor...
echo.
goto LOOP
