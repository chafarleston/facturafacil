<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->boolean('reconocimiento_activo')->default(false)->after('suspension_graves_count');
            $table->decimal('reconocimiento_umbral', 8, 4)->default(0.6)->after('reconocimiento_activo');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn(['reconocimiento_activo', 'reconocimiento_umbral']);
        });
    }
};
