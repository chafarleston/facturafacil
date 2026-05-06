<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('ruc', 11)->unique();
            $table->string('razon_social');
            $table->string('nombre_comercial')->nullable();
            $table->string('direccion')->nullable();
            $table->string('departamento', 50)->nullable();
            $table->string('provincia', 50)->nullable();
            $table->string('distrito', 50)->nullable();
            $table->string('ubigeo', 6)->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('certificado_path')->nullable();
            $table->string('certificado_password')->nullable();
            $table->string('certificado_vence')->nullable();
            $table->enum('tipo_contribuyente', ['RIESGO', 'MYPES', 'OTROS'])->default('RIESGO');
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            
            // SUNAT SOAP credentials
            $table->string('soap_type_id')->default('01'); // 01 = Beta, 02 = Producción
            $table->string('soap_username')->nullable();
            $table->string('soap_password')->nullable();
            $table->string('certificate')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('companies');
    }
};