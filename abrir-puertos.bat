@echo off
title Abrir Puertos FacturaFacil
echo ============================================
echo  Abriendo puertos para FacturaFacil
echo ============================================
echo.

echo Abriendo puerto 80 (HTTP)...
netsh advfirewall firewall add rule name="FacturaFacil HTTP (80)" dir=in action=allow protocol=TCP localport=80 >nul 2>&1
if %errorlevel% equ 0 (
    echo   [OK] Puerto 80 abierto
) else (
    echo   [INFO] Puerto 80 ya estaba abierto o hubo un error
)

echo Abriendo puerto 3306 (MySQL)...
netsh advfirewall firewall add rule name="FacturaFacil MySQL (3306)" dir=in action=allow protocol=TCP localport=3306 >nul 2>&1
if %errorlevel% equ 0 (
    echo   [OK] Puerto 3306 abierto
) else (
    echo   [INFO] Puerto 3306 ya estaba abierto o hubo un error
)

echo.
echo ============================================
echo  Puertos configurados correctamente
echo ============================================
pause
