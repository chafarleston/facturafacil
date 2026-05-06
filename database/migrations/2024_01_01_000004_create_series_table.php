<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('tipo_documento', 2);
            $table->string('serie', 4);
            $table->integer('numero_actual')->default(0);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->unique(['company_id', 'tipo_documento', 'serie']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('series');
    }
};