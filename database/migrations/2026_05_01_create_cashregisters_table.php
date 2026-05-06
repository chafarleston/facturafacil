<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cashregisters')) {
            Schema::create('cashregisters', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->unsignedBigInteger('user_id');
                $table->decimal('monto_apertura', 12, 2)->default(0);
                $table->decimal('monto_cierre', 12, 2)->nullable();
                $table->decimal('ventas_efectivo', 12, 2)->default(0);
                $table->decimal('ventas_tarjeta', 12, 2)->default(0);
                $table->decimal('ventas_yape', 12, 2)->default(0);
                $table->decimal('ventas_plin', 12, 2)->default(0);
                $table->decimal('ventas_otro', 12, 2)->default(0);
                $table->integer('cantidad_ventas')->default(0);
                $table->decimal('total_ventas', 12, 2)->default(0);
                $table->string('estado', 20)->default('ABIERTA');
                $table->timestamp('fecha_apertura')->nullable();
                $table->timestamp('fecha_cierre')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cashregisters');
    }
};