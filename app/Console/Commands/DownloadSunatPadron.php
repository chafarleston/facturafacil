<?php

namespace App\Console\Commands;

use App\Services\SunatCustomerStatusService;
use App\Services\SunatPadronImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class DownloadSunatPadron extends Command
{
    protected $signature = 'sunat:download-padron';
    protected $description = 'Descarga e importa el padrón reducido de SUNAT';

    public function handle(SunatPadronImporter $importer, SunatCustomerStatusService $statusService): int
    {
        $this->info('Descargando padrón reducido de SUNAT...');

        $zipUrl = 'http://www2.sunat.gob.pe/padron_reducido_ruc.zip';
        $zipPath = storage_path('app/sunat_padron.zip');
        $extractPath = storage_path('app/');

        try {
            $this->info('Descargando archivo ZIP...');
            $response = Http::timeout(600)->get($zipUrl);

            if (!$response->successful() || strlen($response->body()) <= 1000) {
                $this->error('No se pudo descargar el padrón.');
                return self::FAILURE;
            }

            File::put($zipPath, $response->body());
            $this->info('Archivo ZIP descargado: ' . number_format(filesize($zipPath) / 1024 / 1024, 2) . ' MB');

            $this->info('Extrayendo archivo...');
            $zip = new ZipArchive();

            if ($zip->open($zipPath) !== true) {
                $this->error('No se pudo abrir el ZIP del padrón.');
                return self::FAILURE;
            }

            $zip->extractTo($extractPath);
            $zip->close();

            if (File::exists($zipPath)) {
                File::delete($zipPath);
                $this->info('Archivo ZIP eliminado');
            }

            $files = glob(storage_path('app/*.txt'));
            foreach ($files as $file) {
                if (stripos($file, 'padron') === false && stripos($file, 'ruc') === false) {
                    continue;
                }

                $this->info('Padrón encontrado: ' . basename($file));
                $this->info('Importando a SQLite...');

                $result = $importer->import($file);

                $this->info('Padrón importado: ' . number_format($result['records']) . ' registros');
                $this->info('Base: ' . $result['path']);
                $this->info('Tamaño: ' . $result['size']);

                $this->info('Sincronizando estados SUNAT de clientes...');
                try {
                    $summary = $statusService->syncAll();

                    $this->line('Clientes revisados: ' . number_format($summary['checked']));
                    $this->line('Activos: ' . number_format($summary['active']));
                    $this->line('Bloqueados: ' . number_format($summary['blocked']));
                    $this->line('No encontrados: ' . number_format($summary['not_found']));
                } catch (\Throwable $e) {
                    $this->warn('El padrón se importó, pero no se pudieron sincronizar los clientes: ' . $e->getMessage());
                }

                $this->info('Proceso completado correctamente');

                return self::SUCCESS;
            }

            $this->error('No se encontró el archivo TXT del padrón después de extraer el ZIP.');
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
