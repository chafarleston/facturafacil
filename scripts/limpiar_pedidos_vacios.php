<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
echo " LIMPIAR PEDIDOS VACIOS - FACTURAFACIL\n";
echo " Base de datos: {$database}\n";
echo ' Modo: ' . ($apply ? 'APLICAR CAMBIOS' : 'SIMULACION (sin cambios)') . "\n";
echo "============================================\n\n";

// Solo se eliminan pedidos CANCELLED sin items y los COMPLETED sin ningun item pagado.
$completedIds = [3, 4, 6, 7, 8, 9, 10];

$emptyCancelledQuery = RestaurantOrder::where('status', 'CANCELLED')
    ->whereDoesntHave('items');

$completedNoPaidQuery = RestaurantOrder::whereIn('id', $completedIds)
    ->where('status', 'COMPLETED')
    ->whereDoesntHave('items', function ($query) {
        $query->whereNotNull('paid_invoice_id');
    });

$emptyCancelledIds = (clone $emptyCancelledQuery)->pluck('id');
$completedNoPaidOrders = (clone $completedNoPaidQuery)->get();
$completedNoPaidIds = $completedNoPaidOrders->pluck('id');

$activeOrders = RestaurantOrder::whereNotIn('status', ['COMPLETED', 'CANCELLED'])->count();
$paidItemsBefore = RestaurantOrderItem::whereNotNull('paid_invoice_id')->count();
$invoicesBefore = Invoice::count();
$orphanItemsBefore = RestaurantOrderItem::whereDoesntHave('order')->count();
$orphanBatchesBefore = \App\Models\RestaurantKitchenBatch::whereDoesntHave('order')->count();

echo "Pedidos CANCELLED vacios: {$emptyCancelledIds->count()}\n";
echo "Pedidos COMPLETED sin pago (IDs fijos): {$completedNoPaidIds->count()}\n";
echo "  IDs: " . ($completedNoPaidIds->isEmpty() ? '(ninguno)' : $completedNoPaidIds->implode(', ')) . "\n";
echo "Pedidos activos que NO se tocaran: {$activeOrders}\n";
echo "Items pagados que NO se tocaran: {$paidItemsBefore}\n";
echo "Facturas que NO se tocaran: {$invoicesBefore}\n";
echo "Items huerfanos antes: {$orphanItemsBefore}\n";
echo "Lotes huerfanos antes: {$orphanBatchesBefore}\n\n";

if ($emptyCancelledIds->isEmpty() && $completedNoPaidIds->isEmpty()) {
    echo "No hay pedidos para eliminar.\n";
    exit(0);
}

if (!$apply) {
    echo "Simulacion completada. No se modifico nada.\n";
    echo "Ejecutar con --apply para eliminar.\n";
    exit(0);
}

$backup = crearBackup($connection);

if (!$backup) {
    echo "ERROR: no se pudo crear el backup. No se aplicaron cambios.\n";
    exit(1);
}

echo "Backup: {$backup}\n\n";

$down = false;
$upFailed = false;

try {
    $downCode = Artisan::call('down', ['--retry' => 60]);
    if ($downCode !== 0) {
        throw new \RuntimeException('No se pudo activar el modo mantenimiento.');
    }
    $down = true;

    $deleted = DB::transaction(function () use ($emptyCancelledIds, $completedIds) {
        $emptyIds = RestaurantOrder::whereIn('id', $emptyCancelledIds)
            ->where('status', 'CANCELLED')
            ->whereDoesntHave('items')
            ->lockForUpdate()
            ->pluck('id');

        $completedIdsNow = RestaurantOrder::whereIn('id', $completedIds)
            ->where('status', 'COMPLETED')
            ->whereDoesntHave('items', function ($query) {
                $query->whereNotNull('paid_invoice_id');
            })
            ->lockForUpdate()
            ->pluck('id');

        $ids = $emptyIds->merge($completedIdsNow)->unique()->values();

        if ($ids->isNotEmpty()) {
            RestaurantOrder::whereIn('id', $ids)->delete();
        }

        return $ids;
    });

    echo "Pedidos eliminados: {$deleted->count()}\n";
    echo "  CANCELLED vacios incluidos: " . $deleted->intersect($emptyCancelledIds)->count() . "\n";
    echo "  COMPLETED sin pago incluidos: " . $deleted->intersect($completedIds)->count() . "\n\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    Log::error('Error limpiando pedidos vacios', [
        'message' => $e->getMessage(),
        'database' => $database,
    ]);
} finally {
    if ($down) {
        $upCode = Artisan::call('up');
        if ($upCode !== 0) {
            $upFailed = true;
            echo "ADVERTENCIA: no se pudo desactivar el modo mantenimiento.\n";
        }
    }
}

$emptyCancelledAfter = RestaurantOrder::where('status', 'CANCELLED')
    ->whereDoesntHave('items')
    ->count();
$completedRemaining = RestaurantOrder::whereIn('id', $completedIds)
    ->where('status', 'COMPLETED')
    ->pluck('id');
$paidItemsAfter = RestaurantOrderItem::whereNotNull('paid_invoice_id')->count();
$invoicesAfter = Invoice::count();
$orphanItemsAfter = RestaurantOrderItem::whereDoesntHave('order')->count();
$orphanBatchesAfter = \App\Models\RestaurantKitchenBatch::whereDoesntHave('order')->count();
$activeAfter = RestaurantOrder::whereNotIn('status', ['COMPLETED', 'CANCELLED'])->count();

echo "Verificacion:\n";
echo "- CANCELLED vacios restantes: {$emptyCancelledAfter}\n";
echo "- IDs 3,4,6,7,8,9,10 restantes: " . ($completedRemaining->isEmpty() ? '0' : $completedRemaining->implode(', ')) . "\n";
echo "- Items pagados restantes: {$paidItemsAfter}\n";
echo "- Facturas restantes: {$invoicesAfter}\n";
echo "- Items huerfanos restantes: {$orphanItemsAfter}\n";
echo "- Lotes huerfanos restantes: {$orphanBatchesAfter}\n";
echo "- Pedidos activos restantes: {$activeAfter}\n";

$ok = $emptyCancelledAfter === 0
    && $completedRemaining->isEmpty()
    && $paidItemsAfter === $paidItemsBefore
    && $invoicesAfter === $invoicesBefore
    && $orphanItemsAfter === 0
    && $orphanBatchesAfter === 0
    && $activeAfter === $activeOrders
    && !$upFailed;

echo "\n" . ($ok ? 'RESULTADO: OK' : 'RESULTADO: REVISAR VERIFICACION') . "\n";
exit($ok ? 0 : 1);

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

    $file = $directory . DIRECTORY_SEPARATOR . 'pre-delete-orders-' . date('Ymd-His') . '.sql';
    $command = escapeshellarg($mysqldump)
        . ' --host=' . escapeshellarg((string) ($config['host'] ?? '127.0.0.1'))
        . ' --port=' . escapeshellarg((string) ($config['port'] ?? '3306'))
        . ' --user=' . escapeshellarg((string) ($config['username'] ?? 'root'))
        . ' --password=' . escapeshellarg((string) ($config['password'] ?? ''))
        . ' --single-transaction --quick --routines --triggers --default-character-set=utf8mb4 '
        . escapeshellarg($database)
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
