<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('pro51_token')->nullable()->after('pro51_tenant');
            $table->dropColumn(['pro51_email', 'pro51_password']);
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('pro51_email')->nullable()->after('pro51_tenant');
            $table->string('pro51_password')->nullable()->after('pro51_email');
            $table->dropColumn('pro51_token');
        });
    }
};
