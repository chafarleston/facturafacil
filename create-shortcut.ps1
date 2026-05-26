$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$desktop = [Environment]::GetFolderPath('Desktop')
$shortcutPath = Join-Path $desktop 'FacturaFacil Print Server.lnk'

if (Test-Path $shortcutPath) {
    Write-Host '[OK] El acceso directo ya existe en el escritorio.'
    exit 0
}

$WshShell = New-Object -ComObject WScript.Shell
$shortcut = $WshShell.CreateShortcut($shortcutPath)
$shortcut.TargetPath = Join-Path $scriptDir 'start-hidden.vbs'
$shortcut.WorkingDirectory = $scriptDir
$shortcut.IconLocation = 'shell32.dll,14'
$shortcut.WindowStyle = 0
$shortcut.Description = 'FacturaFacil Print Server (oculto, siempre activo)'
$shortcut.Save()

Write-Host '[OK] Acceso directo creado en el escritorio.'
