<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('restaurant_order_items', function (Blueprint $table) {
            $table->json('auxiliary_items')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_order_items', function (Blueprint $table) {
            $table->dropColumn('auxiliary_items');
        });
    }
};
