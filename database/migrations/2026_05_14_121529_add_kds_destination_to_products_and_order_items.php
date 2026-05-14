<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('kds_destination', 20)->default('cocina')->after('stock');
        });

        Schema::table('restaurant_order_items', function (Blueprint $table) {
            $table->string('kds_destination', 20)->default('cocina')->after('kitchen_status');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('kds_destination');
        });

        Schema::table('restaurant_order_items', function (Blueprint $table) {
            $table->dropColumn('kds_destination');
        });
    }
};
