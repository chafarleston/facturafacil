<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('tipo_documento', 2);
            $table->string('serie', 4);
            $table->integer('numero');
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->string('moneda', 3)->default('PEN');
            $table->decimal('gravado', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('exonerado', 12, 2)->default(0);
            $table->decimal('inafecto', 12, 2)->default(0);
            $table->decimal('exento', 12, 2)->default(0);
            $table->decimal('igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('total_letras')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('codigo_hash')->nullable();
            $table->string('xml_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('cdr_path')->nullable();
            $table->string('sunat_response')->nullable();
            $table->enum('sunat_estado', ['PENDIENTE', 'ENVIADO', 'ACEPTADO', 'RECHAZADO', 'ANULADO'])->default('PENDIENTE');
            $table->string('sunat_ticket')->nullable();
            $table->string('sunat_cdr')->nullable();
            $table->string('sunat_serie')->nullable();
            $table->integer('sunat_numero')->nullable();
            $table->timestamp('sunat_fecha')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'tipo_documento', 'serie', 'numero']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};