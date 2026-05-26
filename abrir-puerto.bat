@echo off
REM Ejecutar como ADMINISTRADOR (clic derecho > Ejecutar como administrador)
netsh advfirewall firewall add rule name="Print Server 9100" dir=in action=allow protocol=TCP localport=9100
echo Puerto 9100 abierto en firewall
echo.
echo Tu IP local:
ipconfig | findstr IPv4
echo.
echo Tu IP publica:
curl -s ifconfig.me
echo.
pause
