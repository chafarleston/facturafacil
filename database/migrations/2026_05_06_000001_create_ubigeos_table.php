<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubigeos', function (Blueprint $table) {
            $table->string('codigo', 6)->primary();
            $table->string('departamento', 50);
            $table->string('provincia', 50);
            $table->string('distrito', 50);
            $table->index(['departamento', 'provincia', 'distrito']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubigeos');
    }
};