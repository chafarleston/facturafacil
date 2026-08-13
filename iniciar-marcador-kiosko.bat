@echo off
chcp 65001 >nul
title Marcador de Asistencia (KIOSCO) - RealComputer
REM ============================================================
REM  Iniciar Marcador en MODO KIOSCO (pantalla completa)
REM  IMPORTANTE: antes ejecute "iniciar-marcador.bat" una vez y
REM  haga clic en "Permitir" para el acceso a la camara.
REM ============================================================

set "CHROME=C:\Program Files\Google\Chrome\Application\chrome.exe"
if not exist "%CHROME%" set "CHROME=C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
if not exist "%CHROME%" set "CHROME=C:\Program Files\Microsoft\Edge\Application\msedge.exe"
if not exist "%CHROME%" set "CHROME=C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

if not exist "%CHROME%" (
    echo No se encontro Chrome ni Edge. Instale Google Chrome.
    pause
    exit /b 1
)

set "SERVIDOR=http://192.168.100.15:8000"

start "" "%CHROME%" --unsafely-treat-insecure-origin-as-secure="%SERVIDOR%" --kiosk "%SERVIDOR%/marcar"
