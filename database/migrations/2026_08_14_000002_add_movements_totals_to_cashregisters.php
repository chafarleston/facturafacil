<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashregisters', function (Blueprint $table) {
            $table->decimal('total_ingresos', 12, 2)->default(0)->after('total_ventas');
            $table->decimal('total_egresos', 12, 2)->default(0)->after('total_ingresos');
        });
    }

    public function down(): void
    {
        Schema::table('cashregisters', function (Blueprint $table) {
            $table->dropColumn(['total_ingresos', 'total_egresos']);
        });
    }
};
