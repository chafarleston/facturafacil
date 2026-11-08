<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('restaurant_orders', 'legacy_order_number')) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->string('legacy_order_number')->nullable()->after('order_number');
            });
        }

        if (!Schema::hasTable('restaurant_order_sequences')) {
            Schema::create('restaurant_order_sequences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('scope', 40);
                $table->unsignedInteger('last_number')->default(0);
                $table->timestamps();

                $table->unique(['company_id', 'scope'], 'restaurant_order_sequences_company_scope_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_order_sequences');

        if (Schema::hasColumn('restaurant_orders', 'legacy_order_number')) {
            Schema::table('restaurant_orders', function (Blueprint $table) {
                $table->dropColumn('legacy_order_number');
            });
        }
    }
};
