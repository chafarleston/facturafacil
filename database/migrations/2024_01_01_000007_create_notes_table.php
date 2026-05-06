<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->string('tipo_documento', 2);
            $table->string('serie', 4);
            $table->integer('numero');
            $table->date('fecha_emision');
            $table->string('tipo_nota', 2);
            $table->string('motivo');
            $table->decimal('total', 12, 2)->default(0);
            $table->string('moneda', 3)->default('PEN');
            $table->string('codigo_hash')->nullable();
            $table->string('xml_path')->nullable();
            $table->enum('sunat_estado', ['PENDIENTE', 'ENVIADO', 'ACEPTADO', 'RECHAZADO'])->default('PENDIENTE');
            $table->string('sunat_response')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'tipo_documento', 'serie', 'numero']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notes');
    }
};