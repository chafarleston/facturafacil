<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_discount_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->integer('tardanza_min');
            $table->enum('tipo', ['FIJO', 'PORCENTAJE'])->default('FIJO');
            $table->decimal('valor', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['company_id', 'tardanza_min']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_discount_rules');
    }
};
