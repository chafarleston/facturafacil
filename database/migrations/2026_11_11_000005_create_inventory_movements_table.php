<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory_movements')) {
            return;
        }

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30);
            $table->decimal('quantity', 12, 4);
            $table->decimal('stock_before', 12, 4);
            $table->decimal('stock_after', 12, 4);
            $table->nullableMorphs('reference');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'product_id', 'created_at'], 'inventory_movements_product_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
