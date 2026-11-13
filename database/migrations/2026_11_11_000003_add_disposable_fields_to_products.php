<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'disposable_enabled')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('disposable_enabled')->default(false)->after('kds_destination');
            });
        }

        if (!Schema::hasColumn('products', 'disposable_product_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('disposable_product_id')->nullable()->after('disposable_enabled');
                $table->foreign('disposable_product_id')
                    ->references('id')
                    ->on('products')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'disposable_product_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['disposable_product_id']);
                $table->dropColumn('disposable_product_id');
            });
        }

        if (Schema::hasColumn('products', 'disposable_enabled')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('disposable_enabled');
            });
        }
    }
};
