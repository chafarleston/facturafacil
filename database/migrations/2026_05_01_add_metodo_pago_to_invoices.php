<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'metodo_pago')) {
                $table->string('metodo_pago', 50)->nullable()->after('total');
            }
            if (!Schema::hasColumn('invoices', 'referencia_pago')) {
                $table->string('referencia_pago', 100)->nullable()->after('metodo_pago');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['metodo_pago', 'referencia_pago']);
        });
    }
};