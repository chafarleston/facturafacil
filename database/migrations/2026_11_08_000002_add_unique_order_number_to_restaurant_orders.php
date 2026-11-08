<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('restaurant_orders')
            ->select('company_id', 'order_number')
            ->whereNotNull('order_number')
            ->groupBy('company_id', 'order_number')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicates) {
            Log::warning('No se agrego el indice unico de order_number: existen duplicados. Ejecutar orders:renumber-duplicates.');
            return;
        }

        $indexExists = DB::selectOne(
            'SHOW INDEX FROM restaurant_orders WHERE Key_name = ?',
            ['restaurant_orders_company_number_unique']
        );

        if (!$indexExists) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->unique(['company_id', 'order_number'], 'restaurant_orders_company_number_unique');
            });
        }
    }

    public function down(): void
    {
        $indexExists = DB::selectOne(
            'SHOW INDEX FROM restaurant_orders WHERE Key_name = ?',
            ['restaurant_orders_company_number_unique']
        );

        if ($indexExists) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->dropUnique('restaurant_orders_company_number_unique');
            });
        }
    }
};
