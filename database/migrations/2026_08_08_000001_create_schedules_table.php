<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->enum('tipo', ['corrido', 'dividido'])->default('corrido');
            $table->time('entrada_1')->nullable();
            $table->time('salida_1')->nullable();
            $table->time('entrada_2')->nullable();
            $table->time('salida_2')->nullable();
            $table->integer('tolerancia_1')->default(0);
            $table->integer('tolerancia_2')->default(0);
            $table->json('dias_laborables')->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
