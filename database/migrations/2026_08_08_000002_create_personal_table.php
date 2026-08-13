<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('dni', 8);
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('cargo')->nullable();
            $table->decimal('sueldo', 10, 2)->default(0);
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
            $table->unique(['company_id', 'dni']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal');
    }
};
