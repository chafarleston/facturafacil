<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cash_report_settings')) {
            Schema::create('cash_report_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->onDelete('cascade');
                $table->boolean('mostrar_lista_comprobantes')->default(true);
                $table->boolean('mostrar_productos_vendidos')->default(true);
                $table->boolean('mostrar_lineas_eliminadas')->default(true);
                $table->timestamps();
                $table->unique('company_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_report_settings');
    }
};