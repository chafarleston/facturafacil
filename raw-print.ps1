param(
    [string]$printerName,
    [string]$filePath
)

Add-Type -TypeDefinition @"
using System;
using System.IO;
using System.Runtime.InteropServices;
public class RawPrinter {
    [DllImport("winspool.drv", CharSet=CharSet.Unicode)]
    public static extern bool OpenPrinter(string pPrinterName, out IntPtr phPrinter, IntPtr pDefault);
    [DllImport("winspool.drv")]
    public static extern bool ClosePrinter(IntPtr hPrinter);
    [DllImport("winspool.drv", CharSet=CharSet.Unicode)]
    public static extern bool StartDocPrinter(IntPtr hPrinter, int level, IntPtr pDocInfo);
    [DllImport("winspool.drv")]
    public static extern bool EndDocPrinter(IntPtr hPrinter);
    [DllImport("winspool.drv")]
    public static extern bool StartPagePrinter(IntPtr hPrinter);
    [DllImport("winspool.drv")]
    public static extern bool EndPagePrinter(IntPtr hPrinter);
    [DllImport("winspool.drv")]
    public static extern bool WritePrinter(IntPtr hPrinter, IntPtr pBytes, int dwCount, out int dwWritten);
    [DllImport("winspool.drv")]
    public static extern int GetLastError();

    public static void Print(string printerName, byte[] data) {
        IntPtr hPrinter = IntPtr.Zero;
        bool opened = OpenPrinter(printerName, out hPrinter, IntPtr.Zero);
        if (!opened) {
            int err = GetLastError();
            throw new Exception("Cannot open printer '" + printerName + "'. Error code: " + err);
        }
        try {
            DOCINFO di = new DOCINFO();
            di.pDocName = "FacturaFacil";
            di.pDataType = "RAW";
            IntPtr pDocInfo = Marshal.AllocHGlobal(Marshal.SizeOf(typeof(DOCINFO)));
            try {
                Marshal.StructureToPtr(di, pDocInfo, false);
                bool docStarted = StartDocPrinter(hPrinter, 1, pDocInfo);
                if (!docStarted) throw new Exception("StartDocPrinter failed. Error: " + GetLastError());
                bool pageStarted = StartPagePrinter(hPrinter);
                if (!pageStarted) throw new Exception("StartPagePrinter failed. Error: " + GetLastError());
                IntPtr pData = Marshal.AllocHGlobal(data.Length);
                try {
                    Marshal.Copy(data, 0, pData, data.Length);
                    int written = 0;
                    bool wrote = WritePrinter(hPrinter, pData, data.Length, out written);
                    if (!wrote) throw new Exception("WritePrinter failed. Written: " + written + " Error: " + GetLastError());
                } finally { Marshal.FreeHGlobal(pData); }
                EndPagePrinter(hPrinter);
                EndDocPrinter(hPrinter);
            } finally { Marshal.FreeHGlobal(pDocInfo); }
        } finally { ClosePrinter(hPrinter); }
    }
    [StructLayout(LayoutKind.Sequential, CharSet=CharSet.Unicode)]
    public struct DOCINFO {
        [MarshalAs(UnmanagedType.LPWStr)] public string pDocName;
        [MarshalAs(UnmanagedType.LPWStr)] public string pOutputFile;
        [MarshalAs(UnmanagedType.LPWStr)] public string pDataType;
    }
}
"@ -ErrorAction Stop

try {
    $data = [System.IO.File]::ReadAllBytes($filePath)
    [RawPrinter]::Print($printerName, $data)
    Write-Output "OK"
    exit 0
} catch {
    Write-Output "ERROR: $_"
    exit 1
}
