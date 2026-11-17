<?php

namespace App\Console\Commands;

use App\Models\RestaurantKitchenBatch;
use App\Models\RestaurantOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CleanStaleKdsBatches extends Command
{
    protected $signature = 'kds:clean-stale-batches
                            {--apply : Marca los lotes históricos como cancelados}';

    protected $description = 'Cancela lotes KDS activos que pertenecen a pedidos completados o anulados';

    public function handle(): int
    {
        $query = RestaurantKitchenBatch::whereIn('status', [
            RestaurantKitchenBatch::STATUS_PENDING,
            RestaurantKitchenBatch::STATUS_PREPARING,
        ])->whereHas('order', function ($order) {
            $order->whereIn('status', [
                RestaurantOrder::STATUS_COMPLETED,
                RestaurantOrder::STATUS_CANCELLED,
            ]);
        });

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('No hay lotes KDS históricos activos.');

            return self::SUCCESS;
        }

        $byStatus = (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $this->info('Lotes KDS históricos activos: ' . number_format($total));
        $this->line('Pendientes: ' . number_format((int) ($byStatus[RestaurantKitchenBatch::STATUS_PENDING] ?? 0)));
        $this->line('Preparando: ' . number_format((int) ($byStatus[RestaurantKitchenBatch::STATUS_PREPARING] ?? 0)));

        if (!$this->option('apply')) {
            $this->warn('Simulación: no se modificó ningún registro. Use --apply para cancelar estos lotes.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($query) {
            $batches = $query->lockForUpdate()->get();
            $companyIds = $batches->pluck('company_id')->filter()->unique();
            $ids = $batches->pluck('id')->all();

            RestaurantKitchenBatch::whereIn('id', $ids)->update([
                'status' => RestaurantKitchenBatch::STATUS_CANCELLED,
                'finished_at' => now(),
            ]);

            foreach ($companyIds as $companyId) {
                Cache::forget('kitchen_updated_' . $companyId);
                Cache::forget('restaurant_updated_' . $companyId);
            }
        });

        $this->info('Lotes históricos cancelados: ' . number_format($total));

        return self::SUCCESS;
    }
}
