Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "cmd /c cd /d C:\laragon\www\facturafacil && php artisan schedule:run", 0, False
