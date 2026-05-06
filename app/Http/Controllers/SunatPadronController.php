<?php
namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SunatPadronController extends Controller
{
    public function downloadPadron(Request $request): RedirectResponse
    {
        try {
            $exitCode = Artisan::call('sunat:download-padron');
            $output = trim(Artisan::output());
            $message = 'Padrón descargado';
            if (preg_match('/(\\d+)\\s*(registros|contribuyentes|afiliados|entidades)/i', $output, $matches)) {
                $message .= ' - Registros: ' . $matches[1];
            }
            $padronStatus = ($exitCode === 0) ? 'success' : 'error';
            $payload = [
                'status' => $padronStatus,
                'message' => $message . ($exitCode === 0 ? '' : ' - ' . $output),
                'timestamp' => \Carbon\Carbon::now()->toDateTimeString(),
            ];
            file_put_contents(storage_path('app/padron_last_run.json'), json_encode($payload));

            if ($exitCode === 0) {
                // Cleanup: extract padrón zip and delete it
                $zipPath = $this->locatePadronZip(storage_path('app'));
                if ($zipPath) {
                    $dest = storage_path('app/padron_extracted_' . date('Ymd_His'));
                    if (!is_dir($dest)) {
                        mkdir($dest, 0777, true);
                    }
                    $zip = new \ZipArchive();
                        if ($zip->open($zipPath) === true) {
                            $zip->extractTo($dest);
                            $zip->close();
                            if (file_exists($zipPath)) {
                                unlink($zipPath);
                            }
                        $extractedFiles = count(array_filter(scandir($dest), function($f){ return !in_array($f, ['.','..']); }));
                        $message .= ' | Extraído a ' . $dest . ' (archivos: ' . $extractedFiles . ')';
                    }
                }
                return redirect()->back()->with('status', $message . '. El ZIP fue procesado y eliminado.');
            } else {
                return redirect()->back()->with('error', 'Error descargando padrón. Código: '.$exitCode.' - '.$output);
            }
        } catch (\Exception $e) {
            file_put_contents(storage_path('app/padron_last_run.json'), json_encode(['status' => 'error', 'message' => 'Excepción: '.$e->getMessage(), 'timestamp' => \Carbon\Carbon::now()->toDateTimeString()]));
            return redirect()->back()->with('error', 'Excepción al descargar padrón: '.$e->getMessage());
        }
    }

    /** Locate padron ZIP file in a given base directory */
    private function locatePadronZip(string $baseDir): ?string
    {
        if (!is_dir($baseDir)) {
            return null;
        }
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($baseDir));
        foreach ($rii as $file) {
            if ($file->isFile()) {
                $name = $file->getFilename();
                if (stripos($name, 'padron') !== false && strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'zip') {
                    return $file->getRealPath();
                }
            }
        }
        return null;
    }
}
