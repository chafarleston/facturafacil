<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('restaurant_order_items', 'parent_item_id')) {
            Schema::table('restaurant_order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_item_id')->nullable()->after('product_name');
                $table->foreign('parent_item_id')
                    ->references('id')
                    ->on('restaurant_order_items')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('restaurant_order_items', 'is_disposable')) {
            Schema::table('restaurant_order_items', function (Blueprint $table) {
                $table->boolean('is_disposable')->default(false)->after('parent_item_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('restaurant_order_items', 'is_disposable')) {
            Schema::table('restaurant_order_items', function (Blueprint $table) {
                $table->dropColumn('is_disposable');
            });
        }

        if (Schema::hasColumn('restaurant_order_items', 'parent_item_id')) {
            Schema::table('restaurant_order_items', function (Blueprint $table) {
                $table->dropForeign(['parent_item_id']);
                $table->dropColumn('parent_item_id');
            });
        }
    }
};
