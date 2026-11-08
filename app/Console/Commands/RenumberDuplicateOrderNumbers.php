<?php

namespace App\Console\Commands;

use App\Models\RestaurantOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RenumberDuplicateOrderNumbers extends Command
{
    protected $signature = 'orders:renumber-duplicates
        {--apply : Aplicar los cambios}
        {--dry-run : Forzar simulacion sin cambios}
        {--company= : Limitar a una empresa}
        {--db= : Base de datos a usar}';

    protected $description = 'Renumera pedidos con order_number duplicado conservando uno por grupo';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $companyId = $this->option('company');

        if ($this->option('db')) {
            config(['database.connections.mysql.database' => $this->option('db')]);
            DB::purge('mysql');
        }

        if (!\Schema::hasColumn('restaurant_orders', 'legacy_order_number')) {
            $this->error('Falta la columna legacy_order_number. Ejecute las migraciones primero.');
            return self::FAILURE;
        }

        $groups = DB::table('restaurant_orders')
            ->select('company_id', 'order_number', DB::raw('COUNT(*) as total'))
            ->whereNotNull('order_number')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->groupBy('company_id', 'order_number')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('company_id')
            ->orderBy('order_number')
            ->get();

        if ($groups->isEmpty()) {
            $this->info('No hay numeros de pedido duplicados.');
            return self::SUCCESS;
        }

        $plans = [];
        $rowsInGroups = 0;
        $skipped = [];
        $maxByScope = [];

        foreach ($groups as $group) {
            $orders = RestaurantOrder::where('company_id', $group->company_id)
                ->where('order_number', $group->order_number)
                ->orderBy('id')
                ->get(['id', 'order_number', 'status']);

            $rowsInGroups += $orders->count();

            $canonical = $orders->first(fn ($order) => !in_array($order->status, ['COMPLETED', 'CANCELLED'], true))
                ?? $orders->first();

            foreach ($orders as $order) {
                if ($canonical && $order->id === $canonical->id) {
                    continue;
                }

                $parsed = $this->parseNumber((string) $order->order_number);

                if (!$parsed) {
                    $skipped[] = $order->order_number;
                    continue;
                }

                $scopeKey = $group->company_id . '|' . $parsed['scope'];

                if (!array_key_exists($scopeKey, $maxByScope)) {
                    $maxByScope[$scopeKey] = $this->maxExisting(
                        (int) $group->company_id,
                        $parsed['prefix']
                    );
                }

                $maxByScope[$scopeKey]++;
                $newNumber = $parsed['prefix']
                    . str_pad((string) $maxByScope[$scopeKey], $parsed['pad'], '0', STR_PAD_LEFT);

                $plans[] = [
                    'id' => $order->id,
                    'company_id' => $group->company_id,
                    'scope' => $parsed['scope'],
                    'old' => $order->order_number,
                    'new' => $newNumber,
                ];
            }
        }

        $this->line('Grupos duplicados: ' . $groups->count());
        $this->line('Pedidos en grupos: ' . $rowsInGroups);
        $this->line('Pedidos a renumerar: ' . count($plans));

        if ($skipped) {
            $this->warn('Numeros con formato no soportado: ' . count($skipped));
            $this->line(implode(', ', array_slice(array_unique($skipped), 0, 20)));
        }

        foreach (array_slice($plans, 0, 15) as $plan) {
            $this->line("  {$plan['id']}: {$plan['old']} -> {$plan['new']}");
        }

        if (!$apply) {
            $this->info('Simulacion completada. No se modifico nada.');
            return self::SUCCESS;
        }

        if ($skipped) {
            $this->error('Hay numeros duplicados con formato desconocido. Corrija el formato antes de aplicar.');
            return self::FAILURE;
        }

        $backup = $this->backup();

        if (!$backup) {
            $this->error('No se pudo crear el backup. No se aplicaron cambios.');
            return self::FAILURE;
        }

        $this->line('Backup: ' . $backup);

        DB::transaction(function () use ($plans) {
            foreach ($plans as $plan) {
                DB::table('restaurant_orders')
                    ->where('id', $plan['id'])
                    ->update([
                        'order_number' => $plan['new'],
                        'legacy_order_number' => $plan['old'],
                    ]);
            }

            $sequences = [];

            foreach ($plans as $plan) {
                $key = $plan['company_id'] . '|' . $plan['scope'];
                $sequence = $this->parseNumber($plan['new']);
                $sequences[$key] = max($sequences[$key] ?? 0, (int) ltrim(substr($plan['new'], strlen($sequence['prefix'])), '0'));
            }

            foreach ($sequences as $key => $lastNumber) {
                [$companyId, $scope] = explode('|', $key, 2);

                DB::table('restaurant_order_sequences')->insertOrIgnore([
                    'company_id' => (int) $companyId,
                    'scope' => $scope,
                    'last_number' => $lastNumber,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('restaurant_order_sequences')
                    ->where('company_id', (int) $companyId)
                    ->where('scope', $scope)
                    ->update([
                        'last_number' => DB::raw('GREATEST(last_number, ' . (int) $lastNumber . ')'),
                        'updated_at' => now(),
                    ]);
            }
        });

        $duplicatesAfter = DB::table('restaurant_orders')
            ->select('company_id', 'order_number')
            ->whereNotNull('order_number')
            ->groupBy('company_id', 'order_number')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        if ($duplicatesAfter > 0) {
            $this->error('Aun quedan grupos duplicados: ' . $duplicatesAfter);
            return self::FAILURE;
        }

        $this->ensureUniqueIndex();

        $this->writeAuditCsv($plans);

        Log::info('Renumeracion de pedidos duplicados aplicada', [
            'groups' => $groups->count(),
            'renumbered' => count($plans),
            'backup' => $backup,
        ]);

        $this->info('Renumeracion aplicada: ' . count($plans) . ' pedidos.');
        $this->info('Duplicados restantes: 0');

        return self::SUCCESS;
    }

    private function parseNumber(string $number): ?array
    {
        if (preg_match('/^P-(\d{8})-(\d+)$/', $number, $matches)) {
            return [
                'scope' => 'P-' . $matches[1],
                'prefix' => 'P-' . $matches[1] . '-',
                'pad' => 4,
            ];
        }

        if (preg_match('/^A-(\d+)$/', $number, $matches)) {
            return [
                'scope' => 'A',
                'prefix' => 'A-',
                'pad' => 3,
            ];
        }

        return null;
    }

    private function maxExisting(int $companyId, string $prefix): int
    {
        $max = 0;
        $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)$/';

        foreach (RestaurantOrder::where('company_id', $companyId)->where('order_number', 'like', $prefix . '%')->pluck('order_number') as $number) {
            if (preg_match($pattern, (string) $number, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $max;
    }

    private function ensureUniqueIndex(): void
    {
        $exists = DB::selectOne(
            'SHOW INDEX FROM restaurant_orders WHERE Key_name = ?',
            ['restaurant_orders_company_number_unique']
        );

        if (!$exists) {
            DB::statement('ALTER TABLE restaurant_orders ADD UNIQUE KEY restaurant_orders_company_number_unique (company_id, order_number)');
        }
    }

    private function backup(): ?string
    {
        $config = config('database.connections.mysql');
        $directory = storage_path('app/backup');

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return null;
        }

        $mysqldump = $this->findMysqldump();
        $file = $directory . DIRECTORY_SEPARATOR . 'pre-renumber-' . now()->format('Ymd-His') . '.sql';
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
            return null;
        }

        return $file;
    }

    private function writeAuditCsv(array $plans): void
    {
        $directory = storage_path('app/backup');
        $file = $directory . DIRECTORY_SEPARATOR . 'order-number-map-' . now()->format('Ymd-His') . '.csv';
        $handle = fopen($file, 'w');

        if (!$handle) {
            return;
        }

        fputcsv($handle, ['order_id', 'company_id', 'scope', 'old_number', 'new_number']);

        foreach ($plans as $plan) {
            fputcsv($handle, [
                $plan['id'],
                $plan['company_id'],
                $plan['scope'],
                $plan['old'],
                $plan['new'],
            ]);
        }

        fclose($handle);
    }

    private function findMysqldump(): string
    {
        foreach ([
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mariadb-10.6.27-winx64\\bin\\mysqldump.exe',
        ] as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return 'mysqldump';
    }
}
