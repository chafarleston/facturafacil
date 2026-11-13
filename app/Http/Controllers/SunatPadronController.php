<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\SunatCustomerStatusService;
use App\Services\SunatPadronService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SunatPadronController extends Controller
{
    public function index()
    {
        $padronInfo = $this->getPadronInfo();
        $syncInfo = $this->getSyncInfo();

        return view('sunat-padron.index', compact('padronInfo', 'syncInfo'));
    }

    public function syncCustomers(SunatCustomerStatusService $service): RedirectResponse
    {
        try {
            $summary = $service->syncAll();

            return redirect()->route('sunat-padron.index')->with(
                'success',
                'Estados SUNAT sincronizados. Revisados: ' . number_format($summary['checked'])
                . ' · Activos: ' . number_format($summary['active'])
                . ' · Bloqueados: ' . number_format($summary['blocked'])
                . ' · No encontrados: ' . number_format($summary['not_found'])
            );
        } catch (\Throwable $e) {
            return redirect()->route('sunat-padron.index')
                ->with('error', 'No se pudieron sincronizar los clientes: ' . $e->getMessage());
        }
    }

    public function download(): RedirectResponse
    {
        try {
            $exitCode = Artisan::call('sunat:download-padron');
            $output = trim(Artisan::output());

            if ($exitCode === 0) {
                return redirect()->route('sunat-padron.index')
                    ->with('success', 'Padrón descargado e importado correctamente. ' . $output);
            }

            return redirect()->route('sunat-padron.index')
                ->with('error', 'Error al descargar el padrón: ' . $output);
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
            $message = 'Padrón descargado e importado';

            if (preg_match('/([\d.,]+)\s+registros/i', $output, $matches)) {
                $message .= ' - Registros: ' . $matches[1];
            }

            if (preg_match('/Bloqueados:\s*([\d.,]+)/i', $output, $matches)) {
                $message .= ' - Clientes bloqueados: ' . $matches[1];
            }

            $payload = [
                'status' => $exitCode === 0 ? 'success' : 'error',
                'message' => $message . ($exitCode === 0 ? '' : ' - ' . $output),
                'timestamp' => \Carbon\Carbon::now()->toDateTimeString(),
            ];
            file_put_contents(storage_path('app/padron_last_run.json'), json_encode($payload));

            if ($exitCode === 0) {
                return redirect()->back()->with('status', $message);
            }

            return redirect()->back()->with('error', 'Error descargando padrón. Código: ' . $exitCode . ' - ' . $output);
        } catch (\Exception $e) {
            file_put_contents(storage_path('app/padron_last_run.json'), json_encode([
                'status' => 'error',
                'message' => 'Excepción: ' . $e->getMessage(),
                'timestamp' => \Carbon\Carbon::now()->toDateTimeString(),
            ]));

            return redirect()->back()->with('error', 'Excepción al descargar padrón: ' . $e->getMessage());
        }
    }

    private function getSyncInfo(): array
    {
        $lastSync = Customer::whereNotNull('sunat_checked_at')->max('sunat_checked_at');

        return [
            'blocked' => Customer::where('documento_tipo', '6')
                ->where('sunat_bloqueado', true)
                ->count(),
            'last_sync' => $lastSync
                ? \Carbon\Carbon::parse($lastSync)->format('d/m/Y H:i:s')
                : null,
        ];
    }

    private function getPadronInfo(): array
    {
        $stats = app(SunatPadronService::class)->stats();

        if (!$stats['exists']) {
            return [
                'exists' => false,
                'driver_missing' => !SunatPadronService::driverAvailable(),
                'message' => SunatPadronService::driverAvailable()
                    ? 'El padrón no está importado en SQLite.'
                    : 'El servidor web no tiene habilitada la extensión pdo_sqlite.',
            ];
        }

        return [
            'exists' => true,
            'file' => basename(SunatPadronService::RELATIVE_PATH),
            'size' => $stats['size'],
            'records' => number_format($stats['records']),
            'last_modified' => $stats['last_modified'],
        ];
    }
}
