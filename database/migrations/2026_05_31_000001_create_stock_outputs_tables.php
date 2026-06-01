<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('stock', 12, 4)->default(0)->change();
        });

        Schema::create('stock_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('motivo', 50);
            $table->string('motivo_otro')->nullable();
            $table->string('referencia', 100)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_output_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_output_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->decimal('cantidad', 12, 4);
            $table->decimal('stock_antes', 12, 4);
            $table->decimal('stock_despues', 12, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_output_items');
        Schema::dropIfExists('stock_outputs');
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->default(0)->change();
        });
    }
};
