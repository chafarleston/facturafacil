<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('personal_id')->constrained('personal')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('entrada_1')->nullable();
            $table->time('salida_1')->nullable();
            $table->time('entrada_2')->nullable();
            $table->time('salida_2')->nullable();
            $table->integer('tardanza_min')->default(0);
            $table->enum('estado', ['PUNTUAL', 'TARDANZA', 'FALTA', 'DESCANSO'])->default('PUNTUAL');
            $table->decimal('descuento', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['personal_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
