<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('order_source', 10)->nullable()->after('credit_note_id');
        });
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->string('order_type', 10)->default('mozo')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('order_source');
        });
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->dropColumn('order_type');
        });
    }
};
