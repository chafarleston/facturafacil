<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Descargar padrón SUNAT cada semana
        $schedule->command('sunat:download-padron')->weekly()->sunday()->at('02:00');

        // Reintentar impresiones fallidas cada minuto
        $schedule->command('print:process-queue')->everyMinute();

        // Sincronizar productos con pro51 cada día
        $schedule->command('pro51:sync-products')->dailyAt('03:00');

        // Reintentar comprobantes pendientes de pro51 cada 5 minutos
        $schedule->command('pro51:retry-pending')->everyFiveMinutes();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}