<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('restaurant_orders', 'delivery_reference')) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->string('delivery_reference', 250)->nullable()->after('delivery_address');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('restaurant_orders', 'delivery_reference')) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->dropColumn('delivery_reference');
            });
        }
    }
};
