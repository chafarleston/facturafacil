<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\SunatAlertService;
use Illuminate\Console\Command;

class VerifyUnresolved extends Command
{
    protected $signature = 'sunat:verify-unresolved';
    protected $description = 'Verifica si quedan boletas PENDIENTE de días anteriores y crea la alerta de facturación';

    public function handle()
    {
        $pending = Invoice::where('tipo_documento', '03')
            ->where('sunat_estado', 'PENDIENTE')
            ->whereDate('fecha_emision', '<', now()->toDateString())
            ->count();

        if ($pending > 0) {
            $descripcion = "Hay {$pending} boleta(s) sin enviar a SUNAT desde días anteriores (verificado "
                . now()->format('d/m/Y H:i') . "). Contacte al área de sistemas."
                . " Si ya las envió, este aviso se ocultará automáticamente al ser aceptadas.";

            SunatAlertService::raise(SunatAlertService::TIPO_BOLETAS, $descripcion);
            $this->warn('Alerta de facturación: ' . $pending . ' boleta(s) de días previos sin enviar a SUNAT.');

            return 1;
        }

        SunatAlertService::evaluate();
        $this->info('Sin boletas pendientes de días anteriores.');

        return 0;
    }
}