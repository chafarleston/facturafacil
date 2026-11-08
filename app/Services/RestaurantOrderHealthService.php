<?php

namespace App\Services;

use App\Models\RestaurantKitchenBatch;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RestaurantOrderHealthService
{
    public function scan(): array
    {
        $emptyCancelled = RestaurantOrder::with(['table'])
            ->withCount('kitchenBatches')
            ->where('status', 'CANCELLED')
            ->whereDoesntHave('items')
            ->orderByDesc('id')
            ->get();

        $completedNoPaid = RestaurantOrder::with(['table'])
            ->withCount([
                'items as unpaid_items_count' => fn ($query) => $query
                    ->whereNull('paid_invoice_id')
                    ->where('kitchen_status', '!=', 'CANCELLED'),
            ])
            ->where('status', 'COMPLETED')
            ->whereHas('items', fn ($query) => $query
                ->whereNull('paid_invoice_id')
                ->where('kitchen_status', '!=', 'CANCELLED'))
            ->whereDoesntHave('items', fn ($query) => $query->whereNotNull('paid_invoice_id'))
            ->orderByDesc('id')
            ->get();

        $completedPartial = RestaurantOrder::with(['table'])
            ->withCount([
                'items as unpaid_items_count' => fn ($query) => $query
                    ->whereNull('paid_invoice_id')
                    ->where('kitchen_status', '!=', 'CANCELLED'),
                'items as paid_items_count' => fn ($query) => $query->whereNotNull('paid_invoice_id'),
            ])
            ->where('status', 'COMPLETED')
            ->whereHas('items', fn ($query) => $query
                ->whereNull('paid_invoice_id')
                ->where('kitchen_status', '!=', 'CANCELLED'))
            ->whereHas('items', fn ($query) => $query->whereNotNull('paid_invoice_id'))
            ->orderByDesc('id')
            ->get();

        $orphanItems = RestaurantOrderItem::whereDoesntHave('order')
            ->whereNull('paid_invoice_id')
            ->orderByDesc('id')
            ->get();

        $orphanPaidItems = RestaurantOrderItem::whereDoesntHave('order')
            ->whereNotNull('paid_invoice_id')
            ->orderByDesc('id')
            ->get();

        $orphanBatches = RestaurantKitchenBatch::whereDoesntHave('order')
            ->orderByDesc('id')
            ->get();

        $activeCount = RestaurantOrder::whereNotIn('status', ['COMPLETED', 'CANCELLED'])->count();

        $duplicateGroups = DB::table('restaurant_orders')
            ->select('company_id', 'order_number', DB::raw('COUNT(*) as total'))
            ->whereNotNull('order_number')
            ->groupBy('company_id', 'order_number')
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('total')
            ->limit(100)
            ->get();

        $duplicateCount = DB::table('restaurant_orders')
            ->select('company_id', 'order_number')
            ->whereNotNull('order_number')
            ->groupBy('company_id', 'order_number')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $safeCount = $emptyCancelled->count() + $orphanItems->count() + $orphanBatches->count();

        return [
            'empty_cancelled' => $emptyCancelled,
            'completed_no_paid' => $completedNoPaid,
            'completed_partial' => $completedPartial,
            'orphan_items' => $orphanItems,
            'orphan_paid_items' => $orphanPaidItems,
            'orphan_batches' => $orphanBatches,
            'active_count' => $activeCount,
            'duplicate_groups' => $duplicateGroups,
            'duplicate_count' => $duplicateCount,
            'safe_count' => $safeCount,
            'completed_no_paid_count' => $completedNoPaid->count(),
            'partial_count' => $completedPartial->count(),
        ];
    }

    public function backup(): string
    {
        $config = config('database.connections.mysql');
        $directory = storage_path('app/backup');

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('No se pudo crear el directorio de backup.');
        }

        $mysqldump = $this->findMysqldump();
        $file = $directory . DIRECTORY_SEPARATOR . 'order-health-' . now()->format('Ymd-His') . '.sql';
        $password = $config['password'] ? '-p' . escapeshellarg($config['password']) : '';

        $command = sprintf(
            '"%s" -h %s -P %s -u %s %s --single-transaction --quick --routines --triggers --default-character-set=utf8mb4 %s > %s 2>&1',
            $mysqldump,
            escapeshellarg($config['host']),
            escapeshellarg($config['port']),
            escapeshellarg($config['username']),
            $password,
            escapeshellarg($config['database']),
            escapeshellarg($file)
        );

        $output = [];
        $code = 0;
        exec($command, $output, $code);

        if ($code !== 0 || !is_file($file) || filesize($file) === 0) {
            @unlink($file);
            throw new RuntimeException('No se pudo crear el backup: ' . implode(' ', array_slice($output, -5)));
        }

        return $file;
    }

    public function cleanupSafe(): array
    {
        return DB::transaction(function () {
            $emptyIds = RestaurantOrder::where('status', 'CANCELLED')
                ->whereDoesntHave('items')
                ->lockForUpdate()
                ->pluck('id');

            $orphanItemIds = RestaurantOrderItem::whereDoesntHave('order')
                ->whereNull('paid_invoice_id')
                ->lockForUpdate()
                ->pluck('id');

            $orphanBatchIds = RestaurantKitchenBatch::whereDoesntHave('order')
                ->lockForUpdate()
                ->pluck('id');

            if ($emptyIds->isNotEmpty()) {
                RestaurantOrder::whereIn('id', $emptyIds)->delete();
            }

            if ($orphanItemIds->isNotEmpty()) {
                RestaurantOrderItem::whereIn('id', $orphanItemIds)->delete();
            }

            if ($orphanBatchIds->isNotEmpty()) {
                RestaurantKitchenBatch::whereIn('id', $orphanBatchIds)->delete();
            }

            return [
                'orders' => $emptyIds->count(),
                'items' => $orphanItemIds->count(),
                'batches' => $orphanBatchIds->count(),
            ];
        });
    }

    public function cleanupCompleted(array $ids): array
    {
        $deleted = 0;
        $skipped = [];

        DB::transaction(function () use ($ids, &$deleted, &$skipped) {
            $orders = RestaurantOrder::whereIn('id', $ids)
                ->lockForUpdate()
                ->get();

            foreach ($orders as $order) {
                $hasPaidItems = $order->items()->whereNotNull('paid_invoice_id')->exists();
                $hasActiveItems = $order->items()
                    ->where('kitchen_status', '!=', 'CANCELLED')
                    ->exists();

                if ($order->status !== 'COMPLETED' || $hasPaidItems || !$hasActiveItems) {
                    $skipped[] = $order->id;
                    continue;
                }

                $order->delete();
                $deleted++;
            }
        });

        return [
            'deleted' => $deleted,
            'skipped' => $skipped,
        ];
    }

    private function findMysqldump(): string
    {
        foreach ([
            'C:\\laragon\\bin\\mysql\\mariadb-10.6.27-winx64\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
        ] as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return 'mysqldump';
    }
}
