<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SunatPadronController extends Controller
{
    public function index()
    {
        $padronInfo = $this->getPadronInfo();
        return view('sunat-padron.index', compact('padronInfo'));
    }

    public function download(): RedirectResponse
    {
        try {
            $exitCode = Artisan::call('sunat:download-padron');
            $output = trim(Artisan::output());

            if ($exitCode === 0) {
                return redirect()->route('sunat-padron.index')
                    ->with('success', 'Padrón descargado correctamente. ' . $output);
            } else {
                return redirect()->route('sunat-padron.index')
                    ->with('error', 'Error al descargar el padrón: ' . $output);
            }
        } catch (\Exception $e) {
            return redirect()->route('sunat-padron.index')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

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
                return redirect()->back()->with('status', $message);
            } else {
                return redirect()->back()->with('error', 'Error descargando padrón. Código: '.$exitCode.' - '.$output);
            }
        } catch (\Exception $e) {
            file_put_contents(storage_path('app/padron_last_run.json'), json_encode(['status' => 'error', 'message' => 'Excepción: '.$e->getMessage(), 'timestamp' => \Carbon\Carbon::now()->toDateTimeString()]));
            return redirect()->back()->with('error', 'Excepción al descargar padrón: '.$e->getMessage());
        }
    }

    private function getPadronInfo(): array
    {
        $padronPath = $this->findPadronFile();

        if (!$padronPath) {
            return [
                'exists' => false,
                'message' => 'El padrón no está descargado.',
            ];
        }

        $fp = fopen($padronPath, 'r');
        $lines = 0;
        while (!feof($fp)) {
            fgets($fp);
            $lines++;
        }
        fclose($fp);

        return [
            'exists' => true,
            'file' => basename($padronPath),
            'size' => number_format(filesize($padronPath) / 1024 / 1024, 2) . ' MB',
            'records' => number_format($lines),
            'last_modified' => date('d/m/Y H:i:s', filemtime($padronPath)),
        ];
    }

    private function findPadronFile(): ?string
    {
        $patterns = ['padron_reducido_ruc.txt', 'padron*.txt', '*ruc*.txt'];
        foreach ($patterns as $pattern) {
            $files = glob(storage_path('app/' . $pattern));
            if ($files) {
                return $files[0];
            }
        }
        return null;
    }
}
