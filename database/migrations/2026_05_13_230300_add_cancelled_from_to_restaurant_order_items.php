<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_order_items', function (Blueprint $table) {
            $table->string('cancelled_from')->nullable()->after('kitchen_status');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_from');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_order_items', function (Blueprint $table) {
            $table->dropColumn(['cancelled_from', 'cancelled_at']);
        });
    }
};
