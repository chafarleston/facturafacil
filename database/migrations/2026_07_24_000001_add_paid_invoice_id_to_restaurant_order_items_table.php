<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_order_items', function (Blueprint $table) {
            $table->foreignId('paid_invoice_id')->nullable()->after('cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_order_items', function (Blueprint $table) {
            $table->dropColumn('paid_invoice_id');
        });
    }
};
