<?php

namespace App\Services;

use App\Models\RestaurantOrder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderNumberService
{
    public function next(int $companyId, string $scope, string $prefix, int $pad = 4): string
    {
        return DB::transaction(function () use ($companyId, $scope, $prefix, $pad) {
            $sequence = DB::table('restaurant_order_sequences')
                ->where('company_id', $companyId)
                ->where('scope', $scope)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $maxExisting = $this->maxExistingNumber($companyId, $prefix);

                DB::table('restaurant_order_sequences')->insertOrIgnore([
                    'company_id' => $companyId,
                    'scope' => $scope,
                    'last_number' => $maxExisting,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $sequence = DB::table('restaurant_order_sequences')
                    ->where('company_id', $companyId)
                    ->where('scope', $scope)
                    ->lockForUpdate()
                    ->first();
            }

            if (!$sequence) {
                throw new RuntimeException('No se pudo inicializar la secuencia de pedidos.');
            }

            $next = (int) $sequence->last_number + 1;

            DB::table('restaurant_order_sequences')
                ->where('company_id', $companyId)
                ->where('scope', $scope)
                ->update([
                    'last_number' => $next,
                    'updated_at' => now(),
                ]);

            return $prefix . str_pad((string) $next, $pad, '0', STR_PAD_LEFT);
        });
    }

    public function nextWaiter(int $companyId): string
    {
        $date = now()->format('Ymd');

        return $this->next($companyId, 'P-' . $date, 'P-' . $date . '-', 4);
    }

    public function nextKiosk(int $companyId): string
    {
        return $this->next($companyId, 'A', 'A-', 3);
    }

    public function maxExistingNumber(int $companyId, string $prefix): int
    {
        $numbers = RestaurantOrder::where('company_id', $companyId)
            ->where('order_number', 'like', $prefix . '%')
            ->pluck('order_number');

        $max = 0;
        $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)$/';

        foreach ($numbers as $number) {
            if (preg_match($pattern, (string) $number, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $max;
    }
}
