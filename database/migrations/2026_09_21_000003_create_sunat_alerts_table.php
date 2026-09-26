<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sunat_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 50)->default('sunat_boletas');
            $table->text('descripcion')->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sunat_alerts');
    }
};