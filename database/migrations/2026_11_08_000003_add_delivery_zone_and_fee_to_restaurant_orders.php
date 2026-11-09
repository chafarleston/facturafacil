<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('restaurant_orders', 'delivery_zone')) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->string('delivery_zone', 100)->nullable()->after('delivery_type');
            });
        }

        if (!Schema::hasColumn('restaurant_orders', 'delivery_fee')) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->decimal('delivery_fee', 10, 2)->default(0)->after('delivery_zone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('restaurant_orders', 'delivery_fee')) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->dropColumn('delivery_fee');
            });
        }

        if (Schema::hasColumn('restaurant_orders', 'delivery_zone')) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->dropColumn('delivery_zone');
            });
        }
    }
};
