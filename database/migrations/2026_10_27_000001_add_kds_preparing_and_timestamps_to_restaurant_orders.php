<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('restaurant_orders', 'sent_to_kitchen_at')) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->timestamp('sent_to_kitchen_at')->nullable()->after('order_type');
            });
        }

        if (!Schema::hasColumn('restaurant_orders', 'preparing_started_at')) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->timestamp('preparing_started_at')->nullable()->after('sent_to_kitchen_at');
            });
        }

        if (!Schema::hasColumn('restaurant_orders', 'finished_at')) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->timestamp('finished_at')->nullable()->after('preparing_started_at');
            });
        }

        // Agrega PREPARING al ENUM conservando los estados actuales
        DB::statement("ALTER TABLE restaurant_orders MODIFY COLUMN status ENUM('OPEN','SENT_TO_KITCHEN','PREPARING','READY','DELIVERED','COMPLETED','CANCELLED','PENDING_PAYMENT') NOT NULL DEFAULT 'OPEN'");

        // Backfill de sent_to_kitchen_at usando los ítems enviados
        DB::statement("UPDATE restaurant_orders o SET o.sent_to_kitchen_at = COALESCE((SELECT MIN(i.sent_to_kitchen_at) FROM restaurant_order_items i WHERE i.restaurant_order_id = o.id AND i.sent_to_kitchen_at IS NOT NULL), o.created_at) WHERE o.sent_to_kitchen_at IS NULL AND o.status IN ('SENT_TO_KITCHEN','READY','DELIVERED','COMPLETED')");

        // Pedidos READY existentes pasan a PREPARING (compatibilidad)
        DB::table('restaurant_orders')->where('status', 'READY')->update([
            'status' => 'PREPARING',
            'preparing_started_at' => DB::raw('COALESCE(preparing_started_at, updated_at)'),
        ]);

        // finished_at para pedidos ya entregados/completados
        DB::table('restaurant_orders')->whereIn('status', ['DELIVERED', 'COMPLETED'])->whereNull('finished_at')->update([
            'finished_at' => DB::raw('updated_at'),
        ]);
    }

    public function down(): void
    {
        DB::table('restaurant_orders')->where('status', 'PREPARING')->update(['status' => 'SENT_TO_KITCHEN']);

        DB::statement("ALTER TABLE restaurant_orders MODIFY COLUMN status ENUM('OPEN','SENT_TO_KITCHEN','READY','DELIVERED','COMPLETED','CANCELLED','PENDING_PAYMENT') NOT NULL DEFAULT 'OPEN'");

        foreach (['finished_at', 'preparing_started_at', 'sent_to_kitchen_at'] as $column) {
            if (Schema::hasColumn('restaurant_orders', $column)) {
                Schema::table('restaurant_orders', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
