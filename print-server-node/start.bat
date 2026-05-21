@echo off
title FacturaFacil Print Server
color 0A
cls

echo ============================================
echo   FacturaFacil Print Server
echo ============================================
echo.

node --version >nul 2>nul
if errorlevel 1 goto NONODE
echo [OK] Node.js detectado.
goto CHECK_MODULES

:NONODE
echo [ERROR] Node.js no esta instalado.
echo Descargalo desde https://nodejs.org/
echo.
pause
exit

:CHECK_MODULES
if exist "node_modules" goto CHECK_PS1
echo.
echo [AVISO] Instalando dependencias...
npm install
if errorlevel 1 goto NPMFAIL
echo [OK] Dependencias instaladas.
goto CHECK_PS1

:NPMFAIL
echo [ERROR] Fallo npm install.
pause
exit

:CHECK_PS1
if exist "raw-print.ps1" goto START
echo.
echo [AVISO] No se encontro raw-print.ps1
echo La impresion local puede fallar.
echo.

:START
echo.
echo [OK] Iniciando servidor...
echo URL: http://localhost:9100
echo Presiona Ctrl+C para detener.
echo.
echo ============================================
echo.

node server.js

echo.
echo [AVISO] Servidor detenido.
pause
