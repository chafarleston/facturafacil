<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('tipo_documento', 2); // 20=Retencion, 09=Guia, 40=Percepcion
            $table->string('serie', 4);
            $table->integer('numero');
            $table->string('full_number', 20);
            $table->date('fecha_emision');
            $table->string('moneda', 3)->default('PEN');
            $table->decimal('total', 12, 2)->default(0);
            $table->string('sunat_estado', 20)->default('PENDIENTE');
            $table->string('sunat_code', 10)->nullable();
            $table->string('sunat_description')->nullable();
            $table->text('xml_content')->nullable();
            $table->string('cdr_path')->nullable();
            $table->string('ticket')->nullable();
            $table->timestamps();
        });

        // Related entity for the documents (supplier/provider)
        Schema::create('special_document_entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('special_document_id')->constrained()->onDelete('cascade');
            $table->string('tipo_doc', 2);
            $table->string('num_doc', 15);
            $table->string('razon_social');
            $table->string('direccion')->nullable();
        });

        // Items for despatch (guia de remision)
        Schema::create('special_document_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('special_document_id')->constrained()->onDelete('cascade');
            $table->string('codigo')->nullable();
            $table->string('descripcion');
            $table->decimal('cantidad', 12, 4);
            $table->string('unidad', 10)->default('NIU');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_document_items');
        Schema::dropIfExists('special_document_entities');
        Schema::dropIfExists('special_documents');
    }
};
