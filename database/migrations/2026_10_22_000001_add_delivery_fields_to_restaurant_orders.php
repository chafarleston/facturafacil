<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->string('delivery_type', 10)->default('local')->after('order_type');
            $table->string('customer_name', 150)->nullable()->after('delivery_type');
            $table->string('delivery_address', 250)->nullable()->after('customer_name');
            $table->string('contact_number', 50)->nullable()->after('delivery_address');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_type', 'customer_name', 'delivery_address', 'contact_number']);
        });
    }
};
