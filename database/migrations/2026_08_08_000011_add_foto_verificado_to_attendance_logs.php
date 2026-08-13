<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('tipo_evento');
            $table->boolean('verificado')->default(true)->nullable()->after('foto');
            $table->decimal('distancia', 8, 4)->nullable()->after('verificado');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn(['foto', 'verificado', 'distancia']);
        });
    }
};
