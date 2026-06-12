<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('special_documents', function (Blueprint $table) {
            $table->string('regimen', 2)->nullable()->after('total');
            $table->decimal('tasa', 5, 2)->nullable()->after('regimen');
            $table->decimal('imp_retenido', 12, 2)->nullable()->after('tasa');
            $table->decimal('imp_pagado', 12, 2)->nullable()->after('imp_retenido');
            $table->string('observacion')->nullable()->after('imp_pagado');
            $table->string('dir_partida')->nullable()->after('observacion');
            $table->string('dir_llegada')->nullable()->after('dir_partida');
        });
    }

    public function down(): void
    {
        Schema::table('special_documents', function (Blueprint $table) {
            $table->dropColumn(['regimen', 'tasa', 'imp_retenido', 'imp_pagado', 'observacion', 'dir_partida', 'dir_llegada']);
        });
    }
};
