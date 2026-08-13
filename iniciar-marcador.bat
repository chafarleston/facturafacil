@echo off
chcp 65001 >nul
title Marcador de Asistencia - RealComputer
REM ============================================================
REM  Iniciar Marcador de Asistencia (kiosco)
REM  Permite usar la camara sobre HTTP en red local
REM  URL servidor: http://192.168.100.15/marcar
REM ============================================================

REM --- Ruta del navegador (Chrome / Edge) ---
set "CHROME=C:\Program Files\Google\Chrome\Application\chrome.exe"
if not exist "%CHROME%" set "CHROME=C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
if not exist "%CHROME%" set "CHROME=C:\Program Files\Microsoft\Edge\Application\msedge.exe"
if not exist "%CHROME%" set "CHROME=C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

if not exist "%CHROME%" (
    echo No se encontro Chrome ni Edge. Instale Google Chrome.
    pause
    exit /b 1
)

REM --- Servidor (cambiar IP/puerto si es necesario) ---
set "SERVIDOR=http://192.168.100.15"

REM --- Opciones del kiosco ---
REM 1ra vez: abrir NORMAL para permitir el acceso a la camara (clic en "Permitir").
REM Luego puede habilitar el modo kiosco descomentando la linea 2 (agregar --kiosk).
REM start "" "%CHROME%" --unsafely-treat-insecure-origin-as-secure="%SERVIDOR%" --kiosk "%SERVIDOR%/marcar"
start "" "%CHROME%" --unsafely-treat-insecure-origin-as-secure="%SERVIDOR%" "%SERVIDOR%/marcar"
