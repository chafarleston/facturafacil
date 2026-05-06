<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('soap_type_id')->default('01')->after('estado');
            $table->string('soap_username')->nullable()->after('soap_type_id');
            $table->string('soap_password')->nullable()->after('soap_username');
            $table->string('certificate')->nullable()->after('soap_password');
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['soap_type_id', 'soap_username', 'soap_password', 'certificate']);
        });
    }
};