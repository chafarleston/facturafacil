<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('summary_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->date('fecha_emision');
            $table->date('fecha_operacion');
            $table->string('correlativo', 10);
            $table->integer('cantidad_documentos')->default(0);
            $table->string('ticket')->nullable();
            $table->enum('sunat_estado', ['PENDIENTE', 'ENVIADO', 'ACEPTADO', 'RECHAZADO'])->default('PENDIENTE');
            $table->text('sunat_response')->nullable();
            $table->timestamp('sunat_fecha')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('summary_documents');
    }
};