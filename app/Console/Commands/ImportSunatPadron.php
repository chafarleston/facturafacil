<?php

namespace App\Console\Commands;

use App\Services\SunatCustomerStatusService;
use App\Services\SunatPadronImporter;
use Illuminate\Console\Command;

class ImportSunatPadron extends Command
{
    protected $signature = 'sunat:import-padron
        {--file= : Ruta del archivo TXT del padrón}
        {--remove-txt : Eliminar el TXT después de una importación exitosa}';

    protected $description = 'Importa el padrón reducido de SUNAT a SQLite';

    public function handle(SunatPadronImporter $importer, SunatCustomerStatusService $statusService): int
    {
        $source = $this->option('file') ?: $this->findSource();

        if (!$source) {
            $this->error('No se encontró el archivo del padrón. Use --file=ruta.');
            return self::FAILURE;
        }

        $this->info('Importando padrón: ' . basename($source));

        try {
            $result = $importer->import(
                $source,
                null,
                (bool) $this->option('remove-txt')
            );

            $this->info('Padrón importado correctamente.');
            $this->line('Registros: ' . number_format($result['records']));
            $this->line('Base: ' . $result['path']);
            $this->line('Tamaño: ' . $result['size']);

            if ($result['backup']) {
                $this->line('Backup anterior: ' . $result['backup']);
            }

            $this->syncCustomers($statusService);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error al importar el padrón: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function syncCustomers(SunatCustomerStatusService $statusService): void
    {
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
    }

    private function findSource(): ?string
    {
        foreach (['padron_reducido_ruc.txt', 'padron*.txt', '*ruc*.txt'] as $pattern) {
            $files = glob(storage_path('app/' . $pattern));
            if (!empty($files)) {
                return $files[0];
            }
        }

        return null;
    }
}
