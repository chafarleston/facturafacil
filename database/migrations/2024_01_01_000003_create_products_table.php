<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('codigo', 50);
            $table->string('descripcion');
            $table->string('codigo_sunat', 8)->nullable();
            $table->string('umedida_codigo', 3)->default('NIU');
            $table->decimal('precio', 12, 2)->default(0);
            $table->decimal('precio_minimo', 12, 2)->nullable();
            $table->enum('tipo_afectacion', ['GRA', 'EXO', 'INA', 'EXE'])->default('GRA');
            $table->decimal('igv_percent', 5, 2)->default(18);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->unique(['company_id', 'codigo']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};