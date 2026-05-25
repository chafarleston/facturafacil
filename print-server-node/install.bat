@echo off
title Instalador FacturaFacil Print Server
color 0B
cls

echo ============================================
echo   Instalador FacturaFacil Print Server
echo ============================================
echo.

REM --- Detectar instalacion anterior ---
echo [INFO] Verificando instalaciones anteriores...

set "DESKTOP=%USERPROFILE%\Desktop"
set "STARTUP=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup"
set "OLD_FOUND=0"

if exist "%DESKTOP%\FacturaFacil Print Server.lnk" (
    echo   - Encontrado acceso directo en escritorio
    set OLD_FOUND=1
)
if exist "%DESKTOP%\Start Print Server.lnk" (
    echo   - Encontrado acceso directo antiguo en escritorio
    set OLD_FOUND=1
)
if exist "%STARTUP%\FacturaFacil Print Server.lnk" (
    echo   - Encontrado inicio automatico actual
    set OLD_FOUND=1
)
if exist "%STARTUP%\Start Print Server.lnk" (
    echo   - Encontrado inicio automatico antiguo
    set OLD_FOUND=1
)

if %OLD_FOUND%==1 (
    echo.
    echo Se detecto una instalacion anterior.
    set /p REPLACE="Deseas reemplazarla? (S/N): "
    if /I "%REPLACE%" NEQ "S" goto CANCEL_REPLACE

    echo.
    echo [INFO] Eliminando archivos antiguos...

    if exist "%DESKTOP%\FacturaFacil Print Server.lnk" (
        del "%DESKTOP%\FacturaFacil Print Server.lnk" >nul 2>nul
        echo   [OK] Acceso directo nuevo eliminado
    )
    if exist "%DESKTOP%\Start Print Server.lnk" (
        del "%DESKTOP%\Start Print Server.lnk" >nul 2>nul
        echo   [OK] Acceso directo antiguo eliminado
    )
    if exist "%STARTUP%\FacturaFacil Print Server.lnk" (
        del "%STARTUP%\FacturaFacil Print Server.lnk" >nul 2>nul
        echo   [OK] Inicio automatico nuevo eliminado
    )
    if exist "%STARTUP%\Start Print Server.lnk" (
        del "%STARTUP%\Start Print Server.lnk" >nul 2>nul
        echo   [OK] Inicio automatico antiguo eliminado
    )

    echo [OK] Limpieza completada.
    echo.
)

REM --- Verificar Node.js ---
node --version >nul 2>nul
if errorlevel 1 goto NONODE
for /f "tokens=*" %%a in ('node --version') do set NODEV=%%a
echo [OK] Node.js encontrado: %NODEV%
goto CHECK_NPM

:NONODE
echo [ERROR] Node.js NO esta instalado.
echo.
echo Para usar este servidor necesitas Node.js 18 o superior.
echo.
echo 1. Ve a https://nodejs.org/
echo 2. Descarga la version "LTS" (recomendada)
echo 3. Instala con las opciones por defecto
echo 4. Reinicia esta computadora
echo 5. Vuelve a ejecutar este instalador
echo.
pause
exit

:CHECK_NPM
echo [OK] npm encontrado.

REM --- Verificar node_modules ---
if exist "node_modules" goto CHECK_DEPS
echo.
echo [INFO] Instalando dependencias por primera vez...
call npm install
if errorlevel 1 goto NPMFAIL
echo [OK] Dependencias instaladas correctamente.
goto CHECK_PS1

:CHECK_DEPS
echo.
echo [INFO] Verificando dependencias instaladas...
node -e "require('express'); require('cors'); require('iconv-lite');" >nul 2>nul
if errorlevel 1 (
    echo [AVISO] Dependencias incompletas o desactualizadas.
    echo [INFO] Reinstalando...
    rmdir /s /q "node_modules" >nul 2>nul
    del "package-lock.json" >nul 2>nul
    call npm install
    if errorlevel 1 goto NPMFAIL
    echo [OK] Dependencias reinstaladas.
) else (
    echo [OK] Todas las dependencias estan correctas.
)

:CHECK_PS1
echo.
if exist "raw-print.ps1" goto SHORTCUT
echo [AVISO] No se encontro raw-print.ps1
echo.
echo raw-print.ps1 es necesario para imprimir en impresoras
echo locales en Windows. Si vas a usar solo impresoras de
echo red (IP:puerto), puedes ignorar este aviso.
echo.
echo Si necesitas impresion local, copia raw-print.ps1
echo en esta carpeta antes de continuar.
echo.
pause

:SHORTCUT
echo.
echo [INFO] Creando acceso directo en el escritorio (minimizado)...
powershell -NoProfile -ExecutionPolicy Bypass -File "create-shortcut.ps1"
if errorlevel 1 goto SHORTCUTFAIL
echo [OK] Acceso directo creado.

REM --- Inicio automatico ---
echo.
echo Deseas que el servidor inicie automaticamente al encender la PC?
echo.
echo 1 = Si, agregar al inicio automatico (minimizado)
echo 2 = No, iniciar manualmente
echo.
set /p AUTO="Selecciona 1 o 2: "
if "%AUTO%"=="1" goto AUTOSTART_YES
if "%AUTO%"=="2" goto AUTOSTART_NO
echo Opcion no valida. Se omite inicio automatico.
goto FINISH

:AUTOSTART_YES
echo [INFO] Configurando inicio automatico minimizado...
powershell -NoProfile -Command "$s = [Environment]::GetFolderPath('Startup'); $t = Join-Path $s 'FacturaFacil Print Server.lnk'; if (-not (Test-Path $t)) { $WshShell = New-Object -ComObject WScript.Shell; $shortcut = $WshShell.CreateShortcut($t); $shortcut.TargetPath = '%CD%\start-minimized.vbs'; $shortcut.WorkingDirectory = '%CD%'; $shortcut.IconLocation = 'shell32.dll,14'; $shortcut.WindowStyle = 7; $shortcut.Save(); Write-Host '[OK] Inicio automatico configurado (minimizado).' } else { Write-Host '[OK] Ya estaba configurado.' }"
goto FINISH

:AUTOSTART_NO
echo [OK] Inicio manual seleccionado.
goto FINISH

:NPMFAIL
echo [ERROR] No se pudieron instalar las dependencias.
echo Asegurate de tener conexion a internet.
pause
exit

:SHORTCUTFAIL
echo [AVISO] No se pudo crear el acceso directo automaticamente.
echo Puedes crearlo manualmente:
echo   1. Clic derecho en start-minimized.vbs
echo   2. Enviar a -^> Escritorio (crear acceso directo)
echo.

:FINISH
echo.
echo ============================================
echo   INSTALACION COMPLETADA
echo ============================================
echo.
echo El servidor esta listo para usar.
echo.
echo Para iniciar:
echo   - Doble clic en "FacturaFacil Print Server"
echo     en el escritorio (se abrira minimizado)
echo   - O ejecuta start.bat directamente
echo   - O ejecuta start-minimized.vbs para minimizado
echo   - O ejecuta start-hidden.vbs para oculto total
echo.
echo URL del servidor: http://localhost:9100
echo.

REM --- Esperar tecla antes de salir ---
pause
goto :EOF

:CANCEL_REPLACE
echo.
echo Instalacion cancelada. No se realizaron cambios.

pause
