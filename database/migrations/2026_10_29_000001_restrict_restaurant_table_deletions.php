<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->foreign('table_id')
                ->references('id')
                ->on('restaurant_tables')
                ->onDelete('restrict');
        });

        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropForeign(['floor_id']);
            $table->foreign('floor_id')
                ->references('id')
                ->on('floors')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->foreign('table_id')
                ->references('id')
                ->on('restaurant_tables')
                ->onDelete('cascade');
        });

        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropForeign(['floor_id']);
            $table->foreign('floor_id')
                ->references('id')
                ->on('floors')
                ->onDelete('cascade');
        });
    }
};
