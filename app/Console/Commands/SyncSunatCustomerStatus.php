<?php

namespace App\Console\Commands;

use App\Services\SunatCustomerStatusService;
use Illuminate\Console\Command;

class SyncSunatCustomerStatus extends Command
{
    protected $signature = 'customers:sync-sunat-status';

    protected $description = 'Sincroniza el estado SUNAT de los clientes con el padrón';

    public function handle(SunatCustomerStatusService $service): int
    {
        try {
            $this->info('Sincronizando estados SUNAT de clientes...');

            $summary = $service->syncAll();

            $this->info('Clientes revisados: ' . number_format($summary['checked']));
            $this->info('Activos: ' . number_format($summary['active']));
            $this->info('Bloqueados: ' . number_format($summary['blocked']));
            $this->info('No encontrados: ' . number_format($summary['not_found']));
            $this->info('Sincronización completada.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error al sincronizar clientes: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
