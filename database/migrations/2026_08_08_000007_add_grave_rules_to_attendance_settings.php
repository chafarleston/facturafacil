<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->integer('falta_grave_threshold_min')->default(120)->after('falta_threshold_min');
            $table->integer('suspension_graves_count')->default(3)->after('falta_grave_threshold_min');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn(['falta_grave_threshold_min', 'suspension_graves_count']);
        });
    }
};
