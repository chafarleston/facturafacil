<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tipo', 20);
            $table->string('numero', 30);
            $table->string('cliente_nombre');
            $table->string('cliente_documento', 20)->nullable();
            $table->string('cliente_email')->nullable();
            $table->string('cliente_telefono', 30)->nullable();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->decimal('monto', 12, 2)->nullable();
            $table->string('estado', 30)->default('BORRADOR');
            $table->date('fecha');
            $table->date('fecha_vencimiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'tipo', 'numero']);
            $table->index(['company_id', 'tipo', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventas');
    }
};
