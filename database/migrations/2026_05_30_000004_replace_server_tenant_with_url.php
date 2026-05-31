<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('pro51_url')->nullable()->after('pro51_activated_at');
            $table->dropColumn(['pro51_server', 'pro51_tenant']);
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('pro51_server')->nullable()->after('pro51_token');
            $table->string('pro51_tenant')->nullable()->after('pro51_server');
            $table->dropColumn('pro51_url');
        });
    }
};
