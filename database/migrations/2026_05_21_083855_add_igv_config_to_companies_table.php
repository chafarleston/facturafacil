<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('tax_type', 20)->default('general')->after('email');
            $table->decimal('igv_percent', 5, 2)->default(18.00)->after('tax_type');
            $table->decimal('reduced_igv_percent', 5, 2)->default(10.50)->after('igv_percent');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['tax_type', 'igv_percent', 'reduced_igv_percent']);
        });
    }
};
