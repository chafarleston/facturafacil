<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashregisters', function (Blueprint $table) {
            $table->string('referencia', 100)->nullable()->after('observaciones');
        });
    }

    public function down(): void
    {
        Schema::table('cashregisters', function (Blueprint $table) {
            $table->dropColumn('referencia');
        });
    }
};
