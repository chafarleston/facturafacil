<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\RestaurantKitchenBatch;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

$apply = in_array('--apply', $argv, true);
$database = null;

foreach ($argv as $argument) {
    if (strpos($argument, '--db=') === 0) {
        $database = trim(substr($argument, 5));
    }
}

if ($database) {
    config(['database.connections.mysql.database' => $database]);
    DB::purge('mysql');
}

$connection = config('database.default');
$config = config("database.connections.{$connection}");
$database = $config['database'] ?? '';

echo "============================================\n";
echo " LIMPIAR HUERFANOS - FACTURAFACIL\n";
echo " Base de datos: {$database}\n";
echo ' Modo: ' . ($apply ? 'APLICAR CAMBIOS' : 'SIMULACION (sin cambios)') . "\n";
echo "============================================\n\n";

$backup = null;

if ($apply) {
    $backup = crearBackup($connection);

    if (!$backup) {
        echo "ERROR: no se pudo crear el backup. No se aplicaron cambios.\n";
        exit(1);
    }

    echo "Backup: {$backup}\n\n";
}

$orders = RestaurantOrder::with(['items', 'kitchenBatches', 'table'])
    ->where(function ($query) {
        $query->where(function ($cancelled) {
            $cancelled->where('status', 'CANCELLED')
                ->where(function ($orphans) {
                    $orphans->whereHas('items', function ($items) {
                        $items->where('kitchen_status', '!=', 'CANCELLED');
                    })->orWhereHas('kitchenBatches', function ($batches) {
                        $batches->whereIn('status', ['PENDING', 'PREPARING']);
                    });
                });
        })->orWhere(function ($completed) {
            $completed->where('status', 'COMPLETED')
                ->where(function ($orphans) {
                    $orphans->whereHas('items', function ($items) {
                        $items->where('kitchen_status', '!=', 'CANCELLED')
                            ->whereNull('paid_invoice_id');
                    })->orWhereHas('kitchenBatches', function ($batches) {
                        $batches->whereIn('status', ['PENDING', 'PREPARING']);
                    });
                });
        });
    })
    ->orderBy('id')
    ->get();

$orphanItems = RestaurantOrderItem::whereDoesntHave('order')->get();
$orphanBatches = RestaurantKitchenBatch::whereDoesntHave('order')->get();

$ordersToClean = 0;
$itemsToCancel = 0;
$batchesToCancel = 0;
$completedBatchOnly = 0;
$completedReviewOrders = 0;
$completedReviewItems = 0;

echo "Pedidos CANCELLED/COMPLETED con huerfanos: {$orders->count()}\n";

foreach ($orders as $order) {
    $activeItems = $order->items
        ->where('kitchen_status', '!=', 'CANCELLED');

    $activeBatches = $order->kitchenBatches
        ->whereIn('status', ['PENDING', 'PREPARING']);

    if ($order->status === 'CANCELLED') {
        if ($activeItems->isEmpty() && $activeBatches->isEmpty()) {
            continue;
        }

        $ordersToClean++;
        $itemsToCancel += $activeItems->count();
        $batchesToCancel += $activeBatches->count();

        echo "- Pedido {$order->id} ({$order->order_number}) CANCELLED"
            . " | items a cancelar: {$activeItems->count()}"
            . " | lotes a cancelar: {$activeBatches->count()}\n";

        foreach ($activeItems as $item) {
            echo "    item {$item->id}: {$item->product_name}"
                . " | estado {$item->kitchen_status}"
                . ' | pagado=' . ($item->paid_invoice_id ?: 'NO') . "\n";
        }

        if ($apply) {
            cancelarOrden($order, $activeItems, $activeBatches);
        }

        continue;
    }

    $unpaidItems = $activeItems->whereNull('paid_invoice_id');

    if ($unpaidItems->isEmpty() && $activeBatches->isEmpty()) {
        continue;
    }

    $ordersToClean++;
    $batchesToCancel += $activeBatches->count();

    if ($unpaidItems->isEmpty()) {
        $completedBatchOnly++;
    } else {
        $completedReviewOrders++;
        $completedReviewItems += $unpaidItems->count();

        echo "- Pedido {$order->id} ({$order->order_number}) COMPLETED"
            . " | REVISAR: {$unpaidItems->count()} items impagos (no se tocan)"
            . " | lotes a cancelar: {$activeBatches->count()}\n";

        foreach ($unpaidItems as $item) {
            echo "    item {$item->id}: {$item->product_name}"
                . " | estado {$item->kitchen_status}"
                . " | pagado=NO\n";
        }
    }

    if ($apply) {
        cancelarOrden($order, collect(), $activeBatches);
    }
}

if ($orphanItems->isNotEmpty()) {
    echo "\nItems sin pedido: {$orphanItems->count()}\n";
    foreach ($orphanItems as $item) {
        echo "    item {$item->id}: {$item->product_name}\n";
    }
}

if ($orphanBatches->isNotEmpty()) {
    echo "Lotes sin pedido: {$orphanBatches->count()}\n";
    foreach ($orphanBatches as $batch) {
        echo "    lote {$batch->id}: {$batch->kds_destination} / {$batch->status}\n";
    }
}

if ($apply && $orphanItems->isNotEmpty()) {
    RestaurantOrderItem::whereDoesntHave('order')->delete();
}

if ($apply && $orphanBatches->isNotEmpty()) {
    RestaurantKitchenBatch::whereDoesntHave('order')->delete();
}

if ($apply) {
    $companyIds = $orders->pluck('company_id')->filter()->unique();

    foreach ($companyIds as $companyId) {
        Cache::put('restaurant_updated_' . $companyId, now()->timestamp, 10);
        Cache::put('kitchen_updated_' . $companyId, now()->timestamp, 10);
    }
}

echo "\nResumen:\n";
echo "- Pedidos revisados: {$ordersToClean}\n";
echo "- Pedidos COMPLETED solo con lotes pendientes: {$completedBatchOnly}\n";
echo "- Pedidos COMPLETED con items impagos (sin tocar): {$completedReviewOrders} ({$completedReviewItems} items)\n";
echo '- Items de pedidos CANCELLED ' . ($apply ? 'marcados' : 'a marcar') . " CANCELLED: {$itemsToCancel}\n";
echo '- Lotes ' . ($apply ? 'marcados' : 'a marcar') . " CANCELLED: {$batchesToCancel}\n";
echo '- Items sin pedido ' . ($apply ? 'eliminados' : 'a eliminar') . ": {$orphanItems->count()}\n";
echo '- Lotes sin pedido ' . ($apply ? 'eliminados' : 'a eliminar') . ": {$orphanBatches->count()}\n";

if (!$apply) {
    echo "\nEsto fue una simulacion. Ejecuta con --apply para aplicar cambios.\n";
}

exit(0);

function cancelarOrden(RestaurantOrder $order, $items, $activeBatches): void
{
    DB::transaction(function () use ($order, $items, $activeBatches) {
        foreach ($items as $item) {
            $item->cancelled_from = $item->kitchen_status;
            $item->cancelled_at = now();
            $item->cancelled_by = null;
            $item->kitchen_status = 'CANCELLED';
            $item->save();
        }

        foreach ($activeBatches as $batch) {
            $batch->update([
                'status' => RestaurantKitchenBatch::STATUS_CANCELLED,
                'finished_at' => now(),
            ]);
        }

        if ($order->table_id) {
            $hasActiveOrder = RestaurantOrder::where('table_id', $order->table_id)
                ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
                ->exists();

            if (!$hasActiveOrder && $order->table) {
                $order->table->update(['status' => 'AVAILABLE']);
            }
        }
    });
}

function crearBackup(string $connection): ?string
{
    $config = config("database.connections.{$connection}");
    $database = $config['database'] ?? '';

    if (!$database) {
        return null;
    }

    $directory = base_path('backups');

    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        return null;
    }

    $mysqldump = buscarMysqldump();

    if (!$mysqldump) {
        echo "ERROR: no se encontro mysqldump.exe. Instala MySQL/MariaDB o agrega mysqldump al PATH.\n";
        return null;
    }

    $file = $directory . DIRECTORY_SEPARATOR . 'pre-clean-orphans-' . date('Ymd-His') . '.sql';
    $command = escapeshellarg($mysqldump)
        . ' --host=' . escapeshellarg((string) ($config['host'] ?? '127.0.0.1'))
        . ' --port=' . escapeshellarg((string) ($config['port'] ?? '3306'))
        . ' --user=' . escapeshellarg((string) ($config['username'] ?? 'root'))
        . ' --password=' . escapeshellarg((string) ($config['password'] ?? ''))
        . ' ' . escapeshellarg($database)
        . ' > ' . escapeshellarg($file) . ' 2>&1';

    exec($command, $output, $code);

    if ($code !== 0 || !is_file($file) || filesize($file) === 0) {
        echo "ERROR backup: " . implode("\n", $output) . "\n";
        @unlink($file);
        return null;
    }

    return $file;
}

function buscarMysqldump(): ?string
{
    $candidates = glob('C:\\laragon\\bin\\mysql\\*\\bin\\mysqldump.exe');

    if (is_array($candidates) && !empty($candidates)) {
        rsort($candidates);
        return $candidates[0];
    }

    $candidates = glob('C:\\laragon\\bin\\mariadb\\*\\bin\\mysqldump.exe');

    if (is_array($candidates) && !empty($candidates)) {
        rsort($candidates);
        return $candidates[0];
    }

    $where = @shell_exec('where mysqldump 2>NUL');

    if ($where) {
        $lines = preg_split('/\r?\n/', trim($where));
        if (!empty($lines[0]) && is_file($lines[0])) {
            return $lines[0];
        }
    }

    return null;
}
