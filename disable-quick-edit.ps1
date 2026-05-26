Add-Type -TypeDefinition @"
using System;
using System.Runtime.InteropServices;
public class ConsoleUtil {
    [DllImport("kernel32.dll")]
    public static extern bool SetConsoleMode(IntPtr hConsoleHandle, uint dwMode);
}
"@

$handle = (Get-Process -Id $pid).Handle
[ConsoleUtil]::SetConsoleMode($handle, 0x0080)
